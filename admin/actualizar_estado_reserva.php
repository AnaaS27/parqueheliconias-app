<?php
session_start();
require_once('../includes/verificar_admin.php');
require_once("../includes/supabase.php");
require_once("../includes/email_api.php");  // ← IMPORTANTE (correo)

// --- Validar datos recibidos ---
if (!isset($_POST['id_reserva']) || !isset($_POST['estado'])) {
    $_SESSION['toast'] = ['mensaje' => '❌ Datos incompletos.', 'tipo' => 'error'];
    header("Location: reservas.php");
    exit;
}

$id_reserva = intval($_POST['id_reserva']);
$nuevo_estado = $_POST['estado'];

// ================================
// 1️⃣ OBTENER RESERVA
// ================================
$endpointReserva = "reservas?id_reserva=eq.$id_reserva&select=*";
list($codeRes, $dataReserva) = supabase_get($endpointReserva);

if ($codeRes !== 200 || empty($dataReserva)) {
    $_SESSION['toast'] = ['mensaje' => '⚠ La reserva no existe.', 'tipo' => 'warning'];
    header("Location: reservas.php");
    exit;
}

$reserva = $dataReserva[0];
$id_usuario   = $reserva["id_usuario"];
$id_actividad = $reserva["id_actividad"];
$fecha_visita = $reserva["fecha_reserva"];

// ================================
// 2️⃣ OBTENER DATOS DEL USUARIO
// ================================
$endpointUser = "usuarios?id_usuario=eq.$id_usuario&select=nombre,apellido,correo";
list($codeUser, $dataUser) = supabase_get($endpointUser);

if ($codeUser !== 200 || empty($dataUser)) {
    $_SESSION['toast'] = ['mensaje' => '⚠ No se encontró el usuario asociado.', 'tipo' => 'warning'];
    header("Location: reservas.php");
    exit;
}

$usuario = $dataUser[0];
$nombreUsuario = $usuario["nombre"] . " " . $usuario["apellido"];
$correoUsuario = $usuario["correo"];

// ================================
// 3️⃣ OBTENER ACTIVIDAD
// ================================
$endpointAct = "actividades?id_actividad=eq.$id_actividad&select=nombre";
list($codeAct, $dataAct) = supabase_get($endpointAct);

$nombreActividad = $dataAct[0]["nombre"] ?? "Actividad desconocida";

// ================================
// 4️⃣ ACTUALIZAR ESTADO
// ================================
$updateEndpoint = "reservas?id_reserva=eq.$id_reserva";
list($codeUpdate, $respUpdate) = supabase_update($updateEndpoint, ["estado" => $nuevo_estado]);

if ($codeUpdate !== 200 && $codeUpdate !== 204) {
    $_SESSION['toast'] = [
        'mensaje' => '❌ Error al actualizar el estado de la reserva.',
        'tipo' => 'error'
    ];
    header("Location: reservas.php");
    exit;
}

// ================================
// 5️⃣ CREAR NOTIFICACIÓN + ENVIAR CORREO
// ================================
if ($nuevo_estado === "confirmada") {

    // Notificación DB
    supabase_insert("notificaciones", [
        "id_usuario" => $id_usuario,
        "id_reserva" => $id_reserva,
        "titulo"     => "🎉 ¡Reserva Confirmada!",
        "mensaje"    => "Tu reserva del Parque Las Heliconias fue confirmada.",
        "tipo"       => "exito",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida" => false
    ]);

    // ENVIAR CORREO
    enviarCorreoReserva(
        $correoUsuario,
        $nombreUsuario,
        $id_reserva,
        $fecha_visita,
        $nombreActividad
    );

} elseif ($nuevo_estado === "cancelada") {

    // Notificación DB
    supabase_insert("notificaciones", [
        "id_usuario" => $id_usuario,
        "id_reserva" => $id_reserva,
        "titulo"     => "❌ Reserva Cancelada",
        "mensaje"    => "Tu reserva fue cancelada por el administrador.",
        "tipo"       => "error",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida" => false
    ]);

    // ENVIAR CORREO
    enviarCorreoCancelacion(
        $correoUsuario,
        $nombreUsuario,
        $id_reserva,
        $nombreActividad,
        $fecha_visita
    );

} else {

    supabase_insert("notificaciones", [
        "id_usuario" => $id_usuario,
        "id_reserva" => $id_reserva,
        "titulo"     => "ℹ Estado actualizado",
        "mensaje"    => "El estado de tu reserva ha sido modificado.",
        "tipo"       => "info",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida" => false
    ]);
}

// ================================
// 6️⃣ MENSAJE FINAL Y REDIRECCIÓN
// ================================
$_SESSION['toast'] = ['mensaje' => '✅ Estado actualizado con éxito.', 'tipo' => 'exito'];
header("Location: reservas.php");
exit;

?>
