<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');
include_once('../includes/enviarCorreo.php');

// ===============================
//  🔐 VALIDAR SESIÓN
// ===============================
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('⚠️ Debes iniciar sesión.'); window.location='../login.php';</script>";
    exit;
}

$id_usuario = intval($_SESSION['usuario_id']);

// ===============================
//  🔎 OBTENER DATOS DEL USUARIO
// ===============================
list($codeUser, $userData) = supabase_get("usuarios?id_usuario=eq.$id_usuario&select=*");

if ($codeUser !== 200 || empty($userData)) {
    echo "<script>alert('❌ No se encontró el usuario.'); window.location='actividades.php';</script>";
    exit;
}

$user = $userData[0];

$nombre_usuario  = $user["nombre"];
$apellido_usuario= $user["apellido"];
$doc_usuario     = $user["documento"];
$correo_usuario  = $user["correo"];

// ===============================
//  VALIDACIONES BÁSICAS
// ===============================
if (!isset($_GET['id_actividad']) || !isset($_GET['cantidad'])) {
    echo "<script>alert('❌ Faltan datos para la reserva.'); window.location='actividades.php';</script>";
    exit;
}

$id_actividad = intval($_GET['id_actividad']);
$cantidad     = intval($_GET['cantidad']);

if ($cantidad < 2) {
    echo "<script>alert('⚠️ Una reserva grupal requiere mínimo 2 participantes.'); window.location='actividades.php';</script>";
    exit;
}

// ===============================
//  FUNCIONES AUXILIARES
// ===============================
function calcularEdad($fecha) {
    $hoy = new DateTime();
    $nac = new DateTime($fecha);
    return $hoy->diff($nac)->y;
}

function log_error_email($texto) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents("$logDir/email_errors.log", "[".date("Y-m-d H:i:s")."] $texto\n", FILE_APPEND);
}

// ===============================
//  🚨 PROCESAR FORMULARIO
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fecha_visita = $_POST['fecha_visita'] ?? null;

    if (!$fecha_visita) {
        echo "<script>alert('⚠️ Debes seleccionar una fecha.'); history.back();</script>";
        exit;
    }

    // ===============================
    // 1️⃣ INSERTAR RESERVA GRUPAL
    // ===============================
    $nuevaReserva = [
        "id_usuario"           => $id_usuario,
        "id_actividad"         => $id_actividad,
        "fecha_reserva"        => date("Y-m-d H:i:s"),
        "fecha_visita"         => $fecha_visita,
        "estado"               => "pendiente",
        "tipo_reserva"         => "grupal",
        "numero_participantes" => $cantidad
    ];

    list($codeRes, $dataRes) = supabase_insert("reservas", $nuevaReserva);

    if ($codeRes !== 201) {
        echo "<script>alert('❌ Error al crear la reserva.'); window.location='actividades.php';</script>";
        exit;
    }

    $id_reserva = $dataRes[0]["id_reserva"];

    // ===============================
    // 2️⃣ REGISTRAR PARTICIPANTE CREADOR
    // ===============================
    $fecha_nac_creador = $_POST['fecha_nacimiento_creador'];
    $edad_creador = calcularEdad($fecha_nac_creador);

    $creador = [
        "id_reserva"        => $id_reserva,
        "nombre"            => $nombre_usuario,
        "apellido"          => $apellido_usuario,
        "documento"         => $doc_usuario,
        "telefono"          => $_POST['telefono_creador'] ?? null,
        "edad"              => $edad_creador,
        "sexo"              => $_POST['sexo_creador'] ?? null,
        "ciudad_origen"     => $_POST['ciudad_creador'] ?? null,
        "observaciones"     => $_POST['observaciones_creador'] ?? null,
        "fecha_nacimiento"  => $fecha_nac_creador,
        "es_usuario_registrado" => true,
        "fecha_visita"      => $fecha_visita
    ];

    list($codeCreador, $resCreador) = supabase_insert("participantes_reserva", $creador);

    if ($codeCreador !== 201) {
        echo "<script>alert('❌ No se pudo registrar al creador del grupo.'); window.location='actividades.php';</script>";
        exit;
    }

    // ===============================
    // 3️⃣ PARTICIPANTES ADICIONALES
    // ===============================
    for ($i = 0; $i < $cantidad - 1; $i++) {

        if (!isset($_POST['nombre'][$i]) || empty($_POST['nombre'][$i]))
            continue;

        $edad = calcularEdad($_POST['fecha_nacimiento'][$i]);

        $p = [
            "id_reserva"        => $id_reserva,
            "nombre"            => $_POST['nombre'][$i],
            "apellido"          => $_POST['apellido'][$i],
            "documento"         => $_POST['documento'][$i],
            "telefono"          => $_POST['telefono'][$i] ?? null,
            "edad"              => $edad,
            "sexo"              => $_POST['sexo'][$i],
            "ciudad_origen"     => $_POST['ciudad_origen'][$i],
            "observaciones"     => $_POST['observaciones'][$i] ?? null,
            "fecha_nacimiento"  => $_POST['fecha_nacimiento'][$i],
            "es_usuario_registrado" => false,
            "fecha_visita"      => $fecha_visita
        ];

        supabase_insert("participantes_reserva", $p);
    }

    // ===============================
    // 4️⃣ NOTIFICACIONES
    // ===============================
    $notif_admin = [
        "id_usuario"   => 1,
        "id_reserva"   => $id_reserva,
        "titulo"       => "Nueva reserva grupal",
        "mensaje"      => "El usuario $nombre_usuario creó la reserva grupal #$id_reserva",
        "tipo"         => "info",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida"        => false
    ];

    supabase_insert("notificaciones", $notif_admin);

    $notif_user = [
        "id_usuario"   => $id_usuario,
        "id_reserva"   => $id_reserva,
        "titulo"       => "Reserva registrada",
        "mensaje"      => "Tu reserva grupal para la fecha $fecha_visita fue creada con éxito.",
        "tipo"         => "exito",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida"        => false
    ];

    supabase_insert("notificaciones", $notif_user);

    // ===============================
    // 🎉 TODO OK
    // ===============================
    echo "<script>alert('🎉 ¡Reserva grupal registrada correctamente!'); window.location='mis_reservas.php';</script>";
    exit;
}

?>
