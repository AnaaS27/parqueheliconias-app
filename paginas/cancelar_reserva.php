<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

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

// 1️⃣ Verificar que la reserva existe y pertenece al usuario
list($codeCheck, $dataCheck) = supabase_get("reservas?id_reserva=eq.$id_reserva&select=id_reserva,estado,id_usuario");

if ($codeCheck !== 200 || empty($dataCheck)) {
    $_SESSION['toast'] = [
        'tipo' => 'warning',
        'mensaje' => '⚠️ No se encontró la reserva.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

$reserva = $dataCheck[0];

if ($reserva["id_usuario"] != $id_usuario) {
    $_SESSION['toast'] = [
        'tipo' => 'error',
        'mensaje' => '❌ Esta reserva no pertenece a tu cuenta.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

if ($reserva["estado"] !== "pendiente") {
    $_SESSION['toast'] = [
        'tipo' => 'error',
        'mensaje' => '❌ Solo puedes cancelar reservas pendientes.'
    ];
    header("Location: mis_reservas.php");
    exit;
}

// 2️⃣ Actualizar estado → cancelada
$updateData = [
    "estado" => "cancelada",
    "fecha_cancelacion" => date("Y-m-d H:i:s")
];

list($codeUpdate, $resUpdate) = supabase_update("reservas?id_reserva=eq.$id_reserva", $updateData);

// ¿Funcionó?
if ($codeUpdate === 200) {
    $_SESSION['toast'] = [
        'tipo' => 'success',
        'mensaje' => '✅ ¡La reserva ha sido cancelada exitosamente!'
    ];
} else {
    $_SESSION['toast'] = [
        'tipo' => 'error',
        'mensaje' => '❌ Error al cancelar la reserva.'
    ];
}

// 3️⃣ Guardar notificación
$notif = [
    "id_usuario" => $id_usuario,
    "id_reserva" => $id_reserva,
    "titulo" => "Reserva cancelada",
    "mensaje" => "Tu reserva #$id_reserva ha sido cancelada.",
    "tipo" => "alerta",
    "leida" => false,
    "fecha_creacion" => date("Y-m-d H:i:s")
];

supabase_insert("notificaciones", $notif);

// Volver al listado
header("Location: mis_reservas.php");
exit;
?>
