<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/PHPMailer/src/Exception.php";
require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";

/**
 * ✅ FPDF para generar el ticket en PDF
 * Asegúrate de tener: includes/FPDF/fpdf.php
 */
require_once __DIR__ . "/FPDF/fpdf.php";

/**
 * CONFIGURACIÓN SMTP CENTRALIZADA
 */
function smtpConfig(PHPMailer $mail)
{
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "pruebaheliconas@gmail.com";
    $mail->Password = "wuwa asyl thes woxw"; // contraseña de app
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    $mail->setFrom("pruebaheliconas@gmail.com", "Parque Las Heliconias");
}

/**
 * FUNCIÓN GENÉRICA PARA ENVIAR CORREOS (SIN PDF)
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
                $mail->AddEmbeddedImage($rutaLogo, "logoHeliconias", basename($rutaLogo));
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
 * 📩 CORREO PLANTILLA: CONFIRMACIÓN DE RESERVA INDIVIDUAL
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

/* ============================================================
 * 🧾 GENERAR PDF: TICKET DE RESERVA GRUPAL
 * ============================================================ */
function generarTicketPDFReservaGrupal($id_reserva, $fecha_visita, $actividad, $responsableInfo, $listaParticipantes = [])
{
    // Carpeta para guardar tickets
    $dir = __DIR__ . '/../tickets';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $rutaPdf = $dir . "/ticket_reserva_" . intval($id_reserva) . ".pdf";

    $pdf = new FPDF();
    $pdf->AddPage();

    // Encabezado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Parque Las Heliconias', 0, 1, 'C');
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Ticket de Reserva Grupal', 0, 1, 'C');
    $pdf->Ln(5);

    // Datos principales
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, 'ID Reserva: ' . $id_reserva, 0, 1);
    $pdf->Cell(0, 6, 'Actividad: ' . utf8_decode($actividad), 0, 1);
    $pdf->Cell(0, 6, 'Fecha de visita: ' . $fecha_visita, 0, 1);
    $pdf->Ln(4);

    // Responsable
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'Responsable del grupo', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $nombreCompletoResp = trim(($responsableInfo['nombre'] ?? '') . ' ' . ($responsableInfo['apellido'] ?? ''));
    $pdf->Cell(0, 6, 'Nombre: ' . utf8_decode($nombreCompletoResp), 0, 1);
    $pdf->Cell(0, 6, 'Documento: ' . ($responsableInfo['documento'] ?? ''), 0, 1);
    if (!empty($responsableInfo['telefono'])) {
        $pdf->Cell(0, 6, 'Telefono: ' . $responsableInfo['telefono'], 0, 1);
    }
    if (!empty($responsableInfo['ciudad'])) {
        $pdf->Cell(0, 6, 'Ciudad: ' . $responsableInfo['ciudad'], 0, 1);
    }
    $pdf->Ln(4);

    // Participantes
    if (!empty($listaParticipantes)) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 7, 'Participantes', 0, 1);
        $pdf->SetFont('Arial', '', 10);

        foreach ($listaParticipantes as $i => $p) {
            $pdf->Ln(1);
            $pdf->Cell(0, 6, ($i + 1) . '. ' . utf8_decode(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? '')), 0, 1);
            $linea = '   Doc: ' . ($p['documento'] ?? '');
            if (!empty($p['ciudad'])) {
                $linea .= ' | Ciudad: ' . $p['ciudad'];
            }
            $pdf->Cell(0, 5, utf8_decode($linea), 0, 1);
        }
    }

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->MultiCell(0, 5, utf8_decode("Por favor presenta este ticket (impreso o en tu celular) al momento de ingresar al parque.\n¡Gracias por visitarnos!"));

    $pdf->Output('F', $rutaPdf);

    return $rutaPdf;
}

/* ============================================================
 * 📩 CORREO: RESERVA GRUPAL CON PDF ADJUNTO
 * ============================================================ */
