<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');

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


// 🔐 INICIAR TRANSACCIÓN
pg_query($conn, "BEGIN");

try {

    // 1️⃣ INSERTAR RESERVA
    $sql_reserva = "
        INSERT INTO reservas (
            id_usuario, id_actividad, id_institucion, tipo_reserva,
            estado, numero_participantes, fecha_reserva
        ) VALUES (
            $1, $2, $3, $4,
            'pendiente', 1, NOW()
        )
        RETURNING id_reserva
    ";

    $result_reserva = pg_query_params($conn, $sql_reserva, [
        $id_usuario,
        $id_actividad,
        $institucion,
        $tipo_reserva
    ]);

    if (!$result_reserva) {
        throw new Exception("Error al insertar la reserva.");
    }

    $data = pg_fetch_assoc($result_reserva);
    $id_reserva = $data['id_reserva'];


    // 2️⃣ INSERTAR PARTICIPANTE
    $sql_participante = "
        INSERT INTO participantes_reserva (
            id_reserva, id_usuario, nombre, apellido, documento, telefono,
            es_usuario_registrado, fecha_registro,
            id_genero, id_institucion, fecha_nacimiento,
            id_ciudad, fecha_visita, observaciones
        ) VALUES (
            $1, NULL, $2, $3, $4, $5,
            FALSE, NOW(),
            $6, $7, $8,
            $9, $10, $11
        )
    ";

    $result_participante = pg_query_params($conn, $sql_participante, [
        $id_reserva,
        $nombre,
        $apellido,
        $documento,
        $telefono,
        $id_genero,
        $institucion,
        $fecha_nacimiento,
        $id_ciudad,
        $fecha_visita,
        $observaciones
    ]);

    if (!$result_participante) {
        throw new Exception("Error al insertar participante.");
    }


    // ✔ SI TODO VA BIEN → CONFIRMAMOS
    pg_query($conn, "COMMIT");

    echo "<script>
        alert('✅ Reserva realizada exitosamente.');
        window.location = 'mis_reservas.php';
    </script>";
    exit;

} catch (Exception $e) {

    // ❌ SI OCURRE ALGO → ROLLBACK
    pg_query($conn, "ROLLBACK");

    echo "<script>
        alert('❌ Ocurrió un error al guardar la reserva: " . $e->getMessage() . "');
        window.location = 'actividades.php';
    </script>";
    exit;
}

?>
