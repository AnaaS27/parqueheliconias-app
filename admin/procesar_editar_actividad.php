<?php
include('../includes/conexion.php');
include('../includes/verificar_admin.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = intval($_POST['id_actividad']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $duracion = intval($_POST['duracion_minutos']);
    $cupo = intval($_POST['cupo_maximo']);
    $activo = ($_POST['activo'] == "1") ? 1 : 0;

    if (empty($nombre) || empty($descripcion) || $duracion <= 0 || $cupo <= 0) {
        echo "<script>alert('⚠️ Todos los campos son obligatorios.'); window.history.back();</script>";
        exit;
    }

    // Actualizar actividad
    $sql = "
        UPDATE actividades 
        SET nombre = $1, descripcion = $2, duracion_minutos = $3, cupo_maximo = $4, activo = $5 
        WHERE id_actividad = $6
    ";

    $result = pg_query_params($conn, $sql, [$nombre, $descripcion, $duracion, $cupo, $activo, $id]);

    if ($result) {

        // Crear notificación global
        $titulo = "🔄 Actividad actualizada";
        $mensaje = "La actividad <b>$nombre</b> ha sido modificada. Consulta los nuevos detalles.";
        $tipo = "info";

        $sql_notificacion = "
            INSERT INTO notificaciones (titulo, mensaje, tipo, id_usuario, id_reserva)
            VALUES ($1, $2, $3, NULL, NULL)
        ";

        pg_query_params($conn, $sql_notificacion, [$titulo, $mensaje, $tipo]);

        echo "<script>alert('✅ Actividad actualizada exitosamente'); window.location='actividades.php';</script>";
    } else {
        echo "<script>alert('❌ Error al actualizar la actividad'); window.history.back();</script>";
    }

    pg_close($conn);
}
?>

