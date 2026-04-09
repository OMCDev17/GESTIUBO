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
$mysqli->query("SET NAMES {$config['charset']}");

$sessionUser = getSessionUser();
$sessionRole = strtolower(trim($sessionUser['rol'] ?? ''));
$sessionGroup = $mysqli->real_escape_string(trim($sessionUser['group_name'] ?? $sessionUser['grupo'] ?? ''));

$where = [];
if ($sessionRole !== 'admin') {
    $where[] = "LOWER(e.rol) <> 'admin'";
}
if (in_array($sessionRole, ['supervisor', 'coordinador'], true) && $sessionGroup !== '') {
    $where[] = "g.name = '{$sessionGroup}'";
}
$whereSql = empty($where) ? '' : ' WHERE ' . implode(' AND ', $where);
$securityView = (isset($_GET['view']) && $_GET['view'] === 'security');

if ($securityView) {
    // Vista de seguridad: usar la estancia real más relevante por empleado
    // (prioriza activa; si no hay activa, toma la más reciente archivada).
    $query = "SELECT e.id, e.nombre, e.apellidos, e.dni_pasaporte, e.fecha_nacimiento, e.email,
                     s.motivo, s.fecha_inicio, s.fecha_fin, s.group_id, g.name AS group_name, e.foto_url, e.rol, s.horario, s.institucion, s.pais
              FROM employees e
              LEFT JOIN stays s ON s.id = (
                  SELECT s1.id
                  FROM stays s1
                  WHERE s1.employee_id = e.id
                  ORDER BY (s1.status = 'active') DESC, s1.fecha_fin DESC, s1.updated_at DESC, s1.id DESC
                  LIMIT 1
              )
              LEFT JOIN groups g ON g.id = s.group_id
              {$whereSql}
              ORDER BY g.name, e.apellidos DESC, e.nombre DESC";
} else {
    // Vista general (supervisión/edición): solo estancia activa.
    $query = "SELECT e.id, e.nombre, e.apellidos, e.dni_pasaporte, e.fecha_nacimiento, e.email,
                     s.motivo, s.fecha_inicio, s.fecha_fin, s.group_id, g.name AS group_name, e.foto_url, e.rol, s.horario, s.institucion, s.pais
              FROM employees e
              LEFT JOIN stays s ON s.employee_id = e.id AND s.status = 'active'
              LEFT JOIN groups g ON g.id = s.group_id
              {$whereSql}
              ORDER BY g.name, e.apellidos DESC, e.nombre DESC";
}
$result = $mysqli->query($query);

$employees = [];
$history = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $result->free();
}

// Historial opcional para admin
if (isset($_GET['include_history']) && $_GET['include_history'] === '1') {
    $historySql = "SELECT s.*, e.nombre, e.apellidos, e.foto_url, e.rol, g.name AS group_name
                   FROM stays s
                   JOIN employees e ON e.id = s.employee_id
                   LEFT JOIN groups g ON g.id = s.group_id
                   WHERE s.status = 'archived'
                   ORDER BY s.fecha_fin DESC, COALESCE(s.archived_at, s.updated_at) DESC";
    $histRes = $mysqli->query($historySql);
    if ($histRes) {
        while ($row = $histRes->fetch_assoc()) {
            $history[] = $row;
        }
        $histRes->free();
    }
}

echo json_encode(['employees' => $employees, 'history' => $history]);
