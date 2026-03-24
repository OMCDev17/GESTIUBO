<?php
// Procesa el formulario de recuperación y envía el email con token de restablecimiento.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Recuperacion.html');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('Location: Recuperacion.html?error=invalid');
    exit;
}

$config = require __DIR__ . '/api/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    header('Location: Recuperacion.html?error=db');
    exit;
}
$mysqli->set_charset($config['charset']);

$stmt = $mysqli->prepare('SELECT id FROM employees WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

// No revelar si el email existe o no.
if (!$user) {
    header('Location: email_exitoso.php');
    exit;
}

$userId = intval($user['id']);
$token = bin2hex(random_bytes(24));
$expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

$createTableSql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($createTableSql);

$deleteSql = 'DELETE FROM password_resets WHERE user_id = ?';
$stmt = $mysqli->prepare($deleteSql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->close();

$insertSql = 'INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)';
$stmt = $mysqli->prepare($insertSql);
$stmt->bind_param('iss', $userId, $token, $expiresAt);
$stmt->execute();
$stmt->close();

$host = $_SERVER['HTTP_HOST'];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$resetUrl = "{$scheme}://{$host}{$basePath}/change_password.php?token={$token}";

$subject = 'Recuperación de contraseña';
$message = "Hola,\n\nRecibimos una solicitud de restablecimiento de contraseña para tu cuenta.\n\nHaz clic en el siguiente enlace para crear una nueva contraseña:\n\n{$resetUrl}\n\nEste enlace caduca en 1 hora.\n\nSi no solicitaste esto, ignora este correo.\n";
$headers = "From: no-reply@localhost\r\nReply-To: no-reply@localhost\r\n";

@mail($email, $subject, $message, $headers);

header('Location: email_exitoso.php');
exit;
