<?php
require __DIR__ . '/api/auth.php';
requireRole(['empleado', 'supervisor', 'coordinador', 'admin']);

// Obtener datos del usuario autenticado desde la base de datos
$employee = getSessionUser();
$config = require __DIR__ . '/api/config.php';
$mysqli = @new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if (!$mysqli->connect_errno) {
    $mysqli->set_charset($config['charset']);
    $stmt = $mysqli->prepare('SELECT id, nombre, apellidos, dni_pasaporte, username, fecha_nacimiento, email, institucion, pais, motivo, fecha_inicio, fecha_fin, grupo, foto_url, rol FROM employees WHERE id = ? LIMIT 1');
    if ($stmt && isset($employee['id'])) {
        $id = (int) $employee['id'];
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $employee = $result->fetch_assoc();
        }
        $stmt->close();
    }
    $mysqli->close();
}

// Helper para escapar texto
$safe = function ($key, $fallback = 'ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â') use ($employee) {
    return htmlspecialchars($employee[$key] ?? $fallback, ENT_QUOTES, 'UTF-8');
};
$fotoUrl = !empty($employee['foto_url'])
    ? $employee['foto_url']
    : 'https://i.pravatar.cc/160?u=' . urlencode($employee['email'] ?? ($employee['username'] ?? 'user'));
?>

<!DOCTYPE html>

<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Usuario - Lab Portal</title>
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
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Argentum Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<!-- Navigation / Header -->
<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-10 py-4 sticky top-0 z-50">
<div class="flex items-center gap-4">
<img alt="Logo de la InstituciÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â³n" class="h-10 w-auto object-contain" src="data:image/png;base64,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"/>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Bienvenido/a / Welcome <?= $safe('nombre') ?> <?= $safe('apellidos') ?></h2>
</div>
</header>

<main class="flex-1 flex justify-center py-10 px-4 md:px-0">
<div class="w-full max-w-[860px] flex flex-col gap-6">
<!-- Profile Card -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-100 dark:border-slate-800 p-8">
<div class="flex flex-col gap-8">
<!-- Section: Información Personal -->
<section>
<h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-sm">person</span>
                                Información Personal / Personal Information
                            </h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nombre / Name</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('nombre') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Apellidos / Surnames</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('apellidos') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5 md:col-span-2">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">DNI/Pasaporte / DNI/Passport</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('dni_pasaporte') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Fecha de Nacimiento / Date of Birth</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('fecha_nacimiento') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Email / Email</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('email') ?></p>
</div>
</div>
</section>
<hr class="border-slate-100 dark:border-slate-800"/>
<!-- Section: Origen Académico -->
<section>
<h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-sm">school</span>
                                Origen Académico / Academic Background
                            </h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Institución / Institution</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('institucion') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">País / Country</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('pais') ?></p>
</div>
</div>
</section>
<hr class="border-slate-100 dark:border-slate-800"/>
<!-- Section: Detalles de Incorporación -->
<section>
<h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-sm">science</span>
                                Detalles de la Incorporación / Incorporation Details
                            </h3>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Motivo / Purpose</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('motivo') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Fecha de Inicio / Start Date</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('fecha_inicio') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Fecha de Finalización / End Date</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('fecha_fin') ?></p>
</div>
</div>
<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Grupo / Group</p>
<p class="mt-2 text-base font-semibold text-slate-900 dark:text-slate-100"><?= $safe('grupo') ?></p>
</div>
<div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5 flex flex-col items-center gap-3">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Foto / Photo</p>
<img class="h-28 w-28 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Foto del empleado" />
</div>
</div>
</section>
</div>
</div>
<!-- Cambio de contraseña -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-100 dark:border-slate-800 p-8 mt-6">
    <h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">key</span>
        Actualizar contraseña
    </h3>
    <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">Cambia tu contraseña de acceso. Debe tener al menos 6 caracteres.</p>
    <form id="pwdInlineForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">contraseña actual</label>
            <input id="pwdCurrent" type="password" class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" required>
        </div>
        <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Nueva contraseña</label>
            <input id="pwdNew" type="password" class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" required>
        </div>
        <div class="flex flex-col gap-2">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Confirmar nueva</label>
            <input id="pwdConfirm" type="password" class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-3 focus:ring-primary focus:border-primary" required>
        </div>
        <div class="md:col-span-3 flex items-center gap-3">
            <button id="pwdSubmit" type="submit" class="h-11 px-5 rounded-lg bg-primary text-white font-semibold hover:opacity-90 transition flex items-center gap-2">
                <span id="pwdSubmitText">Guardar nueva contraseña</span>
                <span class="material-symbols-outlined text-sm">check</span>
            </button>
            <span id="pwdMsg" class="text-sm"></span>
        </div>
    </form>
</div>

<!-- Actions -->
<div class="flex flex-col gap-3 mt-4">
<a href="#" onclick="logout(); return false;" class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:opacity-90 transition-opacity">
<span class="truncate">Cerrar sesión / Log out</span>
</a>
</div>
</div>
</main>

<footer class="text-center py-6 text-slate-500 text-sm">
                    © 2026 GESTIUBO. Todos los derechos reservados / All rights reserved.
                </footer>
</div>
</div>
<script>
    function logout() {
        window.location.href = 'api/logout.php';
    }

    // Cambio de contraseña inline
    (() => {
        const form = document.getElementById('pwdInlineForm');
        if (!form) return;
        const msg = document.getElementById('pwdMsg');
        const btn = document.getElementById('pwdSubmit');
        const btnText = document.getElementById('pwdSubmitText');
        const showMsg = (text, ok = false) => {
            msg.textContent = text;
            msg.className = ok ? 'text-sm text-emerald-600' : 'text-sm text-rose-600';
        };
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const current = document.getElementById('pwdCurrent').value.trim();
            const neu = document.getElementById('pwdNew').value.trim();
            const confirm = document.getElementById('pwdConfirm').value.trim();
            if (neu !== confirm) {
                showMsg('Las contraseñas no coinciden.');
                return;
            }
            if (neu.length < 6) {
                showMsg('La nueva contraseña debe tener al menos 6 caracteres.');
                return;
            }
            btn.disabled = true;
            btn.classList.add('opacity-70');
            btnText.textContent = 'Guardando...';
            try {
                const resp = await fetch('api/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ current, new: neu, confirm }),
                });
                const json = await resp.json();
                if (!resp.ok || json.error) {
                    showMsg(json.error || 'No se pudo actualizar la contraseña.');
                } else {
                    showMsg('contraseña actualizada correctamente.', true);
                    form.reset();
                }
            } catch (err) {
                console.error(err);
                showMsg('Error de red al actualizar la contraseña.');
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
                btnText.textContent = 'Guardar nueva contraseña';
            }
        });
    })();
</script>
</body></html>


