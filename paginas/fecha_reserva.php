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
$tipo = $_POST['tipo_reserva'] ?? 'individual';

if (!$actividad_id) {
    echo "<script>
        alert('⚠️ Actividad no seleccionada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

// -----------------------------------------------------
// 🔎 Obtener actividad desde Supabase
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
// 🔎 Traer instituciones desde Supabase
// -----------------------------------------------------
[$codeInst, $instData] = supabase_get("instituciones?select=id_institucion,nombre_institucion&order=nombre_institucion.asc");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fecha de Reserva</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white w-full max-w-lg shadow-xl rounded-xl p-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
        Reservar: <?= htmlspecialchars($actividad['nombre']) ?>
    </h2>

    <form action="procesar_reserva.php" method="POST" class="space-y-5">

        <!-- Hidden fields -->
        <input type="hidden" name="actividad_id" value="<?= $actividad_id ?>">

        <?php if ($tipo === 'grupal'): ?>
            <input type="hidden" name="tipo_reserva" value="grupal">
        <?php else: ?>
            <input type="hidden" name="tipo_reserva" value="individual">
        <?php endif; ?>

        <!-- Fecha de visita -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">📅 Fecha de visita</label>
            <input 
                type="date" 
                name="fecha" 
                required 
                min="<?= date("Y-m-d") ?>"
                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
        </div>

        <!-- Institución (si la usas en reservas, si no puedes quitarla) -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">🏫 Institución</label>
            <select 
                name="id_institucion"
                class="w-full px-3 py-2 border rounded-md bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
                <option value="">Seleccione</option>

                <?php if ($codeInst === 200 && !empty($instData)): ?>
                    <?php foreach ($instData as $inst): ?>
                        <option value="<?= $inst['id_institucion'] ?>">
                            <?= htmlspecialchars($inst['nombre_institucion']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Campos solo grupales -->
        <?php if ($tipo === 'grupal'): ?>

            <div>
                <label class="block mb-1 font-semibold text-gray-700">👥 Nombre del grupo</label>
                <input type="text" name="nombre_grupo" required
                    class="w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block mb-1 font-semibold text-gray-700">👤 Número de participantes</label>
                <input type="number" name="cantidad" min="2" required
                    class="w-full px-3 py-2 border rounded-md focus:ring-blue-500 focus:outline-none">
            </div>

        <?php else: ?>

            <!-- Individual: Cantidad = 1 -->
            <input type="hidden" name="cantidad" value="1">

        <?php endif; ?>

        <button 
            type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition"
        >
            Continuar →
        </button>

    </form>

</div>

</body>
</html>
