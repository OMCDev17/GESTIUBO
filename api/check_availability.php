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

// Comprobar también colisiones cruzadas: username vs email y email vs username.
if ($type === 'email') {
    $stmt = $mysqli->prepare("SELECT id FROM employees WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?) LIMIT 1");
} else {
    $stmt = $mysqli->prepare("SELECT id FROM employees WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1");
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando consulta']);
    exit;
}

$stmt->bind_param('ss', $value, $value);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result && $result->num_rows > 0;
$stmt->close();

echo json_encode([
    'available' => !$exists,
    'type' => $type,
    'value' => $value
]);
