<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');

if (!isset($_POST['id_reserva'])) {
    echo "<script>alert('Error: datos incompletos'); window.location='actividades.php';</script>";
    exit;
}

$id_reserva = intval($_POST['id_reserva']);
$cantidad = intval($_POST['cantidad']);
$fecha_visita = $_POST['fecha_visita'];

// Arrays
$nombres = $_POST['nombre'];
$apellidos = $_POST['apellido'];
$documentos = $_POST['documento'];
$telefonos = $_POST['telefono'];
$nacimientos = $_POST['fecha_nacimiento'];
$generos = $_POST['id_genero'];
$ciudades = $_POST['id_ciudad'];
$intereses = $_POST['id_interes'];

$sql = "INSERT INTO participantes_reserva (
    id_reserva,
    id_usuario,
    nombre,
    apellido,
    documento,
    telefono,
    es_usuario_registrado,
    id_genero,
    id_institucion,
    fecha_nacimiento,
    id_ciudad,
    id_interes,
    fecha_visita
) VALUES (?, NULL, ?, ?, ?, ?, 0, ?, NULL, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

for ($i = 0; $i < $cantidad; $i++) {
    $stmt->bind_param(
        "issssissss",
        $id_reserva,
        $nombres[$i],
        $apellidos[$i],
        $documentos[$i],
        $telefonos[$i],
        $generos[$i],
        $nacimientos[$i],
        $ciudades[$i],
        $intereses[$i],
        $fecha_visita
    );
    $stmt->execute();
}

echo "<script>
    alert('Participantes registrados correctamente.');
    window.location='mis_reservas.php';
</script>";
exit;

?>
