<?php
require __DIR__ . '/api/auth.php';
requireRole('admin');

$user = getSessionUser();
$fullName = $user ? htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) : '';
?>

<!DOCTYPE html>

<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin - Lab Portal</title>
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
<header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 md:px-10 py-4 sticky top-0 z-50">
<div class="flex items-center gap-3 flex-wrap">
<img alt="Logo de la Institución" class="h-10 w-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Admin / Administración</h2>
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
<h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Panel de Administración</h1>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Edita cualquier dato de los empleados y guarda los cambios cuando termines.</p>
</div>

<div id="groupsContainer" class="flex flex-col gap-8"></div>

<section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
    <h2 class="text-lg font-bold text-primary">Historial de estancias finalizadas</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Aquí puedes ver ejemplos de empleados que ya no tienen estancia activa.</p>
    <div id="historyContainer" class="mt-4 grid gap-4"></div>
</section>

<div class="flex justify-end">
<button id="saveAll" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2 text-sm font-semibold text-white shadow hover:bg-primary/90 focus:ring-2 focus:ring-primary/50 focus:outline-none">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1, 'wght' 700;">save</span>
Guardar cambios
</button>
</div>

</div>
</main>

<footer class="text-center py-6 text-slate-500 text-sm">
                    © 2026 Laboratory Academic Management System. Todos los derechos reservados / All rights reserved.
                </footer>
</div>
</div>

