$path = "seguridad.php"
$content = Get-Content -Raw -Path $path

$content = $content -replace "grupo: e\.group_name \|\| e\.grupo \|\| '.+?',\r?\n\s*foto:", "grupo: e.group_name || e.grupo || '-',`r`n                coordinador_grupo: e.coordinator_name || '-',`r`n                coordinador_telefono: e.coordinator_phone || '',`r`n                pendiente_aprobacion: Number(e.pending_approval) === 1,`r`n                foto:"

$content = $content -replace "const badgeClass = isSoloLectivo \?[\s\S]*?bg-green-900/30';", "const soloLectivoBadgeClass = 'text-sky-700 dark:text-sky-200 bg-sky-100 dark:bg-sky-900/35';"

$content = $content -replace "<p class=\"text-sm text-slate-500 dark:text-slate-400\">Grupo: \$\{emp\.grupo \|\| '.+?'\}</p>", "<p class=\"text-sm text-slate-500 dark:text-slate-400\">Grupo: ${emp.grupo || '-'}</p>`r`n                        <p class=\"text-sm text-slate-500 dark:text-slate-400\">Coordinador de grupo: ${emp.coordinador_grupo || '-'}</p>`r`n                        <p class=\"text-sm text-slate-500 dark:text-slate-400\">Tel. coordinador: ${emp.coordinador_telefono || ''}</p>"

$content = $content -replace "<p class=\"mt-1 inline-flex items-center gap-2 text-xs font-semibold \$\{badgeClass\} px-2 py-1 rounded-full\>[\s\S]*?</p>", "${isSoloLectivo ? `<p class=\"mt-1 inline-flex items-center gap-2 text-xs font-semibold ${soloLectivoBadgeClass} px-2 py-1 rounded-full\>`r`n                            <span class=\"material-symbols-outlined text-base\">schedule</span>${formatHorario(emp.horario)}`r`n                        </p>` : ''}`r`n                        ${emp.pendiente_aprobacion ? `<p class=\"mt-2 inline-flex items-center gap-2 text-xs font-semibold text-amber-800 dark:text-amber-100 bg-amber-200 dark:bg-amber-900/45 px-2 py-1 rounded-full\>`r`n                            <span class=\"material-symbols-outlined text-base\">pending</span>Pendiente de aprobación`r`n                        </p>` : ''}"

Set-Content -Path $path -Value $content
Write-Output "OK"
