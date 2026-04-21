<?php
$_GET['view']='security';
$_SERVER['REQUEST_METHOD']='GET';
ob_start();
include __DIR__ . '/api/employees.php';
$out = ob_get_clean();
$first = substr($out,0,1);
echo 'FIRST_CHAR_HEX=' . bin2hex($first) . PHP_EOL;
echo 'START=' . substr($out,0,30) . PHP_EOL;
