<?php
session_start();
include('../includes/verificar_sesion_admin.php');
include('../includes/conexion.php'); // Aquí ya tienes $conn como pg_connect

// --- Validar datos recibidos ---
if (!isset($_POST['id_reserva']) || !isset($_POST['estado'])) {
    $_SESSION['toast'] = [
        'mensaje' => '❌ Datos incompletos para actualizar la reserva.',
        'tipo' => 'error'
    ];
    header("Location: reservas.php");
    exit;
}

$id_reserva = intval($_POST['id_reserva']);
$nuevo_estado = $_POST['estado'];

// --- 1️⃣ Verificar que la reserva exista ---
$sql_check = "SELECT id_usuario FROM reservas WHERE id_reserva = $1";
$result = pg_query_params($conn, $sql_check, [$id_reserva]);

if (!$result || pg_num_rows($result) === 0) {
    $_SESSION['toast'] = [
        'mensaje' => '⚠️ La reserva no existe.',
        'tipo' => 'warning'
    ];
    header("Location: reservas.php");
    exit;
}

$reserva = pg_fetch_assoc($result);
$id_usuario = $reserva['id_usuario'];

// --- 2️⃣ Actualizar estado de la reserva ---
$sql_update = "UPDATE reservas SET estado = $1 WHERE id_reserva = $2";
$update_result = pg_query_params($conn, $sql_update, [$nuevo_estado, $id_reserva]);

if ($update_result) {

    // --- 3️⃣ Crear notificación según el estado ---
    if ($nuevo_estado === 'confirmada') {
        $titulo = '🎉 ¡Reserva Confirmada!';
        $mensaje = 'Tu reserva ha sido confirmada por el administrador. Te esperamos para disfrutar del Parque de las Heliconias.';
        $tipo = 'exito';
    } elseif ($nuevo_estado === 'cancelada') {
        $titulo = '❌ Reserva Cancelada';
        $mensaje = 'Tu reserva ha sido cancelada por el administrador. Puedes volver a realizar otra cuando desees.';
        $tipo = 'error';
    } else {
        $titulo = 'ℹ️ Estado actualizado';
        $mensaje = 'El estado de tu reserva ha sido actualizado.';
        $tipo = 'info';
    }

    $sql_notif = "INSERT INTO notificaciones (id_usuario, id_reserva, titulo, mensaje, tipo, fecha_creacion, leida)
                  VALUES ($1, $2, $3, $4, $5, NOW(), false)";
    pg_query_params($conn, $sql_notif, [$id_usuario, $id_reserva, $titulo, $mensaje, $tipo]);

    $_SESSION['toast'] = [
        'mensaje' => '✅ Estado actualizado correctamente.',
        'tipo' => 'exito'
    ];

} else {
    $_SESSION['toast'] = [
        'mensaje' => '❌ Error al actualizar el estado de la reserva.',
        'tipo' => 'error'
    ];
}

// --- 4️⃣ Redirigir ---
header("Location: reservas.php");
exit;
?>

