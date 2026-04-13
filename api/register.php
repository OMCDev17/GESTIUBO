<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexion con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);
$mysqli->autocommit(false);

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
    INDEX idx_group_join_requests_status (status),
    CONSTRAINT fk_group_join_requests_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    CONSTRAINT fk_group_join_requests_group FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
)");

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload invalido']);
    exit;
}

foreach (['nombre', 'apellidos', 'dni_pasaporte', 'username', 'email'] as $key) {
    if (isset($data[$key]) && is_string($data[$key])) {
        $data[$key] = trim($data[$key]);
    }
}

$required = ['nombre', 'apellidos', 'dni_pasaporte', 'username', 'password', 'email'];
$missing = [];
foreach ($required as $field) {
    if (empty($data[$field]) && $data[$field] !== '0') {
        $missing[] = $field;
    }
}
if ($missing) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios', 'missing' => $missing]);
    exit;
}

if (strlen((string) $data['password']) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La contrasena debe tener al menos 6 caracteres']);
    exit;
}

$today = new DateTime('today');
$startDate = null;
$endDate = null;
if (!empty($data['fecha_inicio'])) {
    try {
        $startDate = new DateTime($data['fecha_inicio']);
    } catch (Exception $e) {
        $startDate = null;
    }
}
if (!empty($data['fecha_fin'])) {
    try {
        $endDate = new DateTime($data['fecha_fin']);
    } catch (Exception $e) {
        $endDate = null;
    }
}
if ($startDate && $startDate < $today) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha de inicio debe ser hoy o posterior']);
    exit;
}
if ($startDate && $endDate && $endDate < $startDate) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio']);
    exit;
}

$ensureGroupId = function (mysqli $db, string $name): ?int {
    $name = trim($name);
    if ($name === '') {
        return null;
    }

    $stmt = $db->prepare('SELECT id FROM groups WHERE LOWER(name) = LOWER(?) LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result($gid);
        if ($stmt->fetch()) {
            $stmt->close();
            return (int) $gid;
        }
        $stmt->close();
    }

    $ins = $db->prepare('INSERT INTO groups (name) VALUES (?)');
    if ($ins) {
        $ins->bind_param('s', $name);
        if ($ins->execute()) {
            $gid = (int) $ins->insert_id;
            $ins->close();
            return $gid;
        }
        $ins->close();
    }

    return null;
};

if (!isset($data['group_id']) && !empty($data['grupo'])) {
    $gid = $ensureGroupId($mysqli, (string) $data['grupo']);
    if ($gid) {
        $data['group_id'] = $gid;
    }
}
if (isset($data['group_id']) && empty($data['grupo'])) {
    $gid = (int) $data['group_id'];
    $stmtGroup = $mysqli->prepare('SELECT name FROM groups WHERE id = ? LIMIT 1');
    if ($stmtGroup) {
        $stmtGroup->bind_param('i', $gid);
        $stmtGroup->execute();
        $stmtGroup->bind_result($groupName);
        if ($stmtGroup->fetch()) {
            $data['grupo'] = $groupName;
        }
        $stmtGroup->close();
    }
}

if (empty($data['group_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Debes seleccionar un grupo', 'field' => 'group_id']);
    exit;
}

$username = (string) $data['username'];
$email = (string) $data['email'];
$dni = (string) $data['dni_pasaporte'];

$dupStmt = $mysqli->prepare('SELECT id, username, email, dni_pasaporte FROM employees WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR dni_pasaporte = ? LIMIT 1');
if (!$dupStmt) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar comprobacion de duplicados']);
    exit;
}
$dupStmt->bind_param('sss', $username, $email, $dni);
$dupStmt->execute();
$dupStmt->store_result();
if ($dupStmt->num_rows > 0) {
    $dupStmt->bind_result($existingId, $existingUser, $existingEmail, $existingDni);
    $dupStmt->fetch();
    $dupStmt->free_result();
    $dupStmt->close();
    $mysqli->rollback();

    $field = 'username';
    if (strcasecmp((string) $existingEmail, $email) === 0) {
        $field = 'email';
    } elseif ((string) $existingDni === $dni) {
        $field = 'dni_pasaporte';
    }

    echo json_encode([
        'success' => true,
        'id' => $existingId,
        'existing' => true,
        'field' => $field,
        'message' => "El {$field} ya estaba registrado.",
    ]);
    exit;
}
$dupStmt->free_result();
$dupStmt->close();

$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
$data['rol'] = 'empleado';
$data['horario'] = isset($data['horario']) ? (int) !!$data['horario'] : 1;

$allowedEmployee = [
    'nombre', 'apellidos', 'dni_pasaporte', 'username', 'password', 'email',
    'fecha_nacimiento', 'foto_url', 'rol'
];

$fields = [];
$params = [];
$types = '';
foreach ($allowedEmployee as $col) {
    if (array_key_exists($col, $data) && $data[$col] !== null) {
        $fields[] = $col;
        $params[] = $data[$col];
        $types .= 's';
    }
}

if (!$fields) {
    http_response_code(400);
    echo json_encode(['error' => 'No se proporcionaron campos validos']);
    exit;
}

$placeholders = implode(', ', array_fill(0, count($fields), '?'));
$columns = implode(', ', $fields);
$sql = "
INSERT INTO employees ($columns)
SELECT $placeholders
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM employees
    WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR dni_pasaporte = ?
)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta']);
    exit;
}

