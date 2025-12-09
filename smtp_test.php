<?php
echo "<h2>Prueba de conexión SMTP</h2>";

$host = "smtp.gmail.com";
$port = 465;

echo "Probando conexión a $host:$port ...<br>";

$connection = @fsockopen($host, $port, $errno, $errstr, 10);

if (!$connection) {
    echo "<p style='color:red;'><b>❌ NO se pudo conectar al servidor SMTP</b></p>";
    echo "Error: $errstr ($errno)";
} else {
    echo "<p style='color:green;'><b>✔ Conexión SMTP exitosa</b></p>";
    fclose($connection);
}

?>
