<?php
session_start();
require '../includes/verificar_sesion.php';
require '../includes/conexion.php';

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
$usuario_id      = $_SESSION['usuario_id'];
$actividad_id    = $_POST['actividad_id'] ?? null;
$fecha_reserva   = $_POST['fecha'] ?? null;
$tipo_reserva    = $_POST['tipo_reserva'] ?? 'individual';
$numero_particip = $_POST['cantidad'] ?? 1;
$id_institucion  = $_POST['id_institucion'] ?? null;

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
// 📝 INSERTAR RESERVA
// -----------------------------------------------------------
$sql = "
INSERT INTO reservas 
(id_usuario, id_actividad, id_institucion, fecha_reserva, tipo_reserva, estado, numero_participantes)
VALUES ($1, $2, $3, $4, $5, 'pendiente', $6)
RETURNING id_reserva
";

$result = pg_query_params($conn, $sql, [
    $usuario_id,
    $actividad_id,
    $id_institucion,
    $fecha_reserva,
    $tipo_reserva,
    $numero_particip
]);

if (!$result) {
    die("❌ Error al registrar reserva: " . pg_last_error($conn));
}

$reserva = pg_fetch_assoc($result);
$reserva_id = $reserva['id_reserva'];

// -----------------------------------------------------------
// 👤 SI ES RESERVA INDIVIDUAL → INSERTAR PARTICIPANTE
// -----------------------------------------------------------
if ($tipo_reserva === 'individual') {

    $sql = "
    INSERT INTO participantes_reserva
    (id_reserva, id_usuario, nombre, apellido, documento, telefono, es_usuario_registrado,
     id_genero, id_institucion, fecha_nacimiento, id_ciudad, id_interes, fecha_visita)
    VALUES ($1,$2,$3,$4,$5,$6,true,$7,$8,$9,$10,$11,$12)
    ";

    $params = [
        $reserva_id,
        $usuario_id,
        $_SESSION['nombre'] ?? 'N/A',
        $_SESSION['apellido'] ?? null,
        $_SESSION['documento'] ?? null,
        $_SESSION['telefono'] ?? null,
        $_SESSION['id_genero'] ?? null,
        $id_institucion,
        $_SESSION['fecha_nacimiento'] ?? null,
        $_SESSION['id_ciudad'] ?? null,
        $_SESSION['id_interes'] ?? null,
        $fecha_reserva
    ];

    if (!pg_query_params($conn, $sql, $params)) {
        die("❌ Error al insertar participante individual: " . pg_last_error($conn));
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

        $sql = "
        INSERT INTO participantes_reserva
        (id_reserva, id_usuario, nombre, apellido, documento, telefono, es_usuario_registrado,
         id_genero, id_institucion, fecha_nacimiento, id_ciudad, id_interes, fecha_visita)
        VALUES ($1,$2,$3,$4,$5,$6,false,$7,$8,$9,$10,$11,$12)
        ";

        $params = [
            $reserva_id,
            $usuario_id,
            $p['nombre'],
            $p['apellido'] ?? null,
            $p['documento'],
            $p['telefono'] ?? null,
            $p['id_genero'] ?? null,
            $id_institucion,
            $p['fecha_nacimiento'] ?? null,
            $p['id_ciudad'] ?? null,
            $p['id_interes'] ?? null,
            $fecha_reserva
        ];

        if (!pg_query_params($conn, $sql, $params)) {
            die("❌ Error al insertar participante grupal: " . pg_last_error($conn));
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
