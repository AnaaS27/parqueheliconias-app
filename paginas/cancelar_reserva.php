<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');

$id_usuario = $_SESSION['usuario_id'];

// Validar ID recibido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['toast'] = [
        'tipo' => 'warning',
        'mensaje' => '⚠️ Parámetro inválido para cancelar la reserva.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

$id_reserva = intval($_GET['id']);

// 🔍 Verificar si la reserva pertenece al usuario y está pendiente
$sql = "SELECT estado 
        FROM reservas 
        WHERE id_reserva = $1 AND id_usuario = $2";

$result = pg_query_params($conn, $sql, [$id_reserva, $id_usuario]);

if (!$result || pg_num_rows($result) === 0) {
    $_SESSION['toast'] = [
        'tipo' => 'warning',
        'mensaje' => '⚠️ No se encontró la reserva o no pertenece a tu cuenta.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

$reserva = pg_fetch_assoc($result);

if ($reserva['estado'] !== 'pendiente') {
    $_SESSION['toast'] = [
        'tipo' => 'error',
        'mensaje' => '❌ Solo se pueden cancelar reservas en estado pendiente.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

// 📝 Actualizar estado a cancelada
$update = "UPDATE reservas 
           SET estado = 'cancelada', fecha_cancelacion = NOW() 
           WHERE id_reserva = $1";

$updateResult = pg_query_params($conn, $update, [$id_reserva]);

if ($updateResult) {
    $_SESSION['toast'] = [
        'tipo' => 'success',
        'mensaje' => '✅ ¡Reserva cancelada exitosamente!'
    ];
} else {
    $_SESSION['toast'] = [
        'tipo' => 'error',
        'mensaje' => '❌ Error al cancelar la reserva. Intenta nuevamente.'
    ];
}

// Crear notificación
$mensaje = "Tu reserva #$id_reserva ha sido cancelada exitosamente.";
$titulo  = "Reserva Cancelada";
$tipo    = "alerta";

$sql_notificacion = "INSERT INTO notificaciones (id_usuario, id_reserva, mensaje, titulo, tipo, leida, fecha_creacion) 
                     VALUES ($1, $2, $3, $4, $5, false, NOW())";

pg_query_params($conn, $sql_notificacion, [$id_usuario, $id_reserva, $mensaje, $titulo, $tipo]);


// Redirigir de vuelta al listado
header("Location: mis_reservas.php");
exit;
?>

