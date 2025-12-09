<?php
$carpeta = __DIR__ . "/logs";

if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$archivo = $carpeta . "/mail_errors.log";

if (!file_exists($archivo)) {
    file_put_contents($archivo, "LOG DE CORREOS\n");
}

chmod($carpeta, 0777);
chmod($archivo, 0666);

echo "✔ Archivo mail_errors.log creado con permisos correctos.";
?>
