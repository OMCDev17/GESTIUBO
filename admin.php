<?php
require __DIR__ . '/api/auth.php';
requireRole('admin');
header('Content-Type: text/html; charset=UTF-8');

$user = getSessionUser();
$fullName = $user ? htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) : '';
?>

<!DOCTYPE html>

<html class="light" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>GestIUBO - Administración</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="icon" href="/GESTIUBO/imagenes/icono_circulo.png" type="image/png">
    <link rel="icon" type="image/png" sizes="32x32" href="/GESTIUBO/imagenes/icono_circulo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/GESTIUBO/imagenes/icono_circulo.png">
    <link rel="apple-touch-icon" href="/GESTIUBO/imagenes/icono_circulo.png">
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
                    <img alt="Logo de la Institucion" class="h-10 w-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png" />
                    <h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Administración</h2>
                    <?php if ($fullName): ?>
                        <span class="text-sm text-slate-500 dark:text-slate-400 pl-4">Hola, <?php echo $fullName; ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    <button id="mobileMenuToggleAdmin" type="button" class="md:hidden flex shrink-0 items-center justify-center overflow-hidden rounded-xl h-11 w-11 border border-primary bg-white dark:bg-slate-900 text-primary hover:bg-primary hover:text-white transition-colors" aria-label="Abrir menu">
                        <span class="material-symbols-outlined text-base">menu</span>
                    </button>
                    <div class="hidden md:flex items-center gap-3">
                        <button id="saveAll" class="flex shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-xl h-11 px-4 border border-primary text-primary text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary hover:text-white transition-colors">
                            <span class="truncate">Guardar cambios</span>
                        </button>
                        <a href="#" onclick="logout(); return false;" aria-label="Cerrar sesion" title="Cerrar sesion" class="flex shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-xl h-11 w-11 border border-primary bg-white dark:bg-slate-900 text-primary text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-base">power_settings_new</span>
                        </a>
                    </div>
                </div>
                <div id="mobileMenuAdmin" class="hidden md:hidden w-full border-t border-slate-200 dark:border-slate-800 pt-3 flex flex-col gap-2">
                    <button type="button" onclick="document.getElementById('saveAll')?.click();" class="w-full rounded-xl h-11 border border-primary text-primary text-sm font-bold hover:bg-primary hover:text-white transition-colors">Guardar cambios</button>
                    <a href="#" onclick="logout(); return false;" class="w-full flex items-center justify-center rounded-xl h-11 border border-primary text-primary text-sm font-bold hover:bg-primary hover:text-white transition-colors">Cerrar sesion</a>
                </div>
            </header>

            <main class="flex-1 flex justify-center pt-36 md:pt-28 pb-10 px-4 md:px-0">
                <div class="w-full max-w-[980px] flex flex-col gap-6">
                    <div class="text-center">
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">Panel de Administración</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Edita cualquier dato de los usuarios y guarda los cambios cuando termines.</p>
                    </div>

                    <div id="groupManager"></div>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-lg font-bold text-primary">Usuarios activos</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Selecciona un grupo para ver y editar sus usuarios.</p>
                        <div class="mt-4 flex items-center gap-2 mb-4">
                            <select 
                                id="groupFilterSelect" 
                                class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary"
                            >
                                <option value="">- Selecciona un grupo -</option>
                            </select>
                            <span class="text-sm text-slate-500 dark:text-slate-400" id="groupUserCount"></span>
                        </div>
                        <div id="groupsContainer" class="flex flex-col gap-8"></div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-lg font-bold text-primary">Historial de estancias finalizadas</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Aqui puedes ver ejemplos de usuarios que ya no tienen estancia activa.</p>
                        <div class="mt-4 flex items-center gap-2 mb-4">
                            <input 
                                type="text" 
                                id="historySearchInput" 
                                placeholder="Buscar por nombre..." 
                                class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary"
                            />
                            <span class="text-sm text-slate-500 dark:text-slate-400" id="historyResultCount"></span>
                        </div>
                        <div id="historyContainer" class="mt-4 grid gap-4"></div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                        <h2 class="text-lg font-bold text-primary">Solicitudes de estancias pendientes</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Revisa y aprueba o rechaza todas las solicitudes de estancias de los usuarios.</p>
                        <div class="mt-4 flex items-center gap-2 mb-4">
                            <span class="text-sm text-slate-500 dark:text-slate-400" id="stayRequestsCount">Cargando solicitudes...</span>
                        </div>
                        <div id="stayRequestsContainer" class="mt-4 grid gap-4"></div>
                    </section>

                </div>
            </main>

            <footer class="text-center py-6 text-slate-500 text-sm">
                 2026 GestIUBO. Todos los derechos reservados / All rights reserved.
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
        const motivoOptions = [
            { value: 'PDI', label: 'PDI' },
            { value: 'Postdoctoral', label: 'Postdoctoral' },
            { value: 'Predoctoral', label: 'Predoctoral' },
            { value: 'TFG', label: 'TFG' },
            { value: 'TFM', label: 'TFM' },
            { value: 'ERASMUS', label: 'ERASMUS' },
            { value: 'Visitante', label: 'Visitante' },
        ];
        const phonePrefixOptions = [
            { value: '+34', label: 'Espana (+34)' },
            { value: '+1', label: 'Estados Unidos (+1)' },
            { value: '+44', label: 'Reino Unido (+44)' },
            { value: '+33', label: 'Francia (+33)' },
            { value: '+49', label: 'Alemania (+49)' },
            { value: '+39', label: 'Italia (+39)' },
            { value: '+81', label: 'Japon (+81)' },
            { value: '+86', label: 'China (+86)' },
            { value: '+91', label: 'India (+91)' },
            { value: '+55', label: 'Brasil (+55)' },
            { value: '+52', label: 'Mexico (+52)' },
            { value: '+54', label: 'Argentina (+54)' },
            { value: '+56', label: 'Chile (+56)' },
            { value: '+506', label: 'Costa Rica (+506)' },
            { value: '+57', label: 'Colombia (+57)' },
            { value: '+51', label: 'Peru (+51)' },
            { value: '+58', label: 'Venezuela (+58)' },
            { value: '+36', label: 'Hungria (+36)' },
            { value: '+48', label: 'Polonia (+48)' },
            { value: '+31', label: 'Paises Bajos (+31)' },
            { value: '+32', label: 'Blgica (+32)' },
            { value: '+43', label: 'Austria (+43)' },
            { value: '+41', label: 'Suiza (+41)' },
            { value: '+46', label: 'Suecia (+46)' },
            { value: '+47', label: 'Noruega (+47)' },
            { value: '+45', label: 'Dinamarca (+45)' },
            { value: '+358', label: 'Finlandia (+358)' },
            { value: '+30', label: 'Grecia (+30)' },
            { value: '+60', label: 'Malasia (+60)' },
            { value: '+65', label: 'Singapur (+65)' },
            { value: '+62', label: 'Indonesia (+62)' },
            { value: '+66', label: 'Tailandia (+66)' },
            { value: '+84', label: 'Vietnam (+84)' },
            { value: '+82', label: 'Corea del Sur (+82)' },
            { value: '+61', label: 'Australia (+61)' },
            { value: '+64', label: 'Nueva Zelanda (+64)' },
            { value: '+27', label: 'Sudafrica (+27)' },
            { value: '+20', label: 'Egipto (+20)' },
            { value: '+212', label: 'Marruecos (+212)' },
            { value: '+1', label: 'Canada (+1)' },
        ];

        let employees = [];
        let historyStays = [];
        let employeeBaselineById = new Map();
        let stayBaselineById = new Map();
        const saveButtonBaseText = 'Guardar cambios';
        const normalizeGroup = (value) => value ? String(value).toUpperCase() : '';
        const maskDni = (value) => {
            const str = String(value ?? '').trim();
            if (!str) return '';
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
            if (isIndef && isCoveredRole) return 'Perusonal indefinido';
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

        // Confirm modal con estilo de la pgina
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
            const clean = text.replace(/^\uFEFF/, '').trim();
            try {
                return JSON.parse(clean);
            } catch (e) {
                const firstObj = clean.indexOf('{');
                const lastObj = clean.lastIndexOf('}');
                if (firstObj !== -1 && lastObj !== -1 && lastObj > firstObj) {
                    const candidate = clean.slice(firstObj, lastObj + 1);
                    try {
                        return JSON.parse(candidate);
                    } catch (_) {}
                }
                throw new Error(`Respuesta no JSON (HTTP ${resp.status}): ${clean.slice(0, 200)}`);
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
                <h3 class="text-lg font-bold text-primary">Gestion de grupos</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Crea nuevos grupos o elimina los que ya no necesites. Las estancias finalizadas conservaran su nombre de grupo.</p>
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
                        `Este grupo ya exista y est eliminado: "${name}".\nSe va a REACTIVAR ese grupo.\nDeseas continuar?` :
                        `Se crear el nuevo grupo: "${name}".\nDeseas continuar?`;
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
            const ok = await uiConfirm(`Se eliminara el grupo: "${group.label}" Deseas continuar?\nLos empleados existentes conservaran el nombre.`);
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
            const ok = await uiConfirm(`Vas a cambiar el nombre del grupo:\n"${group.label}" -> "${newName}"\nConfirmas?`);
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
            return {
                ...emp,
                username: emp.username || '',
                dni: emp.dni_pasaporte,
                foto: emp.foto_url || '',
                horario: typeof emp.horario !== 'undefined' ? Number(emp.horario) : 1,
                group_id: emp.group_id || null,
                grupo: resolveGroupName(emp.group_name || emp.grupo),
                phone_prefix: emp.phone_prefix || '+34',
                phone_number: emp.phone_number || '000000000',
            };
        }

        function mapToDb(emp) {
            return {
                id: emp.id,
                nombre: emp.nombre,
                apellidos: emp.apellidos,
                username: emp.username || null,
                dni_pasaporte: emp.dni,
                fecha_nacimiento: emp.fecha_nacimiento || null,
                email: emp.email,
                phone_prefix: emp.phone_prefix || '+34',
                phone_number: emp.phone_number || '000000000',
                institucion: emp.institucion || null,
                pais: emp.pais || null,
                motivo: emp.motivo || null,
                fecha_inicio: emp.fecha_inicio || null,
                fecha_fin: emp.fecha_fin || null,
                group_id: emp.group_id || null,
                grupo: emp.grupo || emp.group_name || null,
                foto_url: emp.foto_url || emp.foto || null,
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
                username: db.username || null,
                dni_pasaporte: db.dni_pasaporte || '',
                fecha_nacimiento: db.fecha_nacimiento || null,
                email: db.email || '',
                phone_prefix: db.phone_prefix || '+34',
                phone_number: db.phone_number || '000000000',
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
            className = '',
            required = false
        }) {
            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            if (type !== 'file') input.value = value;
            if (required) input.required = true;
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
                // Carga ligera para evitar respuestas muy grandes/truncadas en hosting compartido.
                const resp = await fetch(apiUrl('api/employees.php?include_history=1'), {
                    credentials: 'same-origin'
                });
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const json = await parseJsonSafe(resp);
                if (!Array.isArray(json.employees)) throw new Error('Respuesta invlida');
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
                if (!Array.isArray(json.groups)) throw new Error('Respuesta invlida');
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
            const filterSelect = document.getElementById('groupFilterSelect');
            const userCount = document.getElementById('groupUserCount');
            
            container.innerHTML = '';
            if (userCount) userCount.textContent = '';

            const activeEmployees = employees.filter((e) => isContractActive(e.fecha_fin));

            // Llenar el selector de grupos con todas las opciones disponibles
            if (filterSelect) {
                const currentValue = filterSelect.value;
                filterSelect.innerHTML = '<option value="">- Selecciona un grupo -</option>';
                
                // Usar groupOptions para llenar el selector (dinmicamente desde BD)
                groupOptions.forEach((group) => {
                    const option = document.createElement('option');
                    option.value = group.label;
                    option.textContent = group.label;
                    filterSelect.appendChild(option);
                });
                
                // Restaurar el valor seleccionado si existe
                filterSelect.value = currentValue;
            }

            // Si no hay grupo seleccionado, no renderizar nada
            const selectedGroup = filterSelect?.value;
            if (!selectedGroup) {
                container.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">Selecciona un grupo para ver a sus usuarios.</p>`;
                return;
            }

            // Renderizar solo el grupo seleccionado
            const groupEmployees = activeEmployees.filter(e => resolveGroupName(e.grupo) === selectedGroup);
            
            if (groupEmployees.length === 0) {
                container.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">No hay usuarios en este grupo.</p>`;
                return;
            }

            if (userCount) userCount.textContent = `${groupEmployees.length} usuario${groupEmployees.length !== 1 ? 's' : ''}`;

            const groupSection = document.createElement('section');
            groupSection.className = 'space-y-4';

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-3';
            header.innerHTML = `
                <h2 class="text-lg font-bold text-primary">Grupo ${selectedGroup}</h2>
                <span class="text-sm text-slate-500 dark:text-slate-400">${groupEmployees.length} usuarios activos</span>
            `;

            const list = document.createElement('div');
            list.className = 'grid gap-6';

            groupEmployees.forEach((emp) => {
                const card = document.createElement('div');
                card.className = 'relative bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-100 dark:border-slate-800 p-6';

                const row = document.createElement('div');
                row.className = 'grid gap-5 md:grid-cols-[1fr_1.2fr]';

                const avatarSection = document.createElement('div');
                avatarSection.className = 'flex flex-col items-center justify-center gap-3';
                avatarSection.innerHTML = `
                        <label class="group relative block h-24 w-24 cursor-pointer" title="Cambiar foto">
                            <img class="h-24 w-24 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-sm transition-transform duration-200 group-hover:scale-[1.03]" src="${emp.foto_url || emp.foto || 'https://i.pravatar.cc/160?u=' + encodeURIComponent(emp.email || emp.username || '')}" alt="${emp.nombre || ''} ${emp.apellidos || ''}" />
                            <span class="absolute inset-0 rounded-full bg-primary/70 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-lg">photo_camera</span>
                            </span>
                        </label>
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Cambiar foto</p>
                    `;
                const fotoInput = createInput({
                    type: 'file',
                    name: 'foto',
                    className: ''
                });
                fotoInput.className = 'hidden';
                fotoInput.accept = 'image/*';
                const fotoPicker = avatarSection.querySelector('label');
                if (fotoPicker) {
                    fotoPicker.appendChild(fotoInput);
                }
                fotoInput.addEventListener('change', async (event) => {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    if (!/^image\/(jpeg|png)$/i.test(file.type)) {
                        showToast('Formato no permitido (solo JPG/PNG)', 'error');
                        event.target.value = '';
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        showToast('La imagen supera los 5MB', 'error');
                        event.target.value = '';
                        return;
                    }

                    const previewUrl = URL.createObjectURL(file);
                    const imgEl = card.querySelector('img');
                    const previousUrl = emp.foto_url || emp.foto || '';
                    if (imgEl) imgEl.src = previewUrl;

                    try {
                        const fd = new FormData();
                        fd.append('photo', file);
                        const upRes = await fetch(apiUrl('api/upload_photo.php'), {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin'
                        });
                        const upJson = await parseJsonSafe(upRes);
                        if (!upRes.ok || !upJson.url) {
                            throw new Error(upJson.error || 'No se pudo subir la imagen');
                        }
                        emp.foto_url = upJson.url;
                        emp.foto = upJson.url;
                        if (imgEl) imgEl.src = upJson.url;
                        updateSaveButtonLabel();
                    } catch (err) {
                        if (imgEl) {
                            imgEl.src = previousUrl || ('https://i.pravatar.cc/160?u=' + encodeURIComponent(emp.email || emp.username || ''));
                        }
                        showToast(err?.message || 'No se pudo subir la imagen', 'error');
                    } finally {
                        URL.revokeObjectURL(previewUrl);
                        event.target.value = '';
                    }
                });

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
                        label: 'Usuario',
                        name: 'username',
                        value: emp.username || ''
                    },
                    {
                        label: 'Email',
                        name: 'email',
                        value: emp.email,
                        type: 'email'
                    },
                    {
                        label: 'Telefono (Prefijo)',
                        name: 'phone_prefix',
                        value: emp.phone_prefix || '+34',
                        type: 'select',
                        options: phonePrefixOptions
                    },
                    {
                        label: 'Telefono (Numero)',
                        name: 'phone_number',
                        value: emp.phone_number,
                        type: 'tel',
                        required: true
                    },
                    {
                        label: 'DNI / Paisaporte',
                        name: 'dni',
                        value: emp.dni,
                        type: 'text'
                    },
                    {
                        label: 'Institucion',
                        name: 'institucion',
                        value: emp.institucion || '',
                        type: 'text'
                    },
                    {
                        label: 'Pais',
                        name: 'pais',
                        value: emp.pais || '',
                        type: 'text'
                    },
                    {
                        label: 'Motivo',
                        name: 'motivo',
                        value: emp.motivo || '',
                        type: 'select',
                        options: motivoOptions
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
                ];

                fieldConfigs.forEach(({
                    label,
                    name,
                    value,
                    type = 'text',
                    options,
                    required = false
                }) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'space-y-1';
                    wrapper.innerHTML = `<p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">${label}${required ? ' <span class="text-red-500">*</span>' : ''}</p>`;

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
                                name,
                                required
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

                const dateRow = document.createElement('div');
                dateRow.className = 'md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4';

                const startWrapper = document.createElement('div');
                startWrapper.className = 'space-y-1';
                startWrapper.innerHTML = '<p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">Inicio</p>';
                const startInput = createInput({
                    type: 'date',
                    value: emp.fecha_inicio,
                    name: 'fecha_inicio'
                });
                startInput.addEventListener('input', (event) => {
                    emp.fecha_inicio = event.target.value;
                    updateSaveButtonLabel();
                });
                startWrapper.appendChild(startInput);

                const endWrapper = document.createElement('div');
                endWrapper.className = 'space-y-1';
                endWrapper.innerHTML = '<p class="text-[11px] uppercase tracking-widest font-semibold text-slate-500 dark:text-slate-400">Fin</p>';
                const endInput = createInput({
                    type: 'date',
                    value: emp.fecha_fin,
                    name: 'fecha_fin'
                });
                endInput.addEventListener('input', (event) => {
                    emp.fecha_fin = event.target.value;
                    updateSaveButtonLabel();
                });
                endWrapper.appendChild(endInput);

                dateRow.appendChild(startWrapper);
                dateRow.appendChild(endWrapper);
                fields.appendChild(dateRow);

                row.appendChild(avatarSection);
                row.appendChild(fields);
                card.appendChild(row);
                
                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'absolute left-4 top-4 h-9 w-9 inline-flex items-center justify-center rounded-full border border-primary/35 bg-white/95 dark:bg-slate-900/95 text-primary hover:bg-primary hover:text-white hover:border-primary transition-colors shadow-sm';
                deleteButton.innerHTML = '<span class="material-symbols-outlined text-lg">delete</span>';
                deleteButton.title = 'Eliminar usuario';
                deleteButton.setAttribute('aria-label', 'Eliminar usuario');
                deleteButton.addEventListener('click', async () => {
                    const confirmed = await uiConfirm(
                        `¿Estás seguro que deseas borrar este usuario?\n${emp.nombre} ${emp.apellidos}\n\nLa acción será permanente.`
                    );
                    if (!confirmed) return;
                    
                    try {
                        const resp = await fetch(apiUrl('api/delete_employee.php'), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'same-origin',
                            body: JSON.stringify({ employee_id: emp.id })
                        });
                        const result = await parseJsonSafe(resp);
                        if (!resp.ok || result.error) {
                            showToast(result.error || 'Error al eliminar el usuario', 'error');
                            return;
                        }
                        showToast('Usuario eliminado correctamente', 'success');
                        // Eliminar del array y re-renderizar
                        employees = employees.filter(e => e.id !== emp.id);
                        render();
                    } catch (error) {
                        console.error('Error eliminando usuario:', error);
                        showToast('No se pudo conectar con el servidor', 'error');
                    }
                });
                card.appendChild(deleteButton);
                
                list.appendChild(card);
            });

            groupSection.appendChild(header);
            groupSection.appendChild(list);
            container.appendChild(groupSection);

            renderHistory();
        }

        function renderHistory() {
            const historyContainer = document.getElementById('historyContainer');
            const resultCount = document.getElementById('historyResultCount');
            const searchInput = document.getElementById('historySearchInput');
            
            historyContainer.innerHTML = '';
            if (resultCount) resultCount.textContent = '';

            const searchTerm = (searchInput?.value?.trim() || '').toLowerCase();

            // Si no hay termino de busqueda, no renderizar nada
            if (!searchTerm) {
                historyContainer.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">Usa el buscador para encontrar estancias finalizadas.</p>`;
                return;
            }
            if (searchTerm.length < 3) {
                historyContainer.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">Escribe al menos 3 caracteres para buscar.</p>`;
                return;
            }

            const expired = historyStays
                .slice()
                .filter((stay) => {
                    const fullName = `${(stay.nombre || '').toLowerCase()} ${(stay.apellidos || '').toLowerCase()}`;
                    return fullName.includes(searchTerm);
                })
                .sort((a, b) => new Date(b.fecha_fin) - new Date(a.fecha_fin));

            if (expired.length === 0) {
                historyContainer.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">No se encontraron resultados para "${searchInput.value}"</p>`;
                resultCount.textContent = '0 resultados';
                return;
            }

            resultCount.textContent = `${expired.length} resultado${expired.length !== 1 ? 's' : ''}`;

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
                            <p class="text-xs text-slate-500 dark:text-slate-400">${displayRole}  Grupo ${label}</p>
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

        async function fetchStayRequests() {
            try {
                const resp = await fetch(apiUrl('api/group_requests.php'), {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const json = await parseJsonSafe(resp);
                if (resp.ok && json.requests) {
                    return json.requests;
                }
                return [];
            } catch (error) {
                console.error('Error fetching stay requests:', error);
                return [];
            }
        }

        function renderStayRequests(requests) {
            const container = document.getElementById('stayRequestsContainer');
            const countEl = document.getElementById('stayRequestsCount');
            
            if (!container) return;

            container.innerHTML = '';
            
            if (!requests || requests.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-500 dark:text-slate-400">No hay solicitudes de estancias pendientes.</p>';
                if (countEl) countEl.textContent = '0 solicitudes pendientes';
                return;
            }

            if (countEl) countEl.textContent = `${requests.length} solicitud${requests.length !== 1 ? 'es' : ''} pendiente${requests.length !== 1 ? 's' : ''}`;

            requests.forEach((req) => {
                const card = document.createElement('div');
                card.className = 'flex flex-col gap-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-5';
                
                const headerRow = document.createElement('div');
                headerRow.className = 'flex items-center justify-between gap-3';
                headerRow.innerHTML = `
                    <div class="flex items-center gap-3 flex-1">
                        <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center text-lg font-bold">
                            ${((req.nombre || '')[0] || 'U').toUpperCase()}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">${req.nombre || ''} ${req.apellidos || ''}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">${req.email || ''}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-primary">${req.group_name || ''}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Solicitado el ${formatDate(req.created_at || '')}</p>
                    </div>
                `;
                
                const detailsRow = document.createElement('div');
                detailsRow.className = 'grid grid-cols-2 md:grid-cols-4 gap-3 text-xs';
                detailsRow.innerHTML = `
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Motivo</p>
                        <p class="text-slate-700 dark:text-slate-200 font-medium">${req.motivo || ''}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Inicio</p>
                        <p class="text-slate-700 dark:text-slate-200 font-medium">${formatDate(req.fecha_inicio || '')}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Fin</p>
                        <p class="text-slate-700 dark:text-slate-200 font-medium">${formatDate(req.fecha_fin || '')}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Horario</p>
                        <p class="text-slate-700 dark:text-slate-200 font-medium">${parseInt(req.horario) === 1 ? 'Completo' : 'Solo lectivo'}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Institucion</p>
                        <p class="text-slate-700 dark:text-slate-200 font-medium">${req.institucion || ''}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Pais</p>
                        <p class="text-slate-700 dark:text-slate-200 font-medium">${req.pais || ''}</p>
                    </div>
                `;
                
                const actionsRow = document.createElement('div');
                actionsRow.className = 'flex gap-3 pt-3 border-t border-slate-200 dark:border-slate-700';
                
                const rejectBtn = document.createElement('button');
                rejectBtn.className = 'flex-1 px-4 py-2 rounded-lg bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold transition-colors';
                rejectBtn.innerHTML = '<span class="material-symbols-outlined inline text-base mr-1">close</span>Rechazar';
                rejectBtn.addEventListener('click', () => rejectStayRequest(req.id));
                
                const approveBtn = document.createElement('button');
                approveBtn.className = 'flex-1 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition-colors';
                approveBtn.innerHTML = '<span class="material-symbols-outlined inline text-base mr-1">check_circle</span>Aprobar';
                approveBtn.addEventListener('click', () => approveStayRequest(req.id));
                
                actionsRow.appendChild(rejectBtn);
                actionsRow.appendChild(approveBtn);
                
                card.appendChild(headerRow);
                card.appendChild(detailsRow);
                card.appendChild(actionsRow);
                container.appendChild(card);
            });
        }

        async function approveStayRequest(requestId) {
            const confirmed = await uiConfirm('Deseas aprobar esta solicitud de estancia?');
            if (!confirmed) return;

            try {
                const resp = await fetch(apiUrl('api/group_requests.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        request_id: requestId,
                        action: 'approve'
                    })
                });
                const json = await parseJsonSafe(resp);
                if (!resp.ok || json.error) {
                    showToast(json.error || 'Error al aprobar la solicitud', 'error');
                    return;
                }
                showToast('Solicitud aprobada correctamente', 'success');
                // Recargar las solicitudes
                const stayRequests = await fetchStayRequests();
                renderStayRequests(stayRequests);
            } catch (error) {
                console.error('Error approving request:', error);
                showToast('Error de conexion al servidor', 'error');
            }
        }

        async function rejectStayRequest(requestId) {
            const confirmed = await uiConfirm('Deseas rechazar esta solicitud de estancia?');
            if (!confirmed) return;

            try {
                const resp = await fetch(apiUrl('api/group_requests.php'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        request_id: requestId,
                        action: 'reject'
                    })
                });
                const json = await parseJsonSafe(resp);
                if (!resp.ok || json.error) {
                    showToast(json.error || 'Error al rechazar la solicitud', 'error');
                    return;
                }
                showToast('Solicitud rechazada', 'success');
                // Recargar las solicitudes
                const stayRequests = await fetchStayRequests();
                renderStayRequests(stayRequests);
            } catch (error) {
                console.error('Error rejecting request:', error);
                showToast('Error de conexion al servidor', 'error');
            }
        }

        async function loadAndRender() {
            await fetchGroups();
            renderGroupManager();
            await fetchEmployees();
            employeeBaselineById = new Map(employees.map((emp) => [Number(emp.id), employeeSignature(emp)]));
            stayBaselineById = new Map(historyStays.map((stay) => [Number(stay.id), staySignature(stay)]));
            render();
            renderGroupManager();
            updateSaveButtonLabel();
            
            // Cargar y renderizar solicitudes de estancias
            const stayRequests = await fetchStayRequests();
            renderStayRequests(stayRequests);
            
            // Agregar event listeners
            const searchInput = document.getElementById('historySearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    renderHistory();
                });
            }
            
            const groupFilter = document.getElementById('groupFilterSelect');
            if (groupFilter) {
                groupFilter.addEventListener('change', () => {
                    render();
                });
            }
        }

        document.getElementById('saveAll').addEventListener('click', saveAll);

        document.addEventListener('DOMContentLoaded', loadAndRender);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mobileMenuToggleAdmin');
            const menu = document.getElementById('mobileMenuAdmin');
            if (!btn || !menu) return;
            btn.addEventListener('click', () => menu.classList.toggle('hidden'));
        });

        function logout() {
            window.location.href = '/GESTIUBO/logout';
        }
    </script>
</body>

</html>
