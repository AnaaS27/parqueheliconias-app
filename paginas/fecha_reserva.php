<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('⚠️ Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// -----------------------------------------------------
// 🔒 Validar actividad enviada
// -----------------------------------------------------
if (!isset($_POST['id_actividad'])) {
    echo "<script>
        alert('⚠️ Actividad no seleccionada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$id_actividad = intval($_POST['id_actividad']);

// ===============================================
// 🔹 1. Obtener datos de la actividad desde Supabase
// ===============================================
[$codeAct, $actividadData] = supabase_get("actividades?id_actividad=eq.$id_actividad&select=*");

if ($codeAct !== 200 || empty($actividadData)) {
    echo "<script>
        alert('⚠️ Actividad no encontrada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$actividad = $actividadData[0];

// Cargar institución
$instituciones = $conn->query("SELECT id_institucion, nombre FROM instituciones ORDER BY nombre_institucion ASC");

// Tipo de reserva
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'individual';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fecha de Reserva</title>
</head>
<body>

<h2>Reservar actividad</h2>

<form action="procesar_reserva.php" method="POST">

    <input type="hidden" name="actividad_id" value="<?= $actividad_id ?>">
    <input type="hidden" name="tipo_reserva" value="<?= $tipo ?>">

    <!-- Fecha -->
    <label>Fecha de visita:</label>
    <input type="date" name="fecha_visita" required><br><br>

    <!-- Institución -->
    <label>Institución:</label>
    <select name="id_institucion" required>
        <option value="">Seleccione</option>
        <?php while ($row = $instituciones->fetch_assoc()): ?>
            <option value="<?= $row['id_institucion'] ?>"><?= $row['nombre'] ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <?php if ($tipo == 'grupal'): ?>

        <!-- Nombre del grupo -->
        <label>Nombre del grupo:</label>
        <input type="text" name="nombre_grupo" required><br><br>

        <!-- Número de participantes -->
        <label>Número de participantes:</label>
        <input type="number" name="numero_participantes" min="2" required><br><br>

    <?php endif; ?>

    <button type="submit">Continuar</button>

</form>

</body>
</html>