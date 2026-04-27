<?php
$p = 'api/email_templates.php';
$c = file_get_contents($p);
$needle1 = "function sendPasswordResetEmail(\$email, \$userName, \$code, \$config) {\n    require_once __DIR__ . '/../vendor/autoload.php';\n";
$insert1 = "function sendPasswordResetEmail(\$email, \$userName, \$code, \$config) {\n    require_once __DIR__ . '/../vendor/autoload.php';\n    \$email = trim((string)\$email);\n    if (!filter_var(\$email, FILTER_VALIDATE_EMAIL)) {\n        error_log(\"Error enviando correo de restablecimiento: email destino invalido [\" . \$email . \"]\");\n        return false;\n    }\n";
if (strpos($c, "email destino invalido") === false) {
  $c = str_replace($needle1, $insert1, $c);
  $needle2 = "function sendNewStayWelcomeEmail(\$email, \$firstName, \$stayData, \$loginUrl, \$config) {\n    require_once __DIR__ . '/../vendor/autoload.php';\n";
  $insert2 = "function sendNewStayWelcomeEmail(\$email, \$firstName, \$stayData, \$loginUrl, \$config) {\n    require_once __DIR__ . '/../vendor/autoload.php';\n    \$email = trim((string)\$email);\n    if (!filter_var(\$email, FILTER_VALIDATE_EMAIL)) {\n        error_log(\"Error enviando correo de nueva estancia: email destino invalido [\" . \$email . \"]\");\n        return false;\n    }\n";
  $c = str_replace($needle2, $insert2, $c);
}
file_put_contents($p, $c);
