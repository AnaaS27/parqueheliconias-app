<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');

// Verificar parámetros necesarios
if (!isset($_GET['id_reserva']) || !isset($_GET['cant']) || !isset($_GET['fecha'])) {
    echo "<script>alert('Datos incompletos.'); window.location='actividades.php';</script>";
    exit;
}

$id_reserva = intval($_GET['id_reserva']);
$cant = intval($_GET['cant']);
$fecha_visita = $_GET['fecha'];

// Aseguramos mínimo 2
if ($cant < 2) $cant = 2;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Participantes</title>
</head>
<body>

<h2>Registro de Participantes del Grupo</h2>
<p>Reserva #<?= $id_reserva ?></p>
<p>Participantes requeridos: <strong><?= $cant ?></strong></p>

<form action="guardar_participantes.php" method="POST">

    <input type="hidden" name="id_reserva" value="<?= $id_reserva ?>">
    <input type="hidden" name="fecha_visita" value="<?= $fecha_visita ?>">
    <input type="hidden" name="cantidad" value="<?= $cant ?>">

    <?php for ($i = 1; $i <= $cant; $i++): ?>
        <fieldset style="margin-bottom: 15px; padding: 10px; border: 1px solid #999;">
            <legend><strong>Participante <?= $i ?></strong></legend>

            <label>Nombre:</label>
            <input type="text" name="nombre[]" required><br><br>

            <label>Apellido:</label>
            <input type="text" name="apellido[]" required><br><br>

            <label>Documento:</label>
            <input type="text" name="documento[]" required><br><br>

            <label>Teléfono:</label>
            <input type="text" name="telefono[]" required><br><br>

            <label>Fecha de nacimiento:</label>
            <input type="date" name="fecha_nacimiento[]" required><br><br>

            <label>Género:</label>
            <select name="id_genero[]" required>
                <option value="">Seleccione</option>
                <option value="1">Femenino</option>
                <option value="2">Masculino</option>
                <option value="3">Otro</option>
            </select><br><br>

            <label>Ciudad:</label>
            <input type="text" name="id_ciudad[]" placeholder="ID ciudad o nombre" required><br><br>

            <label>Interés:</label>
            <input type="text" name="id_interes[]" placeholder="ID o nombre de interés" required><br><br>

        </fieldset>
    <?php endfor; ?>

    <button type="submit">Guardar Participantes</button>

</form>

</body>
</html>
