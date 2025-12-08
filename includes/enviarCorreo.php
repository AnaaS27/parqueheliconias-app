<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/PHPMailer/src/Exception.php";
require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";

/**
 * CONFIGURACIÓN SMTP CENTRALIZADA
 */
function smtpConfig(PHPMailer $mail)
{
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "pruebaheliconas@gmail.com";
    $mail->Password = "wuwa asyl thes woxw"; 
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    $mail->setFrom("pruebaheliconas@gmail.com", "Parque Las Heliconias");

    // 🔥 Corrección importante para acentos
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
}

/**
 * FUNCIÓN GENÉRICA PARA ENVIAR CORREOS
 */
function enviarCorreo($correoDestino, $nombreUsuario, $asunto, $mensajeHTML, $embedLogo = true)
{
    $mail = new PHPMailer(true);

    try {
        smtpConfig($mail);

        $mail->addAddress($correoDestino, $nombreUsuario);

        // Insertar logo como recurso incrustado CID
        if ($embedLogo) {
            $rutaLogo = __DIR__ . "/../assets/img/logoo.png";
            if (file_exists($rutaLogo)) {
                $idLogo = uniqid('logo_');
                $mail->AddEmbeddedImage($rutaLogo, $idLogo, basename($rutaLogo));
                $mensajeHTML = str_replace("cid:logoHeliconias", "cid:$idLogo", $mensajeHTML);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensajeHTML;

        $mail->send();
        return true;

    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/mail_errors.log', 
            date('Y-m-d H:i:s') . " - Error enviando correo a {$correoDestino}: " . $mail->ErrorInfo . PHP_EOL, 
            FILE_APPEND);

        return false;
    }
}

/**
 * 📩 CORREO PLANTILLA: CONFIRMACIÓN DE RESERVA
 */
function enviarCorreoReserva($correoDestino, $nombreUsuario, $id_reserva, $fecha_visita, $actividad)
{
    $asunto = "Confirmación de Reserva #$id_reserva - Parque Las Heliconias";

    $mensajeHTML = '
    <div style="width: 100%; background: #f0f7f0; padding: 30px 0; font-family: Arial, sans-serif;">
        <div style="max-width: 600px; background: white; margin:auto; padding: 25px; border-radius: 10px;">
            
            <div style="text-align:center;">
                <img src="cid:logoHeliconias" style="width:120px;margin-bottom:10px">
            </div>

            <h2 style="text-align:center;color:#2e6a30">🌿 Reserva Confirmada</h2>

            <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
            <p>Tu reserva ha sido registrada exitosamente.</p>

            <div style="background:#eaf5ea;padding:15px;border-radius:8px;margin:10px 0;">
                <p><strong>ID Reserva:</strong> ' . intval($id_reserva) . '</p>
                <p><strong>Actividad:</strong> ' . htmlspecialchars($actividad) . '</p>
                <p><strong>Fecha:</strong> ' . htmlspecialchars($fecha_visita) . '</p>
            </div>

            <p>Gracias por elegirnos 💚</p>

            <hr>
            <p style="font-size:12px;text-align:center;color:#777">
                © ' . date("Y") . ' Parque Las Heliconias - Mensaje automático
            </p>

        </div>
    </div>';

    return enviarCorreo($correoDestino, $nombreUsuario, $asunto, $mensajeHTML, true);
}

function enviarCorreoReservaGrupal($correoDestino, $nombreUsuario, $id_reserva, $fecha_visita, $actividad, $cantidad, $responsable, $participantesExtra = [])
{
    $asunto = "Confirmación de Reserva Grupal #$id_reserva - Parque Las Heliconias";

    // =====================================
    // 📝 Construir tabla de participantes
    // =====================================
    $tablaParticipantes = "";

    if (!empty($participantesExtra)) {
        $tablaParticipantes .= '
            <table style="width:100%; border-collapse:collapse; margin-top:15px;">
                <tr style="background:#e8f3ea;">
                    <th style="padding:8px; border:1px solid #cddccc;">Nombre</th>
                    <th style="padding:8px; border:1px solid #cddccc;">Documento</th>
                    <th style="padding:8px; border:1px solid #cddccc;">Género</th>
                    <th style="padding:8px; border:1px solid #cddccc;">Ciudad</th>
                </tr>';
        
        foreach ($participantesExtra as $p) {
            $tablaParticipantes .= "
                <tr>
                    <td style='padding:8px; border:1px solid #cddccc;'>{$p['nombre']} {$p['apellido']}</td>
                    <td style='padding:8px; border:1px solid #cddccc;'>{$p['documento']}</td>
                    <td style='padding:8px; border:1px solid #cddccc;'>{$p['genero']}</td>
                    <td style='padding:8px; border:1px solid #cddccc;'>{$p['ciudad']}</td>
                </tr>";
        }

        $tablaParticipantes .= '</table>';
    }

    // =====================================
    // 📄 PLANTILLA COMPLETA DEL CORREO
    // =====================================
    $mensajeHTML = '
    <div style="width:100%; background:#f0f7f0; padding:25px 0; font-family:Arial,sans-serif;">
        <div style="max-width:700px; background:#fff; margin:auto; padding:30px; border-radius:12px;">

            <div style="text-align:center;">
                <img src="cid:logoHeliconias" style="width:130px; margin-bottom:15px;">
            </div>

            <h2 style="text-align:center; color:#2e6a30;">🌿 Reserva Grupal Confirmada</h2>
            
            <p style="font-size:16px;">Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
            <p>Tu reserva grupal ha sido registrada exitosamente. Estos son los detalles:</p>

            <div style="background:#e8f5e8; padding:15px; border-radius:10px;">
                <p><strong>ID Reserva:</strong> ' . $id_reserva . '</p>
                <p><strong>Actividad:</strong> ' . htmlspecialchars($actividad) . '</p>
                <p><strong>Fecha de Visita:</strong> ' . htmlspecialchars($fecha_visita) . '</p>
                <p><strong>Total Participantes:</strong> ' . $cantidad . '</p>
            </div>

            <h3 style="margin-top:25px; color:#2e6a30;">🧍 Responsable del Grupo</h3>
            <p>
                <strong>' . htmlspecialchars($responsable['nombre']) . ' ' . htmlspecialchars($responsable['apellido']) . '</strong><br>
                Documento: ' . htmlspecialchars($responsable['documento']) . '<br>
                Teléfono: ' . htmlspecialchars($responsable['telefono']) . '<br>
                Ciudad: ' . htmlspecialchars($responsable['ciudad']) . '
            </p>

            <h3 style="margin-top:25px; color:#2e6a30;">👥 Participantes Adicionales</h3>
            ' . ($tablaParticipantes ?: "<p>No se ingresaron participantes adicionales.</p>") . '

            <p style="margin-top:25px;">Gracias por elegirnos 💚</p>

            <hr style="margin-top:30px;">
            <p style="font-size:12px; text-align:center; color:#777;">
                © ' . date("Y") . ' Parque Las Heliconias - Este es un mensaje automático.
            </p>

        </div>
    </div>';

    return enviarCorreo($correoDestino, $nombreUsuario, $asunto, $mensajeHTML, true);
}

/**
 * 📩 CORREO PLANTILLA: CAMBIO DE DATOS O CONTRASEÑA
 */
function enviarCorreoCambioDatos($correoDestino, $nombreUsuario, $infoCambiosHtml)
{
    $asunto = "Actualización de Cuenta - Parque Las Heliconias";

    $mensajeHTML = '
    <div style="background:#fafcf9;padding:30px 0;font-family:Arial,sans-serif;">
      <div style="max-width:600px;margin:auto;background:white;padding:25px;border-radius:10px;">
        
        <div style="text-align:center;">
            <img src="cid:logoHeliconias" style="width:100px;">
        </div>

        <h2 style="color:#2e6a30;text-align:center;">🔐 Cambios en tu Cuenta</h2>

        <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
        <p>Se realizaron los siguientes cambios:</p>

        <div style="background:#eef6ee;padding:12px;border-radius:8px;margin:10px 0;">
            ' . $infoCambiosHtml . '
        </div>

        <p>Si no realizaste estos cambios, contacta con soporte inmediatamente.</p>

        <hr>
        <p style="font-size:12px;text-align:center;color:#777;">
            © ' . date("Y") . ' Parque Las Heliconias
        </p>
      </div>
    </div>';

    return enviarCorreo($correoDestino, $nombreUsuario, $asunto, $mensajeHTML, true);
}

/**
 * 📩 CORREO ESPECIAL: CAMBIO DE CONTRASEÑA
 */
function enviarCorreoPassword($correoDestino, $nombreUsuario)
{
    $asunto = "⚠ Cambio de contraseña - Parque Las Heliconias";

    $mensajeHTML = '
    <div style="padding:30px;background:#fafafa;font-family:Arial">
        <div style="max-width:600px;margin:auto;background:#fff;padding:20px;border-radius:8px;">
            <h2 style="color:#d9534f;text-align:center;">⚠ Cambio de Contraseña</h2>
            <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
            <p>La contraseña de tu cuenta ha sido modificada.</p>
            <p>Si <strong>no autorizaste</strong> este cambio, cambia tu contraseña inmediatamente y contáctanos.</p>
            <hr>
            <small>© Parque Las Heliconias</small>
        </div>
    </div>';

    return enviarCorreo($correoDestino, $nombreUsuario, $asunto, $mensajeHTML, true);
}

/**
 * 📩 CORREO: CANCELACIÓN DE RESERVA
 */
function enviarCorreoCancelacion($correoDestino, $nombreUsuario, $id_reserva, $actividad, $fecha_visita)
{
    $asunto = "Cancelación de Reserva #$id_reserva - Parque Las Heliconias";

    $mensajeHTML = '
    <div style="width: 100%; background: #fff4f4; padding: 30px 0; font-family: Arial, sans-serif;">
        <div style="max-width: 600px; background: white; margin:auto; padding: 25px; border-radius: 10px;">

            <div style="text-align:center;">
                <img src="cid:logoHeliconias" style="width:120px;margin-bottom:10px">
            </div>

            <h2 style="text-align:center;color:#b32d2e">❌ Reserva Cancelada</h2>

            <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
            <p>Tu reserva fue cancelada correctamente.</p>

            <div style="background:#fdeaea;padding:15px;border-radius:8px;margin:10px 0;">
                <p><strong>ID Reserva:</strong> ' . intval($id_reserva) . '</p>
                <p><strong>Actividad:</strong> ' . htmlspecialchars($actividad) . '</p>
                <p><strong>Fecha programada:</strong> ' . htmlspecialchars($fecha_visita) . '</p>
            </div>

            <p>Si tienes dudas o deseas programar una nueva visita, estaremos encantados de ayudarte 💚</p>

            <hr>
            <p style="font-size:12px;text-align:center;color:#777">
                © ' . date("Y") . ' Parque Las Heliconias - Mensaje automático
            </p>
        </div>
    </div>';

    return enviarCorreo($correoDestino, $nombreUsuario, $asunto, $mensajeHTML, true);
}

?>
