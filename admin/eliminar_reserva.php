<?php
include('../includes/conexion.php');
include('../includes/verificar_admin.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ ID no especificado'); window.location='reservas.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// Obtener datos antes de eliminar para enviar notificación
$sqlInfo = "SELECT r.id_usuario, a.nombre AS actividad 
            FROM reservas r 
            INNER JOIN actividades a ON r.id_actividad = a.id_actividad
            WHERE r.id_reserva = $1";
$result = pg_query_params($conn, $sqlInfo, [$id]);

if (!$result || pg_num_rows($result) === 0) {
    echo "<script>alert('❌ La reserva no existe'); window.location='reservas.php';</script>";
    exit;
}

$datos = pg_fetch_assoc($result);
$id_usuario = $datos['id_usuario'];
$actividad = $datos['actividad'];

// Eliminar la reserva
$sql = "DELETE FROM reservas WHERE id_reserva = $1";
$resultDelete = pg_query_params($conn, $sql, [$id]);

if ($resultDelete) {

    // Registrar notificación
    $titulo = "Reserva Eliminada";
    $mensaje = "Tu reserva para '$actividad' fue eliminada por el administrador.";
    $tipo = "alerta";

    $sqlNotif = "INSERT INTO notificaciones (id_usuario, titulo, mensaje, tipo, leida, fecha_creacion) 
                 VALUES ($1, $2, $3, $4, FALSE, NOW())";

    pg_query_params($conn, $sqlNotif, [$id_usuario, $titulo, $mensaje, $tipo]);

    echo "<script>alert('✅ Reserva eliminada correctamente'); window.location='reservas.php';</script>";
} else {
    echo "<script>alert('❌ No se pudo eliminar la reserva'); window.history.back();</script>";
}

pg_close($conn);
?>
