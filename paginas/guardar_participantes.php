<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

if (!isset($_POST['id_reserva'])) {
    echo "<script>alert('Datos incompletos'); window.location='mis_reservas.php';</script>";
    exit;
}

$id_reserva = intval($_POST['id_reserva']);
$cantidad = intval($_POST['cantidad']);
$fecha_visita = $_POST['fecha_visita'];

$nombres = $_POST['nombre'];
$apellidos = $_POST['apellido'];
$documentos = $_POST['documento'];
$telefonos = $_POST['telefono'];
$nacimientos = $_POST['fecha_nacimiento'];
$generos = $_POST['id_genero'];
$ciudades = $_POST['id_ciudad'];
$intereses = $_POST['id_interes'];

for ($i = 0; $i < $cantidad; $i++) {

    $data = [
        "id_reserva"            => $id_reserva,
        "nombre"                => $nombres[$i],
        "apellido"              => $apellidos[$i],
        "documento"             => $documentos[$i],
        "telefono"              => $telefonos[$i],
        "id_genero"             => $generos[$i],
        "fecha_nacimiento"      => $nacimientos[$i],
        "id_ciudad"             => $ciudades[$i],
        "id_interes"            => $intereses[$i],
        "fecha_visita"          => $fecha_visita,
        "es_usuario_registrado" => false,
        "id_usuario"            => null
    ];

    list($code, $res) = supabase_insert("participantes_reserva", $data);

    if ($code !== 201) {
        echo "<script>alert('Error guardando participante ".($i+1)."'); window.location='mis_reservas.php';</script>";
        exit;
    }
}

echo "<script>alert('Participantes registrados correctamente'); window.location='mis_reservas.php';</script>";
exit;
?>
