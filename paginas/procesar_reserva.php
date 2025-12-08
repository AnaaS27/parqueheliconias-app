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
    !isset($_POST['id_actividad']) ||
    !isset($_POST['fecha']) ||
    !isset($_POST['cantidad'])
) {
    echo "<script>alert('❌ Faltan datos para procesar la reserva'); window.history.back();</script>";
    exit;
}

$actividad_id   = intval($_POST['id_actividad']);
$fecha          = $_POST['fecha'];
$cantidad       = intval($_POST['cantidad']);

// =============================
// 📌 Consultar actividad en Supabase
// =============================
$res = supabaseFetch("actividades?id=eq.$actividad_id");

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
// 📝 Crear reserva en Supabase
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
// 👥 Insertar participantes
// =============================
for ($i = 0; $i < $cantidad; $i++) {
    $nombre = $_POST["nombre_$i"] ?? "Participante $i";
    $documento = $_POST["documento_$i"] ?? null;

    $participante = [
        "reserva_id" => $reserva_id,
        "nombre" => $nombre,
        "documento" => $documento
    ];

    supabaseFetch("participantes_reservas", "POST", $participante);
}

// =============================
// ➖ Actualizar cupos restantes
// =============================
$nuevos_cupos = $cupos - $cantidad;

supabaseFetch("actividades?id=eq.$actividad_id", "PATCH", [
    "cupos" => $nuevos_cupos
]);

// =============================
// 🎉 Confirmación
// =============================
echo "<script>
    alert('✅ ¡Reserva realizada exitosamente!');
    window.location = 'mis_reservas.php';
</script>";

?>
