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

$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!is_array($data) || !isset($data['employees']) || !is_array($data['employees'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido']);
    exit;
}

$allowed = [
    'nombre', 'apellidos', 'dni_pasaporte', 'fecha_nacimiento', 'email',
    'institucion', 'pais', 'motivo', 'fecha_inicio', 'fecha_fin',
    'grupo', 'foto_url', 'rol', 'horario'
];

$updateStmt = $mysqli->prepare(
    "UPDATE employees SET %s WHERE id = ?"
);

$updated = 0;
$errors = [];

foreach ($data['employees'] as $emp) {
    if (!isset($emp['id'])) {
        $errors[] = ['error' => 'Usuario sin ID'];
        continue;
    }

    $fields = [];
    $params = [];
    $types = '';

    foreach ($allowed as $col) {
        if (array_key_exists($col, $emp)) {
            $fields[] = "$col = ?";
            if ($col === 'horario') {
                $params[] = isset($emp[$col]) ? (int) !!$emp[$col] : 0;
                $types .= 'i';
            } else {
                $params[] = $emp[$col];
                $types .= 's';
            }
        }
    }

    if (count($fields) === 0) {
        continue;
    }

    $types .= 'i'; // id
    $params[] = $emp['id'];

    $sql = sprintf("UPDATE employees SET %s WHERE id = ?", implode(', ', $fields));
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        $errors[] = ['id' => $emp['id'], 'error' => 'Error al preparar la consulta'];
        continue;
    }

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        $errors[] = ['id' => $emp['id'], 'error' => $stmt->error];
    } else {
        $updated++;
    }
    $stmt->close();
}

$response = ['updated' => $updated];
if (!empty($errors)) {
    $response['errors'] = $errors;
}

echo json_encode($response);
