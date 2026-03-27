<!DOCTYPE html>

<html class="light" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
                        "background-dark": "#1a131f",
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
        .input-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239,68,68,0.25);
            background-color: #fff1f2;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<!-- Navigation / Header -->
<header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 md:px-10 py-4 sticky top-0 z-50">
<div class="flex items-center gap-3 flex-wrap">
<img alt="Logo de la Institución" class="h-10 w-auto object-contain" src="data:image/png;base64,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"/>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">Registro de Personal / Personnel Registration</h2>
</div>
</header>
<main class="flex-1 flex justify-center py-10 px-4 md:px-0">
<div class="w-full max-w-[800px] flex flex-col gap-6">
<!-- Hero/Banner Section -->
<div class="relative w-full h-48 overflow-hidden rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
<div class="absolute inset-0 bg-gradient-to-r from-primary/90 to-primary/40 z-10"></div>
<img alt="Laboratory Background" class="absolute inset-0 w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAZINg3o_zUPuS5vXuAfwcYBTPAY7UjRzIkRxchiRppJOXJBYT77Q4ZjA6ibEOrIGyCZJm_6886aBItSr_RkLtAaQ6FaAoNDGT08M-qYujjz5SOp4Z0euT6ZLlandn9BC1XDLC9RZzDwHHE9LQJaCDDxUepy-vii0jOsCshBOOTfYTUvyoLltdOSHSVKEcGqiuUPaw5svrDnuc-KTMst5JN7kDCGOx4YoU3CtE1b74EN_nQCMwnPu-1mKtJjd674GTO8ufwI2YAk6o"/>
<div class="relative z-20 h-full flex flex-col justify-end p-8">
<h1 class="text-white text-3xl font-bold">Alta de Miembro / Member Registration</h1>
<p class="text-white/80 text-sm mt-2">Formulario oficial para la incorporación de personal académico y científico / Official form for the incorporation of academic and scientific staff.</p>
</div>
</div>
<!-- Main Form Card -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-100 dark:border-slate-800 p-8">
<form id="registrationForm" class="flex flex-col gap-8">
<!-- Section: Datos Personales -->
<div>
<h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-sm">person</span>
                                Información Personal / Personal Information
                            </h3>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
<div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Nombre / Name</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="Ej: Francisco / e.g., Francis" type="text" name="nombre" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Apellidos / Surnames</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="Ej: García Martínez / e.g., Smith Jones" type="text" name="apellidos" required/>
</label>
<label class="flex flex-col gap-2 md:col-span-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">DNI/Pasaporte / DNI/Passport</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="DNI o número de pasaporte / ID or passport number" type="text" name="dni_pasaporte" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Fecha de Nacimiento / Date of Birth</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-600 dark:text-slate-300" type="date" name="fecha_nacimiento" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Email / Email</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="ejemplo@universidad.es / example@university.edu" type="email" name="email" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Usuario / Username</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="ej: francisco.garcia" type="text" name="username" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold flex items-center justify-between">
    <span>Contraseña / Password</span>
    <button type="button" class="toggle-pass text-sm text-primary hover:text-primary/80 flex items-center gap-1" data-target="password" aria-label="Mostrar u ocultar contraseña">
        <span class="material-symbols-outlined text-base">visibility</span>
    </button>
</p>
<input id="password" class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="*******" type="password" name="password" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold flex items-center justify-between">
    <span>Repetir contraseña / Confirm Password</span>
    <button type="button" class="toggle-pass text-sm text-primary hover:text-primary/80 flex items-center gap-1" data-target="password_confirm" aria-label="Mostrar u ocultar contraseña">
        <span class="material-symbols-outlined text-base">visibility</span>
    </button>
