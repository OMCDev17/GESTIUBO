<?php
$isNewStay = (isset($_GET['mode']) && $_GET['mode'] === 'newstay');
$prefill = [];
if ($isNewStay) {
    require __DIR__ . '/api/auth.php';
    requireLogin();
    $u = getSessionUser() ?? [];

    // Completar datos personales desde la base de datos para prefill completo
    try {
        $config = require __DIR__ . '/api/config.php';
        $mysqli = @new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
        if (!$mysqli->connect_errno) {
            $mysqli->set_charset($config['charset']);
            if (isset($u['id'])) {
                $stmt = $mysqli->prepare('SELECT e.nombre, e.apellidos, e.dni_pasaporte, e.fecha_nacimiento, e.email, e.username, e.phone_prefix, e.phone_number, s.group_id, g.name AS grupo, e.foto_url FROM employees e LEFT JOIN stays s ON s.employee_id = e.id AND s.status = "active" LEFT JOIN groups g ON g.id = s.group_id WHERE e.id = ? LIMIT 1');
                if ($stmt) {
                    $id = (int)$u['id'];
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res && $res->num_rows === 1) {
                        $row = $res->fetch_assoc();
                        $u = array_merge($u, $row);
                    }
                    $stmt->close();
                }
            }
            $mysqli->close();
        }
    } catch (Throwable $e) {
        // Silencioso: si falla, usamos los datos de sesi?n
    }

        $prefill = [
        'nombre' => $u['nombre'] ?? '',
        'apellidos' => $u['apellidos'] ?? '',
        'dni_pasaporte' => $u['dni_pasaporte'] ?? '',
        'fecha_nacimiento' => $u['fecha_nacimiento'] ?? '',
        'email' => $u['email'] ?? '',
        'username' => $u['username'] ?? '',
        'grupo' => $u['grupo'] ?? '',
        'group_id' => $u['group_id'] ?? '',
        'foto_url' => $u['foto_url'] ?? '',
        'phone_prefix' => $u['phone_prefix'] ?? '+34',
        'phone_number' => $u['phone_number'] ?? '',
    ];
}
$prefillSafe = function ($key) use ($prefill) {
    return htmlspecialchars($prefill[$key] ?? '', ENT_QUOTES, 'UTF-8');
};
$prefillDate = function ($key) use ($prefill) {
    $v = $prefill[$key] ?? '';
    if (!$v) return '';
    try {
        $dt = new DateTime($v);
        return $dt->format('Y-m-d');
    } catch (Throwable $e) {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }
};
$groupOptions = [];
try {
    $config = require __DIR__ . '/api/config.php';
    $mysqli = @new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if (!$mysqli->connect_errno) {
        $mysqli->set_charset($config['charset']);
        $res = $mysqli->query("SELECT id, name FROM groups WHERE deleted_at IS NULL ORDER BY name");
        while ($row = $res->fetch_assoc()) {
            $groupOptions[] = $row;
        }
        $res->free();
    }
} catch (Throwable $e) {
    // Mantener $groupOptions vacío; el frontend intentará cargar vía fetch
}
?>
<!DOCTYPE html>

