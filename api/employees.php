<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole(['admin', 'supervisor', 'coordinador', 'seguridad'], true);

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con la base de datos']);
    exit;
}
$mysqli->set_charset($config['charset']);

$sessionUser = getSessionUser();
$sessionRole = strtolower(trim($sessionUser['rol'] ?? ''));
$sessionGroup = $mysqli->real_escape_string(trim($sessionUser['grupo'] ?? ''));

$where = [];
// Solo los administradores pueden ver al rol admin; el resto lo excluye.
if ($sessionRole !== 'admin') {
    $where[] = "LOWER(rol) <> 'admin'";
}
// Supervisores / coordinadores solo ven su grupo si se conoce
if (in_array($sessionRole, ['supervisor', 'coordinador'], true) && $sessionGroup !== '') {
    $where[] = "grupo = '{$sessionGroup}'";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = ' WHERE ' . implode(' AND ', $where);
}

$query = "SELECT id, nombre, apellidos, dni_pasaporte, fecha_nacimiento, email, institucion, pais, motivo, fecha_inicio, fecha_fin, grupo, foto_url, rol, horario FROM employees {$whereSql} ORDER BY grupo, apellidos DESC, nombre DESC";
$result = $mysqli->query($query);

$employees = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $result->free();
}

echo json_encode(['employees' => $employees]);

