<?php
$config = require __DIR__ . '/api/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo 'Error de conexion con la base de datos.';
    exit;
}
$mysqli->set_charset($config['charset']);

$mysqli->query("
CREATE TABLE IF NOT EXISTS group_join_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    group_id INT NOT NULL,
    requested_by_email VARCHAR(255) NOT NULL,
    requested_by_name VARCHAR(255) NOT NULL,
    motivo VARCHAR(150) NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    horario TINYINT(1) NOT NULL DEFAULT 1,
    institucion VARCHAR(255) NULL,
    pais VARCHAR(255) NULL,
    approval_token VARCHAR(64) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    email_sent_at DATETIME NULL,
    approved_at DATETIME NULL,
    approved_by_employee_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_group_join_requests_token (approval_token),
    INDEX idx_group_join_requests_employee (employee_id),
    INDEX idx_group_join_requests_group (group_id),
    INDEX idx_group_join_requests_status (status)
)");
$mysqli->query("
CREATE TABLE IF NOT EXISTS stays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    motivo VARCHAR(150) NULL,
    group_id INT NULL,
    horario TINYINT(1) NOT NULL DEFAULT 1,
    institucion VARCHAR(255) NULL,
    pais VARCHAR(255) NULL,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    archived_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stays_employee (employee_id),
    INDEX idx_stays_status (status)
)");

$token = trim((string)($_GET['token'] ?? ''));
if ($token === '') {
    http_response_code(400);
    echo 'Falta el token de aprobacion.';
    exit;
}

$stmt = $mysqli->prepare("
    SELECT r.*, g.name AS group_name, e.nombre, e.apellidos, e.email
    FROM group_join_requests r
    INNER JOIN employees e ON e.id = r.employee_id
    INNER JOIN groups g ON g.id = r.group_id
    WHERE r.approval_token = ?
    LIMIT 1
");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$request = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$request) {
    http_response_code(404);
    echo 'Solicitud no encontrada.';
    exit;
}

if (($request['status'] ?? '') === 'approved') {
    $message = 'Esta solicitud ya habia sido aprobada anteriormente.';
} elseif (($request['status'] ?? '') !== 'pending') {
    $message = 'Esta solicitud ya no se encuentra pendiente.';
} else {
    $mysqli->begin_transaction();
    try {
        $checkActive = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'active' LIMIT 1");
        $employeeId = (int)$request['employee_id'];
        $checkActive->bind_param('i', $employeeId);
        $checkActive->execute();
        $activeRes = $checkActive->get_result();
        $activeStay = $activeRes ? $activeRes->fetch_assoc() : null;
        $checkActive->close();

        if ($activeStay) {
            throw new RuntimeException('El usuario ya tiene una estancia activa.');
        }

        $ins = $mysqli->prepare("
            INSERT INTO stays (employee_id, fecha_inicio, fecha_fin, motivo, group_id, horario, institucion, pais, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $groupId = (int)$request['group_id'];
        $horario = (int)$request['horario'];
        $ins->bind_param(
            'isssiiss',
            $employeeId,
            $request['fecha_inicio'],
            $request['fecha_fin'],
            $request['motivo'],
            $groupId,
            $horario,
            $request['institucion'],
            $request['pais']
        );
        $ins->execute();
        $ins->close();

        $approvedByEmployeeId = null;
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        if (!empty($_SESSION['user']['id'])) {
            $approvedByEmployeeId = (int)$_SESSION['user']['id'];
        }

        $upd = $mysqli->prepare("
            UPDATE group_join_requests
            SET status = 'approved', approved_at = NOW(), approved_by_employee_id = ?
            WHERE id = ? AND status = 'pending'
            LIMIT 1
        ");
        $requestId = (int)$request['id'];
        $upd->bind_param('ii', $approvedByEmployeeId, $requestId);
        $upd->execute();
        $upd->close();

        $mysqli->commit();
        $message = 'Solicitud aprobada correctamente. El empleado ya pertenece al grupo.';
    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        $message = 'No se pudo aprobar la solicitud: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobacion de grupo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
        <h1 class="text-2xl font-bold text-slate-900 mb-3">Aprobacion de grupo</h1>
        <p class="text-slate-600 mb-6"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-700">
            <p><strong>Empleado:</strong> <?php echo htmlspecialchars(trim(($request['nombre'] ?? '') . ' ' . ($request['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Grupo:</strong> <?php echo htmlspecialchars((string)($request['group_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars((string)($request['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="mt-6">
            <a href="Loggin.php" class="inline-flex items-center rounded-xl bg-slate-900 text-white px-5 py-3 font-semibold hover:bg-slate-700 transition">Ir al login</a>
        </div>
    </div>
</body>
</html>
