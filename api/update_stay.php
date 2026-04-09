<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole('admin', true);

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);
$mysqli->query("SET NAMES {$config['charset']}");

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

// Permitir un solo objeto o un array de updates
$updates = [];
if (isset($body['updates']) && is_array($body['updates'])) {
    $updates = $body['updates'];
} else {
    $updates[] = [
        'stay_id' => $body['stay_id'] ?? null,
        'fecha_inicio' => $body['fecha_inicio'] ?? null,
        'fecha_fin' => $body['fecha_fin'] ?? null,
    ];
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay actualizaciones']);
    exit;
}

$errors = [];
$ok = 0;

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("UPDATE stays SET fecha_inicio = ?, fecha_fin = ?, status = ?, archived_at = ? WHERE id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la consulta');
    }
    foreach ($updates as $u) {
        $stayId = isset($u['stay_id']) ? (int)$u['stay_id'] : 0;
        $start = $u['fecha_inicio'] ?? null;
        $end   = $u['fecha_fin'] ?? null;
        if (!$stayId || !$start || !$end) {
            $errors[] = ['stay_id' => $stayId, 'error' => 'Faltan stay_id o fechas'];
            continue;
        }
        try {
            $startDt = new DateTime($start);
            $endDt   = new DateTime($end);
            if ($endDt < $startDt) {
                $errors[] = ['stay_id' => $stayId, 'error' => 'La fecha de fin no puede ser anterior a la de inicio'];
                continue;
            }
        } catch (Throwable $e) {
            $errors[] = ['stay_id' => $stayId, 'error' => 'Formato de fecha inválido'];
            continue;
        }
        // Obtener employee_id y status de la estancia a editar
        $metaStmt = $mysqli->prepare("SELECT employee_id, status FROM stays WHERE id = ? LIMIT 1");
        if (!$metaStmt) {
            $errors[] = ['stay_id' => $stayId, 'error' => 'No se pudo preparar meta'];
            continue;
        }
        $metaStmt->bind_param('i', $stayId);
        $metaStmt->execute();
        $metaRes = $metaStmt->get_result();
        if (!$metaRes || $metaRes->num_rows === 0) {
            $errors[] = ['stay_id' => $stayId, 'error' => 'Estancia no encontrada'];
            $metaStmt->close();
            continue;
        }
        $meta = $metaRes->fetch_assoc();
        $metaStmt->close();

        // Verificar solapamientos con otras estancias del mismo empleado
        $overlap = $mysqli->prepare("SELECT id, status, fecha_inicio, fecha_fin FROM stays WHERE employee_id = ? AND id <> ? AND fecha_inicio <= ? AND fecha_fin >= ? LIMIT 1");
        if ($overlap) {
            $overlap->bind_param('iiss', $meta['employee_id'], $stayId, $end, $start);
            $overlap->execute();
            $ovRes = $overlap->get_result();
            if ($ovRes && $ovRes->num_rows > 0) {
                $ov = $ovRes->fetch_assoc();
                $msg = ($ov['status'] === 'active')
                    ? 'Este empleado ya tiene una estancia activa.'
                    : 'Las fechas se solapan con otra estancia de este empleado.';
                $errors[] = ['stay_id' => $stayId, 'error' => $msg];
                $overlap->close();
                continue;
            }
            $overlap->close();
        }

        // Determinar nuevo status
        $today = new DateTime('today');
        $newStatus = ($endDt >= $today || $end === '2100-01-01') ? 'active' : 'archived';

        // Si va a ser activa, asegurar que no exista otra activa
        if ($newStatus === 'active') {
            $chkActive = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'active' AND id <> ? LIMIT 1");
            if ($chkActive) {
                $chkActive->bind_param('ii', $meta['employee_id'], $stayId);
                $chkActive->execute();
                $chkRes = $chkActive->get_result();
                if ($chkRes && $chkRes->num_rows > 0) {
                    $errors[] = ['stay_id' => $stayId, 'error' => 'Este empleado ya tiene una estancia activa.'];
                    $chkActive->close();
                    continue;
                }
                $chkActive->close();
            }
        }

        // archived_at: null si activa, timestamp si archivada
        $archivedAt = ($newStatus === 'archived') ? date('Y-m-d H:i:s') : null;

        $stmt->bind_param('ssssi', $start, $end, $newStatus, $archivedAt, $stayId);
        if (!$stmt->execute()) {
            $errors[] = ['stay_id' => $stayId, 'error' => $stmt->error];
            continue;
        }
        if ($stmt->affected_rows > 0) {
            $ok++;
        }
    }
    $stmt->close();
    $mysqli->commit();
} catch (Throwable $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$resp = ['success' => true, 'updated' => $ok];
if (!empty($errors)) {
    $resp['errors'] = $errors;
}
echo json_encode($resp);
