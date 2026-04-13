<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sendError = function(int $code, string $msg) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
};

$config = require __DIR__ . '/config.php';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
        $mysqli->set_charset($config['charset']);
    } catch (Throwable $e) {
        $sendError(500, 'Error de conexión con la base de datos');
    }

    try {
        $result = $mysqli->query("SELECT id, name, deleted_at FROM groups ORDER BY name");
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }
        $result->free();
        echo json_encode(['groups' => $groups]);
        exit;
    } catch (Throwable $e) {
        $sendError(500, $e->getMessage());
    }
}

// Para el resto de métodos mantenemos la protección de rol admin
requireRole('admin', true);

try {
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    $mysqli->set_charset($config['charset']);
} catch (Throwable $e) {
    $sendError(500, 'Error de conexión con la base de datos');
}

// POST for create or soft-delete
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    $sendError(400, 'Payload inválido');
}

$action = strtolower(trim($body['action'] ?? 'create'));
if ($action === 'delete') {
    $id = isset($body['id']) ? (int)$body['id'] : null;
    if (!$id) $sendError(400, 'Falta id');
    try {
        // asegurar columna deleted_at
        $mysqli->query("ALTER TABLE groups ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL AFTER name");

        // comprobar existencia
        $check = $mysqli->prepare("SELECT deleted_at FROM groups WHERE id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows === 0) {
            $sendError(404, "Grupo no encontrado (id $id)");
        }
        $row = $res->fetch_assoc();
        $alreadyDeleted = !empty($row['deleted_at']);

        if (!$alreadyDeleted) {
            $stmt = $mysqli->prepare("UPDATE groups SET deleted_at = NOW() WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
        }

        echo json_encode(['success' => true, 'id' => $id, 'deleted' => true, 'alreadyDeleted' => $alreadyDeleted]);
        exit;
    } catch (Throwable $e) {
        $sendError(500, $e->getMessage());
    }
}

if ($action === 'rename') {
    $id = isset($body['id']) ? (int)$body['id'] : null;
    $newName = trim($body['name'] ?? '');
    if (!$id || $newName === '') $sendError(400, 'Faltan id o nombre');
    try {
        // comprobar duplicado distinto del propio id
        $dup = $mysqli->prepare("SELECT id, deleted_at FROM groups WHERE LOWER(name) = LOWER(?) AND id <> ? LIMIT 1");
        $dup->bind_param('si', $newName, $id);
        $dup->execute();
        $dupRes = $dup->get_result();
        if ($dupRes && $dupRes->num_rows > 0) {
            $row = $dupRes->fetch_assoc();
            if (!empty($row['deleted_at'])) {
                // Si el duplicado está borrado, impedir para no reabrirlo por error
                $sendError(409, 'Ya existe un grupo con ese nombre (borrado previamente)');
            } else {
                $sendError(409, 'Ya existe un grupo con ese nombre');
            }
        }

        $stmt = $mysqli->prepare("UPDATE groups SET name = ?, deleted_at = NULL WHERE id = ?");
        $stmt->bind_param('si', $newName, $id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            // Si no afectó filas, el id no existe
            $sendError(404, "Grupo no encontrado (id $id)");
        }
        echo json_encode(['success' => true, 'id' => $id, 'name' => $newName]);
        exit;
    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() === 1062) {
            $sendError(409, 'Ya existe un grupo con ese nombre');
        }
        $sendError(500, $ex->getMessage());
    }
}

// create
$name = trim($body['name'] ?? '');
if ($name === '') {
    $sendError(400, 'El nombre es obligatorio');
}

try {
    $stmt = $mysqli->prepare("INSERT INTO groups (name) VALUES (?)");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    exit;
} catch (mysqli_sql_exception $ex) {
    if ($ex->getCode() === 1062) {
        // Ver si está borrado lógicamente y reactivarlo
        $check = $mysqli->prepare("SELECT id, deleted_at FROM groups WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $check->bind_param('s', $name);
        $check->execute();
        $res = $check->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (!empty($row['deleted_at'])) {
                $reactivate = $mysqli->prepare("UPDATE groups SET deleted_at = NULL WHERE id = ?");
                $reactivate->bind_param('i', $row['id']);
                $reactivate->execute();
                echo json_encode(['success' => true, 'id' => $row['id'], 'reactivated' => true]);
                exit;
            }
        }
        $sendError(409, 'El grupo ya existe');
    }
    $sendError(500, $ex->getMessage());
}