function enviarCorreoReservaGrupal(
    $correoDestino,
    $nombreUsuario,
    $id_reserva,
    $fecha_visita,
    $actividad,
    $cantidadParticipantes,
    $responsableInfo,
    $listaParticipantes = []
) {
    $mail = new PHPMailer(true);

    try {
        smtpConfig($mail);

        $mail->addAddress($correoDestino, $nombreUsuario);

        // Logo
        $rutaLogo = __DIR__ . "/../assets/img/logoo.png";
        if (file_exists($rutaLogo)) {
            $mail->AddEmbeddedImage($rutaLogo, "logoHeliconias", basename($rutaLogo));
        }

        $asunto = "Confirmación de Reserva Grupal #$id_reserva - Parque Las Heliconias";

        // Cuerpo HTML
        $mensajeHTML = '
        <div style="width:100%;background:#f5faf5;padding:30px 0;font-family:Arial,sans-serif;">
          <div style="max-width:650px;margin:auto;background:white;padding:25px;border-radius:12px;">
            
            <div style="text-align:center;">
                <img src="cid:logoHeliconias" style="width:120px;margin-bottom:10px">
            </div>

            <h2 style="text-align:center;color:#2e6a30;margin-bottom:5px;">🌿 Reserva Grupal Confirmada</h2>
            <p style="text-align:center;color:#555;margin-top:0;">Gracias por elegir el Parque Las Heliconias.</p>

            <p>Hola <strong>' . htmlspecialchars($nombreUsuario) . '</strong>,</p>
            <p>Hemos registrado tu <strong>reserva grupal</strong> con la siguiente información:</p>

            <div style="background:#eaf5ea;padding:15px;border-radius:8px;margin:10px 0;font-size:14px;">
                <p><strong>ID Reserva:</strong> ' . intval($id_reserva) . '</p>
                <p><strong>Actividad:</strong> ' . htmlspecialchars($actividad) . '</p>
                <p><strong>Fecha de visita:</strong> ' . htmlspecialchars($fecha_visita) . '</p>
                <p><strong>Número de participantes:</strong> ' . intval($cantidadParticipantes) . '</p>
            </div>

            <h3 style="color:#2e6a30;margin-top:20px;">👤 Responsable del grupo</h3>
            <ul style="padding-left:18px;font-size:14px;color:#444;">
                <li><strong>Nombre:</strong> ' . htmlspecialchars(($responsableInfo["nombre"] ?? "") . " " . ($responsableInfo["apellido"] ?? "")) . '</li>
                <li><strong>Documento:</strong> ' . htmlspecialchars($responsableInfo["documento"] ?? "") . '</li>';

        if (!empty($responsableInfo["telefono"])) {
            $mensajeHTML .= '<li><strong>Teléfono:</strong> ' . htmlspecialchars($responsableInfo["telefono"]) . '</li>';
        }
        if (!empty($responsableInfo["ciudad"])) {
            $mensajeHTML .= '<li><strong>Ciudad:</strong> ' . htmlspecialchars($responsableInfo["ciudad"]) . '</li>';
        }

        $mensajeHTML .= '</ul>';

        if (!empty($listaParticipantes)) {
            $mensajeHTML .= '
            <h3 style="color:#2e6a30;margin-top:20px;">👥 Participantes adicionales</h3>
            <p style="font-size:13px;color:#555;">En el PDF adjunto encontrarás el detalle completo de los integrantes.</p>';
        }

        $mensajeHTML .= '
            <p style="margin-top:18px;">Adjuntamos tu <strong>ticket en PDF</strong>. Puedes mostrarlo en tu celular o impreso el día de la visita.</p>

            <p style="margin-top:20px;">Te esperamos 💚</p>

            <hr style="margin-top:25px;">
            <p style="font-size:11px;text-align:center;color:#777;">
                © ' . date("Y") . ' Parque Las Heliconias - Este es un mensaje automático.
            </p>
          </div>
        </div>';

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensajeHTML;

        // Generar PDF y adjuntar
        $rutaPdf = generarTicketPDFReservaGrupal(
            $id_reserva,
            $fecha_visita,
            $actividad,
            $responsableInfo,
            $listaParticipantes
        );

        if (file_exists($rutaPdf)) {
            $mail->addAttachment($rutaPdf, "Ticket_Reserva_{$id_reserva}.pdf");
        }

        $enviado = $mail->send();

        // Limpiar archivo temporal (opcional)
        if (file_exists($rutaPdf)) {
            @unlink($rutaPdf);
        }

        return $enviado;

    } catch (Exception $e) {
        @file_put_contents(__DIR__ . '/../logs/mail_errors.log', 
            date('Y-m-d H:i:s') . " - Error enviando correo grupal a {$correoDestino}: " . $mail->ErrorInfo . PHP_EOL, 
            FILE_APPEND);

        return false;
    }
}
