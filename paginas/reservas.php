<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

// 🛑 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('⚠️ Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// 🛑 Verificar datos del formulario
if (!isset($_POST['id_actividad'])) {
    echo "<script>
        alert('⚠️ No se especificó la actividad.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$id_usuario    = intval($_SESSION['usuario_id']);
$id_actividad  = intval($_POST['id_actividad']);
$tipo_reserva  = $_POST['tipo_reserva'] ?? "individual";

$fecha_visita       = $_POST['fecha_visita'];
$tipo_documento     = $_POST['tipo_documento'];
$documento          = $_POST['numero_identificacion'];
$fecha_nacimiento   = $_POST['fecha_nacimiento'];
$id_genero          = $_POST['sexo'];
$id_ciudad          = $_POST['id_ciudad'];
$telefono           = $_POST['telefono'];

$institucion        = ($_POST['institucion'] !== "") ? intval($_POST['institucion']) : null;
$observaciones      = $_POST['observaciones'] ?? null;

// Tomar nombre y apellido del usuario logueado
$nombre   = $_SESSION['nombre'] ?? "Visitante";
$apellido = $_SESSION['apellido'] ?? "";

// ----------------------------------------------------------------------
// 1️⃣ INSERTAR RESERVA (SUPABASE)
// ----------------------------------------------------------------------
$nuevaReserva = [
    "id_usuario"            => $id_usuario,
    "id_actividad"          => $id_actividad,
    "id_institucion"        => $institucion ?: null,
    "tipo_reserva"          => strtolower($tipo_reserva),
    "estado"                => "pendiente",
    "numero_participantes"  => 1,
    "fecha_reserva"         => date("c"),  // formato ISO8601
    "fecha_visita"          => $fecha_visita   // AHORA SÍ EXISTE EN LA TABLA
];

// Insertar en Supabase
list($codeReserva, $reservaData) = supabase_insert("reservas", $nuevaReserva);

// DEBUG opcional
// var_dump($codeReserva, $reservaData); exit;

if ($codeReserva !== 201) {
    $_SESSION["mensaje_reserva"] = "❌ Error al insertar la reserva. " . json_encode($reservaData);
    $_SESSION["tipo_reserva"] = "error";
    header("Location: mensajes_reserva.php");
    exit;

}

$id_reserva = $reservaData[0]["id_reserva"];

// ----------------------------------------------------------------------
// 2️⃣ INSERTAR PARTICIPANTE INDIVIDUAL
// ----------------------------------------------------------------------

$participante = [
    "id_reserva"           => $id_reserva,
    "id_usuario"           => $id_usuario,  // Usuario autenticado
    "nombre"               => $nombre,
    "apellido"             => $apellido,
    "documento"            => $documento,
    "telefono"             => $telefono,
    "es_usuario_registrado"=> true,          // ✔ usuario del sistema
    "id_genero"            => $id_genero,
    "id_institucion"       => $institucion,
    "fecha_nacimiento"     => $fecha_nacimiento,
    "id_ciudad"            => $id_ciudad,
    "id_interes"           => null,
    "fecha_visita"         => $fecha_visita, // ✔ aquí sí existe en la tabla
    "observaciones"        => $observaciones
];

// Obtener nombre de la actividad desde Supabase
list($codeAct, $actInfo) = supabase_get("actividades?id_actividad=eq.$id_actividad&select=nombre");

$actividad_nombre = $actInfo[0]["nombre"] ?? "Actividad";

list($codePart, $partData) = supabase_insert("participantes_reserva", $participante);

// ===== ENVIAR CORREO DE CONFIRMACIÓN =====
include_once("../includes/enviarCorreo.php");

$correoUsuario = $_SESSION["correo"] ?? null;
$nombreUsuario = $_SESSION["nombre"] ?? "Usuario";

if ($correoUsuario) {
    enviarCorreoReserva(
        $correoUsuario,
        $nombreUsuario,
        $id_reserva,
        $fecha_visita,
        $actividad_nombre
    );
}


// DEBUG opcional
// var_dump($codePart, $partData); exit;

if ($codePart !== 201) {
    $_SESSION["mensaje_reserva"] = "❌ Error al registrar el participante. " . json_encode($partData);
    $_SESSION["tipo_reserva"] = "error";
    header("Location: mensajes_reserva.php");
    exit;

}

// ----------------------------------------------------------------------
// 🎉 FINALIZACIÓN
// ----------------------------------------------------------------------
$_SESSION["mensaje_reserva"] = "¡Reserva realizada exitosamente! 🎉";
$_SESSION["tipo_reserva"] = "exito";
header("Location: mensajes_reserva.php");
exit;

?>
