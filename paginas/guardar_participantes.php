<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

if (!isset($_POST['id_reserva'])) {
    echo "<script>alert('Error: datos incompletos'); window.location='actividades.php';</script>";
    exit;
}

$id_reserva = intval($_POST['id_reserva']);
$cantidad = intval($_POST['cantidad']);
$fecha_visita = $_POST['fecha_visita'];

// Arrays enviados
$nombres = $_POST['nombre'];
$apellidos = $_POST['apellido'];
$documentos = $_POST['documento'];
$telefonos = $_POST['telefono'];
$nacimientos = $_POST['fecha_nacimiento'];
$generos = $_POST['id_genero'];
$ciudades = $_POST['id_ciudad'];
$intereses = $_POST['id_interes'];

// =============================
// 👥 Insertar participantes en Supabase
// =============================
for ($i = 0; $i < $cantidad; $i++) {

    $participante = [
        "id_reserva"             => $id_reserva,
        "id_usuario"             => null,
        "nombre"                 => $nombres[$i],
        "apellido"               => $apellidos[$i],
        "documento"              => $documentos[$i],
        "telefono"               => $telefonos[$i],
        "es_usuario_registrado"  => false,
        "id_genero"              => $generos[$i],
        "id_institucion"         => null,
        "fecha_nacimiento"       => $nacimientos[$i],
        "id_ciudad"              => $ciudades[$i],
        "id_interes"             => $intereses[$i],
        "fecha_visita"           => $fecha_visita
    ];

    $res = supabaseFetch("participantes_reserva", "POST", $participante);

    if ($res["code"] !== 201) {
        echo "<script>alert('❌ Error al guardar participante ".($i+1)."'); window.location='mis_reservas.php';</script>";
        exit;
    }
}

// =============================
// 🎉 Finalizar
// =============================
echo "<script>
    alert('Participantes registrados correctamente.');
    window.location='mis_reservas.php';
</script>";
exit;

?>

