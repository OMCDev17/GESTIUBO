<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan credenciales']);
    exit;
}

$username = trim($input['username']);
$password = trim($input['password']);

// Start session and store user data on successful login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$stmt = $mysqli->prepare(
    'SELECT id, nombre, apellidos, email, rol, grupo, foto_url, password FROM employees WHERE username = ? OR email = ? LIMIT 1'
);
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    exit;
}

// NOTE: Here we compare the password directly.
// For real apps, store hashed passwords and use password_verify().
if ($user['password'] !== $password) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    exit;
}

// Remove password before returning
unset($user['password']);

// Save session info for server-side auth
$_SESSION['user'] = [
    'id' => $user['id'] ?? null,
    'nombre' => $user['nombre'] ?? null,
    'apellidos' => $user['apellidos'] ?? null,
    'email' => $user['email'] ?? null,
    'rol' => $user['rol'] ?? null,
    'grupo' => $user['grupo'] ?? null,
    'foto_url' => $user['foto_url'] ?? null,
];

// Determine landing page based on role.
$role = strtolower($_SESSION['user']['rol'] ?? '');
$redirect = 'Loggin.php';
switch ($role) {
    case 'admin':
        $redirect = 'admin.php';
        break;
    case 'supervisor':
    case 'coordinador':
        $redirect = 'supervisor.php';
        break;
    case 'seguridad':
        $redirect = 'seguridad.php';
        break;
    default:
        $redirect = 'empleado.php';
        break;
}

echo json_encode([
    'success' => true,
    'user' => $user,
    'redirect' => $redirect,
]);