$bindParams = array_merge($params, [$username, $email, $dni]);
$stmt->bind_param($types . 'sss', ...$bindParams);
$stmt->execute();

if ($stmt->errno) {
    $mysqli->rollback();
    if ($stmt->errno === 1062) {
        echo json_encode([
            'success' => true,
            'existing' => true,
            'field' => 'username',
            'message' => 'El usuario ya estaba registrado.',
        ]);
        exit;
    }
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
    exit;
}

if ($stmt->affected_rows === 0) {
    $mysqli->rollback();
    http_response_code(409);
    echo json_encode([
        'error' => 'El usuario ya existe (usuario/email/DNI).',
        'field' => 'username',
    ]);
    exit;
}

$newUserId = (int) $mysqli->insert_id;
$stayMotivo = (string) ($data['motivo'] ?? '');
$stayInicio = (string) ($data['fecha_inicio'] ?? '');
$stayFin = (string) ($data['fecha_fin'] ?? '');
$stayGroup = (int) ($data['group_id'] ?? 0);
$stayHorario = (int) ($data['horario'] ?? 1);
$stayInst = (string) ($data['institucion'] ?? '');
$stayPais = (string) ($data['pais'] ?? '');
$approvalToken = bin2hex(random_bytes(32));
$requestName = trim(($data['nombre'] ?? '') . ' ' . ($data['apellidos'] ?? ''));

$requestStmt = $mysqli->prepare("
    INSERT INTO group_join_requests (
        employee_id, group_id, requested_by_email, requested_by_name, motivo,
        fecha_inicio, fecha_fin, horario, institucion, pais, approval_token, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");
if (!$requestStmt) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo crear la solicitud de aprobacion']);
    exit;
}
$requestStmt->bind_param(
    'iisssssisss',
    $newUserId,
    $stayGroup,
    $email,
    $requestName,
    $stayMotivo,
    $stayInicio,
    $stayFin,
    $stayHorario,
    $stayInst,
    $stayPais,
    $approvalToken
);
$requestStmt->execute();
$requestId = (int) $mysqli->insert_id;
$requestStmt->close();

$mysqli->commit();

require_once __DIR__ . '/email_templates.php';

$firstName = trim((string) ($data['nombre'] ?? ''));
$userName = trim((string) ($data['username'] ?? ''));
$groupName = trim((string) ($data['grupo'] ?? ''));
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
$loginUrl = "{$scheme}://{$host}{$basePath}/Loggin.php";
$approveUrl = "{$scheme}://{$host}{$basePath}/approve_group_request.php?token={$approvalToken}";

$approverQuery = $mysqli->prepare("
    SELECT DISTINCT e.id, e.nombre, e.apellidos, e.email
    FROM employees e
    INNER JOIN stays s ON s.employee_id = e.id AND s.status = 'active'
    WHERE s.group_id = ?
      AND e.rol IN ('supervisor', 'coordinador')
      AND e.email IS NOT NULL
      AND e.email <> ''
    ORDER BY FIELD(e.rol, 'supervisor', 'coordinador'), e.nombre, e.apellidos
");
$approvers = [];
if ($approverQuery) {
    $approverQuery->bind_param('i', $stayGroup);
    $approverQuery->execute();
    $approverRes = $approverQuery->get_result();
    while ($approverRes && ($row = $approverRes->fetch_assoc())) {
        $approvers[] = $row;
    }
    $approverQuery->close();
}

if (!$approvers) {
    $adminQuery = $mysqli->prepare("
        SELECT e.id, e.nombre, e.apellidos, e.email
        FROM employees e
        WHERE e.rol = 'admin'
          AND e.email IS NOT NULL
          AND e.email <> ''
        ORDER BY e.nombre, e.apellidos
    ");
    if ($adminQuery) {
        $adminQuery->execute();
        $adminRes = $adminQuery->get_result();
        while ($adminRes && ($row = $adminRes->fetch_assoc())) {
            $approvers[] = $row;
        }
        $adminQuery->close();
    }
}

$sentApprovalEmail = false;
foreach ($approvers as $approver) {
    $supervisorName = trim(($approver['nombre'] ?? '') . ' ' . ($approver['apellidos'] ?? ''));
    $targetEmail = trim((string) ($approver['email'] ?? ''));
    if ($targetEmail === '') {
        continue;
    }
    $ok = @sendGroupApprovalRequestEmail(
        $targetEmail,
        $supervisorName,
        [
            'employee_name' => $requestName,
            'employee_email' => $email,
            'group_name' => $groupName,
            'motivo' => $stayMotivo,
            'fecha_inicio' => $stayInicio,
            'fecha_fin' => $stayFin,
            'institucion' => $stayInst,
            'pais' => $stayPais,
            'approve_url' => $approveUrl,
        ],
        $config
    );
    $sentApprovalEmail = $sentApprovalEmail || $ok;
}

if ($sentApprovalEmail) {
    $markSentStmt = $mysqli->prepare('UPDATE group_join_requests SET email_sent_at = NOW() WHERE id = ? LIMIT 1');
    if ($markSentStmt) {
        $markSentStmt->bind_param('i', $requestId);
        $markSentStmt->execute();
        $markSentStmt->close();
    }
}

@sendWelcomeEmail($email, $userName, $firstName, $loginUrl, $config);

echo json_encode([
    'success' => true,
    'id' => $newUserId,
    'pending_approval' => true,
    'approval_email_sent' => $sentApprovalEmail,
]);
