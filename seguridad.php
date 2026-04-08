<?php
require __DIR__ . '/api/auth.php';
requireRole(['seguridad', 'admin']);

$user = getSessionUser();
$fullName = $user ? htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) : '';
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Seguridad - Lab Portal</title>
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
        body { font-family: 'Argentum Sans', sans-serif; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
    <div class="layout-container flex h-full grow flex-col">
        <!-- Navigation / Header -->
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 md:px-10 py-4 sticky top-0 z-50">
            <div class="flex items-center gap-3 flex-wrap">
                <img alt="Logo de la Institución" class="h-10 w-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
                <h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Seguridad</h2>
                <?php if ($fullName): ?>
                    <span class="text-sm text-slate-500 dark:text-slate-400 pl-4">Hola, <?php echo $fullName; ?></span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <a href="#" onclick="logout(); return false;" class="text-sm font-semibold text-primary hover:underline">Cerrar sesión / Log out</a>
            </div>
        </header>

        <main class="flex-1 flex justify-center py-10 px-4 md:px-0">
            <div class="w-full max-w-[980px] flex flex-col gap-6">
                <div class="text-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Buscador de usuarios</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Busca por nombre o apellidos para ver el estado de la estancia.</p>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-xl shadow border border-slate-100 dark:border-slate-800 p-5 flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input id="searchInput" type="text" placeholder="Buscar por nombre o apellidos" class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <button id="searchButton" class="rounded-lg bg-primary text-white font-semibold px-4 py-2 text-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50">Buscar</button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Por privacidad no se muestran datos hasta realizar una búsqueda.</p>
                </div>

                <div id="resultsContainer" class="flex flex-col gap-4"></div>
            </div>
        </main>

        <footer class="text-center py-6 text-slate-500 text-sm">
            © 2026 GESTIUBO. Todos los derechos reservados / All rights reserved.
        </footer>
    </div>
</div>

<script>
    // Toast reutilizable con estética del panel (morado)
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

    let employees = [];

    const maskDni = (value) => {
        const str = String(value ?? '').trim();
        if (!str) return '—';
        if (str.length <= 4) return `**${str.slice(0, 1)}***`;
        const middle = str.slice(2, -2) || '***';
        return `**${middle}**`;
    };

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        if (Number.isNaN(d.getTime())) return '';
        return d.toLocaleDateString(undefined, { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    function formatEndDate(dateStr) {
        const isIndef = String(dateStr).split('T')[0] === '2100-01-01';
        return isIndef ? 'Personal indefinido' : formatDate(dateStr);
    }

    async function fetchEmployees() {
        const res = await fetch('api/employees.php', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('No se pudieron cargar los usuarios');
        const json = await res.json();
        employees = (json.employees || []).map((e) => ({
            ...e,
            horario: typeof e.horario !== 'undefined' ? Number(e.horario) : 1,
            foto: e.foto_url || 'https://i.pravatar.cc/160?u=' + encodeURIComponent(e.email || e.username || e.id || Math.random()),
        }));
    }

    function formatHorario(value) {
        return Number(value) === 0 ? 'Solo lectivo' : 'Completo';
    }

    function renderResults(results) {
        const container = document.getElementById('resultsContainer');
        container.innerHTML = '';

        if (!results || results.length === 0) {
            container.innerHTML = `
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Sin resultados. Prueba con otro nombre.</p>
                </div>
            `;
            return;
        }

        results.forEach((emp) => {
            const isSoloLectivo = Number(emp.horario) === 0;
            const badgeClass = isSoloLectivo
                ? 'text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/40'
                : 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30';
            const card = document.createElement('div');
            card.className = 'bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-800 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5';
            card.innerHTML = `
                <div class="flex items-center gap-4 min-w-0">
                    <img class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-sm" src="${emp.foto}" alt="${emp.nombre} ${emp.apellidos}" />
                    <div class="min-w-0">
                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-100 truncate">${emp.nombre} ${emp.apellidos}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Grupo: ${emp.grupo || '—'}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">DNI/Pasaporte: ${maskDni(emp.dni_pasaporte)}</p>
                        <p class="mt-1 inline-flex items-center gap-2 text-xs font-semibold ${badgeClass} px-2 py-1 rounded-full">
                            <span class="material-symbols-outlined text-base">schedule</span>${formatHorario(emp.horario)}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-slate-600 dark:text-slate-300">
                    <div>
                        <p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">Inicio</p>
                        <p>${formatDate(emp.fecha_inicio)}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">Fin</p>
                        <p>${formatEndDate(emp.fecha_fin)}</p>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function performSearch() {
        const term = document.getElementById('searchInput').value.trim().toLowerCase();
        if (!term) {
            document.getElementById('resultsContainer').innerHTML = '';
            return;
        }
        const results = employees.filter((e) => {
            const full = `${e.nombre || ''} ${e.apellidos || ''}`.toLowerCase();
            return full.includes(term);
        });
        renderResults(results);
    }

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            await fetchEmployees();
        } catch (e) {
            console.error(e);
            const container = document.getElementById('resultsContainer');
            container.innerHTML = '<p class="text-sm text-red-600">No se pudieron cargar los usuarios.</p>';
            return;
        }

        document.getElementById('searchButton').addEventListener('click', (e) => {
            e.preventDefault();
            performSearch();
        });
        document.getElementById('searchInput').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    });
</script>

<script>
    function logout() {
        window.location.href = 'api/logout.php';
    }
</script>
</body>
</html>
