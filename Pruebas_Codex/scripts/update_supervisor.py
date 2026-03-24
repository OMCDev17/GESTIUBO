from pathlib import Path

path = Path(r"c:\Users\Tavi\Desktop\pruebas proyecto mayhem\Pruebas Codex\supervisor.html")
text = path.read_text(encoding='utf-8')
start = text.find('<!-- Employee list -->')
if start == -1:
    raise SystemExit('marker not found')
save_marker = '<!-- Save section -->'
save_index = text.find(save_marker, start)
if save_index == -1:
    raise SystemExit('save marker not found')

new_list = """<!-- Employee list -->\n<div id=\"employeesContainer\" class=\"grid gap-6\"></div>\n\n"""
text = text[:start] + new_list + text[save_index:]

script_start = text.find('<script>', save_index)
script_end = text.rfind('</script>')
if script_start == -1 or script_end == -1:
    raise SystemExit('script markers not found')

new_script = """<script>
    let employees = [];
    const groupToShow = 'A';
    const pendingChanges = new Map();

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        if (Number.isNaN(d.getTime())) return '';
        return d.toLocaleDateString(undefined, { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    async function fetchEmployees() {
        try {
            const resp = await fetch('api/employees.php');
            const json = await resp.json();
            if (!Array.isArray(json.employees)) throw new Error('Respuesta inválida');
            employees = json.employees.map((emp) => ({
                ...emp,
                dni: emp.dni_pasaporte,
                foto: emp.foto_url,
            }));
        } catch (error) {
            console.error('No se pudieron cargar los empleados:', error);
            employees = [];
        }
    }

    function render() {
        const container = document.getElementById('employeesContainer');
        container.innerHTML = '';

        const groupEmployees = employees.filter((e) => e.grupo === groupToShow);

        if (groupEmployees.length === 0) {
            container.innerHTML = `<div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 text-center"><p class="text-lg font-semibold text-slate-900 dark:text-slate-100">No hay empleados en el grupo ${groupToShow}.</p></div>`;
            return;
        }

        groupEmployees.forEach((emp) => {
            const card = document.createElement('div');
            card.className = 'bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-100 dark:border-slate-800 p-6';

            const row = document.createElement('div');
            row.className = 'flex flex-col md:flex-row md:items-center md:justify-between gap-4';

            const profile = document.createElement('div');
            profile.className = 'flex items-center gap-4';
            profile.innerHTML = `
                <img class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-slate-700" src="${emp.foto}" alt="${emp.nombre} ${emp.apellidos}" />
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">${emp.nombre} ${emp.apellidos}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">${emp.email}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">DNI: ${emp.dni}</p>
                </div>
            `;

            const dates = document.createElement('div');
            dates.className = 'grid grid-cols-1 sm:grid-cols-2 gap-3 w-full md:w-auto';
            dates.innerHTML = `
                <div class="rounded-lg bg-slate-50 dark:bg-slate-950 p-3 border border-slate-200 dark:border-slate-800">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400">Inicio</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">${formatDate(emp.fecha_inicio)}</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-950 p-3 border border-slate-200 dark:border-slate-800">
                    <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400">Fin</p>
                    <input type="date" value="${emp.fecha_fin}" class="mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm px-3 py-2 focus:outline-none focus:ring-primary focus:border-primary" data-employee-id="${emp.id}" />
                </div>
            `;

            row.appendChild(profile);
            row.appendChild(dates);
            card.appendChild(row);
            container.appendChild(card);
        });

        container.querySelectorAll('input[type="date"]').forEach((input) => {
            input.addEventListener('change', (event) => {
                const id = Number(event.target.dataset.employeeId);
                const emp = employees.find((e) => e.id === id);
                if (!emp) return;
                emp.fecha_fin = event.target.value;
                pendingChanges.set(id, { id, fecha_fin: emp.fecha_fin });
            });
        });
    }

    async function saveChanges() {
        if (pendingChanges.size === 0) {
            showStatus('No hay cambios para guardar.', 'yellow');
            return;
        }

        const payload = { employees: Array.from(pendingChanges.values()) };
        const resp = await fetch('api/save_employees.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await resp.json();

        if (resp.ok) {
            showStatus(`Cambios guardados (${result.updated} actualizaciones).`, 'green');
            pendingChanges.clear();
            await loadAndRender();
        } else {
            console.error(result);
            showStatus('Error al guardar. Revisa la consola.', 'red');
        }
    }

    function showStatus(message, color) {
        const saveStatus = document.getElementById('saveStatus');
        saveStatus.textContent = message;
        saveStatus.classList.remove('hidden', 'text-green-600', 'text-rose-600', 'text-yellow-600');
        if (color === 'green') saveStatus.classList.add('text-green-600');
        if (color === 'red') saveStatus.classList.add('text-rose-600');
        if (color === 'yellow') saveStatus.classList.add('text-yellow-600');
        setTimeout(() => saveStatus.classList.add('hidden'), 3500);
    }

    async function loadAndRender() {
        await fetchEmployees();
        render();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('saveButton').addEventListener('click', saveChanges);
        loadAndRender();
    });
</script>"""

text = text[:script_start] + new_script + text[script_end + len('</script>'):]
path.write_text(text, encoding='utf-8')
print('Updated supervisor.html successfully')
