<?php
// ============================================================================
// Proceso de Recuperación de Contraseña - Nuevo Sistema con Código de 4 Dígitos
// ============================================================================

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

// Obtener el usuario
$stmt = $mysqli->prepare('SELECT id, nombre FROM employees WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

// No revelar si el email existe o no
if (!$user) {
    header('Location: email_exitoso.php');
    exit;
}

$userId = intval($user['id']);
$userName = trim($user['nombre'] ?? '');

// Crear tabla de restablecimiento si no existe (ahora con campo de código)
$createTableSql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(4) NOT NULL,
    token VARCHAR(64),
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (code),
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$mysqli->query($createTableSql);

// Generar código de 4 dígitos aleatorio
$code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
$expiresAt = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

// Eliminar códigos anteriores del usuario
$deleteSql = 'DELETE FROM password_resets WHERE user_id = ?';
$stmt = $mysqli->prepare($deleteSql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->close();

// Insertar nuevo registro con el código
$insertSql = 'INSERT INTO password_resets (user_id, code, expires_at) VALUES (?, ?, ?)';
$stmt = $mysqli->prepare($insertSql);
$stmt->bind_param('iss', $userId, $code, $expiresAt);
$stmt->execute();
$stmt->close();

// Enviar correo con el código
require_once __DIR__ . '/api/email_templates.php';

$mailSent = sendPasswordResetEmail($email, $userName, $code, $config);

$debugMessage = "";
if ($mailSent) {
    $debugMessage = "<span class='text-emerald-600 dark:text-emerald-400'><strong>✓ Correo enviado a:</strong> " . htmlspecialchars($email) . "</span>";
} else {
    $debugMessage = "<span class='text-red-600 dark:text-red-400'><strong>Error:</strong> No se pudo enviar el correo. Intenta de nuevo más tarde.</span>";
}

// Mostrar confirmación y página para validar código
?><!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Código enviado - Instituto de Bio-Orgánica Antonio González</title>
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
                <div class="flex flex-col items-center mb-8">
                    <div class="w-full aspect-video bg-[#f0fdf4] dark:bg-[#064e3b] rounded-xl mb-6 flex items-center justify-center border border-emerald-200 dark:border-emerald-900">
                        <span class="text-primary dark:text-emerald-300 text-7xl font-bold">✓</span>
                    </div>
                    <h2 class="text-slate-900 dark:text-slate-100 tracking-tight text-2xl md:text-3xl font-bold text-center mb-2">¡Correo enviado! / Email sent</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm text-center"><?php echo htmlspecialchars($email); ?></p>
                </div>
                
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 mb-6">
                    <?php echo $debugMessage; ?>
                </div>
                
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 mb-6">
                    <div class="flex gap-3">
                        <span class="text-blue-600 dark:text-blue-400 flex-shrink-0 font-bold text-xl">i</span>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-semibold mb-2">Hemos enviado un código de 4 dígitos a tu correo</p>
                            <p class="mb-3">El código expira en 15 minutos. Utilízalo para restablecer tu contraseña.</p>
                            <a href="resetear_contraseña.php" class="inline-flex items-center gap-2 text-blue-700 dark:text-blue-300 hover:underline font-semibold">
                                Ingresar código de verificación →
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">¿No recibiste el correo?</p>
                    <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                        <li>• Revisa tu carpeta de spam o junk mail</li>
                        <li>• Verifica que hayas introducido el correo correcto</li>
                        <li>• Intenta nuevamente en 5 minutos</li>
                    </ul>
                </div>
                
                <div class="flex gap-3">
                    <a href="resetear_contraseña.php" class="flex-grow text-center h-12 rounded-xl bg-primary text-white font-bold hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                        <span class="text-white font-bold">✔</span>
                        Validar Código / Verify code
                    </a>
                    <a href="Loggin.php" class="flex-grow text-center h-12 rounded-xl border border-primary text-primary font-bold hover:bg-primary/5 transition flex items-center justify-center">
                        Volver / Back
                    </a>
                </div>
            </div>
        </main>
        <footer class="py-6 px-10 border-t border-primary/5 text-center">
            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">© 2026 Instituto de Bio-Orgánica Antonio González. Todos los derechos reservados.</p>
        </footer>
    </div>
</div>
</body>
</html>
<?php
exit;

