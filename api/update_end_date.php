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

$sessionUser = getSessionUser();
$sessionRole = strtolower(trim($sessionUser['rol'] ?? ''));
$sessionGroup = trim($sessionUser['grupo'] ?? '');

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

    $extraFilter = '';
    if (in_array($sessionRole, ['supervisor', 'coordinador'], true) && $sessionGroup !== '') {
        $extraFilter = ' AND grupo = ?';
        $types .= 's';
        $params[] = $sessionGroup;
    }

    $types .= 'i';
    $params[] = $id;

    $sql = sprintf("UPDATE employees SET %s WHERE id = ?%s", implode(', ', $fields), $extraFilter);
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
