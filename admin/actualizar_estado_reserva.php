<?php
session_start();
require_once('../includes/verificar_admin.php');
require_once("../includes/supabase.php"); // ← ÚNICA conexión válida

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

// ================================
// 1️⃣ Verificar que la reserva exista
// ================================
list($codeReserva, $reservaData) = supabase_get("reservas", ["id_reserva" => $id_reserva]);

if ($codeReserva !== 200 || empty($reservaData)) {
    $_SESSION['toast'] = [
        'mensaje' => '⚠ La reserva no existe.',
        'tipo' => 'warning'
    ];
    header("Location: reservas.php");
    exit;
}

$id_usuario = $reservaData[0]["id_usuario"];

// ================================
// 2️⃣ Actualizar estado en Supabase
// ================================
$updateData = ["estado" => $nuevo_estado];

list($codeUpdate, $respUpdate) = supabase_update(
    "reservas",
    ["id_reserva" => $id_reserva],
    $updateData
);

if ($codeUpdate !== 200 && $codeUpdate !== 204) {
    $_SESSION['toast'] = [
        'mensaje' => '❌ Error al actualizar el estado de la reserva.',
        'tipo' => 'error'
    ];
    header("Location: reservas.php");
    exit;
}

// ================================
// 3️⃣ Crear notificación según estado
// ================================
if ($nuevo_estado === 'confirmada') {
    $titulo = '🎉 ¡Reserva Confirmada!';
    $mensaje = 'Tu reserva ha sido confirmada por el administrador. ¡Te esperamos para disfrutar del Parque Las Heliconias!';
    $tipo = 'exito';

} elseif ($nuevo_estado === 'cancelada') {
    $titulo = '❌ Reserva Cancelada';
    $mensaje = 'Tu reserva ha sido cancelada por el administrador. Puedes volver a realizar otra cuando desees.';
    $tipo = 'error';

} else {
    $titulo = 'ℹ Estado actualizado';
    $mensaje = 'El estado de tu reserva ha sido actualizado.';
    $tipo = 'info';
}

$notifData = [
    "id_usuario"    => $id_usuario,
    "id_reserva"    => $id_reserva,
    "titulo"        => $titulo,
    "mensaje"       => $mensaje,
    "tipo"          => $tipo,
    "fecha_creacion"=> date("Y-m-d H:i:s"),
    "leida"         => false
];

supabase_insert("notificaciones", $notifData);

// ================================
// 4️⃣ Mostrar mensaje final
// ================================
$_SESSION['toast'] = [
    'mensaje' => '✅ Estado actualizado correctamente.',
    'tipo' => 'exito'
];

header("Location: reservas.php");
exit;
?>