<html class="light" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
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
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
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
                    <img alt="Logo de la Institución" class="h-10 w-auto object-contain" src="data:image/png;base64,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" />
                    <h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] border-l border-slate-300 dark:border-slate-700 pl-4"><?= $isNewStay ? 'Nueva Estancia / New Stay' : 'Registro de Personal / Personnel Registration' ?></h2>
                </div>
            </header>
            <main class="flex-1 flex justify-center py-10 px-4 md:px-0">
                <div class="w-full max-w-[800px] flex flex-col gap-6">
                    <!-- Hero/Banner Section -->
                    <div class="relative w-full h-48 overflow-hidden rounded-xl shadow-sm border border-slate-200 dark:border-slate-800">
                        <div class="absolute inset-0 bg-gradient-to-r from-primary/90 to-primary/40 z-10"></div>
                        <img alt="Laboratory Background" class="absolute inset-0 w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAZINg3o_zUPuS5vXuAfwcYBTPAY7UjRzIkRxchiRppJOXJBYT77Q4ZjA6ibEOrIGyCZJm_6886aBItSr_RkLtAaQ6FaAoNDGT08M-qYujjz5SOp4Z0euT6ZLlandn9BC1XDLC9RZzDwHHE9LQJaCDDxUepy-vii0jOsCshBOOTfYTUvyoLltdOSHSVKEcGqiuUPaw5svrDnuc-KTMst5JN7kDCGOx4YoU3CtE1b74EN_nQCMwnPu-1mKtJjd674GTO8ufwI2YAk6o" />
                        <div class="relative z-20 h-full flex flex-col justify-end p-8">
                            <h1 class="text-white text-3xl font-bold"><?= $isNewStay ? 'Nueva Estancia / New Stay' : 'Alta de Miembro / Member Registration' ?></h1>
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
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12 <?= $isNewStay ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' ?>" placeholder="Ej: Francisco / e.g., Francis" type="text" name="nombre" value="<?= $isNewStay ? $prefillSafe('nombre') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Apellidos / Surnames</p>
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12 <?= $isNewStay ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' ?>" placeholder="Ej: García Martínez / e.g., Smith Jones" type="text" name="apellidos" value="<?= $isNewStay ? $prefillSafe('apellidos') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2 md:col-span-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">DNI/Pasaporte / DNI/Passport</p>
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12 <?= $isNewStay ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' ?>" placeholder="77777777J" type="text" name="dni_pasaporte" value="<?= $isNewStay ? $prefillSafe('dni_pasaporte') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Fecha de Nacimiento / Date of Birth</p>
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-600 dark:text-slate-300 <?= $isNewStay ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' ?>" type="date" name="fecha_nacimiento" value="<?= $isNewStay ? $prefillDate('fecha_nacimiento') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Email / Email</p>
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12 <?= $isNewStay ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' ?>" placeholder="ejemplo@universidad.es / example@university.edu" type="email" name="email" value="<?= $isNewStay ? $prefillSafe('email') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Teléfono - Prefijo / Phone - Country Code</p>
                                            <select class="form-select rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12" name="phone_prefix" required>
                                                <option value="+34" selected>🇪🇸 España / Spain (+34)</option>
                                                <option value="+1">🇺🇸 Estados Unidos / USA (+1)</option>
                                                <option value="+44">🇬🇧 Reino Unido / UK (+44)</option>
                                                <option value="+33">🇫🇷 Francia / France (+33)</option>
                                                <option value="+49">🇩🇪 Alemania / Germany (+49)</option>
                                                <option value="+39">🇮🇹 Italia / Italy (+39)</option>
                                                <option value="+81">🇯🇵 Japón / Japan (+81)</option>
                                                <option value="+86">🇨🇳 China (+86)</option>
                                                <option value="+91">🇮🇳 India (+91)</option>
                                                <option value="+55">🇧🇷 Brasil / Brazil (+55)</option>
                                                <option value="+52">🇲🇽 México / Mexico (+52)</option>
                                                <option value="+54">🇦🇷 Argentina (+54)</option>
                                                <option value="+56">🇨🇱 Chile (+56)</option>
                                                <option value="+506">🇨🇷 Costa Rica (+506)</option>
                                                <option value="+57">🇨🇴 Colombia (+57)</option>
                                                <option value="+51">🇵🇪 Perú / Peru (+51)</option>
                                                <option value="+58">🇻🇪 Venezuela (+58)</option>
                                                <option value="+36">🇭🇺 Hungría / Hungary (+36)</option>
                                                <option value="+48">🇵🇱 Polonia / Poland (+48)</option>
                                                <option value="+31">🇳🇱 Países Bajos / Netherlands (+31)</option>
                                                <option value="+32">🇧🇪 Bélgica / Belgium (+32)</option>
                                                <option value="+43">🇦🇹 Austria (+43)</option>
                                                <option value="+41">🇨🇭 Suiza / Switzerland (+41)</option>
                                                <option value="+46">🇸🇪 Suecia / Sweden (+46)</option>
                                                <option value="+47">🇳🇴 Noruega / Norway (+47)</option>
                                                <option value="+45">🇩🇰 Dinamarca / Denmark (+45)</option>
                                                <option value="+358">🇫🇮 Finlandia / Finland (+358)</option>
                                                <option value="+30">🇬🇷 Grecia / Greece (+30)</option>
                                                <option value="+60">🇲🇾 Malasia / Malaysia (+60)</option>
                                                <option value="+65">🇸🇬 Singapur / Singapore (+65)</option>
                                                <option value="+62">🇮🇩 Indonesia (+62)</option>
                                                <option value="+66">🇹🇭 Tailandia / Thailand (+66)</option>
                                                <option value="+84">🇻🇳 Vietnam (+84)</option>
                                                <option value="+82">🇰🇷 Corea del Sur / South Korea (+82)</option>
                                                <option value="+61">🇦🇺 Australia (+61)</option>
                                                <option value="+64">🇳🇿 Nueva Zelanda / New Zealand (+64)</option>
                                                <option value="+27">🇿🇦 Sudáfrica / South Africa (+27)</option>
                                                <option value="+20">🇪🇬 Egipto / Egypt (+20)</option>
                                                <option value="+212">🇲🇦 Marruecos / Morocco (+212)</option>
                                            </select>
                                        </label>
                                        <label class="flex flex-col gap-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Teléfono - Número / Phone - Number <span class="text-red-500">*</span></p>
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="ej: 612345678 / e.g., 612345678" type="tel" name="phone_number" value="<?= $isNewStay ? $prefillSafe('phone_number') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Usuario / Username</p>
                                            <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12 <?= $isNewStay ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' ?>" placeholder="ej: francisco.garcia" type="text" name="username" value="<?= $isNewStay ? $prefillSafe('username') : '' ?>" <?= $isNewStay ? 'readonly aria-readonly=\"true\"' : '' ?> required />
                                        </label>
                                        <label class="flex flex-col gap-2 md:col-span-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold flex items-center justify-between">
                                                <span>Contraseña / Password</span>
                                                <button type="button" class="toggle-pass text-sm text-primary hover:text-primary/80 flex items-center gap-1" data-target="password" aria-label="Mostrar u ocultar contraseña">
                                                    <span class="material-symbols-outlined text-base">visibility</span>
                                                </button>
                                            </p>
                                            <input id="password" class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="*******" type="password" name="password" <?= $isNewStay ? "disabled aria-disabled=\"true\"" : "required" ?> />
                                        </label>
                                        <label class="flex flex-col gap-2 md:col-span-2">
                                            <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold flex items-center justify-between">
                                                <span>Repetir contraseña / Confirm Password</span>
                                                <button type="button" class="toggle-pass text-sm text-primary hover:text-primary/80 flex items-center gap-1" data-target="password_confirm" aria-label="Mostrar u ocultar contraseña">
                                                    <span class="material-symbols-outlined text-base">visibility</span>
                                                </button>
                                            </p>
                                            <input id="password_confirm" class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="*******" type="password" name="password_confirm" <?= $isNewStay ? "disabled aria-disabled=\"true\"" : "required" ?> />
                                        </label>
                                    </div>
                                    <div class="lg:col-span-1">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Foto / Photo</p>
                                        <label for="photoUpload" class="group relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition">
                                            <input id="photoUpload" name="photo" type="file" accept="image/*" class="sr-only" />
                                            <span id="photoDropHint" class="flex flex-col items-center gap-2">
                                                <span class="material-symbols-outlined text-4xl text-primary">photo_camera</span>
                                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Arrastra y suelta aquí una foto / Drag & drop a photo here</span>
                                                <span class="text-xs text-slate-500 dark:text-slate-400">JPG/PNG hasta 5?MB / JPG/PNG up to 5?MB</span>
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
                            <hr class="border-slate-100 dark:border-slate-800" />
                            <!-- Section: Datos Institucionales -->
                            <div>
                                <h3 class="text-primary text-sm font-bold uppercase tracking-wider mb-6 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">school</span>
                                    Origen Académico / Academic Background
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <label class="flex flex-col gap-2">
                                        <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Institución de Origen / Origin Institution</p>
                                        <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="Chicago University" type="text" name="institucion" required />
                                    </label>
                                    <label class="flex flex-col gap-2">
                                        <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">País de la institución / Institution Country</p>
                                        <input class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary placeholder:text-slate-400 dark:placeholder:text-slate-600 h-12" placeholder="United States" type="text" name="pais" required />
                                    </label>
                                </div>
                            </div>
                            <hr class="border-slate-100 dark:border-slate-800" />
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
                                            <option value="PDI">PDI (Personal Docente de Investigacion / Teaching and Research Staff)</option>
                                            <option value="Postdoctoral">Investigador Postdoctoral / Postdoctoral Researcher</option>
                                            <option value="Predoctoral">Investigador Predoctoral / Predoctoral Researcher</option>
                                            <option value="TFG">TFG (Trabajo Final de Grado / Bachelor's Thesis)</option>
                                            <option value="TFM">TFM (Trabajo Final de Máster / Master's Thesis)</option>
                                            <option value="ERASMUS">ERASMUS</option>
                                            <option value="Visitante">Investigador Visitante / Visiting Researcher</option>
                                            <option value="Otros">Otros / Others</option>
                                        </select>
                                    </label>
                                    <label class="flex flex-col gap-2">
                                        <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Fecha de Inicio / Start Date</p>
                                        <input id="fechaInicio" name="fecha_inicio" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-600 dark:text-slate-300" type="date" />
                                    </label>
                                    <label class="flex flex-col gap-2">
                                        <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold">Fecha de Finalización / End Date</p>
                                        <input id="fechaFin" name="fecha_fin" required class="form-input rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-600 dark:text-slate-300" type="date" />
                                    </label>
                                </div>
                                <label class="mt-3 flex items-center justify-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    <input id="personalFijo" type="checkbox" class="h-4 w-4 text-primary border-slate-300 dark:border-slate-700 rounded">
                                    Soy personal fijo / I am permanent staff
                                </label>
                                <div class="mt-6">
                                    <label class="flex flex-col gap-2 mb-4">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Grupo / Group</p>
                                        <select name="group_id" required class="form-select rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-primary focus:border-primary h-12 text-slate-700 dark:text-slate-300">
                                            <option disabled selected value="">Seleccione grupo / Select group</option>
                                            <?php foreach ($groupOptions as $g): ?>
                                                <option value="<?= htmlspecialchars($g['id']) ?>" <?= ($isNewStay && isset($prefill['group_id']) && (string)$prefill['group_id'] === (string)$g['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($g['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <!-- Submission -->
                            <div class="flex flex-col gap-4 mt-4">
                                <!-- Privacy Policy Checkbox -->
                                <div class="flex items-start gap-3 p-4 bg-primary/5 rounded-lg border border-primary/10">
                                    <input id="acceptPrivacy" type="checkbox" name="accept_privacy" required class="h-4 w-4 text-primary border-slate-300 dark:border-slate-700 rounded mt-0.5 flex-shrink-0">
                                    <label for="acceptPrivacy" class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed cursor-pointer">
                                        Acepto la <a href="#" id="privacyLinkEs" class="font-semibold text-primary hover:underline">política de privacidad</a> y el tratamiento de datos para fines académicos de la institución. / I accept the <a href="#" id="privacyLinkEn" class="font-semibold text-primary hover:underline">privacy policy</a> and data processing for academic purposes of the institution.
                                    </label>
                                </div>
                                <!-- Confidentiality Commitment Checkbox -->
                                <div class="flex items-start gap-3 p-4 bg-primary/5 rounded-lg border border-primary/10">
                                    <input id="acceptConfidentiality" type="checkbox" name="accept_confidentiality" required class="h-4 w-4 text-primary border-slate-300 dark:border-slate-700 rounded mt-0.5 flex-shrink-0">
                                    <label for="acceptConfidentiality" class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed cursor-pointer">
                                        Acepto el <a href="#" id="confidentialityLinkEs" class="font-semibold text-primary hover:underline">compromiso de confidencialidad</a>. / I accept the <a href="#" id="confidentialityLinkEn" class="font-semibold text-primary hover:underline">confidentiality commitment</a>.
                                    </label>
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
                        © 2026 GestIUBO. Todos los derechos reservados / All rights reserved.
                    </footer>
                </div>
            </main>
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
            const palette = {
                success: 'bg-primary text-white',
                error: 'bg-primary text-white',
                info: 'bg-primary text-white'
            };
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto min-w-[240px] max-w-xs rounded-lg shadow-lg px-4 py-3 text-sm font-semibold ${palette[variant] || palette.info}`;
            toast.textContent = message;
            toastHost.appendChild(toast);
            setTimeout(() => toast.remove(), 3200);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const isNewStay = <?php echo $isNewStay ? 'true' : 'false'; ?>;
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
            const groupSelect = document.querySelector('select[name="group_id"]');
            const form = document.getElementById('registrationForm');
            const initialPhotoUrl = <?php echo json_encode($isNewStay ? ($prefill['foto_url'] ?? '') : ''); ?>;
            const prefillGroupId = <?php echo json_encode($isNewStay ? ($prefill['group_id'] ?? '') : ''); ?>;
            if (isNewStay && form) {
                ['nombre', 'apellidos', 'dni_pasaporte', 'fecha_nacimiento', 'email', 'username'].forEach((name) => {
                    const el = form.querySelector(`[name="${name}"]`);
                    if (el) {
                        el.setAttribute('readonly', 'true');
                        el.classList.add('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                    }
                });
                [password, passwordConfirm].forEach((el) => {
                    if (el) {
                        el.value = '';
                        el.disabled = true;
                        el.removeAttribute('required');
                    }
                });
            }

            // Carga dinámica de grupos desde la base de datos
            const renderGroupPlaceholder = (text) => {
                if (!groupSelect) return;
                groupSelect.innerHTML = '';
                const opt = document.createElement('option');
                opt.disabled = true;
                opt.selected = true;
                opt.value = '';
                opt.textContent = text;
                groupSelect.appendChild(opt);
            };
            const hasServerRenderedGroups = groupSelect && groupSelect.options.length > 1;
            if (!hasServerRenderedGroups) {
                renderGroupPlaceholder('Cargando grupos...');
            }

            const loadGroups = async () => {
                if (!groupSelect) return;
                try {
                    const resp = await fetch('api/groups.php');
                    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                    const json = await resp.json();
                    const groups = Array.isArray(json.groups) ?
                        json.groups.filter((g) => !g.deleted_at) :
                        [];
                    if (groups.length === 0) throw new Error('No hay grupos configurados');

                    groupSelect.innerHTML = '';
                    const placeholder = document.createElement('option');
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    placeholder.value = '';
                    placeholder.textContent = 'Seleccione grupo / Select group';
                    groupSelect.appendChild(placeholder);

                    groups
                        .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
                        .forEach((g) => {
                            const opt = document.createElement('option');
                            opt.value = g.id;
                            opt.textContent = g.name;
                            if (prefillGroupId && String(prefillGroupId) === String(g.id)) {
                                opt.selected = true;
                            }
                            groupSelect.appendChild(opt);
                        });
                } catch (error) {
                    console.error('No se pudieron cargar los grupos', error);
                    if (!hasServerRenderedGroups) {
                        renderGroupPlaceholder('No se pudieron cargar los grupos');
                    }
                }
            };

            loadGroups();

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
            if (initialPhotoUrl) {
                photoPreviewImg.src = initialPhotoUrl;
                photoFileName.textContent = 'Foto actual';
                photoDropHint.classList.add('hidden');
                photoPreview.classList.remove('hidden');
            }

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

            // Validación de disponibilidad de email y username (solo en registro nuevo, no en newstay)
            const availabilityErrors = new Map();
            const emailInput = form?.querySelector('input[name="email"]');
            const usernameInput = form?.querySelector('input[name="username"]');
            let checkTimeout;

            const removeFieldError = (field) => {
                if (!field) return;
                field.classList.remove('input-error');
                const helper = field.parentElement?.querySelector('.field-error');
                if (helper) helper.remove();
            };

            const addFieldError = (field, message) => {
                if (!field) return;
                field.classList.add('input-error');
                if (!field.parentElement) return;
                removeFieldError(field);
                const helper = document.createElement('p');
                helper.className = 'field-error text-xs text-red-600 mt-1';
                helper.textContent = message;
                field.parentElement.appendChild(helper);
            };

            const checkAvailability = async (field, type) => {
                const value = field?.value?.trim();
                if (!value) {
                    removeFieldError(field);
                    availabilityErrors.delete(type);
                    return;
                }

                try {
                    const resp = await fetch(`api/check_availability.php?type=${type}&value=${encodeURIComponent(value)}`);
                    const json = await resp.json();
                    
                    if (json.available) {
                        removeFieldError(field);
                        availabilityErrors.delete(type);
                    } else {
                        const msg = type === 'email' 
                            ? 'Este email ya está registrado / This email is already registered'
                            : 'Este usuario ya existe / This username already exists';
                        addFieldError(field, msg);
                        availabilityErrors.set(type, msg);
                    }
                } catch (error) {
                    console.error(`Error checking ${type} availability:`, error);
                }
            };

            // Validar disponibilidad de form en submit (antes de enviar)
            const validateAvailability = async () => {
                if (isNewStay) return; // No validar en modo newstay
                const checks = [];
                if (emailInput?.value?.trim()) {
                    checks.push(checkAvailability(emailInput, 'email'));
                }
                if (usernameInput?.value?.trim()) {
                    checks.push(checkAvailability(usernameInput, 'username'));
                }
                if (checks.length > 0) {
                    await Promise.all(checks);
                }
            };

            // Solo validar disponibilidad si NO es modo newstay
            if (!isNewStay) {
                emailInput?.addEventListener('blur', () => {
                    clearTimeout(checkTimeout);
                    checkTimeout = setTimeout(() => checkAvailability(emailInput, 'email'), 300);
                });

                usernameInput?.addEventListener('blur', () => {
                    clearTimeout(checkTimeout);
                    checkTimeout = setTimeout(() => checkAvailability(usernameInput, 'username'), 300);
                });
            }

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
            privacyModal?.addEventListener('click', (event) => {
                if (event.target === privacyModal) closePrivacyModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closePrivacyModal();
            });

            // Clear errors on checkbox change
            const acceptPrivacy = document.getElementById('acceptPrivacy');
            const acceptConfidentiality = document.getElementById('acceptConfidentiality');
            
            acceptPrivacy?.addEventListener('change', () => {
                const label = form?.querySelector('label[for="acceptPrivacy"]');
                if (label) {
                    label.classList.remove('input-error');
                    const helper = label.querySelector('.field-error');
                    if (helper) helper.remove();
                }
            });
            
            acceptConfidentiality?.addEventListener('change', () => {
                const label = form?.querySelector('label[for="acceptConfidentiality"]');
                if (label) {
                    label.classList.remove('input-error');
                    const helper = label.querySelector('.field-error');
                    if (helper) helper.remove();
                }
            });

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
                if (el.disabled) return false;
                // En nueva estancia solo saltamos las credenciales; datos personales sí deben ir (ya vienen pre-rellenados)
                if (isNewStay && (el.name === 'password' || el.name === 'password_confirm')) return false;
                return el.hasAttribute('required');
            });

            const requiredCheckboxes = Array.from(form?.querySelectorAll('input[type="checkbox"][required]') || []);
            const titleCaseFieldNames = ['nombre', 'apellidos', 'institucion', 'pais'];
            const titleCaseFields = titleCaseFieldNames
                .map((name) => form?.querySelector(`[name="${name}"]`))
                .filter(Boolean);

            const normalizeTitleCase = (value) => {
                const clean = String(value ?? '').trim().replace(/\s+/g, ' ');
                if (!clean) return '';
                const lower = clean.toLocaleLowerCase('es-ES');
                const particles = new Set(['de', 'del', 'la', 'las', 'los', 'y', 'e', 'o', 'u', 'da', 'das', 'do', 'dos', 'di', 'van', 'von']);
                const capitalizeWord = (word) => {
                    if (!word) return word;
                    return word
                        .split('-')
                        .map((part) => part ? part.charAt(0).toLocaleUpperCase('es-ES') + part.slice(1) : part)
                        .join('-')
                        .split("'")
                        .map((part) => part ? part.charAt(0).toLocaleUpperCase('es-ES') + part.slice(1) : part)
                        .join("'");
                };

                return lower
                    .split(' ')
                    .map((word, index) => (index > 0 && particles.has(word) ? word : capitalizeWord(word)))
                    .join(' ');
            };

            titleCaseFields.forEach((field) => {
                field.addEventListener('blur', () => {
                    field.value = normalizeTitleCase(field.value);
                });
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

                // IMPORTANTE: Validar disponibilidad de email/username ANTES de otras validaciones
                await validateAvailability();

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

                // Validate required checkboxes
                requiredCheckboxes.forEach((checkbox) => {
                    if (!checkbox.checked) {
                        const label = form?.querySelector(`label[for="${checkbox.id}"]`);
                        if (label) {
                            label.classList.add('input-error');
                            showFieldError(label, 'Debe aceptar este término / You must accept this term');
                        }
                        if (!firstInvalid) firstInvalid = checkbox;
                    }
                });

                // Validar disponibilidad de email y username (después de haber ejecutado validateAvailability)
                if (availabilityErrors.size > 0) {
                    const errorMsg = Array.from(availabilityErrors.values()).join('. ');
                    showToast(errorMsg, 'error');
                    if (!firstInvalid) {
                        firstInvalid = availabilityErrors.has('email') ? emailInput : usernameInput;
                    }
                }

                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    releaseSubmit();
                    return;
                }

                // Password match validation
                if (!isNewStay && password && passwordConfirm && password.value !== passwordConfirm.value) {
                    showFieldError(password, 'Las contraseñas no coinciden / Passwords do not match');
                    showFieldError(passwordConfirm, 'Las contraseñas no coinciden / Passwords do not match');
                    password.focus();
                    password.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    releaseSubmit();
                    return;
                }

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                titleCaseFieldNames.forEach((name) => {
                    if (Object.prototype.hasOwnProperty.call(data, name)) {
                        const normalized = normalizeTitleCase(data[name]);
                        data[name] = normalized;
                        const field = form.querySelector(`[name="${name}"]`);
                        if (field) field.value = normalized;
                    }
                });
                if (isNewStay) {
                    delete data.password;
                    delete data.password_confirm;
                }
                if (fijo) {
                    const hoy = new Date().toISOString().split('T')[0];
                    data.fecha_inicio = hoy;
                    data.fecha_fin = '2100-01-01';
                }

                if (selectedPhoto) {
                    try {
                        const uploadFd = new FormData();
                        uploadFd.append('photo', selectedPhoto);
                        const upRes = await fetch('api/upload_photo.php', {
                            method: 'POST',
                            body: uploadFd
                        });
                        const upJson = await upRes.json().catch(() => ({}));
                        if (!upRes.ok || upJson.error) {
                            showToast(upJson.error || 'No se pudo subir la foto. Revisa tu conexión.', 'error');
                            releaseSubmit();
                            return;
                        }
                        data.foto_url = upJson.url;
                    } catch (error) {
                        console.error('Upload error', error);
                        showToast('No se pudo conectar con el servidor al subir la foto.', 'error');
                        releaseSubmit();
                        return;
                    }
                }

                try {
                    const endpoint = isNewStay ? 'api/new_stay.php' : 'api/register.php';
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data),
                    });
                    let json;
                    try {
                        json = await res.json();
                    } catch (_) {
                        const text = await res.text().catch(() => '');
                        if (res.status === 401) {
                            showToast('Sesión expirada. Vuelve a iniciar sesión.', 'error');
                            window.location.href = 'Loggin.php';
                            return;
                        }
                        showToast(text || 'No se pudo conectar con el servidor.', 'error');
                        releaseSubmit();
                        return;
                    }
                    if (!res.ok || (json && json.error)) {
                        const message = (json && json.error) || 'Error al registrar. Inténtelo de nuevo.';
                        const lower = message.toLowerCase();
                        const targets = [];
                        // Asignar campo según mensaje
                        if (lower.includes('usuario')) targets.push(form.querySelector('input[name="username"]'));
                        if (lower.includes('dni')) targets.push(form.querySelector('input[name="dni_pasaporte"]'));
                        if (lower.includes('inicio')) targets.push(fechaInicio);
                        if (lower.includes('fin')) targets.push(fechaFin);
                        if (lower.includes('fecha')) {
                            targets.push(fechaInicio, fechaFin);
                        }
                        // Duplicados explícitos (409) se mantienen
                        if (res.status === 409 && targets.length === 0) {
                            const targetField = json.field || 'username';
                            const fieldEl = form.querySelector(`input[name="${targetField}"]`) ||
                                form.querySelector('input[name="username"]');
                            if (fieldEl) targets.push(fieldEl);
                        }
                        const cleanedTargets = targets.filter(Boolean);
                        if (cleanedTargets.length > 0) {
                            cleanedTargets.forEach((field) => showFieldError(field, message));
                            const first = cleanedTargets[0];
                            first.focus();
                            first.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            releaseSubmit();
                            return;
                        }
                        showToast(message, 'error');
                        releaseSubmit();
                        return;
                    }
                    form.classList.add('ring-2', 'ring-primary/40');
                    setTimeout(() => form.classList.remove('ring-2', 'ring-primary/40'), 600);
                    form.reset();
                    toggleFechas();
                    resetPreview();
                    window.location.href = isNewStay ? 'empleado.php' : 'registro_exitoso.php';
                } catch (error) {
                    console.error(error);
                    showToast('No se pudo conectar con el servidor.', 'error');
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
                <button type="button" id="privacyClose" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100" aria-label="Cerrar">X</button>
            </div>
            <div class="p-5 max-h-[60vh] overflow-y-auto text-sm text-slate-700 dark:text-slate-200 space-y-4">
                <p><strong>Política de Privacidad</strong>  Al registrarte en este sitio web y/o utilizar nuestros servicios, declaras haber leído, comprendido y aceptado los Términos y Condiciones de Uso, así como la Política de Privacidad.

En cumplimiento de lo dispuesto en el Reglamento (UE) 2016/679 (Reglamento General de Protección de Datos – RGPD) y en la Ley Orgánica 3/2018 (LOPDGDD), te informamos de que los datos personales facilitados serán tratados de forma confidencial y se incorporarán a un fichero responsabilidad del titular de esta web.

La finalidad del tratamiento de los datos es la gestión de la relación con el usuario, la prestación de los servicios solicitados y, en su caso, el envío de comunicaciones comerciales, siempre que se haya otorgado el consentimiento expreso.

Asimismo, se informa al usuario de que puede ejercer sus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad de sus datos, mediante solicitud escrita dirigida al responsable del tratamiento.

El usuario garantiza que los datos proporcionados son veraces y se compromete a comunicar cualquier modificación de los mismos.</p>
                <p><strong>Data Privacy Policy</strong>  By registering on this website and/or using our services, you declare that you have read, understood, and accepted the Terms and Conditions of Use and the Privacy Policy.

In accordance with the provisions of Regulation (EU) 2016/679 (General Data Protection Regulation – GDPR) and Organic Law 3/2018 (LOPDGDD), we inform you that the personal data provided will be processed confidentially and incorporated into a file under the responsibility of the website owner.

The purpose of data processing is to manage the relationship with the user, provide the requested services, and, where applicable, send commercial communications, provided that explicit consent has been given.

You are also informed that you may exercise your rights of access, rectification, erasure, objection, restriction of processing, and data portability by submitting a written request to the data controller.

The user guarantees that the data provided is accurate and undertakes to notify any changes.</p>
                <p>Para más detalles, consulte la normativa de protección de datos vigente en España (LOPDGDD y RGPD) o contacte con el administrador del sistema. / For more details, consult the current data protection regulations in Spain (LOPDGDD and GDPR) or contact the system administrator.</p>
            </div>
        </div>
    </div>
</body>

</html>
