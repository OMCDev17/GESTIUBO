<!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Registro Exitoso - Instituto de Bio-Orgánica Antonio González</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">
<div class="relative flex h-full min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<!-- Top Logo Area -->
<div class="flex flex-col items-center pt-10 px-6">
<div class="max-w-[360px] mb-6">
<img alt="Logo universidad" class="w-full h-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
</div>
</div>
<!-- Main Content Area -->
<main class="flex-1 flex items-center justify-center p-6">
<div class="layout-content-container flex flex-col max-w-[480px] w-full bg-white dark:bg-slate-900/50 p-8 rounded-2xl shadow-sm border border-primary/5">
<!-- Visual Feedback -->
<div class="flex flex-col items-center mb-8">
<div class="w-full aspect-video bg-primary/5 rounded-xl mb-8 flex items-center justify-center overflow-hidden border border-primary/10">
<div class="relative">
<span class="material-symbols-outlined text-primary text-7xl select-none" style="font-variation-settings: 'FILL' 1, 'wght' 700;">check_circle</span>
</div>
</div>
<!-- Main Message -->
<h2 class="text-slate-900 dark:text-slate-100 tracking-tight text-2xl md:text-3xl font-bold leading-tight text-center mb-2">
                            Registro completado con éxito
                        </h2>
<p class="text-slate-500 dark:text-slate-400 text-lg font-medium leading-normal text-center">
                            You have signed up successfully
                        </p>
</div>
<!-- Informational Text -->
<div class="text-center mb-8">
<p class="text-slate-500 dark:text-slate-400 text-sm">
                            Gracias por registrarte en el portal de laboratorio. Ya puedes iniciar sesión con tus credenciales.
                        </p>
<p class="text-slate-400 dark:text-slate-500 text-xs mt-1 italic">
                            Thank you for registering at Instituto de Bio-Orgánica Antonio González. You can now log in with your credentials.
                        </p>
<p class="text-slate-500 dark:text-slate-400 text-sm mt-4">
                            Se le redirigirá automáticamente a la página de inicio en <span id="countdown" class="font-bold text-primary">10</span> segundos.
                        </p>
</div>
<!-- Actions -->
<div class="flex flex-col gap-3">
<a href="Loggin.php" class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:opacity-90 transition-opacity">
<span class="truncate">Ir a iniciar sesión / Go to Login</span>
</a>
<div class="flex justify-center mt-4">
<div class="flex items-center gap-2 text-slate-400">
<span class="material-symbols-outlined text-sm">lock</span>
<span class="text-[10px] uppercase tracking-widest font-bold">Secure Registration</span>
</div>
</div>
</div>
</div>
</main>
<!-- Footer / Copyright -->
<footer class="py-6 px-10 border-t border-primary/5 text-center">
<p class="text-slate-400 text-xs font-medium uppercase tracking-widest">
                    © 2026 Instituto de Bio-Orgánica Antonio González. All rights reserved.
                </p>
</footer>
</div>
</div>
<script>
    (() => {
        const countdownEl = document.getElementById('countdown');
        let remaining = 10;
        const tick = () => {
            remaining -= 1;
            if (countdownEl) countdownEl.textContent = remaining.toString();
            if (remaining <= 0) {
                window.location.href = 'Loggin.php';
            } else {
                setTimeout(tick, 1000);
            }
        };
        setTimeout(tick, 1000);
    })();
</script>
</body></html>


