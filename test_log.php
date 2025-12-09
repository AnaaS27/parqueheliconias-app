<?php
$log = __DIR__ . "/logs/mail_errors.log";

file_put_contents($log, date("Y-m-d H:i:s") . " - Test de escritura\n", FILE_APPEND);

echo "OK - Si no ves ningún error, revisa mail_errors.log";
