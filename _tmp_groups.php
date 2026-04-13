<?php
$c=require 'api/config.php';
$m=new mysqli($c['host'],$c['user'],$c['pass'],$c['db']);
$m->set_charset($c['charset']);
$r=$m->query('select id,name,deleted_at from groups order by name');
while($row=$r->fetch_assoc()){
    echo $row['id'],",",$row['name'],",",($row['deleted_at']??''),"\n";
}
