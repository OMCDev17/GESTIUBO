<?php
$path = __DIR__ . '/api/groups.php';
$data = file_get_contents($path);
if (strncmp($data, "\xEF\xBB\xBF", 3) === 0) {
    $data = substr($data, 3);
    file_put_contents($path, $data);
    echo "stripped\n";
} else {
    echo "no_bom\n";
}