</p>
<input id="password_confirm" class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="*******" type="password" name="password_confirm" required/>
</label>
</div>
<div class="lg:col-span-1">
    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Foto / Photo</p>
    <label for="photoUpload" class="group relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition">
        <input id="photoUpload" name="photo" type="file" accept="image/*" class="sr-only" />
        <span id="photoDropHint" class="flex flex-col items-center gap-2">
            <span class="material-symbols-outlined text-4xl text-primary">photo_camera</span>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Arrastra y suelta aquí una foto / Drag & drop a photo here</span>
            <span class="text-xs text-slate-500 dark:text-slate-400">JPG/PNG hasta 5 MB / JPG/PNG up to 5 MB</span>
        </span>
        <div id="photoPreview" class="hidden flex flex-col items-center gap-2">
            <img id="photoPreviewImg" alt="Vista previa" class="h-28 w-28 rounded-full object-cover border border-slate-200 dark:border-slate-700" />
            <span id="photoFileName" class="text-sm text-slate-700 dark:text-slate-200"></span>
            <button type="button" id="photoRemoveButton" class="text-xs text-primary hover:underline" aria-label="Eliminar foto">Eliminar</button>
        </div>
        <p id="photoError" class="mt-2 text-xs text-red-600 dark:text-red-400 hidden"></p>
    </label>
</div>
</div>
</div>
<hr class="border-slate-100 dark:border-slate-800"/>
<!-- Section: Datos Institucionales -->
<div>
<h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-sm">school</span>
                                Origen Académico / Academic Background
                            </h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Institución de Origen / Origin Institution</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="Universidad o Centro de Investigación / University or Research Center" type="text" name="institucion" required/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">País de la institución / Institution Country</p>
<input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="País de la institución / Institution Country" type="text" name="pais" required/>
</label>
</div>
</div>
<hr class="border-slate-100 dark:border-slate-800"/>
<!-- Section: Estancia en el Centro -->
<div>
<h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-sm">science</span>
                                Detalles de la Incorporación / Incorporation Details
                            </h3>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<label class="flex flex-col gap-2 md:col-span-1">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Motivo / Purpose</p>
