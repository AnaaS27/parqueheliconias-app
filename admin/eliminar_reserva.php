<?php
require_once("../includes/supabase.php"); 
require_once('../includes/verificar_admin.php');

// ===============================
// Validación del ID
// ===============================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ ID no especificado'); window.location='reservas.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// ===============================
// 1️⃣ OBTENER DATOS DE LA RESERVA (usuario y actividad)
// ===============================
list($codeInfo, $reservaData) = supabase_get("reservas", ["id_reserva" => $id]);

if ($codeInfo !== 200 || empty($reservaData)) {
    echo "<script>alert('❌ La reserva no existe'); window.location='reservas.php';</script>";
    exit;
}

$reserva = $reservaData[0];
$id_usuario = $reserva["id_usuario"];
$id_actividad = $reserva["id_actividad"];

// Obtener nombre de la actividad
list($codeAct, $actividadData) = supabase_get("actividades", ["id_actividad" => $id_actividad]);
$actividad = $actividadData[0]["nombre"] ?? "Actividad desconocida";

// ===============================
// 2️⃣ ELIMINAR RESERVA EN SUPABASE
// ===============================
list($codeDelete, $deleteResponse) = supabase_delete("reservas", ["id_reserva" => $id]);

if ($codeDelete !== 200 && $codeDelete !== 204) {
    echo "<script>alert('❌ No se pudo eliminar la reserva'); window.history.back();</script>";
    exit;
}

// ===============================
// 3️⃣ REGISTRAR NOTIFICACIÓN
// ===============================
$titulo = "Reserva Eliminada";
$mensaje = "Tu reserva para '$actividad' fue eliminada por el administrador.";

$notifData = [
    "id_usuario"      => $id_usuario,
    "titulo"          => $titulo,
    "mensaje"         => $mensaje,
    "tipo"            => "alerta",
    "leida"           => false,
    "fecha_creacion"  => date("Y-m-d H:i:s")
];

supabase_insert("notificaciones", $notifData);

// ===============================
// 4️⃣ Respuesta visual
// ===============================
echo "<script>alert('✅ Reserva eliminada correctamente'); window.location='reservas.php';</script>";
exit;
?>