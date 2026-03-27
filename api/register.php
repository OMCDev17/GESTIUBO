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
// Desactivar autocommit para poder hacer rollback si algo falla
$mysqli->autocommit(false);

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

// Normalizar entradas para evitar duplicados por espacios o mayúsculas
foreach (['nombre', 'apellidos', 'dni_pasaporte', 'username', 'email'] as $k) {
    if (isset($data[$k]) && is_string($data[$k])) {
        $data[$k] = trim($data[$k]);
    }
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

// Validar y hashear la contraseña antes de seguir
if (strlen((string)$data['password']) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}
$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

// Check for existing user by username, email or DNI/passport before inserting
$username = $data['username'];
$email = $data['email'];
$dni = $data['dni_pasaporte'];

$dupStmt = $mysqli->prepare('SELECT id, username, email, dni_pasaporte FROM employees WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR dni_pasaporte = ? LIMIT 1');
if (!$dupStmt) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar comprobación de duplicados']);
    exit;
}
$dupStmt->bind_param('sss', $username, $email, $dni);
$dupStmt->execute();
$dupStmt->store_result();
if ($dupStmt->num_rows > 0) {
    $dupStmt->bind_result($existingId, $existingUser, $existingEmail, $existingDni);
    $dupStmt->fetch();
    $dupStmt->free_result();
    $dupStmt->close();
    $mysqli->rollback();
    // Respuesta idempotente: si ya existe, devolvemos éxito sin insertar
    $field = 'username';
    if (strcasecmp($existingEmail, $email) === 0) {
        $field = 'email';
    } elseif ($existingDni === $dni) {
        $field = 'dni_pasaporte';
    }
    echo json_encode([
        'success' => true,
        'id' => $existingId,
        'existing' => true,
        'field' => $field,
        'message' => "El {$field} ya estaba registrado.",
    ]);
    exit;
}
$dupStmt->free_result();
$dupStmt->close();

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

$placeholders = implode(', ', array_fill(0, count($fields), '?'));
$columns = implode(', ', $fields);

// Atomic insert that only runs if no duplicate exists (prevents race conditions)
$sql = "
INSERT INTO employees ($columns)
SELECT $placeholders
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM employees
    WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR dni_pasaporte = ?
)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta']);
    exit;
}

// Bind params twice: values, then duplicate checks
$bindParams = array_merge($params, [$username, $email, $dni]);
$typesWithDup = $types . 'sss';
$stmt->bind_param($typesWithDup, ...$bindParams);
$stmt->execute();

if ($stmt->errno) {
    $mysqli->rollback();
    $field = 'username';
    if (strpos($stmt->error, 'email') !== false) {
        $field = 'email';
    } elseif (strpos($stmt->error, 'dni') !== false) {
        $field = 'dni_pasaporte';
    }
    // Idempotente en caso de duplicado
    if ($stmt->errno === 1062) {
        echo json_encode([
            'success' => true,
            'existing' => true,
            'field' => $field,
            'message' => "El {$field} ya estaba registrado.",
        ]);
        exit;
    }
    http_response_code(500);
    echo json_encode(['error' => $stmt->error]);
    exit;
}

if ($stmt->affected_rows === 0) {
    // NOT EXISTS triggered => duplicate
    $mysqli->rollback();
    echo json_encode([
        'success' => true,
        'existing' => true,
        'field' => 'username',
        'message' => 'El usuario ya estaba registrado (usuario/email/DNI).',
    ]);
    exit;
}

$newUserId = $mysqli->insert_id;
$mysqli->commit();

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

