<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

// -----------------------------------------------------
// 🔒 Verificar sesión
// -----------------------------------------------------
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('⚠️ Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// -----------------------------------------------------
// 🔒 Recibir datos enviados por detalle_actividad.php
// -----------------------------------------------------
$actividad_id = $_POST['id_actividad'] ?? null;
$tipo = $_POST['tipo_reserva'] ?? 'individual';   // ✔ nombre correcto

if (!$actividad_id) {
    echo "<script>
        alert('⚠️ Actividad no seleccionada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

// -----------------------------------------------------
// 🔎 1. Obtener actividad desde Supabase
// -----------------------------------------------------
[$codeAct, $actividadData] = supabase_get("actividades?id_actividad=eq.$actividad_id&select=*");

if ($codeAct !== 200 || empty($actividadData)) {
    echo "<script>
        alert('⚠️ Actividad no encontrada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$actividad = $actividadData[0];

// -----------------------------------------------------
// 🔎 2. Traer instituciones desde Supabase
// -----------------------------------------------------
[$codeInst, $instData] = supabase_get("instituciones?select=id_institucion,nombre_institucion&order=nombre_institucion.asc");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fecha de Reserva</title>
</head>
<body>

<h2>Reservar: <?= htmlspecialchars($actividad['nombre']) ?></h2>

<form action="procesar_reserva.php" method="POST">

    <!-- Mandar datos -->
    <input type="hidden" name="actividad_id" value="<?= $actividad_id ?>">
    <input type="hidden" name="tipo_reserva" value="<?= $tipo ?>">

    <!-- Fecha -->
    <label>📅 Fecha de visita:</label>
    <input type="date" name="fecha_visita" required min="<?= date("Y-m-d") ?>">
    <br><br>

    <!-- Institución -->
    <label>🏫 Institución:</label>
    <select name="id_institucion" required>
        <option value="">Seleccione</option>

        <?php if ($codeInst === 200 && !empty($instData)): ?>
            <?php foreach ($instData as $inst): ?>
                <option value="<?= $inst['id_institucion'] ?>">
                    <?= htmlspecialchars($inst['nombre_institucion']) ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option disabled>No hay instituciones registradas</option>
        <?php endif; ?>

    </select>
    <br><br>

    <?php if ($tipo === 'grupal'): ?>

        <!-- Nombre del grupo -->
        <label>👥 Nombre del grupo:</label>
        <input type="text" name="nombre_grupo" required><br><br>

        <!-- Número de participantes -->
        <label>👤 Número de participantes:</label>
        <input type="number" name="numero_participantes" min="2" required><br><br>

    <?php endif; ?>

    <button type="submit">Continuar →</button>

</form>

</body>
</html>
