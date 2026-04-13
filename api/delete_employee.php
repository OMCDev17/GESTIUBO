<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Solo administradores pueden eliminar usuarios
requireRole('admin');

// Obtener el ID del empleado a eliminar desde la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$employeeId = $data['employee_id'] ?? null;

if (!$employeeId || !is_numeric($employeeId)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de empleado inválido']);
    exit;
}

$employeeId = (int)$employeeId;

try {
    $config = require __DIR__ . '/config.php';
    $mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    
    if ($mysqli->connect_errno) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
        exit;
    }
    
    $mysqli->set_charset($config['charset']);
    
    // Verificar que el empleado existe
    $checkStmt = $mysqli->prepare('SELECT id FROM employees WHERE id = ? LIMIT 1');
    if (!$checkStmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error preparando consulta']);
        exit;
    }
    
    $checkStmt->bind_param('i', $employeeId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Empleado no encontrado']);
        exit;
    }
    
    $checkStmt->close();
    
    // Eliminar el empleado
    $deleteStmt = $mysqli->prepare('DELETE FROM employees WHERE id = ?');
    if (!$deleteStmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Error preparando consulta de eliminación']);
        exit;
    }
    
    $deleteStmt->bind_param('i', $employeeId);
    $deleteStmt->execute();
    
    if ($deleteStmt->affected_rows === 0) {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo eliminar el empleado']);
        exit;
    }
    
    $deleteStmt->close();
    $mysqli->close();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Empleado eliminado correctamente']);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}
