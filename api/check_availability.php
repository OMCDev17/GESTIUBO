<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$mysqli->set_charset($config['charset']);

$type = trim($_GET['type'] ?? '');
$value = trim($_GET['value'] ?? '');

if (!$type || !$value) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

if ($type !== 'email' && $type !== 'username') {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo inválido']);
    exit;
}

// Validar que no sea el usuario actual (si viene desde formulario de edición)
// Por ahora solo verificamos que no exista en BD
$stmt = $mysqli->prepare("SELECT id FROM employees WHERE $type = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando consulta']);
    exit;
}

$stmt->bind_param('s', $value);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

echo json_encode([
    'available' => !$exists,
    'type' => $type,
    'value' => $value
]);
