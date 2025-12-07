<?php
session_start();
require '../includes/verificar_sesion.php';
require '../includes/supabase.php'; // ← ahora usamos Supabase API REST

// -----------------------------------------------------------
// 🔒 Validar sesión
// -----------------------------------------------------------
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// -----------------------------------------------------------
// 📥 Validar POST
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>
        alert('Solicitud inválida.');
        window.location = 'fecha_reserva.php';
    </script>";
    exit;
}

// -----------------------------------------------------------
// 🧩 Datos recibidos del formulario
// -----------------------------------------------------------
$usuario_id      = $_SESSION['usuario_id'];
$actividad_id    = $_POST['actividad_id'] ?? null;
$fecha_reserva   = $_POST['fecha'] ?? null;
$tipo_reserva    = $_POST['tipo_reserva'] ?? 'individual';
$numero_particip = $_POST['cantidad'] ?? 1;
$id_institucion  = $_POST['id_institucion'] ?? null;

// -----------------------------------------------------------
// 🛑 Validación
// -----------------------------------------------------------
if (!$actividad_id || !$fecha_reserva) {
    echo "<script>
        alert('Todos los campos son obligatorios.');
        window.location = 'fecha_reserva.php';
    </script>";
    exit;
}

/* =============================================================
   1️⃣ CREAR RESERVA EN SUPABASE
   ============================================================= */
$reservaData = [
    "id_usuario"            => $usuario_id,
    "id_actividad"          => $actividad_id,
    "id_institucion"        => $id_institucion,
    "fecha_reserva"         => $fecha_reserva,
    "tipo_reserva"          => $tipo_reserva,
    "estado"                => "pendiente",
    "numero_participantes"  => $numero_particip
];

[$codeR, $dataR] = supabase_insert("reservas", $reservaData);

if ($codeR !== 201 || empty($dataR)) {
    echo "<script>alert('❌ Error al registrar la reserva.'); window.location='fecha_reserva.php';</script>";
    exit;
}

$reserva_id = $dataR[0]["id_reserva"];

/* =============================================================
   2️⃣ INSERTAR PARTICIPANTE (INDIVIDUAL)
   ============================================================= */
if ($tipo_reserva === 'individual') {

    $participanteData = [
        "id_reserva"            => $reserva_id,
        "id_usuario"            => $usuario_id,
        "nombre"                => $_SESSION['nombre'] ?? 'N/A',
        "apellido"              => $_SESSION['apellido'] ?? null,
        "documento"             => $_SESSION['documento'] ?? null,
        "telefono"              => $_SESSION['telefono'] ?? null,
        "es_usuario_registrado" => true,
        "id_genero"             => $_SESSION['id_genero'] ?? null,
        "id_institucion"        => $id_institucion,
        "fecha_nacimiento"      => $_SESSION['fecha_nacimiento'] ?? null,
        "id_ciudad"             => $_SESSION['id_ciudad'] ?? null,
        "id_interes"            => $_SESSION['id_interes'] ?? null,
        "fecha_visita"          => $fecha_reserva
    ];

    [$codeP, $dataP] = supabase_insert("participantes_reserva", $participanteData);

    if ($codeP !== 201) {
        echo "<script>alert('❌ Error al registrar participante.'); window.location='fecha_reserva.php';</script>";
        exit;
    }
}

/* =============================================================
   3️⃣ INSERTAR PARTICIPANTES (GRUPAL)
   ============================================================= */
if ($tipo_reserva === 'grupal' && isset($_POST['integrantes'])) {

    foreach ($_POST['integrantes'] as $p) {

        if (empty($p['nombre']) || empty($p['documento'])) {
            continue;
        }

        $participanteData = [
            "id_reserva"            => $reserva_id,
            "id_usuario"            => $usuario_id,
            "nombre"                => $p['nombre'],
            "apellido"              => $p['apellido'] ?? null,
            "documento"             => $p['documento'],
            "telefono"              => $p['telefono'] ?? null,
            "es_usuario_registrado" => false,
            "id_genero"             => $p['id_genero'] ?? null,
            "id_institucion"        => $id_institucion,
            "fecha_nacimiento"      => $p['fecha_nacimiento'] ?? null,
            "id_ciudad"             => $p['id_ciudad'] ?? null,
            "id_interes"            => $p['id_interes'] ?? null,
            "fecha_visita"          => $fecha_reserva
        ];

        [$codeP, $dataP] = supabase_insert("participantes_reserva", $participanteData);

        if ($codeP !== 201) {
            echo "<script>alert('❌ Error al registrar participante grupal.'); window.location='fecha_reserva.php';</script>";
            exit;
        }
    }
}

/* =============================================================
   🎉 TODO OK
   ============================================================= */
echo "<script>
    alert('¡Reserva registrada correctamente!');
    window.location = 'mis_reservas.php';
</script>";
exit;

?>
