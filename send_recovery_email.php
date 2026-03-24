<?php
// Procesa el formulario de recuperación y envía el email de prueba.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Recuperacion.html');
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('Location: Recuperacion.html?error=invalid');
    exit;
}

$subject = 'Recuperación de contraseña';
$message = '  esto es una prueba ';
$headers = "From: no-reply@localhost\r\nReply-To: no-reply@localhost\r\n";

if (@mail($email, $subject, $message, $headers)) {
    header('Location: email_exitoso.php');
    exit;
}

header('Location: Recuperacion.html?error=send');
exit;
