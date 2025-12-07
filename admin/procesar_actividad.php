<?php
include('../includes/conexion.php');
include('../includes/verificar_admin.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitizar valores
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $duracion = intval($_POST['duracion_minutos']);
    $cupo = intval($_POST['cupo_maximo']);
    $activo = ($_POST['activo'] == "1") ? 1 : 0;

    if (empty($nombre) || empty($descripcion) || $duracion <= 0 || $cupo <= 0) {
        echo "<script>alert('⚠️ Todos los campos son obligatorios.'); window.history.back();</script>";
        exit;
    }

    // Guardar actividad
    $sql = "
        INSERT INTO actividades (nombre, descripcion, duracion_minutos, cupo_maximo, activo)
        VALUES ($1, $2, $3, $4, $5)
        RETURNING id_actividad
    ";

    $result = pg_query_params($conn, $sql, [$nombre, $descripcion, $duracion, $cupo, $activo]);

    if ($result) {

        // Crear la notificación global
        $titulo = "🆕 Nueva Actividad Disponible";
        $mensaje = "Se ha agregado una nueva actividad: $nombre. ¡Resérvala y disfruta la experiencia!";
        $tipo = "info";

        // Notificación sin usuario ni reserva (global)
        $sqlNotif = "
            INSERT INTO notificaciones (titulo, mensaje, tipo, id_usuario, id_reserva)
            VALUES ($1, $2, $3, NULL, NULL)
        ";

        pg_query_params($conn, $sqlNotif, [$titulo, $mensaje, $tipo]);

        echo "<script>alert('✅ Actividad registrada exitosamente'); window.location='actividades.php';</script>";
    } else {
        echo "<script>alert('❌ Error al registrar la actividad'); window.history.back();</script>";
    }

    pg_close($conn);
}
?>

