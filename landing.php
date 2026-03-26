<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
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
        body { font-family: 'Argentum Sans', sans-serif; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
    <div class="layout-container flex h-full grow flex-col">
        <!-- Navigation / Header (reuse style) -->
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-solid border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 md:px-10 py-4 sticky top-0 z-50">
            <div class="flex items-center gap-3 flex-wrap">
                <img alt="Logo Institucional" class="h-10 w-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
                <h1 id="t-header-title" class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4">
                    Portal Interno | Laboratorio y Operaciones Clínicas
                </h1>
            </div>
            <div class="flex items-center gap-4 text-sm font-semibold">
                <div class="flex items-center gap-2">
                    <span id="lang-es" class="cursor-pointer text-primary border-b-2 border-primary pb-0.5" onclick="switchLanguage('es')">ES</span>
                    <span class="text-slate-300">|</span>
                    <span id="lang-en" class="cursor-pointer text-slate-400 border-b-2 border-transparent hover:text-primary hover:border-primary pb-0.5" onclick="switchLanguage('en')">EN</span>
                </div>
                <a href="Loggin.php" class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-white text-sm font-semibold hover:bg-primary/90 transition-colors">
                    <span id="t-header-login">Ingresar</span>
                </a>
                <a href="Formulario.php" class="inline-flex items-center gap-2 rounded-full border border-slate-300 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-primary hover:border-primary">
                    <span id="t-header-register">Registrar</span>
                </a>
            </div>
        </header>

        <main class="flex-1">
            <!-- Hero -->
            <section class="relative overflow-hidden" id="sobre-nosotros">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/90 via-primary/60 to-slate-900 opacity-90"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_45%)]"></div>
                <div class="relative max-w-6xl mx-auto px-4 md:px-10 py-16 md:py-24 flex flex-col md:flex-row items-center gap-10">
                    <div class="flex-1 space-y-6">
                        <p id="t-hero-badge" class="inline-flex items-center gap-2 bg-white/10 text-white/80 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                            Portal unificado de operaciones
                        </p>
                        <h2 id="t-hero-title" class="text-3xl md:text-4xl font-bold text-white leading-tight tracking-[-0.02em]">
                            Bienvenido al centro operativo del laboratorio
                        </h2>
                        <p id="t-hero-subtitle" class="text-white/80 text-base md:text-lg max-w-2xl">
                            Gestiona personal, accesos, capacitaciones y comunicaciones en tiempo real. Punto único para equipos clínicos, bioseguridad y soporte operativo.
                        </p>
                <div class="grid sm:grid-cols-2 gap-3 text-white/90">
                            <div class="rounded-xl border border-white/20 bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-white/70">Equipo</p>
                                <p class="text-2xl font-bold">142 profesionales</p>
                                <p class="text-xs text-white/70 mt-1">Operativos, investigación y soporte.</p>
                            </div>
                            <div class="rounded-xl border border-white/20 bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-white/70">Experiencia</p>
                                <p class="text-2xl font-bold">Desde 2014</p>
                                <p class="text-xs text-white/70 mt-1">10+ años en bioseguridad y laboratorio clínico.</p>
                            </div>
                            <div class="rounded-xl border border-white/20 bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-white/70">Cobertura</p>
                                <p class="text-2xl font-bold">4 sedes</p>
                                <p class="text-xs text-white/70 mt-1">Campus central y 3 laboratorios satélite.</p>
                            </div>
                            <div class="rounded-xl border border-white/20 bg-white/10 p-4">
                                <p class="text-sm uppercase tracking-wide text-white/70">Proyectos activos</p>
                                <p class="text-2xl font-bold">58</p>
                                <p class="text-xs text-white/70 mt-1">Ensayos clínicos, vigilancia ambiental y R&D.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="Loggin.php" class="inline-flex items-center gap-2 rounded-full bg-white text-primary font-semibold px-5 py-3 shadow-lg hover:shadow-xl transition">
                                <span id="t-hero-cta1">Ir al panel</span>
                            </a>
                            <a href="#informacion" class="inline-flex items-center gap-2 rounded-full border border-white/40 text-white font-semibold px-5 py-3 hover:bg-white/10 transition">
                                <span id="t-hero-cta2">Más detalles</span>
                            </a>
                        </div>
                    </div>
                    <div class="flex-1 w-full max-w-md">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-white/20 bg-white/5 backdrop-blur">
                            <img src="imagenes/foto-iubo2.jpg" alt="Equipo del laboratorio" class="w-full h-[560px] object-cover">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4 text-white">
                                <p class="text-sm font-semibold">Nuestro equipo en acción</p>
                                <p class="text-xs text-white/80">Operaciones, bioseguridad y soporte técnico coordinados en tiempo real.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Información principal -->
            <section id="informacion" class="max-w-6xl mx-auto px-4 md:px-10 py-16 grid gap-10">
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <h3 id="t-card1-title" class="text-lg font-bold">Quiénes pueden acceder</h3>
                            <span class="material-symbols-outlined text-primary sr-only">group</span>
                        </div>
                        <p id="t-card1-body" class="text-slate-600 dark:text-slate-300 mt-2 text-sm">
                            Personal interno acreditado, investigadores invitados, técnicos de mantenimiento autorizados y proveedores con orden de servicio vigente.
                        </p>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <h3 id="t-card2-title" class="text-lg font-bold">Qué puedes hacer</h3>
                            <span class="material-symbols-outlined text-primary sr-only">checklist</span>
                        </div>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                            <li id="t-card2-li1" class="flex items-start gap-2"><span class="text-primary">•</span> Registrar altas, renovaciones y bajas de personal.</li>
                            <li id="t-card2-li2" class="flex items-start gap-2"><span class="text-primary">•</span> Asignar accesos por áreas críticas y turnos.</li>
                            <li id="t-card2-li3" class="flex items-start gap-2"><span class="text-primary">•</span> Subir acreditaciones, fichas médicas y foto oficial.</li>
                        </ul>
                    </div>
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-6 shadow-lg">
                        <div class="flex items-center justify-between">
                            <h3 id="t-card3-title" class="text-lg font-bold">Cómo empezar</h3>
                            <span class="material-symbols-outlined text-primary sr-only">rocket_launch</span>
                        </div>
                        <ol class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300 list-decimal list-inside">
                            <li id="t-card3-li1">Inicia sesión con tu correo institucional.</li>
                            <li id="t-card3-li2">Valida datos personales, vacunas y cursos obligatorios.</li>
                            <li id="t-card3-li3">Solicita accesos, firma protocolos y descarga tu credencial digital.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Nuestros equipos -->
            <section class="max-w-6xl mx-auto px-4 md:px-10 pb-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-primary font-bold">Nuestros equipos</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Grupos activos en el laboratorio</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Listado generado desde la base de datos; puedes ajustar las descripciones libremente.</p>
                    </div>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-slate-100">AFM-NANO</h4>
                            <span class="text-xs px-3 py-1 rounded-full bg-primary/10 text-primary font-semibold">Nanomateriales</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Equipo enfocado en microscopía de fuerza atómica para caracterizar superficies bio-compatibles y soportar validaciones de biomateriales.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-slate-100">AMBILAB</h4>
                            <span class="text-xs px-3 py-1 rounded-full bg-primary/10 text-primary font-semibold">Bioseguridad</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Gestión de muestras ambientales, trazabilidad de residuos y cumplimiento de protocolos BSL-2/3 para salas de bioseguridad.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-slate-100">GEO-GLOBAL</h4>
                            <span class="text-xs px-3 py-1 rounded-full bg-primary/10 text-primary font-semibold">Geoquímica</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Monitoreo geoquímico y modelado de recursos naturales; soporte a campañas de muestreo y análisis remoto.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-slate-100">QUIBIONAT</h4>
                            <span class="text-xs px-3 py-1 rounded-full bg-primary/10 text-primary font-semibold">Bioquímica</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Investigación en química de productos naturales, aislamiento de metabolitos y soporte a proyectos de bioprospección.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-slate-100">QUIMIOPLAN</h4>
                            <span class="text-xs px-3 py-1 rounded-full bg-primary/10 text-primary font-semibold">Quimiometría</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Modelos quimiométricos para control de calidad, análisis predictivo y soporte a escalados de planta piloto.</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-slate-900 dark:text-slate-100">SINTESTER</h4>
                            <span class="text-xs px-3 py-1 rounded-full bg-primary/10 text-primary font-semibold">Síntesis</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Síntesis orgánica y diseño de nuevos compuestos; soporte a rutas de síntesis y validación analítica.</p>
                    </article>
                </div>
            </section>

            <!-- Alertas y Soporte (reubicado) -->
            <section class="max-w-6xl mx-auto px-4 md:px-10 pb-12 grid md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-primary/90 to-primary/70 text-white rounded-2xl p-8 shadow-xl">
                    <div class="flex items-start gap-3">
                        <div>
                            <p id="t-news-label" class="text-sm uppercase tracking-wide text-white/70">Alertas</p>
                            <h3 id="t-news-title" class="text-2xl font-bold">Comunicados internos</h3>
                        </div>
                    </div>
                    <p id="t-news-body" class="mt-4 text-white/90 text-sm">
                        Publicamos cambios en protocolos BSL, ventanas de mantenimiento de equipos críticos, campañas de vacunación y agendas de simulacros.
                    </p>
                    <a href="#" class="mt-6 inline-flex items-center gap-2 text-white font-semibold">
                        <span id="t-news-link">Ver calendario</span> <span aria-hidden="true">→</span>
                    </a>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 shadow-xl">
                    <div class="flex items-start gap-3">
                        <div>
                            <p id="t-support-label" class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-400">Soporte</p>
                            <h3 id="t-support-title" class="text-2xl font-bold">¿Necesitas ayuda?</h3>
                        </div>
                    </div>
                    <p id="t-support-body" class="mt-4 text-slate-600 dark:text-slate-300 text-sm">
                        Registra tu incidencia con detalle (fecha, equipo, sala). Adjunta evidencia y el equipo de soporte priorizará según criticidad operativa.
                    </p>
                    <div class="mt-4 grid gap-2 text-sm">
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200"><span class="text-primary">•</span> <span id="t-support-mail">soporte@laboratorio.test</span></div>
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200"><span class="text-primary">•</span> <span id="t-support-phone">+34 900 000 000</span></div>
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200"><span class="text-primary">•</span> <span id="t-support-hours">Lun - Vie | 08:00 - 18:00</span></div>
                    </div>
                </div>
            </section>

            <!-- FAQs al final -->
            <section class="max-w-6xl mx-auto px-4 md:px-10 pb-16">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-6 shadow-lg">
                    <div class="flex items-start gap-3 mb-4">
                        <h3 id="t-faq-title" class="text-xl font-bold">Preguntas frecuentes</h3>
                    </div>
                    <div class="grid md:grid-cols-2 gap-3 text-sm text-slate-700 dark:text-slate-200" id="faq-list">
                        <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-lg p-3 cursor-pointer">
                            <div class="flex items-center justify-between faq-toggle">
                                <p id="t-faq1-q" class="font-semibold">¿No recuerdo mi contraseña?</p>
                                <span class="faq-arrow transition-transform" aria-hidden="true">▸</span>
                            </div>
                            <p id="t-faq1-a" class="faq-answer text-slate-600 dark:text-slate-300 mt-2 hidden">Selecciona “Olvidé mi contraseña” en el login; se envía un código temporal a tu correo institucional.</p>
                        </div>
                        <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-lg p-3 cursor-pointer">
                            <div class="flex items-center justify-between faq-toggle">
                                <p id="t-faq2-q" class="font-semibold">¿Cómo actualizo mi foto?</p>
                                <span class="faq-arrow transition-transform" aria-hidden="true">▸</span>
                            </div>
                            <p id="t-faq2-a" class="faq-answer text-slate-600 dark:text-slate-300 mt-2 hidden">Accede a tu perfil y sube una imagen reciente en JPG/PNG; se valida en menos de 2 horas.</p>
                        </div>
                        <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-lg p-3 cursor-pointer">
                            <div class="flex items-center justify-between faq-toggle">
                                <p id="t-faq3-q" class="font-semibold">¿Quién aprueba accesos especiales?</p>
                                <span class="faq-arrow transition-transform" aria-hidden="true">▸</span>
                            </div>
                            <p id="t-faq3-a" class="faq-answer text-slate-600 dark:text-slate-300 mt-2 hidden">El coordinador de área y Bioseguridad autorizan accesos temporales o zonas BSL-2/3.</p>
                        </div>
                        <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-lg p-3 cursor-pointer">
                            <div class="flex items-center justify-between faq-toggle">
                                <p id="t-faq4-q" class="font-semibold">¿Puedo registrar a un visitante?</p>
                                <span class="faq-arrow transition-transform" aria-hidden="true">▸</span>
                            </div>
                            <p id="t-faq4-a" class="faq-answer text-slate-600 dark:text-slate-300 mt-2 hidden">Sí, crea la visita indicando responsable, fecha y zonas permitidas; requiere aprobación del anfitrión.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="mt-auto bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-6xl mx-auto px-4 md:px-10 py-6 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-slate-600 dark:text-slate-300">
                <p id="t-footer-note">Portal interno del laboratorio · Información operativa para personal acreditado.</p>
                <div class="flex items-center gap-4"></div>
            </div>
        </footer>
    </div>
</div>

<script>
    const translations = {
        es: {
            headerTitle: 'Portal Interno | Laboratorio y Operaciones Clínicas',
            headerLogin: 'Ingresar',
            headerRegister: 'Registrar',
            heroBadge: 'Portal unificado de operaciones',
            heroTitle: 'Bienvenido al centro operativo del laboratorio',
            heroSubtitle: 'Gestiona personal, accesos, capacitaciones y comunicaciones en tiempo real. Punto único para equipos clínicos, bioseguridad y soporte operativo.',
            heroCta1: 'Ir al panel',
            heroCta2: 'Más detalles',
            stat1Label: 'Accesos',
            stat1Desc: 'Servicios internos y monitoreo activo en cualquier horario.',
            stat2Label: 'Altas verificadas',
            stat2Desc: 'Registros auditados esta semana por RRHH y Bioseguridad.',
            stat3Label: 'Seguridad integral',
            stat3Desc: 'Autorizaciones, credenciales y auditorías centralizadas con trazabilidad completa.',
            card1Title: 'Quiénes pueden acceder',
            card1Body: 'Personal interno acreditado, investigadores invitados, técnicos de mantenimiento autorizados y proveedores con orden de servicio vigente.',
            card2Title: 'Qué puedes hacer',
            card2Li1: 'Registrar altas, renovaciones y bajas de personal.',
            card2Li2: 'Asignar accesos por áreas críticas y turnos.',
            card2Li3: 'Subir acreditaciones, fichas médicas y foto oficial.',
            card3Title: 'Cómo empezar',
            card3Li1: 'Inicia sesión con tu correo institucional.',
            card3Li2: 'Valida datos personales, vacunas y cursos obligatorios.',
            card3Li3: 'Solicita accesos, firma protocolos y descarga tu credencial digital.',
            newsLabel: 'Alertas',
            newsTitle: 'Comunicados internos',
            newsBody: 'Publicamos cambios en protocolos BSL, ventanas de mantenimiento de equipos críticos, campañas de vacunación y agendas de simulacros.',
            newsLink: 'Ver calendario',
            supportLabel: 'Soporte',
            supportTitle: '¿Necesitas ayuda?',
            supportBody: 'Registra tu incidencia con detalle (fecha, equipo, sala). Adjunta evidencia y el equipo de soporte priorizará según criticidad operativa.',
            supportMail: 'soporte@laboratorio.test',
            supportPhone: '+34 900 000 000',
            supportHours: 'Lun - Vie | 08:00 - 18:00',
            faqTitle: 'Preguntas frecuentes',
            faq1q: '¿No recuerdo mi contraseña?',
            faq1a: 'Selecciona “Olvidé mi contraseña” en el login; se envía un código temporal a tu correo institucional.',
            faq2q: '¿Cómo actualizo mi foto?',
            faq2a: 'Accede a tu perfil y sube una imagen reciente en JPG/PNG; se valida en menos de 2 horas.',
            faq3q: '¿Quién aprueba accesos especiales?',
            faq3a: 'El coordinador de área y Bioseguridad autorizan accesos temporales o zonas BSL-2/3.',
            faq4q: '¿Puedo registrar a un visitante?',
            faq4a: 'Sí, crea la visita indicando responsable, fecha y zonas permitidas; requiere aprobación del anfitrión.',
            footerNote: 'Portal interno del laboratorio · Información operativa para personal acreditado.'
        },
        en: {
            headerTitle: 'Internal Portal | Clinical Lab & Operations',
            headerLogin: 'Sign in',
            headerRegister: 'Register',
            heroBadge: 'Unified operations portal',
            heroTitle: 'Welcome to the lab operations hub',
            heroSubtitle: 'Manage staff, access control, training and communications in real time. Single point for clinical teams, biosafety and ops support.',
            heroCta1: 'Go to dashboard',
            heroCta2: 'More details',
            stat1Label: 'Access',
            stat1Desc: 'Internal services and live monitoring around the clock.',
            stat2Label: 'Verified onboardings',
            stat2Desc: 'Records audited this week by HR and Biosafety.',
            stat3Label: 'Integrated security',
            stat3Desc: 'Authorizations, credentials and audits with full traceability.',
            card1Title: 'Who can enter',
            card1Body: 'Accredited staff, invited researchers, authorized maintenance techs and vendors with an active service order.',
            card2Title: 'What you can do',
            card2Li1: 'Register onboardings, renewals and deactivations.',
            card2Li2: 'Assign access by critical areas and shifts.',
            card2Li3: 'Upload licenses, medical clearance and official photo.',
            card3Title: 'How to start',
            card3Li1: 'Sign in with your institutional email.',
            card3Li2: 'Validate personal data, vaccinations and mandatory courses.',
            card3Li3: 'Request access, sign protocols and download your digital badge.',
            newsLabel: 'Alerts',
            newsTitle: 'Internal bulletins',
            newsBody: 'We post BSL protocol updates, maintenance windows for critical gear, vaccination drives and drill schedules.',
            newsLink: 'Open calendar',
            supportLabel: 'Support',
            supportTitle: 'Need assistance?',
            supportBody: 'Log incidents with details (date, device, room). Attach evidence; support will prioritize by operational criticality.',
            supportMail: 'support@laboratory.test',
            supportPhone: '+34 900 000 000',
            supportHours: 'Mon - Fri | 08:00 - 18:00',
            faqTitle: 'FAQs',
            faq1q: 'Forgot your password?',
            faq1a: 'Use “Forgot password” on login; a one-time code goes to your institutional email.',
            faq2q: 'How do I update my photo?',
            faq2a: 'Open your profile and upload a recent JPG/PNG; it is validated within 2 hours.',
            faq3q: 'Who approves special access?',
            faq3a: 'Area coordinator and Biosafety authorize temporary access or BSL-2/3 zones.',
            faq4q: 'Can I register a visitor?',
            faq4a: 'Yes, create a visit with host, date and allowed areas; host approval is required.',
            footerNote: 'Internal lab portal · Operational information for accredited staff.'
        }
    };

    function applyText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function switchLanguage(lang) {
        const t = translations[lang] || translations.es;
        applyText('t-header-title', t.headerTitle);
        applyText('t-header-login', t.headerLogin);
        applyText('t-header-register', t.headerRegister);
        applyText('t-hero-badge', t.heroBadge);
        applyText('t-hero-title', t.heroTitle);
        applyText('t-hero-subtitle', t.heroSubtitle);
        applyText('t-hero-cta1', t.heroCta1);
        applyText('t-hero-cta2', t.heroCta2);
        applyText('t-stat1-label', t.stat1Label);
        applyText('t-stat1-desc', t.stat1Desc);
        applyText('t-stat2-label', t.stat2Label);
        applyText('t-stat2-desc', t.stat2Desc);
        applyText('t-stat3-label', t.stat3Label);
        applyText('t-stat3-desc', t.stat3Desc);
        applyText('t-card1-title', t.card1Title);
        applyText('t-card1-body', t.card1Body);
        applyText('t-card2-title', t.card2Title);
        applyText('t-card2-li1', t.card2Li1);
        applyText('t-card2-li2', t.card2Li2);
        applyText('t-card2-li3', t.card2Li3);
        applyText('t-card3-title', t.card3Title);
        applyText('t-card3-li1', t.card3Li1);
        applyText('t-card3-li2', t.card3Li2);
        applyText('t-card3-li3', t.card3Li3);
        applyText('t-news-label', t.newsLabel);
        applyText('t-news-title', t.newsTitle);
        applyText('t-news-body', t.newsBody);
        applyText('t-news-link', t.newsLink);
        applyText('t-support-label', t.supportLabel);
        applyText('t-support-title', t.supportTitle);
        applyText('t-support-body', t.supportBody);
        applyText('t-support-mail', t.supportMail);
        applyText('t-support-phone', t.supportPhone);
        applyText('t-support-hours', t.supportHours);
        applyText('t-faq-title', t.faqTitle);
        applyText('t-faq1-q', t.faq1q);
        applyText('t-faq1-a', t.faq1a);
        applyText('t-faq2-q', t.faq2q);
        applyText('t-faq2-a', t.faq2a);
        applyText('t-faq3-q', t.faq3q);
        applyText('t-faq3-a', t.faq3a);
        applyText('t-faq4-q', t.faq4q);
        applyText('t-faq4-a', t.faq4a);
        applyText('t-footer-note', t.footerNote);

        const esBtn = document.getElementById('lang-es');
        const enBtn = document.getElementById('lang-en');
        if (lang === 'es') {
            esBtn.classList.add('text-primary', 'border-primary');
            esBtn.classList.remove('text-slate-400', 'border-transparent');
            enBtn.classList.add('text-slate-400', 'border-transparent');
            enBtn.classList.remove('text-primary', 'border-primary');
        } else {
            enBtn.classList.add('text-primary', 'border-primary');
            enBtn.classList.remove('text-slate-400', 'border-transparent');
            esBtn.classList.add('text-slate-400', 'border-transparent');
            esBtn.classList.remove('text-primary', 'border-primary');
        }
    }

    function initFaq() {
        document.querySelectorAll('.faq-item').forEach((item) => {
            const answer = item.querySelector('.faq-answer');
            const arrow = item.querySelector('.faq-arrow');
            const toggleZone = item.querySelector('.faq-toggle') || item;
            if (!answer || !arrow) return;
            toggleZone.addEventListener('click', () => {
                const isOpen = !answer.classList.contains('hidden');
                answer.classList.toggle('hidden', isOpen);
                arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
                arrow.style.transition = 'transform 150ms ease';
            });
        });
    }

    window.addEventListener('DOMContentLoaded', () => {
        switchLanguage('es');
        initFaq();
    });
</script>
</body>
</html>
