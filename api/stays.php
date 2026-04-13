<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireLogin(true);

$sessionUser = getSessionUser();
$userId = (int)($sessionUser['id'] ?? 0);
$userRole = strtolower(trim($sessionUser['rol'] ?? ''));

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);
$mysqli->query("SET NAMES {$config['charset']}");

// Asegurar tabla stays
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
    INDEX idx_stays_status (status)
)");

// Permitir que admin consulte otro usuario vía ?user_id
$targetId = $userId;
if ($userRole === 'admin' && isset($_GET['user_id'])) {
    $targetId = (int) $_GET['user_id'];
}

// Historial (archivadas)
$stmt = $mysqli->prepare("SELECT s.id, s.employee_id, s.fecha_inicio, s.fecha_fin, s.motivo, s.group_id, g.name AS group_name, s.horario, s.institucion, s.pais, s.archived_at
FROM stays s
LEFT JOIN groups g ON g.id = s.group_id
WHERE s.employee_id = ? AND s.status = 'archived'
ORDER BY s.fecha_inicio DESC, s.fecha_fin DESC, COALESCE(s.archived_at, s.updated_at) DESC");
$stmt->bind_param('i', $targetId);
$stmt->execute();
$res = $stmt->get_result();
$history = [];
while ($row = $res->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();

// Estancia activa
$active = null;
$activeStmt = $mysqli->prepare("SELECT s.id, s.employee_id, s.fecha_inicio, s.fecha_fin, s.motivo, s.group_id, g.name AS group_name, s.horario, s.institucion, s.pais
FROM stays s
LEFT JOIN groups g ON g.id = s.group_id
WHERE s.employee_id = ? AND s.status = 'active'
ORDER BY s.updated_at DESC LIMIT 1");
$activeStmt->bind_param('i', $targetId);
$activeStmt->execute();
$activeRes = $activeStmt->get_result();
if ($activeRes && $activeRes->num_rows === 1) {
    $active = $activeRes->fetch_assoc();
}
$activeStmt->close();

// Si la estancia activa ya terminó, tratarla como no activa
if ($active) {
    $today = new DateTime('today');
    $fin = !empty($active['fecha_fin']) ? new DateTime($active['fecha_fin']) : null;
    if ($fin && $fin < $today && $active['fecha_fin'] !== '2100-01-01') {
        $active = null;
    }
}

$mysqli->close();

echo json_encode(['history' => $history, 'active' => $active]);

