<?php
require_once("../includes/supabase.php"); // ← ÚNICA conexión a la BD
require_once('../includes/verificar_admin.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id         = intval($_POST['id_actividad']);
    $nombre     = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $duracion   = intval($_POST['duracion_minutos']);
    $cupo       = intval($_POST['cupo_maximo']);
    $activo     = ($_POST['activo'] == "1") ? true : false;

    if (empty($nombre) || empty($descripcion) || $duracion <= 0 || $cupo <= 0) {
        echo "<script>alert('⚠ Todos los campos son obligatorios.'); window.history.back();</script>";
        exit;
    }

    // ============================================
    // 📌 ACTUALIZAR ACTIVIDAD EN SUPABASE
    // ============================================

    $dataUpdate = [
        "nombre"            => $nombre,
        "descripcion"       => $descripcion,
        "duracion_minutos"  => $duracion,
        "cupo_maximo"       => $cupo,
        "activo"            => $activo
    ];

    list($codeUpdate, $respUpdate) = supabase_update(
        "actividades",
        ["id_actividad" => $id],
        $dataUpdate
    );

    if ($codeUpdate !== 200 && $codeUpdate !== 204) {
        echo "<script>alert('❌ Error al actualizar la actividad en Supabase'); window.history.back();</script>";
        exit;
    }

    // ============================================
    // 🔔 CREAR NOTIFICACIÓN GLOBAL EN SUPABASE
    // ============================================

    $titulo  = "🔄 Actividad actualizada";
    $mensaje = "La actividad <b>$nombre</b> ha sido modificada. Consulta los nuevos detalles.";

    $dataNotif = [
        "titulo"     => $titulo,
        "mensaje"    => $mensaje,
        "tipo"       => "info",
        "id_usuario" => null,
        "id_reserva" => null
    ];

    // Insertar notificación
    supabase_insert("notificaciones", $dataNotif);

    echo "<script>alert('✅ Actividad actualizada exitosamente'); window.location='actividades.php';</script>";
    exit;
}
?>