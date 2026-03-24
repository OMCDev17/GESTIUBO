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

// Enviar email usando PHPMailer / SMTP
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailSent = false;
$mailError = "";
try {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->CharSet = 'UTF-8';
    $mailer->Host = $config['smtp']['host'];
    $mailer->SMTPAuth = true;
    $mailer->Username = $config['smtp']['username'];
    $mailer->Password = $config['smtp']['password'];
    $mailer->SMTPSecure = $config['smtp']['secure'];
    $mailer->Port = $config['smtp']['port'];
    $mailer->setFrom($config['smtp']['from_email'], $config['smtp']['from_name']);
    $mailer->addAddress($email);
    $mailer->Subject = $subject;
    $mailer->Body = $message;
    $mailer->isHTML(false);
    $mailer->AltBody = strip_tags($message);
    $mailer->send();
    $mailSent = true;
} catch (Exception $e) {
    $mailError = $e->getMessage();
    // Fallback a mail() si el SMTP falla
    $headers = "From: {$config['smtp']['from_email']}\r\n";
    $headers .= "Reply-To: {$config['smtp']['from_email']}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($email, $subject, $message, $headers);
    $mailSent = true;
}

$debugMessage = "";
if ($mailError) {
    $debugMessage = "<span class='text-red-600 dark:text-red-400'><strong>Error SMTP:</strong> " . htmlspecialchars($mailError) . "</span>";
} else {
    $debugMessage = "<span class='text-emerald-600 dark:text-emerald-400'><strong>✓ Correo enviado a:</strong> " . htmlspecialchars($email) . "</span>";
}

// Mostrar el token y el enlace de recuperación para que puedas usarlo mientras se resuelve el delivery de correo.
?><!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperación de contraseña - Lab Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#5c068c",
                        "background-light": "#f8f6f6",
                        "background-dark": "#221610",
                    },
                    fontFamily: {
                        "display": ["Argentum Sans", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>body { font-family: 'Argentum Sans', sans-serif; }</style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">
<div class="relative flex h-full min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
    <div class="layout-container flex h-full grow flex-col">
        <div class="flex flex-col items-center pt-10 px-6">
            <div class="max-w-[360px] mb-6">
                <img alt="Logo universidad" class="w-full h-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
            </div>
        </div>
        <main class="flex-1 flex items-center justify-center p-6">
            <div class="layout-content-container flex flex-col max-w-[520px] w-full bg-white dark:bg-slate-900/50 p-8 rounded-2xl shadow-sm border border-primary/5">
                <div class="flex flex-col items-center mb-6">
                    <div class="w-full aspect-video bg-primary/5 rounded-xl mb-6 flex items-center justify-center border border-primary/10">
                        <span class="material-symbols-outlined text-primary text-7xl" style="font-variation-settings: 'FILL' 1, 'wght' 700;">mail</span>
                    </div>
                    <h2 class="text-slate-900 dark:text-slate-100 tracking-tight text-2xl md:text-3xl font-bold text-center mb-2">Recuperación de contraseña</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm text-center">Mensaje de estado de envío y enlace de recuperación.</p>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-5 bg-slate-50 dark:bg-slate-800 mb-6">
                    <p class="text-sm mb-3"><?php echo $debugMessage; ?></p>
                    <p class="text-sm mb-1"><strong>Token:</strong> <code class="bg-white dark:bg-slate-900 p-2 rounded"><?php echo htmlspecialchars($token); ?></code></p>
                    <p class="text-sm mb-1"><strong>Enlace:</strong> <a class="text-primary underline break-all" href="<?php echo htmlspecialchars($resetUrl); ?>" target="_blank"><?php echo htmlspecialchars($resetUrl); ?></a></p>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                    <p class="font-semibold mb-2">Verificación de configuración:</p>
                    <ul class="list-disc ml-5 space-y-1">
                        <li><strong>SMTP Host:</strong> <?php echo htmlspecialchars($config['smtp']['host']); ?></li>
                        <li><strong>SMTP Port:</strong> <?php echo htmlspecialchars($config['smtp']['port']); ?></li>
                        <li><strong>SMTP Secure:</strong> <?php echo htmlspecialchars($config['smtp']['secure']); ?></li>
                        <li><strong>Username:</strong> <?php echo htmlspecialchars($config['smtp']['username']); ?></li>
                        <li><strong>OpenSSL:</strong> <?php echo extension_loaded('openssl') ? '<span class="text-emerald-600">✓ Habilitado</span>' : '<span class="text-red-600">✗ Deshabilitado</span>'; ?></li>
                        <li><strong>Sockets:</strong> <?php echo extension_loaded('sockets') ? '<span class="text-emerald-600">✓ Habilitado</span>' : '<span class="text-red-600">✗ Deshabilitado</span>'; ?></li>
                    </ul>
                    <?php if ($mailError): ?>
                        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/30 rounded text-red-700 dark:text-red-400 text-xs">
                            <p><strong>Error SMTP Detallado:</strong></p>
                            <p><?php echo htmlspecialchars($mailError); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex gap-3">
                    <a href="Recuperacion.html" class="flex-grow text-center h-12 rounded-xl bg-primary text-white font-bold hover:opacity-90 transition-opacity">Volver a recuperar</a>
                    <a href="Loggin.php" class="flex-grow text-center h-12 rounded-xl border border-primary text-primary font-bold hover:bg-primary/5 transition">Ir a login</a>
                </div>
            </div>
        </main>
        <footer class="py-6 px-10 border-t border-primary/5 text-center">
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">© 2026 Lab Portal. All Rights Reserved.</p>
        </footer>
    </div>
</div>
</body>
</html>
<?php
exit;

