<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole('admin', true);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sendError = function(int $code, string $msg) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
};

$config = require __DIR__ . '/config.php';
try {
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    $mysqli->set_charset($config['charset']);
} catch (Throwable $e) {
    $sendError(500, 'Error de conexión con la base de datos');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
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

// POST for create or soft-delete
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    $sendError(400, 'Payload inválido');
}

$action = strtolower(trim($body['action'] ?? 'create'));
$logLine = date('c') . " action={$action} payload=" . json_encode($body) . PHP_EOL;
@file_put_contents(__DIR__ . '/groups.log', $logLine, FILE_APPEND);

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
        @file_put_contents(__DIR__ . '/groups.log', date('c') . " delete_check id={$id} rows={$res->num_rows}" . PHP_EOL, FILE_APPEND);
        if ($res->num_rows === 0) {
            $sendError(404, "Grupo no encontrado (id $id)");
        }
        $row = $res->fetch_assoc();
        $alreadyDeleted = !empty($row['deleted_at']);

        if (!$alreadyDeleted) {
            $stmt = $mysqli->prepare("UPDATE groups SET deleted_at = NOW() WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            @file_put_contents(__DIR__ . '/groups.log', date('c') . " delete_update id={$id} affected={$stmt->affected_rows}" . PHP_EOL, FILE_APPEND);
        }

        echo json_encode(['success' => true, 'id' => $id, 'deleted' => true, 'alreadyDeleted' => $alreadyDeleted]);
        exit;
    } catch (Throwable $e) {
        $sendError(500, $e->getMessage());
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
        // reactivar si estaba borrado
        $reactivate = $mysqli->prepare("UPDATE groups SET deleted_at = NULL WHERE LOWER(name) = LOWER(?)");
        $reactivate->bind_param('s', $name);
        $reactivate->execute();
        echo json_encode(['success' => true, 'reactivated' => true]);
        exit;
    }
    $sendError(500, $ex->getMessage());
}
