<?php
header('Content-Type: application/json; charset=utf-8');

$maxBytes = 5 * 1024 * 1024;
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (empty($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se envió ningún archivo']);
    exit;
}

$file = $_FILES['photo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Error al subir el archivo']);
    exit;
}

if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(['error' => 'El archivo supera los 5MB']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato no permitido (solo JPG/PNG)']);
    exit;
}

$ext = $allowed[$mime];
$uploadsDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$filename = uniqid('photo_', true) . '.' . $ext;
$target = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo guardar el archivo']);
    exit;
}

$relativeUrl = 'uploads/' . $filename;
echo json_encode(['success' => true, 'url' => $relativeUrl]);

