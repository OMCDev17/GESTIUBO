<?php
require __DIR__ . '/api/auth.php';
$token = trim($_GET['token'] ?? '');
$isPasswordResetToken = $token !== '';
$tokenError = '';
$fullName = '';

if (!$isPasswordResetToken) {
    requireLogin();
    $user = getSessionUser();
    $fullName = $user ? htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) : '';
} else {
    $config = require __DIR__ . '/api/config.php';
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if ($mysqli->connect_errno) {
        $tokenError = 'Error de conexión. Vuelve a intentarlo.';
    } else {
        $mysqli->set_charset($config['charset']);
        $stmt = $mysqli->prepare('SELECT pr.id, u.email FROM password_resets pr JOIN employees u ON pr.user_id = u.id WHERE pr.token = ? AND pr.expires_at > NOW() LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            $tokenError = 'Token inválido o expirado';
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Cambiar contraseña - Instituto de Bio-Orgánica Antonio González</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<style>
        body { font-family: 'Argentum Sans', sans-serif; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100">
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">lock</span>
            <div>
                <h1 class="text-2xl font-bold">Cambiar contraseña</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Actualiza tu contraseña de manera segura.</p>
                <?php if ($fullName): ?>
                    <p class="text-xs text-slate-400 mt-1">Sesión: <?php echo $fullName; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div id="alert" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

        <?php if ($tokenError): ?>
            <div class="rounded-xl border border-rose-200 text-rose-700 bg-rose-50 px-4 py-3 text-sm">
                <?php echo htmlspecialchars($tokenError); ?>
            </div>
            <div class="text-center">
                <a href="Recuperacion.html" class="text-sm text-primary hover:underline">Volver a recuperar contraseña</a>
            </div>
        <?php else: ?>
            <form id="pwdForm" class="space-y-4">
                <?php if (!$isPasswordResetToken): ?>
                    <div>
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Contraseña actual</label>
                        <input id="currentPwd" type="password" class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" required>
                    </div>
                <?php endif; ?>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nueva contraseña</label>
                    <input id="newPwd" type="password" class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" required>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Confirmar nueva contraseña</label>
                    <input id="confirmPwd" type="password" class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" required>
                </div>
                <button id="submitBtn" type="submit" class="w-full h-12 rounded-xl bg-primary text-white font-semibold hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span id="btnText"><?php echo $isPasswordResetToken ? 'Restablecer contraseña' : 'Actualizar contraseña'; ?></span>
                </button>
            </form>
        <?php endif; ?>
        <div class="text-center">
            <a href="<?php echo $isPasswordResetToken ? 'Loggin.php' : 'empleado.php'; ?>" class="text-sm text-primary hover:underline"><?php echo $isPasswordResetToken ? 'Volver al inicio de sesión' : 'Volver a mi perfil'; ?></a>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('pwdForm');
    const alertBox = document.getElementById('alert');
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const isRecovery = <?php echo json_encode($isPasswordResetToken); ?>;
    const token = <?php echo json_encode($token); ?>;

    const showAlert = (msg, ok = false) => {
        alertBox.classList.remove('hidden');
        alertBox.textContent = msg;
        alertBox.className = `rounded-xl border px-4 py-3 text-sm ${ok ? 'border-emerald-200 text-emerald-700 bg-emerald-50' : 'border-rose-200 text-rose-700 bg-rose-50'}`;
    };

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const current = isRecovery ? '' : document.getElementById('currentPwd').value.trim();
            const neu = document.getElementById('newPwd').value.trim();
            const confirm = document.getElementById('confirmPwd').value.trim();

            if (neu !== confirm) {
                showAlert('Las contraseñas no coinciden.');
                return;
            }
            if (neu.length < 6) {
                showAlert('La contraseña debe tener al menos 6 caracteres.');
                return;
            }

            if (!isRecovery && current.length < 1) {
                showAlert('Ingresa tu contraseña actual.');
                return;
            }

            btn.disabled = true;
            btn.classList.add('opacity-80');
            btnText.textContent = isRecovery ? 'Restableciendo...' : 'Actualizando...';

            try {
                const endpoint = isRecovery ? `api/reset_password.php?token=${encodeURIComponent(token)}` : 'api/change_password.php';
                const payload = isRecovery ? { new: neu, confirm } : { current, new: neu, confirm };

                const resp = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                const json = await resp.json();
                if (!resp.ok || json.error) {
                    showAlert(json.error || 'No se pudo actualizar la contraseña.');
                } else {
                    showAlert(isRecovery ? 'Contraseña restablecida correctamente.' : 'Contraseña actualizada correctamente.', true);
                    form.reset();
                    if (isRecovery) {
                        setTimeout(() => { window.location.href = 'Loggin.php'; }, 3000);
                    }
                }
            } catch (err) {
                console.error(err);
                showAlert('Error de red al actualizar la contraseña.');
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-80');
                btnText.textContent = isRecovery ? 'Restablecer contraseña' : 'Actualizar contraseña';
            }
        });
    }
</script>
</body></html>

