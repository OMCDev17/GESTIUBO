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

// Solo se permite cambiar fecha_fin. Supervisores/coordinadores limitados a su grupo.
$stmt = $mysqli->prepare("UPDATE employees SET fecha_fin = ? WHERE id = ? %s");

$extraFilter = '';
$paramsTypes = 'si';

if (in_array($sessionRole, ['supervisor', 'coordinador'], true) && $sessionGroup !== '') {
    $extraFilter = ' AND grupo = ?';
    $paramsTypes .= 's';
}

$updated = 0;
$errors = [];

foreach ($data['updates'] as $row) {
    if (!isset($row['id'])) {
        $errors[] = ['error' => 'Falta id'];
        continue;
    }
    $id = (int)$row['id'];
    $fecha = isset($row['fecha_fin']) ? $row['fecha_fin'] : null;

    $sql = sprintf("UPDATE employees SET fecha_fin = ? WHERE id = ?%s", $extraFilter);
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $errors[] = ['id' => $id, 'error' => 'Error al preparar'];
        continue;
    }

    if ($extraFilter) {
        $stmt->bind_param($paramsTypes, $fecha, $id, $sessionGroup);
    } else {
        $stmt->bind_param('si', $fecha, $id);
    }

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
