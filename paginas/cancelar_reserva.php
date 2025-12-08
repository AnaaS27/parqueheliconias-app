<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

$id_usuario = $_SESSION['usuario_id'];

// ---------------------------
// ✔ Validar ID recibido
// ---------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['toast'] = [
        'tipo'    => 'warning',
        'mensaje' => '⚠️ Parámetro inválido para cancelar la reserva.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

$id_reserva = intval($_GET['id']);

// ---------------------------
// 🔍 1. Verificar que la reserva existe y pertenece al usuario
// ---------------------------
list($codeRes, $dataRes) = supabase_get(
    "reservas?id_reserva=eq.$id_reserva&id_usuario=eq.$id_usuario&select=id_reserva,estado"
);

if ($codeRes !== 200 || empty($dataRes)) {
    $_SESSION['toast'] = [
        'tipo'    => 'warning',
        'mensaje' => '⚠️ No se encontró la reserva o no pertenece a tu cuenta.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

$reserva = $dataRes[0];

// ---------------------------
// ❌ No se puede cancelar si no está pendiente
// ---------------------------
if ($reserva["estado"] !== "pendiente") {
    $_SESSION['toast'] = [
        'tipo'    => 'error',
        'mensaje' => '❌ Solo se pueden cancelar reservas en estado pendiente.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

// ---------------------------
// 📝 2. Actualizar estado a CANCELADA
// ---------------------------
$updateData = [
    "estado"           => "cancelada",
    "fecha_cancelacion"=> date("c")  // formato ISO 8601
];

list($codeUpdate, $resUpdate) = supabase_update("reservas?id_reserva=eq.$id_reserva", $updateData);

if ($codeUpdate === 200) {
    $_SESSION['toast'] = [
        'tipo'    => 'success',
        'mensaje' => '✅ ¡Reserva cancelada exitosamente!'
    ];
} else {
    $_SESSION['toast'] = [
        'tipo'    => 'error',
        'mensaje' => '❌ Error al cancelar la reserva. Intenta nuevamente.'
    ];
}

// ---------------------------
// 🔔 3. Crear notificación
// ---------------------------
$notificacion = [
    "id_usuario"     => $id_usuario,
    "id_reserva"     => $id_reserva,
    "mensaje"        => "Tu reserva #$id_reserva ha sido cancelada exitosamente.",
    "titulo"         => "Reserva Cancelada",
    "tipo"           => "alerta",
    "leida"          => false,
    "fecha_creacion" => date("c")
];

supabase_insert("notificaciones", $notificacion); // no importa si falla o no

// ---------------------------
// 🔁 Redirigir
// ---------------------------
header("Location: mis_reservas.php");
exit;
?>
