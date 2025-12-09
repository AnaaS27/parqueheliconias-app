<?php
require_once("../includes/supabase.php"); // ← ÚNICA conexión válida
require_once('../includes/verificar_admin.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitizar valores
    $nombre      = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $duracion    = intval($_POST['duracion_minutos']);
    $cupo        = intval($_POST['cupo_maximo']);
    $activo      = ($_POST['activo'] == "1") ? true : false;

    if (empty($nombre) || empty($descripcion) || $duracion <= 0 || $cupo <= 0) {
        echo "<script>alert('⚠ Todos los campos son obligatorios.'); window.history.back();</script>";
        exit;
    }

    // ============================================
    // 📌 GUARDAR ACTIVIDAD EN SUPABASE
    // ============================================

    $dataActividad = [
        "nombre"           => $nombre,
        "descripcion"      => $descripcion,
        "duracion_minutos" => $duracion,
        "cupo_maximo"      => $cupo,
        "activo"           => $activo
    ];

    list($codeInsert, $respActividad) = supabase_insert("actividades", $dataActividad);

    if ($codeInsert !== 201) {
        echo "<script>alert('❌ Error al registrar la actividad en Supabase'); window.history.back();</script>";
        exit;
    }

    // ID de la actividad creada (Supabase devuelve array dentro de array)
    $idActividad = $respActividad[0]["id_actividad"] ?? null;

    // ============================================
    // 🔔 CREAR NOTIFICACIÓN GLOBAL EN SUPABASE
    // ============================================

    $titulo = "🆕 Nueva Actividad Disponible";
    $mensaje = "Se ha agregado una nueva actividad: $nombre. ¡Resérvala y disfruta la experiencia!";

    $dataNotif = [
        "titulo"      => $titulo,
        "mensaje"     => $mensaje,
        "tipo"        => "info",
        "id_usuario"  => null,
        "id_reserva"  => null
    ];

    list($codeNotif, $respNotif) = supabase_insert("notificaciones", $dataNotif);

    // No detiene el proceso si la notificación falla, solo continúa.

    echo "<script>alert('✅ Actividad registrada exitosamente'); window.location='actividades.php';</script>";
    exit;
}
?>