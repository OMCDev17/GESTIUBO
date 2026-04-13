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

$normalizeTitleCase = static function ($value): string {
    $text = trim((string)$value);
    if ($text === '') {
        return '';
    }
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    $lowerText = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    $particles = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'o', 'u', 'da', 'das', 'do', 'dos', 'di', 'van', 'von'];
    $words = preg_split('/\s+/u', $lowerText) ?: [];
    $result = [];
    foreach ($words as $i => $word) {
        if ($i > 0 && in_array($word, $particles, true)) {
            $result[] = $word;
            continue;
        }
        if (function_exists('mb_convert_case')) {
            $result[] = mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        } else {
            $result[] = ucwords($word);
        }
    }
    return implode(' ', $result);
};

foreach (['institucion', 'pais'] as $key) {
    if (isset($payload[$key])) {
        $payload[$key] = $normalizeTitleCase($payload[$key]);
    }
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

// Validar que acepte las políticas de privacidad y confidencialidad
$acceptPrivacy = isset($payload['accept_privacy']) ? (bool)$payload['accept_privacy'] : false;
$acceptConfidentiality = isset($payload['accept_confidentiality']) ? (bool)$payload['accept_confidentiality'] : false;

if (!$acceptPrivacy || !$acceptConfidentiality) {
    http_response_code(400);
    echo json_encode(['error' => 'Debes aceptar la política de privacidad y el compromiso de confidencialidad.']);
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

// Asegurar tabla group_join_requests para solicitudes de nuevas estancias
$mysqli->query("
CREATE TABLE IF NOT EXISTS group_join_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    group_id INT NOT NULL,
    requested_by_email VARCHAR(255) NOT NULL,
    requested_by_name VARCHAR(255) NOT NULL,
    motivo VARCHAR(150) NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    horario TINYINT(1) NOT NULL DEFAULT 1,
    institucion VARCHAR(255) NULL,
    pais VARCHAR(255) NULL,
    approval_token VARCHAR(64) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    email_sent_at DATETIME NULL,
    approved_at DATETIME NULL,
    approved_by_employee_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_group_join_requests_token (approval_token),
    INDEX idx_group_join_requests_employee (employee_id),
    INDEX idx_group_join_requests_group (group_id),
    INDEX idx_group_join_requests_status (status)
)");

$mysqli->begin_transaction();
try {
    // Obtener datos del usuario que solicita
    $userStmt = $mysqli->prepare("SELECT nombre, apellidos, email FROM employees WHERE id = ? LIMIT 1");
    $userStmt->bind_param('i', $targetId);
    $userStmt->execute();
    $userRes = $userStmt->get_result();
    $userData = $userRes ? $userRes->fetch_assoc() : null;
    $userStmt->close();

    if (!$userData) {
        throw new RuntimeException('No se pudo obtener los datos del usuario');
    }

    // Generar token de aprobación único
    $approvalToken = bin2hex(random_bytes(32));

    // Crear solicitud de nueva estancia (pendiente de aprobación del coordinador)
    $ins = $mysqli->prepare("
        INSERT INTO group_join_requests 
        (employee_id, group_id, requested_by_email, requested_by_name, motivo, fecha_inicio, fecha_fin, horario, institucion, pais, approval_token, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $newGroupId = (int)$payload['group_id'];
    $newMotivo = $payload['motivo'];
    $newInstitucion = $payload['institucion'];
    $newPais = $payload['pais'];
    $newFechaInicio = $payload['fecha_inicio'];
    $newFechaFin = $payload['fecha_fin'];
    $requestedByName = $userData['nombre'] . ' ' . $userData['apellidos'];
    $requestedByEmail = $userData['email'];
    
    $ins->bind_param(
        'iisssssisss',
        $targetId,
        $newGroupId,
        $requestedByEmail,
        $requestedByName,
        $newMotivo,
        $newFechaInicio,
        $newFechaFin,
        $horario,
        $newInstitucion,
        $newPais,
        $approvalToken
    );
    $ins->execute();
    $requestId = $ins->insert_id;
    $ins->close();

    $mysqli->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Solicitud de estancia creada y enviada al coordinador para aprobación.',
        'request_id' => $requestId
    ]);
} catch (Throwable $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
