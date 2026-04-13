<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole(['supervisor', 'coordinador', 'admin']);

$config = require __DIR__ . '/config.php';
$method = $_SERVER['REQUEST_METHOD'];
$user = getSessionUser();

$sendError = function(int $code, string $msg) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
};

try {
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    $mysqli->set_charset($config['charset']);
} catch (Throwable $e) {
    $sendError(500, 'Error de conexión con la base de datos');
}

// GET: Obtener solicitudes pendientes para el supervisor del grupo actual
if ($method === 'GET') {
    try {
        // Obtener el grupo del supervisor actual
        $supervisorId = (int)($user['id'] ?? 0);
        if (!$supervisorId) {
            $sendError(401, 'Usuario no autenticado');
        }

        // Obtener el grupo activo del supervisor
        $stayStmt = $mysqli->prepare("
            SELECT DISTINCT group_id
            FROM stays
            WHERE employee_id = ?
              AND status = 'active'
            LIMIT 1
        ");
        $stayStmt->bind_param('i', $supervisorId);
        $stayStmt->execute();
        $stayRes = $stayStmt->get_result();
        $stayRow = $stayRes ? $stayRes->fetch_assoc() : null;
        $stayStmt->close();

        if (!$stayRow) {
            // Si no tiene grupo activo, retornar array vacío
            echo json_encode(['requests' => []]);
            exit;
        }

        $groupId = (int)$stayRow['group_id'];

        // Obtener solicitudes pendientes para este grupo
        $reqStmt = $mysqli->prepare("
            SELECT 
                gjr.id,
                gjr.employee_id,
                gjr.group_id,
                gjr.requested_by_name,
                gjr.requested_by_email,
                gjr.motivo,
                gjr.fecha_inicio,
                gjr.fecha_fin,
                gjr.horario,
                gjr.institucion,
                gjr.pais,
                gjr.status,
                gjr.created_at,
                e.nombre,
                e.apellidos,
                e.email,
                g.name AS group_name
            FROM group_join_requests gjr
            INNER JOIN employees e ON e.id = gjr.employee_id
            INNER JOIN groups g ON g.id = gjr.group_id
            WHERE gjr.group_id = ? AND gjr.status = 'pending'
            ORDER BY gjr.created_at DESC
        ");
        if (!$reqStmt) {
            $sendError(500, 'Error preparando consulta');
        }
        $reqStmt->bind_param('i', $groupId);
        $reqStmt->execute();
        $reqRes = $reqStmt->get_result();
        $requests = [];
        while ($row = $reqRes->fetch_assoc()) {
            $requests[] = $row;
        }
        $reqStmt->close();

        echo json_encode(['requests' => $requests]);
        exit;
    } catch (Throwable $e) {
        $sendError(500, $e->getMessage());
    }
}

// POST: Aprobar o rechazar una solicitud
if ($method === 'POST') {
    try {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            $sendError(400, 'Payload inválido');
        }

        $requestId = isset($body['request_id']) ? (int)$body['request_id'] : null;
        $action = strtolower(trim($body['action'] ?? ''));

        if (!$requestId || !in_array($action, ['approve', 'reject'])) {
            $sendError(400, 'Faltan parámetros o acción inválida');
        }

        // Obtener la solicitud
        $getReq = $mysqli->prepare("
            SELECT gjr.*, g.name AS group_name, e.nombre, e.apellidos, e.email
            FROM group_join_requests gjr
            INNER JOIN employees e ON e.id = gjr.employee_id
            INNER JOIN groups g ON g.id = gjr.group_id
            WHERE gjr.id = ? AND gjr.status = 'pending'
            LIMIT 1
        ");
        $getReq->bind_param('i', $requestId);
        $getReq->execute();
        $getReqRes = $getReq->get_result();
        $request = $getReqRes ? $getReqRes->fetch_assoc() : null;
        $getReq->close();

        if (!$request) {
            $sendError(404, 'Solicitud no encontrada o ya procesada');
        }

        // Verificar que el usuario pertenece al grupo
        $supervisorId = (int)($user['id'] ?? 0);
        $groupId = (int)($request['group_id'] ?? 0);
        $checkSuper = $mysqli->prepare("
            SELECT 1 FROM stays
            WHERE employee_id = ? AND group_id = ? AND status = 'active'
            LIMIT 1
        ");
        $checkSuper->bind_param('ii', $supervisorId, $groupId);
        $checkSuper->execute();
        $checkSuperRes = $checkSuper->get_result();
        if (!$checkSuperRes || $checkSuperRes->num_rows === 0) {
            $sendError(403, 'No tienes permiso para procesar esta solicitud');
        }
        $checkSuper->close();

        $mysqli->begin_transaction();

        try {
            if ($action === 'approve') {
                // Verificar que no hay estancia activa
                $checkActive = $mysqli->prepare("
                    SELECT id FROM stays 
                    WHERE employee_id = ? AND status = 'active'
                    LIMIT 1
                ");
                $employeeId = (int)$request['employee_id'];
                $checkActive->bind_param('i', $employeeId);
                $checkActive->execute();
                $activeRes = $checkActive->get_result();
                if ($activeRes && $activeRes->num_rows > 0) {
                    throw new RuntimeException('El usuario ya tiene una estancia activa');
                }
                $checkActive->close();

                // Crear la estancia
                $ins = $mysqli->prepare("
                    INSERT INTO stays (
                        employee_id, fecha_inicio, fecha_fin, motivo, group_id,
                        horario, institucion, pais, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
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

                // Actualizar solicitud
                $upd = $mysqli->prepare("
                    UPDATE group_join_requests
                    SET status = 'approved', approved_at = NOW(), approved_by_employee_id = ?
                    WHERE id = ? AND status = 'pending'
                    LIMIT 1
                ");
                $upd->bind_param('ii', $supervisorId, $requestId);
                $upd->execute();
                $upd->close();

                $resultMsg = 'Solicitud aprobada correctamente';
            } else {
                // Rechazar
                $upd = $mysqli->prepare("
                    UPDATE group_join_requests
                    SET status = 'rejected', approved_at = NOW(), approved_by_employee_id = ?
                    WHERE id = ? AND status = 'pending'
                    LIMIT 1
                ");
                $upd->bind_param('ii', $supervisorId, $requestId);
                $upd->execute();
                $upd->close();

                $resultMsg = 'Solicitud rechazada';
            }

            $mysqli->commit();
            echo json_encode(['success' => true, 'message' => $resultMsg]);
            exit;
        } catch (Throwable $e) {
            $mysqli->rollback();
            throw $e;
        }
    } catch (Throwable $e) {
        $sendError(500, 'Error procesando solicitud: ' . $e->getMessage());
    }
}

$sendError(405, 'Método no permitido');
