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
// 📌 Consultar actividad en Supabase (solo para verificar que exista)
// =============================
list($codeAct, $dataAct) = supabase_get("actividades?id_actividad=eq.$actividad_id&select=*");

if ($codeAct !== 200 || empty($dataAct)) {
    echo "<script>alert('❌ Actividad no encontrada.'); window.history.back();</script>";
    exit;
}

// =============================
// 📝 Crear reserva en Supabase
// =============================
$nuevaReserva = [
    "id_usuario"            => $usuario_id,
    "id_actividad"          => $actividad_id,
    "fecha_reserva"         => $fecha,
    "numero_participantes"  => $cantidad,
    "fecha_creacion"        => date('Y-m-d H:i:s'),
    "estado"                => "pendiente"
];

list($codeRes, $resData) = supabase_insert("reservas", $nuevaReserva);

if ($codeRes !== 201) {
    echo "<script>alert('❌ Error al registrar la reserva.'); window.history.back();</script>";
    exit;
}

$reserva_id = $resData[0]["id_reserva"];

// =============================
// 💬 Redirigir a agregar participantes
// =============================
header("Location: agregar_participantes.php?id_reserva=$reserva_id&cant=$cantidad&fecha=$fecha");
exit;

?>
