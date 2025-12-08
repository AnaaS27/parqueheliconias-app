<?php
session_start();
require '../includes/verificar_sesion.php';
require '../includes/supabase.php';

// -----------------------------------------------------------
// 🔒 Validación de sesión
// -----------------------------------------------------------
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// -----------------------------------------------------------
// 📥 Validar que llegaron datos por POST
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>
        alert('Solicitud inválida.');
        window.location = 'fecha_reserva.php';
    </script>";
    exit;
}

// -----------------------------------------------------------
// 🧩 Recibir datos del formulario
// -----------------------------------------------------------
$usuario_id        = $_SESSION['usuario_id'];
$actividad_id      = $_POST['id_actividad'] ?? null;
$fecha_reserva     = $_POST['fecha'] ?? null;
$tipo_reserva      = $_POST['tipo_reserva'] ?? 'individual';
$numero_particip   = $_POST['cantidad'] ?? 1;
$id_institucion    = $_POST['id_institucion'] ?? null;

// -----------------------------------------------------------
// 🛑 Validación básica
// -----------------------------------------------------------
if (!$actividad_id || !$fecha_reserva) {
    echo "<script>
        alert('Todos los campos son obligatorios.');
        window.location = 'fecha_reserva.php';
    </script>";
    exit;
}

// -----------------------------------------------------------
// 📝 INSERTAR RESERVA EN SUPABASE
// -----------------------------------------------------------

$nuevaReserva = [
    "id_usuario"            => $usuario_id,
    "id_actividad"          => intval($actividad_id),
    "id_institucion"        => !empty($id_institucion) ? intval($id_institucion) : null,
    "fecha_reserva"         => $fecha_reserva,
    "tipo_reserva"          => $tipo_reserva,
    "estado"                => "pendiente",
    "numero_participantes"  => intval($numero_particip)
];

list($codeRes, $resultRes) = supabase_insert("reservas", $nuevaReserva);

if ($codeRes !== 201) {
    echo "<script>alert('❌ Error al registrar reserva.'); window.history.back();</script>";
    exit;
}

$reserva_id = $resultRes[0]["id_reserva"];

// -----------------------------------------------------------
// 👤 SI ES RESERVA INDIVIDUAL → INSERTAR PARTICIPANTE
// -----------------------------------------------------------
if ($tipo_reserva === 'individual') {

    $participante = [
        "id_reserva"           => $reserva_id,
        "id_usuario"           => $usuario_id,
        "nombre"               => $_SESSION['nombre']      ?? "N/A",
        "apellido"             => $_SESSION['apellido']    ?? null,
        "documento"            => $_SESSION['documento']   ?? null,
        "telefono"             => $_SESSION['telefono']    ?? null,
        "es_usuario_registrado"=> true,
        "id_genero"            => $_SESSION['id_genero']   ?? null,
        "id_institucion"       => $id_institucion,
        "fecha_nacimiento"     => $_SESSION['fecha_nacimiento'] ?? null,
        "id_ciudad"            => $_SESSION['id_ciudad']   ?? null,
        "id_interes"           => $_SESSION['id_interes']  ?? null,
        "fecha_visita"         => $fecha_reserva,
        "observaciones"        => $_POST['observaciones'] ?? null
    ];

    list($codePart, $resPart) = supabase_insert("participantes_reserva", $participante);

    if ($codePart !== 201) {
        echo "<script>alert('❌ Error al registrar participante.'); window.history.back();</script>";
        exit;
    }
}

// -----------------------------------------------------------
// 👥 SI ES RESERVA GRUPAL → INSERTAR PARTICIPANTES
// -----------------------------------------------------------
if ($tipo_reserva === 'grupal' && isset($_POST['integrantes'])) {

    foreach ($_POST['integrantes'] as $p) {

        if (empty($p['nombre']) || empty($p['documento'])) {
            continue;
        }

        $participante = [
            "id_reserva"           => $reserva_id,
            "id_usuario"           => null,
            "nombre"               => $p['nombre'],
            "apellido"             => $p['apellido'] ?? null,
            "documento"            => $p['documento'],
            "telefono"             => $p['telefono'] ?? null,
            "es_usuario_registrado"=> false,
            "id_genero"            => $p['id_genero'] ?? null,
            "id_institucion"       => $id_institucion,
            "fecha_nacimiento"     => $p['fecha_nacimiento'] ?? null,
            "id_ciudad"            => $p['id_ciudad'] ?? null,
            "id_interes"           => $p['id_interes'] ?? null,
            "fecha_visita"         => $fecha_reserva,
            "observaciones"        => $p['observaciones'] ?? null
        ];

        list($codeGroupPart, $resGroupPart) = supabase_insert("participantes_reserva", $participante);

        if ($codeGroupPart !== 201) {
            echo "<script>alert('❌ Error al guardar participante grupal.'); window.history.back();</script>";
            exit;
        }
    }
}

// -----------------------------------------------------------
// 🎉 TODO OK
// -----------------------------------------------------------
echo "<script>
    alert('¡Reserva registrada correctamente!');
    window.location = 'mis_reservas.php';
</script>";
exit;
?>
