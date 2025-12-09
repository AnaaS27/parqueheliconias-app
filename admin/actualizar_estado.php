<?php
require_once("../includes/supabase.php");
require_once('../includes/verificar_admin.php');

// ===============================
// Validar parámetros GET
// ===============================
if (!isset($_GET['id']) || !isset($_GET['estado'])) {
    echo "<script>alert('Acceso no válido'); window.location='reservas.php';</script>";
    exit;
}

$id_reserva = intval($_GET['id']);
$nuevo_estado = $_GET['estado'];

// Validar estado permitido
if (!in_array($nuevo_estado, ['pendiente', 'confirmada', 'cancelada'])) {
    echo "<script>alert('❌ Estado no válido'); window.history.back();</script>";
    exit;
}

// ===============================
// 1️⃣ Verificar que la reserva exista
// ===============================
list($codeReserva, $reservaData) = supabase_get("reservas", ["id_reserva" => $id_reserva]);

if ($codeReserva !== 200 || empty($reservaData)) {
    echo "<script>alert('❌ La reserva no existe'); window.location='reservas.php';</script>";
    exit;
}

// Obtener ID de usuario
$id_usuario = $reservaData[0]["id_usuario"];

// ===============================
// 2️⃣ Actualizar estado de la reserva
// ===============================
$updateData = ["estado" => $nuevo_estado];

if ($nuevo_estado === "cancelada") {
    $updateData["fecha_cancelacion"] = date("Y-m-d H:i:s");
}

list($codeUpdate, $respUpdate) = supabase_update(
    "reservas",
    ["id_reserva" => $id_reserva],
    $updateData
);

if ($codeUpdate !== 200 && $codeUpdate !== 204) {
    echo "<script>alert('❌ Error al actualizar la reserva'); window.history.back();</script>";
    exit;
}

// ===============================
// 3️⃣ Crear notificación según el estado
// ===============================
if ($nuevo_estado === 'confirmada') {
    $titulo = '🎉 ¡Reserva Confirmada!';
    $mensaje = 'Tu reserva ha sido confirmada por administración. ¡Te esperamos!';
    $tipo = 'exito';

} elseif ($nuevo_estado === 'cancelada') {
    $titulo = '❌ Reserva Cancelada';
    $mensaje = 'Tu reserva ha sido cancelada por administración.';
    $tipo = 'error';

} else {
    $titulo = 'ℹ Actualización de reserva';
    $mensaje = 'Tu reserva ha cambiado de estado.';
    $tipo = 'info';
}

$notifData = [
    "id_usuario"     => $id_usuario,
    "id_reserva"     => $id_reserva,
    "titulo"         => $titulo,
    "mensaje"        => $mensaje,
    "tipo"           => $tipo,
    "leida"          => false,
    "fecha_creacion" => date("Y-m-d H:i:s")
];

// Insertar notificación
supabase_insert("notificaciones", $notifData);

// ===============================
// 4️⃣ Notificación visual final
// ===============================
echo "<script>alert('✅ Estado actualizado y notificación enviada'); window.location='reservas.php';</script>";
exit;
?>