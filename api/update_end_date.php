<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole(['supervisor', 'coordinador', 'admin'], true);

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

$sessionUser = getSessionUser();
$sessionRole = strtolower(trim($sessionUser['rol'] ?? ''));
$sessionGroup = trim($sessionUser['group_name'] ?? $sessionUser['grupo'] ?? '');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!is_array($data) || empty($data['updates']) || !is_array($data['updates'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$updated = 0;
$errors = [];

foreach ($data['updates'] as $row) {
    if (!isset($row['id'])) {
        $errors[] = ['error' => 'Falta id'];
        continue;
    }
    $id = (int)$row['id'];
    $fields = [];
    $types = '';
    $params = [];

    if (array_key_exists('fecha_fin', $row)) {
        $fields[] = 'fecha_fin = ?';
        $types .= 's';
        $params[] = $row['fecha_fin'];
    }
    if (array_key_exists('horario', $row)) {
        $fields[] = 'horario = ?';
        $types .= 'i';
        $params[] = isset($row['horario']) ? (int) !!$row['horario'] : 0;
    }

    if (empty($fields)) {
        $errors[] = ['id' => $id, 'error' => 'Nada que actualizar'];
        continue;
    }

    // Supervisores solo pueden tocar usuarios de su grupo
    if (in_array($sessionRole, ['supervisor', 'coordinador'], true) && $sessionGroup !== '') {
        $chk = $mysqli->prepare("SELECT g.name AS group_name FROM stays s LEFT JOIN groups g ON g.id = s.group_id WHERE s.employee_id = ? AND s.status = 'active' LIMIT 1");
        $chk->bind_param('i', $id);
        $chk->execute();
        $chkRes = $chk->get_result();
        $rowChk = $chkRes ? $chkRes->fetch_assoc() : null;
        $chk->close();
        if (!$rowChk || strcasecmp(trim($rowChk['group_name'] ?? ''), $sessionGroup) !== 0) {
            $errors[] = ['id' => $id, 'error' => 'No autorizado para este usuario'];
            continue;
        }
    }

    // Obtener estancia activa
    $sel = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'active' LIMIT 1");
    $sel->bind_param('i', $id);
    $sel->execute();
    $resSel = $sel->get_result();
    if (!$resSel || $resSel->num_rows === 0) {
        $errors[] = ['id' => $id, 'error' => 'Usuario sin estancia activa'];
        $sel->close();
        continue;
    }
    $stayId = (int)$resSel->fetch_assoc()['id'];
    $sel->close();

    // Armar update
    $types .= 'si'; // status + id
    // Determinar nuevo status
    $status = 'active';
    if (isset($row['fecha_fin']) && $row['fecha_fin'] !== '2100-01-01' && strtotime($row['fecha_fin']) < strtotime('today')) {
        $status = 'archived';
    }
    $params[] = $status;
    $params[] = $stayId;
    $fields[] = "status = ?";
    if ($status === 'archived') {
        $fields[] = "archived_at = COALESCE(archived_at, NOW())";
    } else {
        $fields[] = "archived_at = NULL";
    }

    $sql = sprintf("UPDATE stays SET %s WHERE id = ?", implode(', ', $fields));
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $errors[] = ['id' => $id, 'error' => 'Error al preparar'];
        continue;
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        $errors[] = ['id' => $id, 'error' => $stmt->error];
    } else {
        $updated++;
    }
    $stmt->close();
}

$resp = ['updated' => $updated];
if ($errors) $resp['errors'] = $errors;
echo json_encode($resp);