<script>
    const roles = [
        { value: 'empleado', label: 'Empleado' },
        { value: 'coordinador', label: 'Coordinador' },
        { value: 'seguridad', label: 'Seguridad' },
        { value: 'admin', label: 'Administrador' }
    ];

    let employees = [];
    const normalizeGroup = (value) => value ? String(value).toUpperCase() : '';
    const maskDni = (value) => {
        const str = String(value ?? '').trim();
        if (!str) return '—';
        if (str.length <= 4) return `**${str.slice(0, 1)}***`;
        const middle = str.slice(2, -2) || '***';
        return `**${middle}**`;
    };
    const resolveGroupName = (value) => {
        const upper = normalizeGroup(value);
        if (legacyLetterToName[upper]) return legacyLetterToName[upper];
        return value || '';
    };

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        if (Number.isNaN(d.getTime())) return '';
        return d.toLocaleDateString(undefined, { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    function formatEndDate(dateStr, role) {
        const isIndef = String(dateStr).split('T')[0] === '2100-01-01';
        const isCoveredRole = role === 'empleado' || role === 'seguridad';
        if (isIndef && isCoveredRole) return 'Personal indefinido';
        return formatDate(dateStr);
    }

    function isContractActive(fechaFin) {
        const today = new Date();
        const end = new Date(fechaFin);
        return end >= new Date(today.getFullYear(), today.getMonth(), today.getDate());
    }

    const groupOptions = [
        { value: 'AFM-NANO', label: 'AFM-NANO' },
        { value: 'AMBILAB', label: 'AMBILAB' },
        { value: 'BIOLAB', label: 'BIOLAB' },
        { value: 'GEO-GLOBAL', label: 'GEO-GLOBAL' },
        { value: 'PRODMAR', label: 'PRODMAR' },
        { value: 'QUIBIONAT', label: 'QUIBIONAT' },
        { value: 'QUIMIOPLAN', label: 'QUIMIOPLAN' },
        { value: 'SINTESTER', label: 'SINTESTER' },
        { value: 'PTGAS', label: 'PTGAS' },
        { value: 'ECOBERTURA', label: 'ECOBERTURA' },
        { value: 'Otros usuarios', label: 'Otros usuarios' },
    ];

    const legacyLetterToName = {
        'A': 'AFM-NANO',
        'B': 'AMBILAB',
        'C': 'BIOLAB',
        'D': 'GEO-GLOBAL',
        'E': 'PRODMAR',
        'F': 'QUIBIONAT',
        'G': 'QUIMIOPLAN',
        'H': 'SINTESTER',
    };

    function mapFromDb(emp) {
        return {
            ...emp,
            dni: emp.dni_pasaporte,
            foto: emp.foto_url,
            grupo: resolveGroupName(emp.grupo),
        };
    }

    function mapToDb(emp) {
        return {
            id: emp.id,
            nombre: emp.nombre,
            apellidos: emp.apellidos,
            dni_pasaporte: emp.dni,
            fecha_nacimiento: emp.fecha_nacimiento || null,
            email: emp.email,
            institucion: emp.institucion || null,
            pais: emp.pais || null,
            motivo: emp.motivo || null,
            fecha_inicio: emp.fecha_inicio || null,
            fecha_fin: emp.fecha_fin || null,
            grupo: resolveGroupName(emp.grupo) || null,
            foto_url: emp.foto || null,
            rol: emp.rol || 'empleado',
        };
    }

    function createInput({ type = 'text', value = '', name, className = '' }) {
        const input = document.createElement('input');
        input.type = type;
        input.name = name;
        if (type !== 'file') input.value = value;
        input.className = `mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2 focus:outline-none focus:ring-primary focus:border-primary ${className}`;
        return input;
    }

    function createSelect({ value = '', name, options = [] }) {
        const select = document.createElement('select');
        select.name = name;
        select.className = 'mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2 focus:outline-none focus:ring-primary focus:border-primary';
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.label;
            if (opt.value === value) option.selected = true;
            select.appendChild(option);
        });
        return select;
    }

    async function fetchEmployees() {
        try {
            const resp = await fetch('api/employees.php');
            const json = await resp.json();
            if (!Array.isArray(json.employees)) throw new Error('Respuesta inválida');
            employees = json.employees.map(mapFromDb);
        } catch (error) {
            console.error('No se pudieron cargar los empleados:', error);
            employees = [];
        }
    }

    async function saveAll() {
        const payload = {
            employees: employees.map(mapToDb)
        };

        const resp = await fetch('api/save_employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const result = await resp.json();
        if (resp.ok) {
            alert(`Cambios guardados (${result.updated} actualizaciones).`);
            await loadAndRender();
        } else {
            console.error(result);
            alert('Hubo un error al guardar. Revisa la consola.');
        }
    }

    function render() {
        const container = document.getElementById('groupsContainer');
        container.innerHTML = '';

        // Solo estancias activas en la vista principal
        const activeEmployees = employees.filter((e) => isContractActive(e.fecha_fin));

        const groups = Array.from(new Set(activeEmployees.map(e => resolveGroupName(e.grupo)).filter(Boolean)))
            .sort((a, b) => {
                const order = groupOptions.map(o => o.value);
                const ia = order.indexOf(resolveGroupName(a));
                const ib = order.indexOf(resolveGroupName(b));
                if (ia === -1 && ib === -1) return String(a).localeCompare(String(b));
                if (ia === -1) return 1;
                if (ib === -1) return -1;
                return ia - ib;
            });

        groups.forEach((group) => {
            const currentLabel = resolveGroupName(group);
            const groupSection = document.createElement('section');
            groupSection.className = 'space-y-4';

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-3';
            header.innerHTML = `
                <h2 class="text-lg font-bold text-primary">Grupo ${currentLabel}</h2>
                <span class="text-sm text-slate-500 dark:text-slate-400">${activeEmployees.filter(e => e.grupo === group).length} empleados</span>
            `;

            const list = document.createElement('div');
            list.className = 'grid gap-6';

            activeEmployees
                .filter(e => e.grupo === group)
                .forEach((emp) => {
                    const card = document.createElement('div');
                    card.className = 'bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-100 dark:border-slate-800 p-6';

                    const row = document.createElement('div');
                    row.className = 'grid gap-5 md:grid-cols-[1fr_1.2fr]';

                    const avatarSection = document.createElement('div');
                    avatarSection.className = 'flex flex-col items-center gap-4';
                    avatarSection.innerHTML = `
                        <img class="h-20 w-20 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="${emp.foto}" alt="${emp.nombre} ${emp.apellidos}" />
                        <label class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Foto (subir archivo)</label>
                    `;
                    const fotoInput = createInput({ type: 'file', name: 'foto', className: '' });
                    fotoInput.accept = 'image/*';
                    fotoInput.addEventListener('change', (event) => {
                        const file = event.target.files?.[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = () => {
                            emp.foto = reader.result;
                            card.querySelector('img').src = emp.foto;
                        };
                        reader.readAsDataURL(file);
                    });
                    avatarSection.appendChild(fotoInput);

                    const fields = document.createElement('div');
                    fields.className = 'grid gap-4 md:grid-cols-2';

                    const fieldConfigs = [
                        { label: 'Nombre', name: 'nombre', value: emp.nombre },
                        { label: 'Apellidos', name: 'apellidos', value: emp.apellidos },
                        { label: 'Email', name: 'email', value: emp.email, type: 'email' },
                        { label: 'DNI / Pasaporte', name: 'dni', value: emp.dni, type: 'text' },
                        { label: 'Grupo', name: 'grupo', value: resolveGroupName(emp.grupo), type: 'select', options: groupOptions },
                        { label: 'Rol', name: 'rol', value: emp.rol, type: 'select', options: roles },
                        { label: 'Inicio', name: 'fecha_inicio', value: emp.fecha_inicio, type: 'date' },
                        { label: 'Fin', name: 'fecha_fin', value: emp.fecha_fin, type: 'date' },
                    ];

                    fieldConfigs.forEach(({ label, name, value, type = 'text', options }) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'space-y-1';
                        wrapper.innerHTML = `<p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">${label}</p>`;

                        if (type === 'maskedDni') {
                            const masked = document.createElement('div');
                            masked.className = 'mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200';
                            masked.textContent = maskDni(value);
                            wrapper.appendChild(masked);
                        } else {
                            const input = type === 'select'
                                ? createSelect({ value, name, options })
                                : createInput({ type, value, name });

                            input.addEventListener('input', (event) => {
                                emp[name] = event.target.value;
                            });

                            wrapper.appendChild(input);
                        }
                        fields.appendChild(wrapper);
                    });

                    row.appendChild(avatarSection);
                    row.appendChild(fields);
                    card.appendChild(row);
                    list.appendChild(card);
                });

            groupSection.appendChild(header);
            groupSection.appendChild(list);
            container.appendChild(groupSection);
        });

        renderHistory();
    }

    function renderHistory() {
        const historyContainer = document.getElementById('historyContainer');
        historyContainer.innerHTML = '';

        const expired = employees
            .filter((e) => !isContractActive(e.fecha_fin))
            .sort((a, b) => new Date(b.fecha_fin) - new Date(a.fecha_fin));

        if (expired.length === 0) {
            historyContainer.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">No hay estancias finalizadas aún.</p>`;
            return;
        }

        expired.forEach((emp) => {
            const item = document.createElement('div');
            item.className = 'flex flex-col gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-4';
            const label = resolveGroupName(emp.grupo);
            item.innerHTML = `
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img class="h-12 w-12 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="${emp.foto}" alt="${emp.nombre} ${emp.apellidos}" />
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">${emp.nombre} ${emp.apellidos}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">${emp.rol} — Grupo ${label}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-rose-700 dark:text-rose-200">Estancia finalizada</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        <p class="font-semibold">Inicio</p>
                        <p>${formatDate(emp.fecha_inicio)}</p>
                    </div>
                    <div>
                        <p class="font-semibold">Fin</p>
                        <p>${formatEndDate(emp.fecha_fin, emp.rol)}</p>
                    </div>
                </div>
            `;
            historyContainer.appendChild(item);
        });
    }

    async function loadAndRender() {
        await fetchEmployees();
        render();
    }

    document.getElementById('saveAll').addEventListener('click', saveAll);

    document.addEventListener('DOMContentLoaded', loadAndRender);
</script>

<script>
    function logout() {
        window.location.href = 'api/logout.php';
    }
</script>
</body></html>
