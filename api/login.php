<?php
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

function send500($msg)
{
    http_response_code(500);
    echo json_encode(['error' => $msg]);
    exit;
}

$db = null;
$usingMysqli = class_exists('mysqli');
if ($usingMysqli) {
    $db = @new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if ($db->connect_errno) {
        send500('Error de conexión con la base de datos');
    }
    $db->set_charset($config['charset']);
    $db->query("SET NAMES {$config['charset']}");
} elseif (extension_loaded('pdo_mysql')) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";
        $db = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        send500('Error de conexión con la base de datos');
    }
} else {
    send500('Extensiones mysqli/pdo_mysql no disponibles en PHP.');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input['username']) || !array_key_exists('password', $input)) {
    $input = $_POST;
}
if (!is_array($input) || empty($input['username']) || !array_key_exists('password', $input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan credenciales']);
    exit;
}

$username = trim($input['username']);
$password = $input['password'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = null;
if ($usingMysqli) {
$sql = 'SELECT e.id, e.nombre, e.apellidos, e.email, e.username, e.dni_pasaporte, e.fecha_nacimiento, e.rol, e.password,
                   s.group_id, g.name AS group_name, s.horario, s.fecha_inicio, s.fecha_fin, s.motivo, s.institucion, s.pais, e.foto_url,
                   e.phone_prefix, e.phone_number
            FROM employees e
            LEFT JOIN stays s ON s.employee_id = e.id AND s.status = "active"
            LEFT JOIN groups g ON g.id = s.group_id
            WHERE e.username = ? OR e.email = ?
            LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();

    if ($result = $stmt->get_result()) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
} else { // PDO
    $stmt = $db->prepare('SELECT e.id, e.nombre, e.apellidos, e.email, e.username, e.dni_pasaporte, e.fecha_nacimiento, e.rol, e.password,
                                 s.group_id, g.name AS group_name, s.horario, s.fecha_inicio, s.fecha_fin, s.motivo, s.institucion, s.pais, e.foto_url,
                                 e.phone_prefix, e.phone_number
                          FROM employees e
                          LEFT JOIN stays s ON s.employee_id = e.id AND s.status = "active"
                          LEFT JOIN groups g ON g.id = s.group_id
                          WHERE e.username = ? OR e.email = ?
                          LIMIT 1');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    exit;
}

$stored = $user['password'] ?? '';
$plainMatch = hash_equals((string)$stored, (string)$password);
$hashMatch = password_get_info($stored)['algo'] !== 0 && password_verify($password, $stored);
if (!$plainMatch && !$hashMatch) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    exit;
}
unset($user['password']);

$_SESSION['user'] = $user;

// Determine redirect according to stored role
$role = strtolower($user['rol'] ?? '');
$redirect = 'empleado.php';
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
}

echo json_encode([
    'success' => true,
    'user' => $user,
    'redirect' => $redirect,
]);

