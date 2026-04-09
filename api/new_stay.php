<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireLogin(true);

$sessionUser = getSessionUser();
$sessionRole = strtolower(trim($sessionUser['rol'] ?? ''));
$sessionId   = (int)($sessionUser['id'] ?? 0);

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);
$mysqli->query("SET NAMES {$config['charset']}");

// Asegurar tabla stays (activa + histórico)
$mysqli->query("
CREATE TABLE IF NOT EXISTS stays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    motivo VARCHAR(150) NULL,
    group_id INT NULL,
    horario TINYINT(1) NOT NULL DEFAULT 1,
    institucion VARCHAR(255) NULL,
    pais VARCHAR(255) NULL,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    archived_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stays_employee (employee_id),
    INDEX idx_stays_status (status),
    CONSTRAINT fk_stay_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    CONSTRAINT fk_stay_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
)");

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$targetId = isset($payload['user_id']) ? (int)$payload['user_id'] : $sessionId;

// Solo admin puede crear estancias para otros
if ($targetId !== $sessionId && $sessionRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Validar datos requeridos de estancia
$required = ['fecha_inicio', 'fecha_fin', 'motivo', 'institucion', 'pais'];
foreach ($required as $field) {
    if (empty($payload[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Falta campo: $field"]);
        exit;
    }
}
$accepted = isset($payload['accept_terms']) ? (bool)$payload['accept_terms'] : false;
if (!$accepted) {
    http_response_code(400);
    echo json_encode(['error' => 'Debes aceptar la política de privacidad y condiciones.']);
    exit;
}
$groupId = isset($payload['group_id']) ? (int)$payload['group_id'] : null;

// Resolver group_id a partir de grupo (nombre) si no llega explícito
$ensureGroupId = function (mysqli $db, string $name): ?int {
    $name = trim($name);
    if ($name === '') return null;
    $stmt = $db->prepare('SELECT id FROM groups WHERE LOWER(name) = LOWER(?) LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result($gid);
        if ($stmt->fetch()) {
            $stmt->close();
            return (int)$gid;
        }
        $stmt->close();
    }
    $ins = $db->prepare('INSERT INTO groups (name) VALUES (?)');
    if ($ins) {
        $ins->bind_param('s', $name);
        if ($ins->execute()) {
            $gid = $ins->insert_id;
            $ins->close();
            return (int)$gid;
        }
        $ins->close();
    }
    return null;
};
if (!$groupId && !empty($payload['grupo'])) {
    $groupId = $ensureGroupId($mysqli, (string)$payload['grupo']);
}
if (!$groupId && !empty($sessionUser['group_id'])) {
    $groupId = (int)$sessionUser['group_id'];
}
if (!$groupId) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta campo: grupo', 'field' => 'group_id']);
    exit;
}
$grupoName = $payload['grupo'] ?? '';
if ($grupoName === '') {
    $stmtGroup = $mysqli->prepare('SELECT name FROM groups WHERE id = ? LIMIT 1');
    if ($stmtGroup) {
        $stmtGroup->bind_param('i', $groupId);
        $stmtGroup->execute();
        $stmtGroup->bind_result($gname);
        if ($stmtGroup->fetch()) {
            $grupoName = $gname;
        }
        $stmtGroup->close();
    }
}
$payload['grupo'] = $grupoName;
$payload['group_id'] = $groupId;
$horario = isset($payload['horario']) ? (int) !!$payload['horario'] : 1;

$start = new DateTime($payload['fecha_inicio']);
$end   = new DateTime($payload['fecha_fin']);
$today = new DateTime('today');

if ($start < $today) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha de inicio no puede ser anterior a hoy.']);
    exit;
}
if ($end < $start) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha de fin no puede ser anterior a la fecha de inicio.']);
    exit;
}

// Confirmar que el usuario existe
$stmt = $mysqli->prepare("SELECT id FROM employees WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $targetId);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}
$stmt->close();

$newStart = $payload['fecha_inicio'];
$newEnd   = $payload['fecha_fin'];

// Validar solapamientos con cualquier estancia existente (activa o archivada)
$overlap = $mysqli->prepare("SELECT 1 FROM stays WHERE employee_id = ? AND fecha_inicio <= ? AND fecha_fin >= ? LIMIT 1");
$overlap->bind_param('iss', $targetId, $newEnd, $newStart);
$overlap->execute();
$overRes = $overlap->get_result();
if ($overRes && $overRes->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Las fechas se solapan con otra estancia.']);
    exit;
}
$overlap->close();

$mysqli->begin_transaction();
try {
    // Obtener estancia activa actual
    $activeStmt = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'active' LIMIT 1");
    $activeStmt->bind_param('i', $targetId);
    $activeStmt->execute();
    $activeRes = $activeStmt->get_result();
    $activeStay = $activeRes ? $activeRes->fetch_assoc() : null;
    $activeStmt->close();

    // Archivar estancia activa existente
    if ($activeStay) {
        $updActive = $mysqli->prepare("UPDATE stays SET status = 'archived', archived_at = NOW() WHERE id = ? LIMIT 1");
        $updActive->bind_param('i', $activeStay['id']);
        $updActive->execute();
        $updActive->close();
    }

    // Crear nueva estancia activa
    $ins = $mysqli->prepare("INSERT INTO stays (employee_id, fecha_inicio, fecha_fin, motivo, group_id, horario, institucion, pais, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    $newGroupId = (int)$payload['group_id'];
    $newMotivo = $payload['motivo'];
    $newInstitucion = $payload['institucion'];
    $newPais = $payload['pais'];
    $newFechaInicio = $payload['fecha_inicio'];
    $newFechaFin = $payload['fecha_fin'];
    $ins->bind_param(
        'isssiiss',
        $targetId,
        $newFechaInicio,
        $newFechaFin,
        $newMotivo,
        $newGroupId,
        $horario,
        $newInstitucion,
        $newPais
    );
    $ins->execute();
    $ins->close();

    $mysqli->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
