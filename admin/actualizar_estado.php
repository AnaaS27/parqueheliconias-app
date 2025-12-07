<?php
include('../includes/conexion.php');
include('../includes/verificar_admin.php');

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id_reserva = intval($_GET['id']);
    $nuevo_estado = $_GET['estado'];

    if (!in_array($nuevo_estado, ['pendiente', 'confirmada', 'cancelada'])) {
        echo "<script>alert('❌ Estado no válido'); window.history.back();</script>";
        exit;
    }

    // 1️⃣ Obtener usuario dueño de la reserva
    $sql_user = "SELECT id_usuario FROM reservas WHERE id_reserva = $1";
    $result_user = pg_query_params($conn, $sql_user, [$id_reserva]);

    if (!$result_user || pg_num_rows($result_user) === 0) {
        echo "<script>alert('❌ La reserva no existe'); window.location='reservas.php';</script>";
        exit;
    }

    $row_user = pg_fetch_assoc($result_user);
    $id_usuario = $row_user['id_usuario'];

    // 2️⃣ Actualizar estado
    if ($nuevo_estado === 'cancelada') {
        $sql_update = "UPDATE reservas SET estado = $1, fecha_cancelacion = NOW() WHERE id_reserva = $2";
        $params = [$nuevo_estado, $id_reserva];
    } else {
        $sql_update = "UPDATE reservas SET estado = $1 WHERE id_reserva = $2";
        $params = [$nuevo_estado, $id_reserva];
    }

    $result_update = pg_query_params($conn, $sql_update, $params);

    if ($result_update) {

        // 3️⃣ Crear mensaje según estado
        if ($nuevo_estado === 'confirmada') {
            $titulo = '🎉 ¡Reserva Confirmada!';
            $mensaje = 'Tu reserva ha sido confirmada. ¡Te esperamos en el Parque Las Heliconias!';
            $tipo = 'exito';
        } elseif ($nuevo_estado === 'cancelada') {
            $titulo = '❌ Reserva Cancelada';
            $mensaje = 'Tu reserva fue cancelada por administración.';
            $tipo = 'error';
        } else {
            $titulo = 'ℹ️ Actualización de reserva';
            $mensaje = 'Tu reserva ha cambiado de estado.';
            $tipo = 'info';
        }

        // 4️⃣ Insertar notificación en Supabase
        $sql_notif = "INSERT INTO notificaciones (id_usuario, id_reserva, titulo, mensaje, tipo, leida)
                      VALUES ($1, $2, $3, $4, $5, false)";

        pg_query_params($conn, $sql_notif, [$id_usuario, $id_reserva, $titulo, $mensaje, $tipo]);

        echo "<script>alert('✅ Estado actualizado y notificación enviada'); window.location='reservas.php';</script>";
        
    } else {
        echo "<script>alert('❌ Error al actualizar la reserva'); window.history.back();</script>";
    }

} else {
    echo "<script>alert('Acceso no válido'); window.location='reservas.php';</script>";
}
?>
