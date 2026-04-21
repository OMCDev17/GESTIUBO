<?php
$p='admin.php';
$s=file_get_contents($p);
$prev=$s;
for($i=0;$i<4;$i++){
  $t=@iconv('Windows-1252','UTF-8//IGNORE',$s);
  if($t===false) break;
  if(substr_count($t,'Ã') < substr_count($s,'Ã') || substr_count($t,'Â') < substr_count($s,'Â')){
    $s=$t;
  } else {
    break;
  }
}
file_put_contents($p,$s);
echo 'DONE';