<select name="motivo" required class="form-select rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-700 dark:text-slate-300">
<option disabled="" selected="" value="">Seleccione categoría / Select position</option>
<option value="pdi">PDI (Personal Docente de Investigacion / Teaching and Research Staff)</option>
<option value="postdoctoral">Investigador Postdoctoral / Postdoctoral Researcher</option>
<option value="predoctoral">Investigador Predoctoral / Predoctoral Researcher</option>
<option value="tfg">TFG (Trabajo Final de Grado / Bachelor's Thesis)</option>
<option value="tfm">TFM (Trabajo Final de Máster / Master's Thesis)</option>
<option value="erasmus">ERASMUS</option>
<option value="visitante">Investigador Visitante / Visiting Researcher</option>
<option value="otros">Otros / Others</option>
</select>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Fecha de Inicio / Start Date</p>
<input id="fechaInicio" name="fecha_inicio" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-600 dark:text-slate-300" type="date"/>
</label>
<label class="flex flex-col gap-2">
<p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Fecha de Finalización / End Date</p>
<input id="fechaFin" name="fecha_fin" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-600 dark:text-slate-300" type="date"/>
</label>
</div>
<label class="mt-3 flex items-center justify-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-300">
    <input id="personalFijo" type="checkbox" class="h-4 w-4 text-primary border-slate-300 dark:border-slate-700 rounded">
    Soy personal fijo / I am permanent staff
</label>
<div class="mt-6">
    <label class="flex flex-col gap-2 mb-4">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Grupo / Group</p>
        <select name="grupo" required class="form-select rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-700 dark:text-slate-300">
            <option disabled selected value="">Seleccione grupo / Select group</option>
            <option value="AFM-NANO">AFM-NANO</option>
            <option value="AMBILAB">AMBILAB</option>
            <option value="BIOLAB">BIOLAB</option>
            <option value="GEO-GLOBAL">GEO-GLOBAL</option>
            <option value="PRODMAR">PRODMAR</option>
            <option value="QUIBIONAT">QUIBIONAT</option>
            <option value="QUIMIOPLAN">QUIMIOPLAN</option>
            <option value="SINTESTER">SINTESTER</option>
        </select>
    </label>
</div>
</div>
<!-- Submission -->
<div class="flex flex-col gap-4 mt-4">
<div class="flex items-start gap-3 p-4 bg-primary/5 rounded-lg border border-primary/10">
<span class="material-symbols-outlined text-primary">info</span>
<p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                    Al enviar este formulario, usted confirma que los datos proporcionados son veraces y acepta la <a href="#" id="privacyLinkEs" class="font-semibold text-primary hover:underline">política de privacidad</a> y tratamiento de datos para fines académicos de la institución. / By submitting this form, you confirm that the data provided is truthful and accept the <a href="#" id="privacyLinkEn" class="font-semibold text-primary hover:underline">privacy policy</a> and data processing for academic purposes of the institution.
                                </p>
</div>
<button class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl shadow-lg transition-all transform hover:scale-[1.01] flex items-center justify-center gap-2" type="submit">
<span class="material-symbols-outlined">how_to_reg</span>
                                Enviar Registro / Submit Registration
                            </button>
<div class="text-center">
    <a href="Loggin.php" class="text-sm font-semibold text-primary hover:underline mt-2 inline-block">Volver al login / Back to login</a>
</div>
</div>
</form>
</div>
<!-- Footer -->
<footer class="text-center py-6 text-slate-500 text-sm">
                    © 2026 GESTIUBO. Todos los derechos reservados / All rights reserved.
                </footer>
</div>
</main>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const photoUpload = document.getElementById('photoUpload');
        const photoDropHint = document.getElementById('photoDropHint');
        const photoPreview = document.getElementById('photoPreview');
        const photoPreviewImg = document.getElementById('photoPreviewImg');
        const photoFileName = document.getElementById('photoFileName');
        const photoRemoveButton = document.getElementById('photoRemoveButton');
        const photoError = document.getElementById('photoError');
        const maxBytes = 5 * 1024 * 1024; // 5 MB
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');

        const showPhotoError = (msg) => {
            photoError.textContent = msg;
            photoError.classList.remove('hidden');
        };
        const clearPhotoError = () => {
            photoError.textContent = '';
            photoError.classList.add('hidden');
        };
        let selectedPhoto = null;
        const updatePreview = (file) => {
            if (!file) return;
            const url = URL.createObjectURL(file);
            photoPreviewImg.src = url;
            photoFileName.textContent = file.name;
            photoDropHint.classList.add('hidden');
            photoPreview.classList.remove('hidden');
        };
        const resetPreview = () => {
            photoUpload.value = '';
            selectedPhoto = null;
            photoPreviewImg.src = '';
            photoFileName.textContent = '';
            photoDropHint.classList.remove('hidden');
            photoPreview.classList.add('hidden');
            clearPhotoError();
        };
        const handleFile = (file) => {
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                showPhotoError('Por favor suba una imagen (JPG/PNG). / Please upload an image (JPG/PNG).');
                return;
            }
            if (file.size > maxBytes) {
                showPhotoError('El archivo es demasiado grande. Máximo 5 MB. / File is too large. Max 5 MB.');
                return;
            }
            clearPhotoError();
            selectedPhoto = file;
            updatePreview(file);
        };

        photoUpload?.addEventListener('change', (e) => handleFile(e.target.files?.[0]));
        const dropZone = document.querySelector('label[for="photoUpload"]');
        if (dropZone) {
            ['dragenter', 'dragover'].forEach((ev) => dropZone.addEventListener(ev, (event) => {
                event.preventDefault();
                dropZone.classList.add('border-primary', 'bg-primary/10');
            }));
            ['dragleave', 'drop'].forEach((ev) => dropZone.addEventListener(ev, (event) => {
                event.preventDefault();
                dropZone.classList.remove('border-primary', 'bg-primary/10');
            }));
            dropZone.addEventListener('drop', (event) => {
                if (event.dataTransfer?.files?.[0]) handleFile(event.dataTransfer.files[0]);
            });
        }
        if (photoRemoveButton) photoRemoveButton.addEventListener('click', resetPreview);

        // Toggle password visibility
        document.querySelectorAll('.toggle-pass').forEach((btn) => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) return;
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const iconSpan = btn.querySelector('.material-symbols-outlined');
                if (iconSpan) iconSpan.textContent = isPassword ? 'visibility_off' : 'visibility';
                btn.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });

        // Modal privacidad
        const privacyModal = document.getElementById('privacyModal');
        const privacyLinkEs = document.getElementById('privacyLinkEs');
        const privacyLinkEn = document.getElementById('privacyLinkEn');
        const privacyClose = document.getElementById('privacyClose');
        const openPrivacyModal = (event) => {
            event.preventDefault();
            if (!privacyModal) return;
            privacyModal.classList.remove('hidden');
            privacyModal.classList.add('flex');
        };
        const closePrivacyModal = () => {
            if (!privacyModal) return;
            privacyModal.classList.add('hidden');
            privacyModal.classList.remove('flex');
        };
        privacyLinkEs?.addEventListener('click', openPrivacyModal);
        privacyLinkEn?.addEventListener('click', openPrivacyModal);
        privacyClose?.addEventListener('click', closePrivacyModal);
        privacyModal?.addEventListener('click', (event) => { if (event.target === privacyModal) closePrivacyModal(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closePrivacyModal(); });

        // Fechas y personal fijo
        const fechaInicio = document.getElementById('fechaInicio');
        const fechaFin = document.getElementById('fechaFin');
        const personalFijo = document.getElementById('personalFijo');
        const toggleFechas = () => {
            const fijo = personalFijo?.checked;
            const hoy = new Date().toISOString().split('T')[0];
            const finDefault = '2100-01-01';
            [fechaInicio, fechaFin].forEach((input, idx) => {
                if (!input) return;
                input.disabled = fijo;
                if (fijo) {
                    input.value = idx === 0 ? hoy : finDefault;
                    input.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    input.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        };
        personalFijo?.addEventListener('change', toggleFechas);
        toggleFechas();

        // Validación visual + envío
        const form = document.getElementById('registrationForm');
        const submitBtn = form?.querySelector('button[type="submit"]');
        // Evita envíos concurrentes incluso si el handler se adjunta dos veces
        let isSubmitting = false;
        const releaseSubmit = () => {
            isSubmitting = false;
            submitBtn?.removeAttribute('disabled');
            submitBtn?.classList.remove('opacity-70', 'cursor-not-allowed');
            window.__registerSubmitting = false;
        };
        const requiredFields = Array.from(form?.querySelectorAll('input, select, textarea') || []).filter((el) => {
            if (!el.name) return false;
            if (el.type === 'file') return false;
            if (el.type === 'checkbox') return false;
            return el.hasAttribute('required');
        });

        const clearErrors = () => {
            requiredFields.forEach((field) => {
                field.classList.remove('input-error');
                const helper = field.parentElement?.querySelector('.field-error');
                if (helper) helper.remove();
            });
        };
        const showFieldError = (field, message) => {
            field.classList.add('input-error');
            if (!field.parentElement) return;
            let helper = field.parentElement.querySelector('.field-error');
            if (!helper) {
                helper = document.createElement('p');
                helper.className = 'field-error text-xs text-red-600 mt-1';
                field.parentElement.appendChild(helper);
            }
            helper.textContent = message;
        };

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (isSubmitting || window.__registerSubmitting) return;
            isSubmitting = true;
            window.__registerSubmitting = true;
            submitBtn?.setAttribute('disabled', 'true');
            submitBtn?.classList.add('opacity-70', 'cursor-not-allowed');
            clearErrors();

            const fijo = personalFijo?.checked;
            if (fijo) {
                fechaInicio?.removeAttribute('required');
                fechaFin?.removeAttribute('required');
            } else {
                fechaInicio?.setAttribute('required', 'true');
                fechaFin?.setAttribute('required', 'true');
            }

            let firstInvalid = null;
            requiredFields.forEach((field) => {
                const value = field.value?.trim();
                if (field.id === 'fecha_inicio' && fijo) return;
                if (field.id === 'fecha_fin' && fijo) return;
                if (!value) {
                    showFieldError(field, 'Campo obligatorio / Required field');
                    if (!firstInvalid) firstInvalid = field;
                }
            });
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                releaseSubmit();
                return;
            }

            // Password match validation
            if (password && passwordConfirm && password.value !== passwordConfirm.value) {
                showFieldError(password, 'Las contraseñas no coinciden / Passwords do not match');
                showFieldError(passwordConfirm, 'Las contraseñas no coinciden / Passwords do not match');
                password.focus();
                password.scrollIntoView({ behavior: 'smooth', block: 'center' });
                releaseSubmit();
                return;
            }

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            if (fijo) {
                const hoy = new Date().toISOString().split('T')[0];
                data.fecha_inicio = hoy;
                data.fecha_fin = '2100-01-01';
            }

            if (selectedPhoto) {
                const uploadFd = new FormData();
                uploadFd.append('photo', selectedPhoto);
                const upRes = await fetch('api/upload_photo.php', { method: 'POST', body: uploadFd });
                const upJson = await upRes.json();
                if (!upRes.ok || upJson.error) {
                    showPhotoError(upJson.error || 'No se pudo subir la foto.');
                    releaseSubmit();
                    return;
                }
                data.foto_url = upJson.url;
            }

            try {
                const res = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });
                const json = await res.json();
                if (!res.ok || json.error) {
                    // Duplicate user handling: show same style as required field errors
                    if (res.status === 409) {
                        const targetField = json.field || 'username';
                        const fieldEl = form.querySelector(`input[name="${targetField}"]`) ||
                            form.querySelector('input[name="username"]');
                        if (fieldEl) {
                            showFieldError(fieldEl, json.error || 'El usuario ya existe.');
                            fieldEl.focus();
                            fieldEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else {
                            showPhotoError(json.error || 'El usuario ya existe.');
                        }
                        releaseSubmit();
                        return;
                    }
                    showPhotoError(json.error || 'Error al registrar. Inténtelo de nuevo.');
                    releaseSubmit();
                    return;
                }
                form.classList.add('ring-2', 'ring-primary/40');
                setTimeout(() => form.classList.remove('ring-2', 'ring-primary/40'), 600);
                form.reset();
                toggleFechas();
                resetPreview();
                window.location.href = 'registro_exitoso.php';
            } catch (error) {
                console.error(error);
                showPhotoError('No se pudo conectar con el servidor.');
            } finally {
                releaseSubmit();
            }
        });
    });
