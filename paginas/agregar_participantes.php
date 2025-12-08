<?php
session_start();
include('../includes/verificar_sesion.php');

$id_reserva = $_GET["id_reserva"] ?? null;
$cant = $_GET["cant"] ?? null;
$fecha_visita = $_GET["fecha"] ?? null;

if (!$id_reserva || !$cant || !$fecha_visita) {
    echo "<script>alert('Datos incompletos'); window.location='mis_reservas.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Agregar Participantes</title>
</head>

<body>

<h2>Registro de Participantes</h2>
<p>Reserva #<?= $id_reserva ?></p>

<form action="guardar_participantes.php" method="POST">

<input type="hidden" name="id_reserva" value="<?= $id_reserva ?>">
<input type="hidden" name="cantidad" value="<?= $cant ?>">
<input type="hidden" name="fecha_visita" value="<?= $fecha_visita ?>">

<?php for ($i = 0; $i < $cant; $i++): ?>
    <fieldset>
        <legend><strong>Participante <?= $i + 1 ?></strong></legend>

        <label>Nombre:</label>
        <input type="text" name="nombre[]" required><br>

        <label>Apellido:</label>
        <input type="text" name="apellido[]" required><br>

        <label>Documento:</label>
        <input type="text" name="documento[]" required><br>

        <label>Teléfono:</label>
        <input type="text" name="telefono[]" required><br>

        <label>Fecha nacimiento:</label>
        <input type="date" name="fecha_nacimiento[]" required><br>

        <label>Género:</label>
        <select name="id_genero[]" required>
            <option value="">Seleccione</option>
            <option value="1">Femenino</option>
            <option value="2">Masculino</option>
            <option value="3">Otro</option>
        </select><br>

        <label>Ciudad (ID):</label>
        <input type="text" name="id_ciudad[]" required><br>

        <label>Interés (ID):</label>
        <input type="text" name="id_interes[]" required><br>
    </fieldset>
<?php endfor; ?>

<button type="submit">Guardar Participantes</button>

</form>
</body>
</html>
