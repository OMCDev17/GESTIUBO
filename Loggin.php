<?php
require __DIR__ . '/api/auth.php';

// If already logged in, redirect to the correct dashboard.
$user = getSessionUser();
if ($user) {
    $role = strtolower($user['rol'] ?? '');
    $mapping = [
        'admin' => 'admin.php',
        'supervisor' => 'supervisor.php',
        'coordinador' => 'supervisor.php',
        'seguridad' => 'seguridad.php',
        'empleado' => 'empleado.php',
    ];
    $target = $mapping[$role] ?? 'empleado.php';
    header("Location: $target");
    exit;
}
?>

<!DOCTYPE html>

<html lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Acceso al Laboratorio</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        lab: {
                            primary: '#0f172a',
                            secondary: '#334155',
                            accent: '#5c068c',
                            accentLight: '#f3e8ff',
                            accentHover: '#4a0570',
                            success: '#10b981',
                            warning: '#f59e0b',
                            bg: '#f8fafc'
                        }
                    }
                }
            }
        }
    </script>
<style type="text/tailwindcss">
        body {
            background-color: #f8fafc;
            font-family: 'Argentum Sans', system-ui, -apple-system, sans-serif;
            overscroll-behavior: none;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 md:p-8">
<main class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col relative" data-purpose="main-terminal-card">
<section class="p-8 md:p-16 bg-white flex flex-col relative" data-purpose="login-panel">
<div class="absolute top-8 right-8 flex items-center gap-2 text-sm font-medium">
<span class="text-lab-accent cursor-pointer border-b-2 border-lab-accent pb-0.5" id="lang-es" onclick="switchLanguage('es')">Español</span>
<span class="text-slate-300">|</span>
<span class="text-slate-400 hover:text-lab-primary cursor-pointer transition-colors border-b-2 border-transparent hover:border-slate-400 pb-0.5" id="lang-en" onclick="switchLanguage('en')">English</span>
</div>
<script>
function switchLanguage(lang) {
    const texts = {
        es: {
            title: 'Acceso al Laboratorio',
            desc: 'Ingresa tus credenciales para acceder al panel de control.',
            userLabel: 'Usuario o Correo Electrónico',
            userPlaceholder: 'ej. empleado_123',
            passLabel: 'Contraseña',
            forgot: '¿Olvidaste tu contraseña?',
            remember: 'Recordar mi sesión',
            signIn: 'Iniciar Sesión',
            noAccount: '¿No tienes una cuenta?',
            createAccount: 'Crear Cuenta de Empleado',
            alertForgot: 'Se ha enviado un correo electrónico de recuperación a su dirección registrada.',
            alertCreate: 'Solicitud de registro enviada al administrador.',
            signingIn: 'Iniciando sesión...'
        },
        en: {
            title: 'Laboratory Access',
            desc: 'Enter your credentials to access the control panel.',
            userLabel: 'Username or Email',
            userPlaceholder: 'e.g. employee_123',
            passLabel: 'Password',
            forgot: 'Forgot password?',
            remember: 'Remember session',
            signIn: 'Sign In',
            noAccount: "Don't have an account?",
            createAccount: 'Create Employee Account',
            alertForgot: 'A recovery email has been sent to your registered address.',
            alertCreate: 'Registration request sent to the administrator.',
            signingIn: 'Signing in...'
        }
    };

    const t = texts[lang];
    
    // Update Elements
    document.querySelector('h2').textContent = t.title;
    document.querySelector('h2 + p').textContent = t.desc;
    document.querySelector('label[for="username"]').textContent = t.userLabel;
    document.getElementById('username').placeholder = t.userPlaceholder;
    document.querySelector('label[for="password"]').textContent = t.passLabel;
    const forgotLink = document.getElementById('forgotLink');
    if (forgotLink) {
        forgotLink.textContent = t.forgot;
        forgotLink.onclick = () => { alert(t.alertForgot); return false; };
    }
    const rememberLabel = document.querySelector('label[for="remember"]');
    if (rememberLabel) rememberLabel.textContent = t.remember;
    const signInBtn = document.querySelector('button[type="submit"] span:first-child');
    if (signInBtn) signInBtn.textContent = t.signIn;
    const loginButtonText = document.getElementById('loginButtonText');
    if (loginButtonText) loginButtonText.textContent = t.signIn;
    
    const footerP = document.querySelector('.mt-12.pt-8 p');
    if (footerP) footerP.innerHTML = `${t.noAccount} <a class="text-lab-accent font-bold hover:underline" href="Formulario.php">${t.createAccount}</a>`;

    // Update Language UI Toggle
    const esBtn = document.getElementById('lang-es');
    const enBtn = document.getElementById('lang-en');

    const setActive = (btn) => {
        if (!btn) return;
        btn.classList.add('text-lab-accent', 'border-lab-accent');
        btn.classList.remove('text-slate-400', 'border-transparent');
    };

    const setInactive = (btn) => {
        if (!btn) return;
        btn.classList.add('text-slate-400', 'border-transparent');
        btn.classList.remove('text-lab-accent', 'border-lab-accent');
    };

    if (lang === 'es') {
        setActive(esBtn);
        setInactive(enBtn);
    } else {
        setActive(enBtn);
        setInactive(esBtn);
    }
}

// Ensure the UI starts with the correct language highlighted
switchLanguage('es');
</script>
<div class="mt-8 mb-10 text-center">
<img alt="Universidad de La Laguna Logo" class="h-auto w-60 mx-auto mb-8" src="data:image/png;base64,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"/>
<h2 class="text-3xl font-bold text-lab-primary mb-2">Acceso al Laboratorio</h2>
<p class="text-slate-500">Ingresa tus credenciales para acceder al panel de control.</p>
</div>
<form id="loginForm" class="space-y-6 max-w-md mx-auto w-full" onsubmit="handleLogin(event)">
<div class="space-y-2">
<label class="text-sm font-semibold text-slate-700 block" for="username">Usuario o Correo Electrónico</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">person</span>
<input class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-lab-accent focus:border-transparent outline-none transition-all placeholder:text-slate-400" id="username" name="username" placeholder="ej. empleado_123" type="text"/>
</div>
</div>
<div class="space-y-2">
<div class="flex justify-between items-center">
<label class="text-sm font-semibold text-slate-700" for="password">Contraseña</label>
<a id="forgotLink" class="text-xs font-bold text-lab-accent hover:text-lab-accentHover transition-colors" href="Recuperacion.html">¿Olvidaste tu contraseña?</a>
</div>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
<input class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-lab-accent focus:border-transparent outline-none transition-all placeholder:text-slate-400" id="password" name="password" placeholder="••••••••" type="password"/>
</div>
</div>
<div class="flex items-center">
<input class="w-4 h-4 text-lab-accent border-slate-300 rounded focus:ring-lab-accent" id="remember" type="checkbox"/>
<label class="ml-2 text-sm text-slate-600 cursor-pointer" for="remember">Recordar mi sesión</label>
</div>
<button id="loginButton" class="w-full py-4 bg-lab-accent text-white font-bold rounded-2xl hover:bg-lab-accentHover shadow-lg shadow-purple-100 transition-all flex items-center justify-center gap-2 group" type="submit">
<span id="loginButtonText">Iniciar Sesión</span>
<span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">login</span>
</button>
<p id="loginError" class="text-sm text-red-600 hidden mt-2"></p>
</form>
<div class="mt-12 pt-8 border-t border-slate-100 text-center">
<p class="text-slate-500 text-sm">
                    ¿No tienes una cuenta? 
                    <a class="text-lab-accent font-bold hover:underline" href="Formulario.php">Crear Cuenta de Empleado</a>
</p>
</div>
</section>
</main>
<script>
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const loginButtonText = document.getElementById('loginButtonText');
    const loginError = document.getElementById('loginError');

    function setLoading(isLoading) {
        if (!loginButton) return;
        loginButton.disabled = isLoading;
        loginButton.classList.toggle('opacity-80', isLoading);
        loginButton.classList.toggle('cursor-not-allowed', isLoading);
        loginButtonText.textContent = isLoading ? 'Iniciando sesión...' : 'Iniciar Sesión';
    }

    async function handleLogin(event) {
        event.preventDefault();
        loginError.classList.add('hidden');

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        if (!username || !password) {
            loginError.textContent = 'Por favor, introduce usuario y contraseña.';
            loginError.classList.remove('hidden');
            return;
        }

        setLoading(true);

        try {
            const resp = await fetch('api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ username, password }),
            });
            const result = await resp.json();

            if (!resp.ok) {
                loginError.textContent = result.error || 'Usuario o contraseña incorrectos.';
                loginError.classList.remove('hidden');
                return;
            }

            // Save session in localStorage for other pages to use.
            window.localStorage.setItem('sessionUser', JSON.stringify(result.user));

            // Redirect based on role (from backend)
            if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                window.location.href = 'empleado.php';
            }
        } catch (err) {
            console.error(err);
            loginError.textContent = 'Error de red. Intenta de nuevo.';
            loginError.classList.remove('hidden');
        } finally {
            setLoading(false);
        }
    }

</script>

</body></html>