</script>
<!-- Privacy Policy Modal -->
<div id="privacyModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
    <div class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
        <div class="flex items-start justify-between border-b border-slate-200 dark:border-slate-800 p-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Política de Privacidad / Privacy Policy</h3>
            <button type="button" id="privacyClose" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100" aria-label="Cerrar">✕</button>
        </div>
        <div class="p-5 max-h-[60vh] overflow-y-auto text-sm text-slate-700 dark:text-slate-200 space-y-4">
            <p><strong>Política de Privacidad</strong> – Esta política describe cómo se recopilan, usan y protegen los datos personales en este sistema académico. Los datos se utilizan únicamente para fines administrativos y de seguimiento académico, se almacenan de forma segura y no se comparten con terceros sin el consentimiento del titular, salvo requerimiento legal.</p>
            <p><strong>Data Privacy Policy</strong> – This policy explains how personal data is collected, used and protected in this academic system. Data is used solely for administrative and academic tracking purposes, stored securely, and not shared with third parties without the data subject’s consent, unless required by law.</p>
            <p>Para más detalles, consulte la normativa de protección de datos vigente en España (LOPDGDD y RGPD) o contacte con el administrador del sistema. / For more details, consult the current data protection regulations in Spain (LOPDGDD and GDPR) or contact the system administrator.</p>
        </div>
    </div>
</div>
</body></html>





































