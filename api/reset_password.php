<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Token requerido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$new = trim($input['new'] ?? '');
$confirm = trim($input['confirm'] ?? '');

if ($new === '' || $confirm === '') {
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
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}
$mysqli->set_charset($config['charset']);

$stmt = $mysqli->prepare('SELECT pr.id AS reset_id, pr.user_id FROM password_resets pr WHERE pr.token = ? AND pr.expires_at > NOW() LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    http_response_code(400);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit;
}

$userId = intval($row['user_id']);
$resetId = intval($row['reset_id']);

$newHash = password_hash($new, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare('UPDATE employees SET password = ? WHERE id = ?');
$stmt->bind_param('si', $newHash, $userId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar la contraseña']);
    exit;
}
$stmt->close();

$stmt = $mysqli->prepare('DELETE FROM password_resets WHERE id = ?');
$stmt->bind_param('i', $resetId);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
