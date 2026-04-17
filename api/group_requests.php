<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole(['supervisor', 'coordinador', 'admin']);
require_once __DIR__ . '/email_templates.php';

$config = require __DIR__ . '/config.php';
$method = $_SERVER['REQUEST_METHOD'];
$user = getSessionUser();

$sendError = function (int $code, string $msg) {
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

if ($method === 'GET') {
    try {
        $userId = (int)($user['id'] ?? 0);
        $userRole = strtolower(trim($user['rol'] ?? ''));
        if (!$userId) {
            $sendError(401, 'Usuario no autenticado');
        }

        $query = "
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
            WHERE gjr.status = 'pending'
        ";

        if ($userRole !== 'admin') {
            // Mostrar solicitudes de cualquiera de los grupos activos del coordinador/supervisor.
            $query .= " AND gjr.group_id IN (
                            SELECT DISTINCT s.group_id
                            FROM stays s
                            WHERE s.employee_id = ?
                              AND s.status = 'active'
                              AND s.group_id IS NOT NULL
                        )";
        }

        $query .= " ORDER BY gjr.created_at DESC";

        $reqStmt = $mysqli->prepare($query);
        if (!$reqStmt) {
            $sendError(500, 'Error preparando consulta');
        }
        if ($userRole !== 'admin') {
            $reqStmt->bind_param('i', $userId);
        }
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

if ($method === 'POST') {
    try {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            $body = $_POST;
        }
        if (!is_array($body)) {
            $sendError(400, 'Payload inválido');
        }

        $requestIdRaw = $body['request_id'] ?? $body['requestId'] ?? $body['id'] ?? null;
        $requestId = $requestIdRaw !== null ? (int)$requestIdRaw : null;

        $actionRaw = strtolower(trim((string)($body['action'] ?? $body['accion'] ?? '')));
        $actionMap = [
            'approve' => 'approve', 'approved' => 'approve', 'aprobar' => 'approve', 'aceptar' => 'approve', 'accept' => 'approve',
            'reject' => 'reject', 'rejected' => 'reject', 'rechazar' => 'reject', 'denegar' => 'reject', 'deny' => 'reject',
        ];
        $action = $actionMap[$actionRaw] ?? $actionRaw;

        if (!$requestId || !in_array($action, ['approve', 'reject'], true)) {
            $sendError(400, 'Faltan parámetros o acción inválida');
        }

        $getReq = $mysqli->prepare("SELECT gjr.*, g.name AS group_name, e.nombre, e.apellidos, e.email
            FROM group_join_requests gjr
            INNER JOIN employees e ON e.id = gjr.employee_id
            INNER JOIN groups g ON g.id = gjr.group_id
            WHERE gjr.id = ? AND gjr.status = 'pending'
            LIMIT 1");
        $getReq->bind_param('i', $requestId);
        $getReq->execute();
        $getReqRes = $getReq->get_result();
        $request = $getReqRes ? $getReqRes->fetch_assoc() : null;
        $getReq->close();

        if (!$request) {
            $sendError(404, 'Solicitud no encontrada o ya procesada');
        }

        $supervisorId = (int)($user['id'] ?? 0);
        $userRole = strtolower(trim($user['rol'] ?? ''));
        $groupId = (int)($request['group_id'] ?? 0);

        if ($userRole !== 'admin') {
            $checkSuper = $mysqli->prepare("SELECT 1 FROM stays WHERE employee_id = ? AND group_id = ? AND status = 'active' LIMIT 1");
            $checkSuper->bind_param('ii', $supervisorId, $groupId);
            $checkSuper->execute();
            $checkSuperRes = $checkSuper->get_result();
            if (!$checkSuperRes || $checkSuperRes->num_rows === 0) {
                $sendError(403, 'No tienes permiso para procesar esta solicitud');
            }
            $checkSuper->close();
        }

        $mysqli->begin_transaction();

        try {
            $welcomeEmailSent = null;
            if ($action === 'approve') {
                $checkActive = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'active' FOR UPDATE LIMIT 1");
                $employeeId = (int)$request['employee_id'];
                $checkActive->bind_param('i', $employeeId);
                $checkActive->execute();
                $activeRes = $checkActive->get_result();
                if ($activeRes && $activeRes->num_rows > 0) {
                    throw new RuntimeException('El usuario ya tiene una estancia activa');
                }
                $checkActive->close();

                $ins = $mysqli->prepare("INSERT INTO stays (employee_id, fecha_inicio, fecha_fin, motivo, group_id, horario, institucion, pais, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                $horario = (int)$request['horario'];
                $ins->bind_param('isssiiss', $employeeId, $request['fecha_inicio'], $request['fecha_fin'], $request['motivo'], $groupId, $horario, $request['institucion'], $request['pais']);
                $ins->execute();
                $ins->close();

                $upd = $mysqli->prepare("UPDATE group_join_requests SET status = 'approved', approved_at = NOW(), approved_by_employee_id = ? WHERE id = ? AND status = 'pending' LIMIT 1");
                $upd->bind_param('ii', $supervisorId, $requestId);
                $upd->execute();
                $upd->close();

                $resultMsg = 'Solicitud aprobada correctamente';
            } else {
                $upd = $mysqli->prepare("UPDATE group_join_requests SET status = 'rejected', approved_at = NOW(), approved_by_employee_id = ? WHERE id = ? AND status = 'pending' LIMIT 1");
                $upd->bind_param('ii', $supervisorId, $requestId);
                $upd->execute();
                $upd->close();

                $resultMsg = 'Solicitud rechazada';
            }

            $mysqli->commit();
            if ($action === 'approve') {
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $basePath = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
                $loginUrl = "{$scheme}://{$host}{$basePath}/Loggin.php";

                $stayData = [
                    'group_name' => $request['group_name'] ?? '',
                    'motivo' => $request['motivo'] ?? '',
                    'fecha_inicio' => $request['fecha_inicio'] ?? '',
                    'fecha_fin' => $request['fecha_fin'] ?? '',
                    'institucion' => $request['institucion'] ?? '',
                    'pais' => $request['pais'] ?? '',
                ];
                $welcomeEmailSent = @sendNewStayWelcomeEmail((string)($request['email'] ?? ''), (string)($request['nombre'] ?? ''), $stayData, $loginUrl, $config);
            }

            echo json_encode([
                'success' => true,
                'message' => $resultMsg,
                'welcome_email_sent' => $welcomeEmailSent,
            ]);
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
