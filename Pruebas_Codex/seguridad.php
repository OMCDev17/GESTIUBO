<?php
require __DIR__ . '/api/auth.php';
requireRole(['seguridad', 'admin']);

$user = getSessionUser();
$fullName = $user ? htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) : '';
?>

<!DOCTYPE html>

<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Seguridad - Lab Portal</title>
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
<img alt="Logo de la Institución" class="h-10 w-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Seguridad / Security Dashboard</h2>
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
<h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Visor de empleados por grupo</h1>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Aquí puedes revisar rápidamente el estado de los contratos de cada empleado.</p>
</div>

<div id="groupsContainer" class="flex flex-col gap-8"></div>

<!-- Histórico eliminado para vista de seguridad -->

</div>
</main>

<footer class="text-center py-6 text-slate-500 text-sm">
                    © 2026 Laboratory Academic Management System. Todos los derechos reservados / All rights reserved.
                </footer>
</div>
</div>

<script>
    let employees = [];

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        return d.toLocaleDateString(undefined, { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    function isActiveContract(fechaFin) {
        const today = new Date();
        const end = new Date(fechaFin);
        return end >= new Date(today.getFullYear(), today.getMonth(), today.getDate());
    }

    async function fetchEmployees() {
        const res = await fetch('api/employees.php', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('No se pudieron cargar los empleados');
        const json = await res.json();
        employees = (json.employees || []).map((e) => ({
            ...e,
            grupo: (e.grupo || '').toUpperCase(),
            foto: e.foto_url || 'https://i.pravatar.cc/160?u=' + encodeURIComponent(e.email || e.username || e.id || Math.random()),
        }));
    }

    function render() {
        const container = document.getElementById('groupsContainer');
        const activeEmployees = employees.filter((e) => isActiveContract(e.fecha_fin));

        if (activeEmployees.length === 0) {
            container.innerHTML = `
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center">
                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">No hay contratos activos</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Todos los empleados tienen su contrato caducado o no hay datos disponibles.</p>
                </div>
            `;
            return;
        }

        const groups = Array.from(new Set(activeEmployees.map(e => e.grupo))).sort();

        groups.forEach((group) => {
            const groupSection = document.createElement('section');
            groupSection.className = 'space-y-4';

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-3';
            header.innerHTML = `
                <h2 class="text-lg font-bold text-primary">Grupo ${group}</h2>
                <span class="text-sm text-slate-500 dark:text-slate-400">${activeEmployees.filter(e => e.grupo === group).length} empleados</span>
            `;

            const list = document.createElement('div');
            list.className = 'grid gap-4';

            activeEmployees
                .filter(e => e.grupo === group)
                .sort((a, b) => b.apellidos.localeCompare(a.apellidos) || b.nombre.localeCompare(a.nombre))
                .forEach((emp) => {
                    const active = isActiveContract(emp.fecha_fin);
                    const card = document.createElement('div');
                    card.className = 'bg-white dark:bg-slate-900 rounded-xl shadow border border-slate-100 dark:border-slate-800 p-4 flex items-center gap-4';
                    card.innerHTML = `
                        <img class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="${emp.foto}" alt="${emp.nombre} ${emp.apellidos}" />
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-slate-100 truncate">${emp.nombre} ${emp.apellidos}</p>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <div>
                                    <p class="font-semibold text-[11px] uppercase tracking-widest">Inicio</p>
                                    <p>${formatDate(emp.fecha_inicio)}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-[11px] uppercase tracking-widest">Fin</p>
                                    <p>${formatDate(emp.fecha_fin)}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Contrato</span>
                            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold ${active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-rose-50 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200'}">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1, 'wght' 700;">${active ? 'check_circle' : 'cancel'}</span>
                                ${active ? 'Activo' : 'Caducado'}
                            </span>
                        </div>
                    `;

                    list.appendChild(card);
                });

            groupSection.appendChild(header);
            groupSection.appendChild(list);
            container.appendChild(groupSection);
        });

    }

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            await fetchEmployees();
            render();
        } catch (e) {
            console.error(e);
            const container = document.getElementById('groupsContainer');
            container.innerHTML = '<p class="text-sm text-red-600">No se pudieron cargar los empleados.</p>';
        }
    });
</script>

<script>
    function logout() {
        window.location.href = 'api/logout.php';
    }
</script>
</body></html>
