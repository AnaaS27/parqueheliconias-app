<?php
/**
 * ===========================================================
 * 🔥 SISTEMA DE ENVÍO DE CORREOS — BREVO API (NO SMTP)
 * ===========================================================
 * Este archivo reemplaza tu antiguo enviarCorreo.php pero mantiene
 * todas tus funciones: enviarCorreoReserva(), enviarCorreoGrupal(),
 * enviarCorreoPassword(), etc.
 *
 * Ya NO usa PHPMailer (SMTP no funciona en Render).
 * Ahora usa la API oficial de Brevo (Sendinblue).
 * ===========================================================
 */

/**
 * ===========================================================
 * 1️⃣ FUNCIÓN BASE PARA ENVIAR CORREO VIA BREVO
 * ===========================================================
 */
function enviarCorreoBrevo($correoDestino, $nombreDestino, $asunto, $html)
{
    $apiKey = getenv("BREVO_API_KEY"); 

    $url = "https://api.brevo.com/v3/smtp/email";

    $payload = [
        "sender" => [
            "name"  => "Parque Las Heliconias",
            "email" => "parqueheliconias0@gmail.com"
        ],
        "to" => [
            [
                "email" => $correoDestino,
                "name"  => $nombreDestino
            ]
        ],
        "subject" => $asunto,
        "htmlContent" => $html
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $apiKey",
        "content-type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $respuesta = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Guardar errores en logs
    if ($httpCode >= 400) {
        @file_put_contents(
            __DIR__ . '/../logs/mail_errors.log',
            date("Y-m-d H:i:s") . " - Error enviando correo a $correoDestino: $respuesta\n",
            FILE_APPEND
        );
        return false;
    }

    return true;
}

/**
 * ===========================================================
 * 2️⃣ PLANTILLA: CONFIRMACIÓN DE RESERVA INDIVIDUAL
 * ===========================================================
 */
function enviarCorreoReserva($correoDestino, $nombreUsuario, $id_reserva, $fecha_visita, $actividad)
{
    $asunto = "Confirmación de Reserva #$id_reserva - Parque Las Heliconias";

    $html = '
    <div style="font-family:Arial; background:#f0f7f0; padding:20px;">
        <div style="max-width:600px; background:white; margin:auto; padding:20px; border-radius:10px;">

            <h2 style="color:#2e6a30; text-align:center;">🌿 Reserva Confirmada</h2>

            <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
            <p>Tu reserva ha sido registrada exitosamente.</p>

            <div style="background:#eaf5ea; padding:15px; border-radius:10px;">
                <p><strong>ID Reserva:</strong> ' . intval($id_reserva) . '</p>
                <p><strong>Actividad:</strong> ' . htmlspecialchars($actividad) . '</p>
                <p><strong>Fecha:</strong> ' . htmlspecialchars($fecha_visita) . '</p>
            </div>

            <p style="margin-top:20px;">Gracias por elegirnos 💚</p>
        </div>
    </div>';

    return enviarCorreoBrevo($correoDestino, $nombreUsuario, $asunto, $html);
}

/**
 * ===========================================================
 * 3️⃣ PLANTILLA: RESERVA GRUPAL
 * ===========================================================
 */
function enviarCorreoReservaGrupal($correoDestino, $nombreUsuario, $id_reserva, $fecha_visita, $actividad, $cantidad, $responsable, $participantesExtra = [])
{
    $asunto = "Confirmación de Reserva Grupal #$id_reserva - Parque Las Heliconias";

    // Tabla de participantes
    $tabla = "";
    if (!empty($participantesExtra)) {
        $tabla .= '<table style="width:100%; border-collapse:collapse; margin-top:15px;">
            <tr style="background:#e8f3ea;">
                <th style="padding:8px; border:1px solid #ccc;">Nombre</th>
                <th style="padding:8px; border:1px solid #ccc;">Documento</th>
                <th style="padding:8px; border:1px solid #ccc;">Género</th>
                <th style="padding:8px; border:1px solid #ccc;">Ciudad</th>
            </tr>';

        foreach ($participantesExtra as $p) {
            $tabla .= "
            <tr>
                <td style='padding:8px; border:1px solid #ccc;'>{$p['nombre']} {$p['apellido']}</td>
                <td style='padding:8px; border:1px solid #ccc;'>{$p['documento']}</td>
                <td style='padding:8px; border:1px solid #ccc;'>{$p['genero']}</td>
                <td style='padding:8px; border:1px solid #ccc;'>{$p['ciudad']}</td>
            </tr>";
        }
        $tabla .= "</table>";
    } else {
        $tabla = "<p>No se ingresaron participantes adicionales.</p>";
    }

    // HTML final
    $html = '
    <div style="font-family:Arial; background:#f0f7f0; padding:20px;">
        <div style="max-width:700px; background:white; padding:25px; margin:auto; border-radius:12px;">

            <h2 style="color:#2e6a30; text-align:center;">🌿 Reserva Grupal Confirmada</h2>

            <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>, tu reserva fue registrada.</p>

            <div style="background:#e8f5e8; padding:15px; border-radius:10px;">
                <p><strong>ID Reserva:</strong> ' . intval($id_reserva) . '</p>
                <p><strong>Actividad:</strong> ' . htmlspecialchars($actividad) . '</p>
                <p><strong>Fecha:</strong> ' . htmlspecialchars($fecha_visita) . '</p>
                <p><strong>Total Participantes:</strong> ' . intval($cantidad) . '</p>
            </div>

            <h3>🧍 Responsable del grupo</h3>
            <p>
                <strong>' . $responsable['nombre'] . ' ' . $responsable['apellido'] . '</strong><br>
                Documento: ' . $responsable['documento'] . '<br>
                Teléfono: ' . $responsable['telefono'] . '<br>
                Ciudad: ' . $responsable['ciudad'] . '
            </p>

            <h3>👥 Participantes adicionales</h3>
            ' . $tabla . '

            <p style="margin-top:20px;">Gracias por elegirnos 💚</p>
        </div>
    </div>';

    return enviarCorreoBrevo($correoDestino, $nombreUsuario, $asunto, $html);
}

/**
 * ===========================================================
 * 4️⃣ PLANTILLA: CAMBIO DE DATOS
 * ===========================================================
 */
function enviarCorreoCambioDatos($correoDestino, $nombreUsuario, $infoCambiosHtml)
{
    $asunto = "Actualización de Cuenta - Parque Las Heliconias";

    $html = "
    <div style='font-family:Arial; padding:20px;'>
        <h2>🔐 Cambios en tu cuenta</h2>
        <p>Hola <strong>$nombreUsuario</strong>, se realizaron los siguientes cambios:</p>
        <div style='background:#eef6ee; padding:10px; border-radius:8px;'>$infoCambiosHtml</div>
    </div>";

    return enviarCorreoBrevo($correoDestino, $nombreUsuario, $asunto, $html);
}

/**
 * ===========================================================
 * 5️⃣ PLANTILLA: CAMBIO DE CONTRASEÑA
 * ===========================================================
 */
function enviarCorreoPassword($correoDestino, $nombreUsuario)
{
    $asunto = "⚠ Cambio de contraseña - Parque Las Heliconias";

    $html = "
    <div style='font-family:Arial; padding:20px;'>
        <h2 style='color:#d9534f;'>⚠ Tu contraseña ha sido cambiada</h2>
        <p>Hola <strong>$nombreUsuario</strong>, si no realizaste este cambio contacta soporte.</p>
    </div>";

    return enviarCorreoBrevo($correoDestino, $nombreUsuario, $asunto, $html);
}

/**
 * ===========================================================
 * 6️⃣ PLANTILLA: CANCELACIÓN DE RESERVA
 * ===========================================================
 */
function enviarCorreoCancelacion($correoDestino, $nombreUsuario, $id_reserva, $actividad, $fecha_visita)
{
    $asunto = "Cancelación de Reserva #$id_reserva - Parque Las Heliconias";

    $html = "
    <div style='font-family:Arial; padding:20px; background:#fff4f4;'>
        <h2 style='color:#b32d2e;'>❌ Reserva Cancelada</h2>
        <p>Hola <strong>$nombreUsuario</strong>, tu reserva fue cancelada.</p>

        <div style='background:#fdeaea; padding:15px; border-radius:10px;'>
            <p><strong>ID Reserva:</strong> $id_reserva</p>
            <p><strong>Actividad:</strong> $actividad</p>
            <p><strong>Fecha:</strong> $fecha_visita</p>
        </div>
    </div>";

    return enviarCorreoBrevo($correoDestino, $nombreUsuario, $asunto, $html);
}


