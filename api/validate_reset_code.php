<?php
// ============================================================================
// API: Validar Código de Restablecimiento y Actualizar Contraseña
// ============================================================================

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
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

// Obtener datos del formulario
$resetId = intval($_POST['reset_id'] ?? 0);
$resetCode = trim($_POST['code'] ?? '');
$newPassword = trim($_POST['newPwd'] ?? '');
$confirmPassword = trim($_POST['confirmPwd'] ?? '');

// Validaciones
if ($resetId <= 0 || empty($resetCode) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($newPassword) < 4) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 4 caracteres']);
    exit;
}

// Verificar que el código existe y no ha expirado
$stmt = $mysqli->prepare('SELECT user_id FROM password_resets WHERE id = ? AND code = ? AND expires_at > NOW() LIMIT 1');
$stmt->bind_param('is', $resetId, $resetCode);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    http_response_code(400);
    echo json_encode(['error' => 'Código inválido o expirado']);
    exit;
}

$userId = intval($row['user_id']);

// Actualizar la contraseña del usuario
$stmt = $mysqli->prepare('UPDATE employees SET password = ? WHERE id = ?');
$stmt->bind_param('si', $newPassword, $userId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo actualizar la contraseña']);
    exit;
}
$stmt->close();

// Eliminar el registro de restablecimiento
$stmt = $mysqli->prepare('DELETE FROM password_resets WHERE id = ?');
$stmt->bind_param('i', $resetId);
$stmt->execute();
$stmt->close();

$mysqli->close();

echo json_encode(['success' => true, 'message' => 'Contraseña restablecida exitosamente']);
