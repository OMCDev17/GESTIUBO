<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/auth.php';
requireRole(['admin', 'supervisor', 'coordinador', 'seguridad'], true);

$config = require __DIR__ . '/config.php';
$mysqli = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexiÃ³n con la base de datos']);
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
    // Vista de seguridad: usar la estancia real mÃ¡s relevante por empleado
    // (prioriza activa; si no hay activa, toma la mÃ¡s reciente archivada).
    $query = "SELECT e.id, e.nombre, e.apellidos, e.dni_pasaporte, e.fecha_nacimiento, e.email,
                     e.phone_prefix, e.phone_number,
                     s.motivo, s.fecha_inicio, s.fecha_fin, s.group_id, g.name AS group_name, e.foto_url, e.rol, s.horario, s.institucion, s.pais,
                     (
                         SELECT CONCAT(TRIM(c.nombre), ' ', TRIM(c.apellidos))
                         FROM employees c
                         INNER JOIN stays sc ON sc.employee_id = c.id AND sc.status = 'active'
                         WHERE sc.group_id = s.group_id
                           AND LOWER(c.rol) IN ('coordinador', 'supervisor')
                         ORDER BY FIELD(LOWER(c.rol), 'coordinador', 'supervisor'), c.nombre, c.apellidos
                         LIMIT 1
                     ) AS coordinator_name,
                     (
                         SELECT CONCAT(c.phone_prefix, ' ', c.phone_number)
                         FROM employees c
                         INNER JOIN stays sc ON sc.employee_id = c.id AND sc.status = 'active'
                         WHERE sc.group_id = s.group_id
                           AND LOWER(c.rol) IN ('coordinador', 'supervisor')
                         ORDER BY FIELD(LOWER(c.rol), 'coordinador', 'supervisor'), c.nombre, c.apellidos
                         LIMIT 1
                     ) AS coordinator_phone,
                     CASE
                         WHEN LOWER(e.rol) IN ('coordinador', 'supervisor') AND EXISTS (
                             SELECT 1
                             FROM stays sc
                             WHERE sc.employee_id = e.id
                               AND sc.group_id = s.group_id
                               AND sc.status = 'active'
                               AND LOWER(e.rol) IN ('coordinador', 'supervisor')
                         ) THEN 1
                         ELSE 0
                     END AS is_group_coordinator,
                     EXISTS(
                         SELECT 1
                         FROM group_join_requests gjr
                         WHERE gjr.employee_id = e.id
                           AND gjr.status = 'pending'
                     ) AS pending_approval
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
    // Vista general (supervisiÃ³n/ediciÃ³n): solo estancia activa.
    $query = "SELECT e.id, e.nombre, e.apellidos, e.dni_pasaporte, e.fecha_nacimiento, e.email,
                     e.phone_prefix, e.phone_number,
                     s.motivo, s.fecha_inicio, s.fecha_fin, s.group_id, g.name AS group_name, e.foto_url, e.rol, s.horario, s.institucion, s.pais,
                     (
                         SELECT CONCAT(TRIM(c.nombre), ' ', TRIM(c.apellidos))
                         FROM employees c
                         INNER JOIN stays sc ON sc.employee_id = c.id AND sc.status = 'active'
                         WHERE sc.group_id = s.group_id
                           AND LOWER(c.rol) IN ('coordinador', 'supervisor')
                         ORDER BY FIELD(LOWER(c.rol), 'coordinador', 'supervisor'), c.nombre, c.apellidos
                         LIMIT 1
                     ) AS coordinator_name,
                     (
                         SELECT CONCAT(c.phone_prefix, ' ', c.phone_number)
                         FROM employees c
                         INNER JOIN stays sc ON sc.employee_id = c.id AND sc.status = 'active'
                         WHERE sc.group_id = s.group_id
                           AND LOWER(c.rol) IN ('coordinador', 'supervisor')
                         ORDER BY FIELD(LOWER(c.rol), 'coordinador', 'supervisor'), c.nombre, c.apellidos
                         LIMIT 1
                     ) AS coordinator_phone,
                     CASE
                         WHEN LOWER(e.rol) IN ('coordinador', 'supervisor') AND EXISTS (
                             SELECT 1
                             FROM stays sc
                             WHERE sc.employee_id = e.id
                               AND sc.group_id = s.group_id
                               AND sc.status = 'active'
                               AND LOWER(e.rol) IN ('coordinador', 'supervisor')
                         ) THEN 1
                         ELSE 0
                     END AS is_group_coordinator,
                     EXISTS(
                         SELECT 1
                         FROM group_join_requests gjr
                         WHERE gjr.employee_id = e.id
                           AND gjr.status = 'pending'
                     ) AS pending_approval
              FROM employees e
              LEFT JOIN stays s ON s.id = (
                  SELECT s1.id
                  FROM stays s1
                  WHERE s1.employee_id = e.id
                    AND s1.status = 'active'
                  ORDER BY s1.updated_at DESC, s1.id DESC
                  LIMIT 1
              )
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


