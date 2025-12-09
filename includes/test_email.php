<?php
// ➜ ARCHIVO DE PRUEBA: test_email.php
// -----------------------------------

require_once __DIR__ . "/enviarCorreo.php"; // Ajusta ruta si tu archivo está en otra carpeta

// 👉 Cambia este correo por uno tuyo para probar:
$correoDestino = "parqueheliconias0@gmail.com";
$nombreDestino = "Prueba Heliconias";

$asunto = "🔍 Prueba de Envío de Correo - Parque Las Heliconias";

$mensajeHTML = "
    <div style='font-family: Arial; padding: 20px;'>
        <h2 style='color:#2e6a30;'>🌿 Prueba de correo funcionando</h2>
        <p>Si estás viendo este mensaje, quiere decir que PHPMailer funciona correctamente.</p>
        <p><strong>Fecha de envío:</strong> " . date("Y-m-d H:i:s") . "</p>
        <p style='margin-top:20px;'>Atentamente,<br>Servidor Heliconias</p>
    </div>
";

// ----------------------------------------
// 🔥 Intentar enviar correo
// ----------------------------------------
$enviado = enviarCorreo($correoDestino, $nombreDestino, $asunto, $mensajeHTML, true);

if ($enviado) {
    echo "<h2 style='color:green;'>✔ CORREO ENVIADO EXITOSAMENTE</h2>";
} else {
    echo "<h2 style='color:red;'>❌ ERROR AL ENVIAR EL CORREO</h2>";
    echo "<p>Revisa el archivo <strong>/logs/mail_errors.log</strong> para ver el error exacto.</p>";
}
