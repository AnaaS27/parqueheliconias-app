<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/header.php'); // Aquí ya tienes las funciones supabase_get()

// 🛑 Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>
        alert('⚠️ Debes iniciar sesión para realizar una reserva.');
        window.location = '../login.php';
    </script>";
    exit;
}

// 🛑 Verificar actividad seleccionada
if (!isset($_POST['id_actividad'])) {
    echo "<script>
        alert('⚠️ Actividad no seleccionada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$id_actividad = intval($_POST['id_actividad']);

// ===================================================
// 🔹 1. Obtener datos de la actividad desde Supabase
// ===================================================
$endpoint = "actividades?id_actividad=eq.$id_actividad&select=*";

[$code_actividad, $actividadData] = supabase_get($endpoint);

if ($code_actividad !== 200 || empty($actividadData)) {
    echo "<script>
        alert('⚠️ Actividad no encontrada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$actividad = $actividadData[0];

// ===================================================
// 🔹 2. Cargar instituciones
// ===================================================
[$code_inst, $instituciones] = supabase_get("instituciones?select=id_institucion,nombre_institucion&order=nombre_institucion");

// ===================================================
// 🔹 3. Cargar géneros
// ===================================================
[$code_gen, $generos] = supabase_get("genero?select=id_genero,genero&order=genero");

// ===================================================
// 🔹 4. Cargar países
// ===================================================
[$code_pais, $paises] = supabase_get("pais?select=id,pais&order=pais");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Individual</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<main class="py-10">

<section class="max-w-3xl mx-auto bg-white shadow-xl rounded-xl p-8 border-t-4 border-green-700">

    <h2 class="text-3xl font-bold text-green-700 text-center mb-2 flex items-center justify-center gap-2">
        🌿 Reserva Individual
    </h2>
    <p class="text-center text-gray-600 mb-6">
        Completa tus datos y selecciona la fecha de tu visita para la actividad:
    </p>

    <h3 class="text-2xl font-semibold text-green-800 text-center">
        <?= htmlspecialchars($actividad['nombre']) ?>
    </h3>
    <p class="text-center text-gray-600 mb-8">
        <?= htmlspecialchars($actividad['descripcion']) ?>
    </p>

    <!-- FORMULARIO PRINCIPAL -->
    <form action="reservas.php" method="POST" id="formReserva" class="space-y-6">

        <input type="hidden" name="id_actividad" value="<?= $actividad['id_actividad'] ?>">
        <input type="hidden" name="tipo_reserva" value="individual">

        <!-- FECHA DE VISITA -->
        <div>
            <h3 class="text-xl font-semibold text-green-700 mb-2 flex items-center gap-2">📅 Fecha de visita</h3>

            <input 
                type="date" 
                id="fecha_visita" 
                name="fecha_visita" 
                required 
                min="<?= date('Y-m-d'); ?>"
                class="w-full border border-green-400 rounded-lg p-3 focus:ring focus:ring-green-300 focus:outline-none">
        </div>

        <!-- DATOS DEL VISITANTE -->
        <div>
            <h3 class="text-xl font-semibold text-green-700 mb-2 flex items-center gap-2">
                🧾 Datos del visitante
            </h3>

            <!-- Documento y número -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="font-medium text-gray-700">Tipo de documento:</label>
                    <select name="tipo_documento" required
                            class="w-full border border-green-400 rounded-lg p-3 mt-1">
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="TI">Tarjeta de Identidad</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="PEP">PEP</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="font-medium text-gray-700">Número de identificación:</label>
                    <input type="text" name="numero_identificacion" required
                           class="w-full border border-green-400 rounded-lg p-3 mt-1 solo-numeros">
                </div>
            </div>

            <!-- Fecha nacimiento y género -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="font-medium text-gray-700">Fecha de nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" required
                           class="w-full border border-green-400 rounded-lg p-3 mt-1">
                </div>

                <div>
                    <label class="font-medium text-gray-700">Género:</label>
                    <select name="sexo" required
                            class="w-full border border-green-400 rounded-lg p-3 mt-1">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($generos as $g): ?>
                            <option value="<?= $g['id_genero'] ?>"><?= $g['genero'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- País y ciudad -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="font-medium text-gray-700">País:</label>
                    <select id="paisSelect" name="pais"
                            class="w-full border border-green-400 rounded-lg p-3 mt-1">
                        <option value="">Seleccionar país...</option>
                        <?php foreach ($paises as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['pais']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="font-medium text-gray-700">Ciudad:</label>
                    <select id="ciudadSelect" name="id_ciudad"
                        class="w-full border border-green-400 rounded-lg p-3 mt-1">
                        <option value="">Seleccionar país primero...</option>
                    </select>
                </div>
            </div>

            <!-- Teléfono -->
            <div class="mt-4">
                <label class="font-medium text-gray-700">Teléfono:</label>
                <input type="text" name="telefono" required
                       class="w-full border border-green-400 rounded-lg p-3 mt-1 solo-numeros">
            </div>

        </div>

        <!-- INSTITUCIÓN -->
        <div>
            <label class="font-medium text-gray-700">Institución / Organización (opcional):</label>
            <select name="institucion" 
                    class="w-full border border-green-400 rounded-lg p-3 mt-1">
                <option value="">Seleccionar...</option>
                <?php foreach ($instituciones as $i): ?>
                    <option value="<?= $i['id_institucion'] ?>">
                        <?= $i['nombre_institucion'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- OBSERVACIONES -->
        <div>
            <label class="font-medium text-gray-700">Observaciones (opcional):</label>
            <textarea name="observaciones" rows="3"
                      class="w-full border border-green-400 rounded-lg p-3 mt-1"></textarea>
        </div>

        <!-- BOTONES -->
        <div class="flex justify-between pt-6">
            <a href="actividades.php" 
               class="px-5 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                ← Volver
            </a>

            <button type="submit"
                class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800 shadow-md">
                Confirmar Reserva ✔
            </button>
        </div>

    </form>

</section>

</main>

<?php include('../includes/footer.php'); ?>

<!-- JS: Solo números -->
<script>
document.querySelectorAll(".solo-numeros").forEach(input => {
    input.addEventListener("input", e => {
        e.target.value = e.target.value.replace(/[^0-9]/g, "");
    });
});
</script>

<!-- AJAX: País → Ciudad -->
<script>
document.getElementById("paisSelect").addEventListener("change", function () {
    const pais = this.value;

    fetch("ajax_ciudades.php?pais=" + pais)
        .then(res => res.json())
        .then(data => {
            const ciudadSelect = document.getElementById("ciudadSelect");

            ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad...</option>';

            data.forEach(ciudad => {
                ciudadSelect.innerHTML += 
                    `<option value="${ciudad.id}">${ciudad.nombre}</option>`;
            });
        });
});
</script>

</body>
</html>
