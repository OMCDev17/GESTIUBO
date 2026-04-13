<?php
// ============================================================================
// Página de Validación de Código y Restablecimiento de Contraseña
// ============================================================================

session_start();

$config = require __DIR__ . '/api/config.php';
$step = isset($_GET['step']) ? $_GET['step'] : 1; // Paso 1: ingresa código, Paso 2: nueva contraseña
$validCode = false;
$codeError = '';
$resetCode = '';
$resetId = 0;
$userId = 0;

// Si viene de POST, validar el código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 1) {
    $resetCode = trim($_POST['reset_code'] ?? '');
    
    if (empty($resetCode)) {
        $codeError = 'El código es requerido';
    } elseif (strlen($resetCode) !== 4 || !ctype_digit($resetCode)) {
        $codeError = 'El código debe ser de 4 dígitos';
    } else {
        // Verificar el código en la base de datos
        $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
        if ($mysqli->connect_errno) {
            $codeError = 'Error de conexión. Intenta de nuevo.';
        } else {
            $mysqli->set_charset($config['charset']);
            $stmt = $mysqli->prepare('SELECT id, user_id FROM password_resets WHERE code = ? AND expires_at > NOW() LIMIT 1');
            $stmt->bind_param('s', $resetCode);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            $mysqli->close();
            
            if ($row) {
                $validCode = true;
                $resetId = intval($row['id']);
                $userId = intval($row['user_id']);
                
                // Guardar en sesión para que persista
                $_SESSION['reset_id'] = $resetId;
                $_SESSION['user_id'] = $userId;
                $_SESSION['reset_code'] = $resetCode;
                
                // Redirigir al paso 2
                header('Location: resetear_contraseña.php?step=2');
                exit;
            } else {
                $codeError = 'El código es inválido o ha expirado';
            }
        }
    }
} else if ($step == 2) {
    // Paso 2: recuperar valores de sesión
    if (isset($_SESSION['reset_id'], $_SESSION['user_id'], $_SESSION['reset_code'])) {
        $validCode = true;
        $resetId = intval($_SESSION['reset_id']);
        $userId = intval($_SESSION['user_id']);
        $resetCode = $_SESSION['reset_code'];
    } else {
        // Si no hay datos en sesión, redirige al inicio
        header('Location: resetear_contraseña.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $validCode ? 'Nueva Contraseña' : 'Validar Código'; ?> - Instituto de Bio-Orgánica Antonio González</title>
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
                },
            },
        }
    </script>
    <style>body { font-family: 'Argentum Sans', sans-serif; }</style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100">
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-6">
    <!-- Logo fuera del cuadro -->
    <div class="mb-8">
        <img src="imagenes/instituto-biorganica-agonzalez-original.png" alt="Logo Instituto" class="h-16 w-auto object-contain" />
    </div>
    
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-8 space-y-6">
        
        <!-- Header -->
        <div class="flex items-center justify-end gap-3">
            <div class="flex gap-2">
                <button id="lang-es" type="button" class="text-sm font-medium text-primary underline">ES</button>
                <button id="lang-en" type="button" class="text-sm font-medium text-slate-500 hover:text-primary">EN</button>
            </div>
        </div>
        
        <div class="mt-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary grid place-items-center">
                <span class="text-lg">?</span>
            </div>
            <div>
                <h1 id="mainTitle" class="text-2xl font-bold">
                    <?php echo $validCode ? 'Crear nueva contraseña' : 'Validar código'; ?>
                </h1>
                <p id="mainDesc" class="text-sm text-slate-500 dark:text-slate-400">
                    <?php echo $validCode ? 'Establece una contraseña segura para Gestiubo.' : 'Ingresa el código de 4 dígitos enviado a tu correo.'; ?>
                </p>
            </div>
        </div>

        <!-- Progreso -->
        <div class="flex items-center gap-2 text-xs">
            <div class="flex-1 h-1 rounded-full" style="background-color: <?php echo $validCode ? '#5c068c' : '#5c068c'; ?>"></div>
            <span class="text-slate-500 dark:text-slate-400">Paso <?php echo $validCode ? '2' : '1'; ?> de 2</span>
            <div class="flex-1 h-1 rounded-full" style="background-color: <?php echo $validCode ? '#5c068c' : '#d1d5db'; ?>"></div>
        </div>

        <div id="alert" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

        <?php if ($validCode): ?>
            <!-- Paso 2: Ingresar nueva contraseña -->
            <form id="pwdForm" class="space-y-4" action="api/validate_reset_code.php" method="POST">
                <input type="hidden" name="reset_id" value="<?php echo htmlspecialchars($resetId); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($resetCode); ?>">
                
                <div>
                    <label id="labelNewPassword" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nueva contraseña</label>
                    <input id="newPwd" name="newPwd" type="password" class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" placeholder="Mínimo 4 caracteres" required>
                </div>
                
                <div>
                    <label id="labelConfirmPassword" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Confirmar contraseña</label>
                    <input id="confirmPwd" name="confirmPwd" type="password" class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" placeholder="Repite tu contraseña" required>
                </div>

                <div id="passwordHelp" class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 flex gap-2 text-sm text-blue-800 dark:text-blue-300">
                    <span class="text-blue-600 dark:text-blue-300 flex-shrink-0 font-semibold">i</span>
                    <span id="passwordHelpText">La contraseña debe tener al menos 4 caracteres.</span>
                </div>
                
                <button id="submitBtn" type="submit" class="w-full h-12 rounded-xl bg-primary text-white font-semibold hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span id="btnText">Restablecer contraseña</span>
                </button>
            </form>

            <div class="text-center pt-4 border-t border-slate-200 dark:border-slate-700">
                <form method="GET" style="display:inline;">
                    <button type="submit" name="step" value="1" class="text-sm text-primary hover:underline">← Volver e ingresa otro código</button>
                </form>
            </div>

        <?php else: ?>
            <!-- Paso 1: Ingresar código -->
            <form method="POST" class="space-y-4">
                <div>
                    <label id="labelCode" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Código de verificación</label>
                    <input id="inputCode" type="text" 
                           name="reset_code" 
                           class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary text-center text-3xl tracking-widest font-mono" 
                           placeholder="0000" 
                           maxlength="4" 
                           inputmode="numeric"
                           pattern="[0-9]{4}"
                           required 
                           autofocus>
                </div>

                <?php if ($codeError): ?>
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-300">
                        <?php echo htmlspecialchars($codeError); ?>
                    </div>
                <?php endif; ?>

                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 flex gap-2 text-sm text-blue-800 dark:text-blue-300">
                    <span class="material-symbols-outlined flex-shrink-0 text-base">info</span>
                    <span>Ingresa el código de 4 dígitos que recibiste en tu correo electrónico.</span>
                </div>

                <button id="validateBtn" type="submit" class="w-full h-12 rounded-xl bg-primary text-white font-semibold hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span id="validateBtnText">Validar código</span>
                </button>
            </form>

            <div class="text-center pt-4 border-t border-slate-200 dark:border-slate-700 text-sm">
                <p id="noCodeText" class="text-slate-600 dark:text-slate-400 mb-2">¿No recibiste el código?</p>
                <a id="newCodeText" href="Recuperacion.html" class="text-primary hover:underline">Solicitar un nuevo código →</a>
            </div>
        <?php endif; ?>

        <div class="text-center">
            <a href="Loggin.php" class="text-sm text-slate-500 dark:text-slate-400 hover:underline">Volver al inicio de sesión</a>
        </div>
    </div>
