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

// Asegurar tabla stays
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

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!is_array($data) || !isset($data['employees']) || !is_array($data['employees'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$allowedEmployee = [
    'nombre', 'apellidos', 'dni_pasaporte', 'fecha_nacimiento', 'email',
    'foto_url', 'rol'
];
$allowedStay = ['motivo', 'fecha_inicio', 'fecha_fin', 'group_id', 'horario', 'institucion', 'pais'];

// Resolver / crear group_id desde nombre si llega legacy "grupo"
$ensureGroupId = function (mysqli $db, string $name): ?int {
    $name = trim($name);
    if ($name === '') return null;
    $stmt = $db->prepare('SELECT id FROM groups WHERE LOWER(name) = LOWER(?) LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result($gid);
        if ($stmt->fetch()) {
            $stmt->close();
            return (int)$gid;
        }
        $stmt->close();
    }
    $ins = $db->prepare('INSERT INTO groups (name) VALUES (?)');
    if ($ins) {
        $ins->bind_param('s', $name);
        if ($ins->execute()) {
            $gid = $ins->insert_id;
            $ins->close();
            return (int)$gid;
        }
        $ins->close();
    }
    return null;
};

$updatedEmployees = 0;
$updatedStays = 0;
$errors = [];

$todayTs = strtotime('today');

foreach ($data['employees'] as $emp) {
    if (!isset($emp['id'])) {
        $errors[] = ['error' => 'Usuario sin ID'];
        continue;
    }

    if (!isset($emp['group_id']) && !empty($emp['grupo'])) {
        $gid = $ensureGroupId($mysqli, (string)$emp['grupo']);
        if ($gid) $emp['group_id'] = $gid;
    }

    // Actualizar datos de employees (solo campos personales/rol/foto)
    $fields = [];
    $params = [];
    $types = '';
    foreach ($allowedEmployee as $col) {
        if (array_key_exists($col, $emp)) {
            $fields[] = "$col = ?";
            $params[] = $emp[$col];
            $types .= 's';
        }
    }
    if (!empty($fields)) {
        $types .= 'i';
        $params[] = $emp['id'];
        $sql = sprintf("UPDATE employees SET %s WHERE id = ?", implode(', ', $fields));
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $updatedEmployees++;
            }
            $stmt->close();
        }
    }

    // Gestionar estancia activa en stays
    $hasStayData = array_intersect(array_keys($emp), $allowedStay);
    if (!empty($hasStayData)) {
        // Resolver estado (active/archived)
        $fechaFin = $emp['fecha_fin'] ?? null;
        $fechaInicio = $emp['fecha_inicio'] ?? null;

        // Si faltan fechas, no crear/actualizar estancia; sólo datos personales
        if (!$fechaInicio || !$fechaFin) {
            continue;
        }

        // Validar formato y coherencia de fechas
        try {
            $startDt = new DateTime((string)$fechaInicio);
            $endDt = new DateTime((string)$fechaFin);
            if ($endDt < $startDt) {
                $errors[] = [
                    'id' => (int)$emp['id'],
                    'error' => 'La fecha de fin no puede ser anterior a la fecha de inicio'
                ];
                continue;
            }
        } catch (Throwable $e) {
            $errors[] = [
                'id' => (int)$emp['id'],
                'error' => 'Formato de fecha inválido'
            ];
            continue;
        }

        $status = 'active';
        if ($fechaFin && $fechaFin !== '2100-01-01' && strtotime($fechaFin) < $todayTs) {
            $status = 'archived';
        }

        // Buscar estancia activa actual
        $sel = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'active' LIMIT 1");
        $sel->bind_param('i', $emp['id']);
        $sel->execute();
        $resSel = $sel->get_result();
        $activeId = $resSel && $resSel->num_rows ? (int)$resSel->fetch_assoc()['id'] : null;
        $sel->close();

        if ($activeId) {
            // Actualizar la estancia activa
            $updFields = [];
            $updTypes = '';
            $updParams = [];
            foreach ($allowedStay as $col) {
                if (array_key_exists($col, $emp)) {
                    $updFields[] = "$col = ?";
                    if ($col === 'horario') {
                        $updParams[] = (int) !!$emp[$col];
                        $updTypes .= 'i';
                    } elseif ($col === 'group_id') {
                        $updParams[] = $emp[$col] !== '' ? (int)$emp[$col] : null;
                        $updTypes .= 'i';
                    } else {
                        $updParams[] = $emp[$col];
                        $updTypes .= 's';
                    }
                }
            }
            $updFields[] = "status = ?";
            $updTypes .= 's';
            $updParams[] = $status;
            if ($status === 'archived') {
                $updFields[] = "archived_at = COALESCE(archived_at, NOW())";
            } else {
                $updFields[] = "archived_at = NULL";
            }
            $updTypes .= 'i';
            $updParams[] = $activeId;
            $sqlStay = sprintf("UPDATE stays SET %s WHERE id = ?", implode(', ', $updFields));
            $upd = $mysqli->prepare($sqlStay);
            if ($upd) {
                $upd->bind_param($updTypes, ...$updParams);
                $upd->execute();
                if ($upd->affected_rows > 0) {
                    $updatedStays++;
                }
                $upd->close();
            }
        } else {
            // No hay activa: si es archived, intenta reusar una archivada igual; si no, inserta
            if ($status === 'archived') {
                $selArch = $mysqli->prepare("SELECT id FROM stays WHERE employee_id = ? AND status = 'archived' AND fecha_inicio = ? AND fecha_fin = ? LIMIT 1");
                $selArch->bind_param('iss', $emp['id'], $fechaInicio, $fechaFin);
                $selArch->execute();
                $resArch = $selArch->get_result();
                $archId = $resArch && $resArch->num_rows ? (int)$resArch->fetch_assoc()['id'] : null;
                $selArch->close();
                if ($archId) {
                    $updArch = $mysqli->prepare("UPDATE stays SET motivo = ?, group_id = ?, horario = ?, institucion = ?, pais = ?, archived_at = COALESCE(archived_at, NOW()) WHERE id = ? LIMIT 1");
                    if ($updArch) {
                        $horarioVal = isset($emp['horario']) ? (int) !!$emp['horario'] : 1;
                        $groupVal = isset($emp['group_id']) && $emp['group_id'] !== '' ? (int)$emp['group_id'] : null;
                        $updArch->bind_param(
                            'siissi',
                            $emp['motivo'],
                            $groupVal,
                            $horarioVal,
                            $emp['institucion'] ?? null,
                            $emp['pais'] ?? null,
                            $archId
                        );
                        $updArch->execute();
                        if ($updArch->affected_rows > 0) {
                            $updatedStays++;
                        }
                        $updArch->close();
                    }
                    continue;
                }
            }
            // Insertar nueva estancia sólo si hay fechas válidas (ya filtrado)
            $ins = $mysqli->prepare("INSERT INTO stays (employee_id, fecha_inicio, fecha_fin, motivo, group_id, horario, institucion, pais, status, archived_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($ins) {
                $horarioVal = isset($emp['horario']) ? (int) !!$emp['horario'] : 1;
                $groupVal = isset($emp['group_id']) && $emp['group_id'] !== '' ? (int)$emp['group_id'] : null;
                $archTs = $status === 'archived' ? date('Y-m-d H:i:s') : null;
                $motivoVal = $emp['motivo'] ?? null;
                $instVal = $emp['institucion'] ?? null;
                $paisVal = $emp['pais'] ?? null;
                $empId = (int)$emp['id'];
                $ins->bind_param(
                    'isssiissss',
                    $empId,
                    $fechaInicio,
                    $fechaFin,
                    $motivoVal,
                    $groupVal,
                    $horarioVal,
                    $instVal,
                    $paisVal,
                    $status,
                    $archTs
                );
                $ins->execute();
                if ($ins->affected_rows > 0) {
                    $updatedStays++;
                }
                $ins->close();
            }
        }
    }
}

$response = [
    'updated' => $updatedEmployees + $updatedStays,
    'updated_employees' => $updatedEmployees,
    'updated_stays' => $updatedStays,
];
if (!empty($errors)) {
    $response['errors'] = $errors;
}

echo json_encode($response);

