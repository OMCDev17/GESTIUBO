<?php
$c = require 'api/config.php';
$m = new mysqli($c['host'], $c['user'], $c['pass'], $c['db']);
if ($m->connect_errno) {
    echo 'ERR ' . $m->connect_error;
    exit(1);
}
$m->set_charset($c['charset']);
echo 'OK';