</div>

<script>
    // Toast reutilizable con estilo del panel (morado)
    const toastHost = (() => {
        const existing = document.getElementById('toastHost');
        if (existing) return existing;
        const el = document.createElement('div');
        el.id = 'toastHost';
        el.className = 'fixed bottom-4 right-4 flex flex-col gap-3 z-[9999] pointer-events-none';
        document.addEventListener('DOMContentLoaded', () => document.body.appendChild(el));
        return el;
    })();
    function showToast(message, variant = 'info') {
        const palette = { success: 'bg-primary text-white', error: 'bg-primary text-white', info: 'bg-primary text-white' };
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto min-w-[240px] max-w-xs rounded-lg shadow-lg px-4 py-3 text-sm font-semibold ${palette[variant] || palette.info}`;
        toast.textContent = message;
        toastHost.appendChild(toast);
        setTimeout(() => toast.remove(), 3200);
    }

    <?php if ($validCode): ?>
    const form = document.getElementById('pwdForm');
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const neu = document.getElementById('newPwd').value.trim();
            const confirm = document.getElementById('confirmPwd').value.trim();

            if (neu !== confirm) {
                showToast(window.passwordAlerts?.alertMismatch || 'Las contraseñas no coinciden.', 'error');
                return;
            }
            if (neu.length < 4) {
                showToast(window.passwordAlerts?.alertLength || 'La contraseña debe tener al menos 4 caracteres.', 'error');
                return;
            }

            btn.disabled = true;
            btn.classList.add('opacity-80');
            btnText.textContent = window.passwordAlerts?.alertSuccess || 'Restableciendo...';

            try {
                const fd = new FormData(form);
                // Debug: mostrar datos que se envían
                console.log('Datos enviados:');
                for (let pair of fd.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                
                const res = await fetch('api/validate_reset_code.php', { method: 'POST', body: fd });
                const data = await res.json();
                console.log('Respuesta del servidor:', data);

                if (data.success) {
                    showToast(window.passwordAlerts?.alertSuccess || 'Contraseña restablecida exitosamente', 'success');
                    window.location.href = 'Loggin.php';
                } else {
                    showToast(data.error || window.passwordAlerts?.alertError || 'Error al restablecer la contraseña', 'error');
                    btn.disabled = false;
                    btn.classList.remove('opacity-80');
                    btnText.textContent = window.passwordAlerts?.btnText || 'Restablecer contraseña';
                }
            } catch (err) {
                console.error(err);
                showToast('Error al procesar la solicitud', 'error');
                btn.disabled = false;
                btn.classList.remove('opacity-80');
                btnText.textContent = 'Restablecer contraseña';
            }
        });
    }
    <?php else: ?>
    // Auto-format input para código de 4 dígitos
    const codeInput = document.querySelector('input[name="reset_code"]');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);
        });
    }
    <?php endif; ?>

    const translations = {
        es: {
            mainTitleValid: 'Crear nueva contraseña',
            mainTitleInvalid: 'Validar código',
            mainDescValid: 'Establece una contraseña segura para Gestiubo.',
            mainDescInvalid: 'Ingresa el código de 4 dígitos enviado a tu correo.',
            labelNewPassword: 'Nueva contraseña',
            labelConfirmPassword: 'Confirmar contraseña',
            passwordHelp: 'La contraseña debe tener al menos 4 caracteres.',
            btnText: 'Restablecer contraseña',
            labelCode: 'Código de verificación',
            validateBtnText: 'Validar código',
            noCodeText: '¿No recibiste el código?',
            newCodeText: 'Solicitar un nuevo código →',
            alertMismatch: 'Las contraseñas no coinciden.',
            alertLength: 'La contraseña debe tener al menos 4 caracteres.',
            alertSuccess: 'Contraseña restablecida exitosamente',
            alertError: 'Error al restablecer la contraseña',
        },
        en: {
            mainTitleValid: 'Create new password',
            mainTitleInvalid: 'Verify code',
            mainDescValid: 'Set a secure password for Gestiubo.',
            mainDescInvalid: 'Enter the 4-digit code sent to your email.',
            labelNewPassword: 'New password',
            labelConfirmPassword: 'Confirm password',
            passwordHelp: 'Password must be at least 4 characters.',
            btnText: 'Reset password',
            labelCode: 'Verification code',
            validateBtnText: 'Verify code',
            noCodeText: 'Did not receive the code?',
            newCodeText: 'Request a new code →',
            alertMismatch: 'Passwords do not match.',
            alertLength: 'Password must be at least 4 characters.',
            alertSuccess: 'Password reset successfully',
            alertError: 'Error resetting password',
        }
    };

    function setLanguage(lang) {
        const isValid = <?php echo json_encode($validCode); ?>;
        document.getElementById('lang-es').classList.toggle('text-primary', lang === 'es');
        document.getElementById('lang-en').classList.toggle('text-primary', lang === 'en');

        const map = translations[lang] || translations.es;
        document.getElementById('mainTitle').textContent = isValid ? map.mainTitleValid : map.mainTitleInvalid;
        document.getElementById('mainDesc').textContent = isValid ? map.mainDescValid : map.mainDescInvalid;

        const labelNewPassword = document.getElementById('labelNewPassword');
        if (labelNewPassword) labelNewPassword.textContent = map.labelNewPassword;
        const labelConfirmPassword = document.getElementById('labelConfirmPassword');
        if (labelConfirmPassword) labelConfirmPassword.textContent = map.labelConfirmPassword;

        const passwordHelpText = document.getElementById('passwordHelpText');
        if (passwordHelpText) passwordHelpText.textContent = map.passwordHelp;

        const btnTextEl = document.getElementById('btnText');
        if (btnTextEl) btnTextEl.textContent = map.btnText;

        const labelCode = document.getElementById('labelCode');
        if (labelCode) labelCode.textContent = map.labelCode;

        const validateBtnText = document.getElementById('validateBtnText');
        if (validateBtnText) validateBtnText.textContent = map.validateBtnText;

        const noCodeText = document.getElementById('noCodeText');
        if (noCodeText) noCodeText.textContent = map.noCodeText;

        const newCodeText = document.getElementById('newCodeText');
        if (newCodeText) newCodeText.textContent = map.newCodeText;

        // Actualiza mensajes de alerta si se usan en forms js
        window.passwordAlerts = map;
    }

    document.getElementById('lang-es').addEventListener('click', () => setLanguage('es'));
    document.getElementById('lang-en').addEventListener('click', () => setLanguage('en'));
    setLanguage('es');

</script>
</body>
</html>

