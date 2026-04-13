<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole(['admin', 'coordinador', 'supervisor', 'seguridad', 'empleado'], true);

$user = getSessionUser();
if (!$user || empty($user['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);

$body = json_decode(file_get_contents('php://input'), true);
$current = trim($body['current'] ?? '');
$new = trim($body['new'] ?? '');
$confirm = trim($body['confirm'] ?? '');

if ($new === '' || $confirm === '' || $current === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos son obligatorios']);
    exit;
}
if ($new !== $confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Las contraseñas no coinciden']);
    exit;
}
if (strlen($new) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La nueva contraseña debe tener al menos 6 caracteres']);
    exit;
}

// Obtener contraseña actual
$stmt = $mysqli->prepare('SELECT password FROM employees WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

$stored = $row['password'] ?? '';
$isHash = password_get_info($stored)['algo'] !== 0;
$validCurrent = $isHash ? password_verify($current, $stored) : hash_equals((string)$stored, (string)$current);

if (!$row || !$validCurrent) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña actual no es correcta']);
    exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare('UPDATE employees SET password = ? WHERE id = ?');
$stmt->bind_param('si', $newHash, $user['id']);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar la contraseña']);
    exit;
}
$stmt->close();

echo json_encode(['success' => true]);

