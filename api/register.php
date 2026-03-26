<?php
header('Content-Type: application/json; charset=utf-8');

// Registration endpoint for new employees. This endpoint is intentionally open so that
// users can request an account without requiring an existing session.
// NOTE: For production, consider adding CAPTCHA, email verification, or admin approval.

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$required = ['nombre', 'apellidos', 'dni_pasaporte', 'username', 'password', 'email'];
$missing = [];
foreach ($required as $field) {
    if (empty($data[$field]) && $data[$field] !== '0') {
        $missing[] = $field;
    }
}
if (!empty($missing)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios', 'missing' => $missing]);
    exit;
}

// Check for existing user by username, email or DNI/passport before inserting
$dupStmt = $mysqli->prepare('SELECT id FROM employees WHERE username = ? OR email = ? OR dni_pasaporte = ? LIMIT 1');
if ($dupStmt) {
    $dupStmt->bind_param('sss', $data['username'], $data['email'], $data['dni_pasaporte']);
    $dupStmt->execute();
    $dupStmt->store_result();
    if ($dupStmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'El usuario ya existe con el mismo nombre de usuario, email o DNI/Pasaporte']);
        exit;
    }
}

// Default role for self-registration
$data['rol'] = 'empleado';

$allowed = [
    'nombre', 'apellidos', 'dni_pasaporte', 'username', 'password', 'email',
    'fecha_nacimiento', 'institucion', 'pais', 'motivo', 'fecha_inicio', 'fecha_fin',
    'grupo', 'foto_url', 'rol'
];

$fields = [];
$params = [];
$types = '';
foreach ($allowed as $col) {
    if (array_key_exists($col, $data) && $data[$col] !== null) {
        $fields[] = $col;
        $params[] = $data[$col];
        $types .= 's';
    }
}

if (count($fields) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No se proporcionaron campos válidos']);
    exit;
}

$sql = sprintf(
    "INSERT INTO employees (%s) VALUES (%s)",
    implode(', ', $fields),
    implode(', ', array_fill(0, count($fields), '?'))
);
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta']);
    exit;
}

$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
    exit;
}

$newUserId = $mysqli->insert_id;

// Enviar correo de bienvenida
require_once __DIR__ . '/email_templates.php';

$firstName = trim($data['nombre'] ?? '');
$userName = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');

$host = $_SERVER['HTTP_HOST'];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$loginUrl = "{$scheme}://{$host}{$basePath}/Loggin.php";

// Enviar correo de bienvenida (no es crítico si falla)
@sendWelcomeEmail($email, $userName, $firstName, $loginUrl, $config);

echo json_encode(['success' => true, 'id' => $newUserId]);

