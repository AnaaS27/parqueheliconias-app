<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

$actividad_id = $_POST['id_actividad'] ?? null;
$tipo = $_POST['tipo_reserva'] ?? 'individual';

if (!$actividad_id) {
    echo "<script>alert('⚠️ Actividad no seleccionada.'); window.location='actividades.php';</script>";
    exit;
}

// Obtener actividad
list($codeAct, $actividadData) =
    supabase_get("actividades?id_actividad=eq.$actividad_id&select=*");

if ($codeAct !== 200 || empty($actividadData)) {
    echo "<script>alert('❌ Actividad no encontrada'); window.location='actividades.php';</script>";
    exit;
}

$actividad = $actividadData[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Fecha de Reserva</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-lg mx-auto bg-white p-6 shadow rounded-lg">

<h2 class="text-xl font-bold mb-4 text-center">
    Reservar: <?= htmlspecialchars($actividad['nombre']) ?>
</h2>

<form action="procesar_reserva.php" method="POST">

    <input type="hidden" name="actividad_id" value="<?= $actividad_id ?>">

    <!-- Fecha -->
    <label>Fecha de visita</label>
    <input type="date" name="fecha" required class="border w-full p-2 mb-4"
           min="<?= date('Y-m-d') ?>">

    <!-- Individual o grupal -->
    <?php if ($tipo === 'grupal'): ?>
        <label>Número de participantes</label>
        <input type="number" name="cantidad" min="2" required class="border w-full p-2 mb-4">
    <?php else: ?>
        <input type="hidden" name="cantidad" value="1">
    <?php endif; ?>

    <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">
        Continuar →
    </button>

</form>

</div>
</body>
</html>
