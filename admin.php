<?php
require __DIR__ . '/api/auth.php';
requireRole('admin');

$user = getSessionUser();
$fullName = $user ? htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) : '';
?>

<!DOCTYPE html>

<html class="light" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>GestIUBO - Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="icon" href="../GESTIUBO/imagenes/icono_circulo.png" type="image/png">
    <link rel="icon" type="image/png" sizes="32x32" href="../GESTIUBO/imagenes/icono_circulo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../GESTIUBO/imagenes/icono_circulo.png">
    <link rel="apple-touch-icon" href="../GESTIUBO/imagenes/icono_circulo.png">
    <link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
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
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
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
            <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 md:px-10 py-4 fixed top-0 left-0 right-0 z-50">
                <div class="flex items-center gap-3 flex-wrap">
                    <img alt="Logo de la Institución" class="h-10 w-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png" />
                    <h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Admin / Administración</h2>
                    <?php if ($fullName): ?>
                        <span class="text-sm text-slate-500 dark:text-slate-400 pl-4">Hola, <?php echo $fullName; ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    <button id="saveAll" class="flex shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-xl h-11 px-4 border border-primary text-primary text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary hover:text-white transition-colors">
                        <span class="truncate">Guardar cambios</span>
                    </button>
                    <a href="#" onclick="logout(); return false;" aria-label="Cerrar sesión" title="Cerrar sesión" class="flex shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-xl h-11 w-11 border border-primary bg-white dark:bg-slate-900 text-primary text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-base">power_settings_new</span>
                    </a>
                </div>
            </header>

            <main class="flex-1 flex justify-center pt-36 md:pt-28 pb-10 px-4 md:px-0">
                <div class="w-full max-w-[980px] flex flex-col gap-6">
                    <div class="text-center">
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Panel de Administración</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Edita cualquier dato de los usuarios y guarda los cambios cuando termines.</p>
                    </div>

                    <div id="groupManager"></div>

                    <div id="groupsContainer" class="flex flex-col gap-8"></div>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-lg font-bold text-primary">Historial de estancias finalizadas</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Aquí puedes ver ejemplos de usuarios que ya no tienen estancia activa.</p>
                        <div id="historyContainer" class="mt-4 grid gap-4"></div>
                    </section>

                </div>
            </main>

            <footer class="text-center py-6 text-slate-500 text-sm">
                © 2026 Laboratory Academic Management System. Todos los derechos reservados / All rights reserved.
            </footer>
        </div>
    </div>

    <script>
        const roles = [{
                value: 'empleado',
                label: 'Usuario'
            },
            {
                value: 'coordinador',
                label: 'Coordinador'
            },
            {
                value: 'seguridad',
                label: 'Seguridad'
            },
            {
                value: 'admin',
                label: 'Administrador'
            }
        ];
        const horarioOptions = [{
                value: 1,
                label: 'Completo'
            },
            {
                value: 0,
                label: 'Solo lectivo'
            },
        ];

        let employees = [];
        let historyStays = [];
        let employeeBaselineById = new Map();
        let stayBaselineById = new Map();
        const saveButtonBaseText = 'Guardar cambios';
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
            return d.toLocaleDateString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }

        function normalizeDateKey(dateStr) {
            const raw = String(dateStr ?? '').trim();
            if (!raw) return '';
            return raw.split('T')[0];
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

        let groupOptions = [];
        let allGroups = [];

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

        // Construye rutas absolutas robustas (soporta despliegue en subcarpeta)
        const basePath = `${window.location.origin}${window.location.pathname.replace(/[^/]+$/, '')}`;
        const apiUrl = (path) => `${basePath}${path}`;

        // Toast simple con estilos del sitio
        const toastHost = document.createElement('div');
        toastHost.className = 'fixed bottom-4 right-4 flex flex-col gap-3 z-[9999] pointer-events-none';
        document.addEventListener('DOMContentLoaded', () => document.body.appendChild(toastHost));

        function showToast(message, variant = 'info') {
            // Todos los toasts en el morado de la app, variando solo la opacidad
            const palette = {
                success: 'bg-primary text-white',
                error: 'bg-primary text-white',
                info: 'bg-primary text-white',
            };
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto min-w-[240px] max-w-xs rounded-lg shadow-lg px-4 py-3 text-sm font-semibold ${palette[variant] || palette.info}`;
            toast.textContent = message;
            toastHost.appendChild(toast);
            setTimeout(() => toast.remove(), 3200);
        }

        // Confirm modal con estilo de la página
        function uiConfirm(message) {
            return new Promise((resolve) => {
                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[9998]';

                const dialog = document.createElement('div');
                dialog.className = 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-sm w-full mx-4 p-6 space-y-5';
                dialog.innerHTML = `
            <p class="text-base font-semibold text-center leading-relaxed">${message.replace(/\n/g, '<br>')}</p>
            <div class="flex justify-center gap-3">
                <button id="uiConfirmCancel" class="px-4 py-2 rounded-lg bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-700 text-sm font-semibold shadow hover:bg-slate-50 dark:hover:bg-slate-700">Cancelar</button>
                <button id="uiConfirmOk" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold shadow hover:bg-primary/90">Continuar</button>
            </div>
        `;

                overlay.appendChild(dialog);
                document.body.appendChild(overlay);

                dialog.querySelector('#uiConfirmCancel').onclick = () => {
                    overlay.remove();
                    resolve(false);
                };
                dialog.querySelector('#uiConfirmOk').onclick = () => {
                    overlay.remove();
                    resolve(true);
                };
            });
        }

        // Prompt con estilo para editar nombre
        function uiPrompt(message, defaultValue = '') {
            return new Promise((resolve) => {
                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[9998]';

                const dialog = document.createElement('div');
                dialog.className = 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-sm w-full mx-4 p-6 space-y-4';
                dialog.innerHTML = `
                <p class="text-base font-semibold text-center leading-relaxed">${message.replace(/\n/g, '<br>')}</p>
                <input id="uiPromptInput" type="text" value="${defaultValue.replace(/"/g, '&quot;')}" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary" />
                <div class="flex justify-center gap-3">
                    <button id="uiPromptCancel" class="px-4 py-2 rounded-lg bg-white text-slate-900 dark:bg-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-700 text-sm font-semibold shadow hover:bg-slate-50 dark:hover:bg-slate-700">Cancelar</button>
                    <button id="uiPromptOk" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold shadow hover:bg-primary/90">Guardar</button>
                </div>
            `;

                overlay.appendChild(dialog);
                document.body.appendChild(overlay);

                const input = dialog.querySelector('#uiPromptInput');
                input.focus();
                input.select();

                dialog.querySelector('#uiPromptCancel').onclick = () => {
                    overlay.remove();
                    resolve(null);
                };
                dialog.querySelector('#uiPromptOk').onclick = () => {
                    const val = input.value.trim();
                    overlay.remove();
                    resolve(val || null);
                };
            });
        }

        async function parseJsonSafe(resp) {
            const text = await resp.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(`Respuesta no JSON (HTTP ${resp.status}): ${text.slice(0, 200)}`);
            }
        }

        function renderGroupManager() {
            const host = document.getElementById('groupManager');
            if (!host) return;
            host.innerHTML = '';

            const card = document.createElement('div');
            card.className = 'rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5';

            const header = document.createElement('div');
            header.className = 'flex flex-col md:flex-row md:items-center md:justify-between gap-3';
            header.innerHTML = `
            <div>
                <h3 class="text-lg font-bold text-primary">Gestión de grupos</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Crea nuevos grupos o elimina los que ya no necesites. Las estancias finalizadas conservarán su nombre de grupo.</p>
            </div>
            <div class="flex gap-2">
                <input id="newGroupInput" type="text" placeholder="Nuevo grupo" class="rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2 focus:outline-none focus:ring-primary focus:border-primary" />
                <button id="createGroupBtn" class="rounded-lg bg-primary text-white px-4 py-2 text-sm font-semibold hover:bg-primary/90 focus:outline-none">Crear</button>
            </div>
        `;

            const list = document.createElement('div');
            list.className = 'mt-4 grid sm:grid-cols-2 md:grid-cols-3 gap-3';

            if (groupOptions.length === 0) {
                list.innerHTML = '<p class="text-sm text-slate-500 dark:text-slate-400">No hay grupos.</p>';
            } else {
                groupOptions.forEach((g) => {
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm gap-3';
                    item.innerHTML = `
                    <span class="font-semibold">${g.label}</span>
                    <div class="flex items-center gap-2">
                        <button class="h-8 w-8 inline-flex items-center justify-center rounded-full bg-white dark:bg-slate-800 border border-primary/30 text-primary hover:bg-primary/10 shadow" onclick="editGroup(${g.id})" title="Renombrar">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </button>
                        <button class="h-8 w-8 inline-flex items-center justify-center rounded-full bg-white dark:bg-slate-800 border border-primary/30 text-primary hover:bg-primary/10 shadow" onclick="deleteGroup(${g.id})" title="Eliminar">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                    </div>
                `;
                    list.appendChild(item);
                });
            }

            card.appendChild(header);
            card.appendChild(list);
            host.appendChild(card);

            const input = document.getElementById('newGroupInput');
            const createBtn = document.getElementById('createGroupBtn');
            if (createBtn) {
                createBtn.onclick = async () => {
                    const name = (input.value || '').trim();
                    if (!name) return showToast('Introduce un nombre de grupo', 'error');
                    const deletedMatch = allGroups.find((g) =>
                        g.deleted_at &&
                        String(g.name || '').trim().toLowerCase() === name.toLowerCase()
                    );
                    const confirmMessage = deletedMatch ?
                        `Este grupo ya existía y está eliminado: "${name}".\nSe va a REACTIVAR ese grupo.\n¿Deseas continuar?` :
                        `Se creará el nuevo grupo: "${name}".\n¿Deseas continuar?`;
                    const ok = await uiConfirm(confirmMessage);
                    if (!ok) return;
                    const resp = await fetch(apiUrl('api/groups.php'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            name
                        }),
                    });
                    let json;
                    try {
                        json = await parseJsonSafe(resp);
                    } catch (e) {
                        showToast(e.message, 'error');
                        return;
                    }
                    if (!resp.ok) {
                        showToast(json.error || 'No se pudo crear el grupo', 'error');
                        return;
                    }
                    input.value = '';
                    if (json.reactivated) {
                        showToast(`Grupo "${name}" reactivado`, 'success');
                    } else {
                        showToast(`Grupo "${name}" creado`, 'success');
                    }
                    await fetchGroups();
                    renderGroupManager();
                    render();
                };
            }

        }

        window.deleteGroup = async function(id) {
            const group = groupOptions.find(g => Number(g.id) === Number(id));
            if (!group) {
                showToast('Grupo no encontrado', 'error');
                return;
            }
            const ok = await uiConfirm(`Se eliminará el grupo: "${group.label}" ¿Deseas continuar?\nLos empleados existentes conservarán el nombre.`);
            if (!ok) return;
            const resp = await fetch(apiUrl('api/groups.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'delete',
                    id
                }),
            });
            let json;
            try {
                json = await parseJsonSafe(resp);
            } catch (e) {
                console.error('Delete parse error', e);
                showToast(e.message, 'error');
                return;
            }
            console.log('Respuesta delete', resp.status, json);
            if (!resp.ok) {
                showToast(json.error || 'No se pudo eliminar el grupo', 'error');
                return;
            }
            showToast(`Grupo "${group.label}" eliminado`, 'success');
            groupOptions = groupOptions.filter(g => g.id !== id);
            await fetchGroups();
            renderGroupManager();
            render();
        }

        window.editGroup = async function(id) {
            const group = groupOptions.find(g => Number(g.id) === Number(id));
            if (!group) {
                showToast('Grupo no encontrado', 'error');
                return;
            }
            const newName = await uiPrompt('Editar nombre del grupo', group.label);
            if (!newName || newName === group.label) return;
            const ok = await uiConfirm(`Vas a cambiar el nombre del grupo:\n"${group.label}" → "${newName}"\n¿Confirmas?`);
            if (!ok) return;
            const resp = await fetch(apiUrl('api/groups.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'rename',
                    id,
                    name: newName
                }),
            });
            let json;
            try {
                json = await parseJsonSafe(resp);
            } catch (e) {
                showToast(e.message, 'error');
                return;
            }
            if (!resp.ok) {
                showToast(json.error || 'No se pudo renombrar el grupo', 'error');
                return;
            }
            showToast(`Grupo renombrado a "${newName}"`, 'success');
            await fetchGroups();
            renderGroupManager();
            render();
        }

        function mapFromDb(emp) {
            const fallbackFoto = emp.foto_url || `https://i.pravatar.cc/160?u=${encodeURIComponent(emp.email || emp.username || emp.id || Math.random())}`;
            return {
                ...emp,
                dni: emp.dni_pasaporte,
                foto: fallbackFoto,
                horario: typeof emp.horario !== 'undefined' ? Number(emp.horario) : 1,
                group_id: emp.group_id || null,
                grupo: resolveGroupName(emp.group_name || emp.grupo),
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
                group_id: emp.group_id || null,
                grupo: emp.grupo || emp.group_name || null,
                foto_url: emp.foto || null,
                rol: emp.rol || 'empleado',
                horario: typeof emp.horario !== 'undefined' ? Number(emp.horario) : 1,
            };
        }

        function employeeSignature(emp) {
            const db = mapToDb(emp);
            return JSON.stringify({
                id: Number(db.id),
                nombre: db.nombre || '',
                apellidos: db.apellidos || '',
                dni_pasaporte: db.dni_pasaporte || '',
                fecha_nacimiento: db.fecha_nacimiento || null,
                email: db.email || '',
                institucion: db.institucion || null,
                pais: db.pais || null,
                motivo: db.motivo || null,
                fecha_inicio: normalizeDateKey(db.fecha_inicio),
                fecha_fin: normalizeDateKey(db.fecha_fin),
                group_id: db.group_id ? Number(db.group_id) : null,
                grupo: db.grupo || null,
                foto_url: db.foto_url || null,
                rol: db.rol || 'empleado',
                horario: Number(typeof db.horario !== 'undefined' ? db.horario : 1),
            });
        }

        function staySignature(stay) {
            return JSON.stringify({
                id: Number(stay.id),
                fecha_inicio: normalizeDateKey(stay.fecha_inicio),
                fecha_fin: normalizeDateKey(stay.fecha_fin),
            });
        }

        function employeeHasChanges(emp) {
            const id = Number(emp.id);
            return employeeBaselineById.get(id) !== employeeSignature(emp);
        }

        function stayHasChanges(stay) {
            const id = Number(stay.id);
            return stayBaselineById.get(id) !== staySignature(stay);
        }

        function getPendingChangesCount() {
            const employeeChanges = employees.reduce((count, emp) => count + (employeeHasChanges(emp) ? 1 : 0), 0);
            const stayChanges = historyStays.reduce((count, stay) => count + (stayHasChanges(stay) ? 1 : 0), 0);
            return employeeChanges + stayChanges;
        }

        function updateSaveButtonLabel() {
            const button = document.getElementById('saveAll');
            if (!button) return;
            const label = button.querySelector('.truncate');
            if (!label) return;
            const count = getPendingChangesCount();
            label.textContent = count > 0 ? `${saveButtonBaseText} (${count})` : saveButtonBaseText;
        }

        function createInput({
            type = 'text',
            value = '',
            name,
            className = ''
        }) {
            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            if (type !== 'file') input.value = value;
            input.className = `mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2 focus:outline-none focus:ring-primary focus:border-primary ${className}`;
            return input;
        }

        function createSelect({
            value = '',
            name,
            options = []
        }) {
            const select = document.createElement('select');
            select.name = name;
            select.className = 'mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2 focus:outline-none focus:ring-primary focus:border-primary';
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                if (String(opt.value) === String(value)) option.selected = true;
                select.appendChild(option);
            });
            return select;
        }

        async function fetchEmployees() {
            try {
                const resp = await fetch(apiUrl('api/employees.php?include_history=1'), {
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const json = await parseJsonSafe(resp);
                if (!Array.isArray(json.employees)) throw new Error('Respuesta inválida');
                employees = json.employees.map(mapFromDb);
                historyStays = Array.isArray(json.history) ? json.history : [];
            } catch (error) {
                console.error('No se pudieron cargar los usuarios:', error);
                employees = [];
                historyStays = [];
            }
        }

        async function fetchGroups() {
            try {
                const resp = await fetch(apiUrl('api/groups.php'), {
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const json = await parseJsonSafe(resp);
                if (!Array.isArray(json.groups)) throw new Error('Respuesta inválida');
                allGroups = json.groups.map(g => ({
                    id: Number(g.id),
                    name: String(g.name || '').trim(),
                    deleted_at: g.deleted_at || null,
                }));
                groupOptions = json.groups
                    .filter(g => !g.deleted_at)
                    .map(g => {
                        const display = resolveGroupName(g.name) || g.name;
                        return {
                            value: String(g.id),
                            label: display,
                            id: Number(g.id),
                            name: g.name
                        };
                    })
                    .sort((a, b) => a.label.localeCompare(b.label));
            } catch (error) {
                console.error('No se pudieron cargar los grupos:', error);
                groupOptions = [];
                allGroups = [];
            }
        }

        async function saveAll() {
            const payload = {
                employees: employees.map(mapToDb)
            };

            const resp = await fetch(apiUrl('api/save_employees.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            let result;
            try {
                result = await parseJsonSafe(resp);
            } catch (e) {
                console.error(e);
                showToast(e.message, 'error');
                await loadAndRender();
                return;
            }
            if (!resp.ok) {
                console.error(result);
                showToast(result.error || 'Hubo un error al guardar. Revisa la consola.', 'error');
                await loadAndRender();
                return;
            }
            if (Array.isArray(result.errors) && result.errors.length > 0) {
                const firstErr = result.errors[0]?.error;
                showToast(firstErr || 'Algunos cambios no se guardaron.', 'error');
                await loadAndRender();
                return;
            }

            // Guardar cambios en estancias finalizadas (si hay)
            const stayUpdates = historyStays
                .filter((s) => stayHasChanges(s))
                .map(s => ({
                    stay_id: s.id,
                    fecha_inicio: (s.fecha_inicio || '').split('T')[0] || s.fecha_inicio,
                    fecha_fin: (s.fecha_fin || '').split('T')[0] || s.fecha_fin,
                }));

            let jsonStays;
            if (stayUpdates.length > 0) {
                const respStays = await fetch(apiUrl('api/update_stay.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        updates: stayUpdates
                    }),
                });
                jsonStays = await parseJsonSafe(respStays);
                if (!respStays.ok || jsonStays.error) {
                    const firstErr = (jsonStays.errors && jsonStays.errors[0]?.error) || jsonStays.error;
                    showToast(firstErr || 'Error al guardar fechas de estancias finalizadas.', 'error');
                    await loadAndRender();
                    return;
                }
                if (jsonStays.errors && jsonStays.errors.length > 0) {
                    const firstErr = jsonStays.errors[0]?.error;
                    showToast(firstErr || 'Algunos cambios no se guardaron por solapamientos.', 'error');
                    await loadAndRender();
                    return;
                }
            }

            const totalStaysUpdated = stayUpdates.length > 0 ? (jsonStays?.updated || 0) : 0;
            const totalFromEmployees = (result.updated_employees ?? result.updated ?? 0) + (result.updated_stays ?? 0);
            const totalUpdated = totalFromEmployees + totalStaysUpdated;
            showToast(`Cambios guardados (${totalUpdated} actualizaciones).`, 'success');
            await loadAndRender();
        }

        function render() {
            const container = document.getElementById('groupsContainer');
            container.innerHTML = '';

            const activeEmployees = employees.filter((e) => isContractActive(e.fecha_fin));

            // Solo estancias activas en la vista principal
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
                <span class="text-sm text-slate-500 dark:text-slate-400">${activeEmployees.filter(e => e.grupo === group).length} usuarios activos</span>
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
                        <img class="h-20 w-20 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="${emp.foto_url || emp.foto || 'https://i.pravatar.cc/160?u=' + encodeURIComponent(emp.email || emp.username || '')}" alt="${emp.nombre || ''} ${emp.apellidos || ''}" />
                        <label class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Foto (subir archivo)</label>
                    `;
                        const fotoInput = createInput({
                            type: 'file',
                            name: 'foto',
                            className: ''
                        });
                        fotoInput.accept = 'image/*';
                        fotoInput.addEventListener('change', (event) => {
                            const file = event.target.files?.[0];
                            if (!file) return;
                            const reader = new FileReader();
                            reader.onload = () => {
                                emp.foto = reader.result;
                                card.querySelector('img').src = emp.foto;
                                updateSaveButtonLabel();
                            };
                            reader.readAsDataURL(file);
                        });
                        avatarSection.appendChild(fotoInput);

                        const fields = document.createElement('div');
                        fields.className = 'grid gap-4 md:grid-cols-2';

                        const groupValue = emp.group_id ? String(emp.group_id) : '';
                        const groupOptionsForEmp = [...groupOptions];
                        if (groupValue && !groupOptionsForEmp.some(o => o.value === groupValue)) {
                            // add missing option with name if we have it
                            groupOptionsForEmp.push({
                                value: groupValue,
                                label: resolveGroupName(emp.group_name || emp.grupo) || (emp.group_name || emp.grupo || groupValue),
                                id: Number(groupValue)
                            });
                        }

                        const fieldConfigs = [{
                                label: 'Nombre',
                                name: 'nombre',
                                value: emp.nombre
                            },
                            {
                                label: 'Apellidos',
                                name: 'apellidos',
                                value: emp.apellidos
                            },
                            {
                                label: 'Email',
                                name: 'email',
                                value: emp.email,
                                type: 'email'
                            },
                            {
                                label: 'DNI / Pasaporte',
                                name: 'dni',
                                value: emp.dni,
                                type: 'text'
                            },
                            {
                                label: 'Grupo',
                                name: 'group_id',
                                value: groupValue,
                                type: 'select',
                                options: groupOptionsForEmp.sort((a, b) => a.label.localeCompare(b.label))
                            },
                            {
                                label: 'Rol',
                                name: 'rol',
                                value: emp.rol,
                                type: 'select',
                                options: roles
                            },
                            {
                                label: 'Horario',
                                name: 'horario',
                                value: String(emp.horario ?? 1),
                                type: 'select',
                                options: horarioOptions
                            },
                            {
                                label: 'Inicio',
                                name: 'fecha_inicio',
                                value: emp.fecha_inicio,
                                type: 'date'
                            },
                            {
                                label: 'Fin',
                                name: 'fecha_fin',
                                value: emp.fecha_fin,
                                type: 'date'
                            },
                        ];

                        fieldConfigs.forEach(({
                            label,
                            name,
                            value,
                            type = 'text',
                            options
                        }) => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'space-y-1';
                            wrapper.innerHTML = `<p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">${label}</p>`;

                            if (type === 'maskedDni') {
                                const masked = document.createElement('div');
                                masked.className = 'mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200';
                                masked.textContent = maskDni(value);
                                wrapper.appendChild(masked);
                            } else {
                                const input = type === 'select' ?
                                    createSelect({
                                        value,
                                        name,
                                        options
                                    }) :
                                    createInput({
                                        type,
                                        value,
                                        name
                                    });

                                input.addEventListener('input', (event) => {
                                    const newValue = event.target.value;
                                    if (name === 'horario') {
                                        emp[name] = Number(newValue);
                                    } else if (name === 'group_id') {
                                        emp.group_id = newValue ? Number(newValue) : null;
                                        const opt = event.target.selectedOptions?.[0];
                                        if (opt) emp.group_name = opt.textContent;
                                    } else {
                                        emp[name] = newValue;
                                    }
                                    updateSaveButtonLabel();
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

            const expired = historyStays
                .slice()
                .sort((a, b) => new Date(b.fecha_fin) - new Date(a.fecha_fin));

            if (expired.length === 0) {
                historyContainer.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">No hay estancias finalizadas aún.</p>`;
                return;
            }

            expired.forEach((emp) => {
                const item = document.createElement('div');
                item.className = 'flex flex-col gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-4';
                const label = resolveGroupName(emp.group_name || emp.grupo);
                const displayRole = String(emp.rol || 'empleado').toLowerCase() === 'empleado' ? 'Usuario' : (emp.rol || '');
                item.innerHTML = `
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img class="h-12 w-12 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="${emp.foto_url || emp.foto || 'https://i.pravatar.cc/160?u=' + encodeURIComponent(emp.email || emp.username || '')}" alt="${emp.nombre || ''} ${emp.apellidos || ''}" />
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">${emp.nombre || ''} ${emp.apellidos || ''}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">${displayRole} — Grupo ${label}</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-rose-700 dark:text-rose-200">Estancia finalizada</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        <p class="font-semibold">Inicio</p>
                        <input data-stay-id="${emp.id}" data-field="start" type="date" class="w-32 max-w-[8rem] rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs" value="${(emp.fecha_inicio || '').split('T')[0] || ''}">
                    </div>
                    <div>
                        <p class="font-semibold">Fin</p>
                        <input data-stay-id="${emp.id}" data-field="end" type="date" class="w-32 max-w-[8rem] rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs" value="${(emp.fecha_fin || '').split('T')[0] || ''}">
                    </div>
                </div>
            `;
                const startInput = item.querySelector('input[data-field="start"]');
                const endInput = item.querySelector('input[data-field="end"]');
                const markDirty = () => {
                    emp._dirtyStay = true;
                    updateSaveButtonLabel();
                };
                startInput?.addEventListener('input', (e) => {
                    emp.fecha_inicio = e.target.value;
                    markDirty();
                });
                endInput?.addEventListener('input', (e) => {
                    emp.fecha_fin = e.target.value;
                    markDirty();
                });

                historyContainer.appendChild(item);
            });
        }

        async function loadAndRender() {
            await Promise.all([fetchGroups(), fetchEmployees()]);
            employeeBaselineById = new Map(employees.map((emp) => [Number(emp.id), employeeSignature(emp)]));
            stayBaselineById = new Map(historyStays.map((stay) => [Number(stay.id), staySignature(stay)]));
            render();
            renderGroupManager();
            updateSaveButtonLabel();
        }

        document.getElementById('saveAll').addEventListener('click', saveAll);

        document.addEventListener('DOMContentLoaded', loadAndRender);
    </script>

    <script>
        function logout() {
            window.location.href = 'api/logout.php';
        }
    </script>
</body>

</html>


