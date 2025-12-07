<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php'); // ← ahora usamos Supabase

// 🛑 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('⚠️ Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// 🛑 Verificar que llega actividad
if (!isset($_POST['id_actividad'])) {
    echo "<script>
        alert('⚠️ No se especificó la actividad.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$id_usuario      = $_SESSION['usuario_id'];
$id_actividad    = intval($_POST['id_actividad']);
$tipo_reserva    = "individual";

$fecha_visita       = $_POST['fecha_visita'];
$tipo_documento     = $_POST['tipo_documento'];
$documento          = $_POST['numero_identificacion'];
$fecha_nacimiento   = $_POST['fecha_nacimiento'];
$id_genero          = $_POST['sexo'];
$id_ciudad          = $_POST['id_ciudad'];
$telefono           = $_POST['telefono'];

$institucion        = !empty($_POST['institucion']) ? intval($_POST['institucion']) : null;
$observaciones      = $_POST['observaciones'] ?? null;

$nombre             = $_POST['nombre'] ?? "";
$apellido           = $_POST['apellido'] ?? "";

/* =============================================================
   1️⃣ CREAR RESERVA EN SUPABASE
   ============================================================= */
$reservaData = [
    "id_usuario"            => $id_usuario,
    "id_actividad"          => $id_actividad,
    "id_institucion"        => $institucion,
    "tipo_reserva"          => "individual",
    "estado"                => "pendiente",
    "numero_participantes"  => 1,
    "fecha_reserva"         => date("Y-m-d H:i:s")
];

[$codeR, $dataR] = supabase_insert("reservas", $reservaData);

if ($codeR !== 201 || empty($dataR)) {
    echo "<script>
        alert('❌ Error al crear la reserva.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$id_reserva = $dataR[0]["id_reserva"];

/* =============================================================
   2️⃣ CREAR PARTICIPANTE EN SUPABASE
   ============================================================= */
$participanteData = [
    "id_reserva"            => $id_reserva,
    "id_usuario"            => null,
    "nombre"                => $nombre,
    "apellido"              => $apellido,
    "documento"             => $documento,
    "telefono"              => $telefono,
    "es_usuario_registrado" => false,
    "fecha_registro"        => date("Y-m-d H:i:s"),
    "id_genero"             => $id_genero,
    "id_institucion"        => $institucion,
    "fecha_nacimiento"      => $fecha_nacimiento,
    "id_ciudad"             => $id_ciudad,
    "fecha_visita"          => $fecha_visita,
    "observaciones"         => $observaciones
];

[$codeP, $dataP] = supabase_insert("participantes_reserva", $participanteData);

if ($codeP !== 201) {
    echo "<script>
        alert('❌ Error al registrar el participante.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

/* =============================================================
   ✔ FINALIZADO
   ============================================================= */
echo "<script>
    alert('✅ Reserva realizada exitosamente.');
    window.location = 'mis_reservas.php';
</script>";
exit;

?>
