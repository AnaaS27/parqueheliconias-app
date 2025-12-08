<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

// =============================
// 🔐 Verificar sesión
// =============================
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('⚠️ Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// =============================
// 📌 Validar datos del formulario
// =============================
if (
    !isset($_POST['actividad_id']) ||
    !isset($_POST['fecha']) ||
    !isset($_POST['cantidad'])
) {
    echo "<script>alert('❌ Faltan datos para procesar la reserva'); window.history.back();</script>";
    exit;
}

$actividad_id = intval($_POST['actividad_id']);
$fecha        = $_POST['fecha'];
$cantidad     = intval($_POST['cantidad']);

// =============================
// 📌 Consultar actividad en Supabase
// =============================
$res = supabaseFetch("actividades?id_actividad=eq.$actividad_id");

if ($res["code"] !== 200 || empty($res["data"])) {
    echo "<script>alert('❌ Actividad no encontrada.'); window.history.back();</script>";
    exit;
}

$actividad = $res["data"][0];
$cupos = $actividad["cupos"];

// =============================
// 🚨 Validar disponibilidad
// =============================
if ($cantidad > $cupos) {
    echo "<script>
        alert('❌ No hay suficientes cupos disponibles. Cupos actuales: $cupos');
        window.history.back();
    </script>";
    exit;
}

// =============================
// 📝 Crear reserva en Supabase (sin participantes todavía)
// =============================
$nuevaReserva = [
    "usuario_id"     => $usuario_id,
    "id_actividad"   => $actividad_id,
    "fecha_reserva"  => $fecha,
    "cantidad"       => $cantidad,
    "fecha_creacion" => date('Y-m-d H:i:s')
];

$resReserva = supabaseFetch("reservas", "POST", $nuevaReserva);

if ($resReserva["code"] !== 201) {
    echo "<script>alert('❌ Error al registrar la reserva.'); window.history.back();</script>";
    exit;
}

$reserva_id = $resReserva["data"][0]["id"];

// =============================
// 👉 Redirigir a agregar participantes
// =============================
header("Location: agregar_participantes.php?id_reserva=$reserva_id&cantidad=$cantidad");
exit;

?>
