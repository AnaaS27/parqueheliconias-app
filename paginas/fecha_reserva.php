<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

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

// ================================
// 🔎 OBTENER ACTIVIDAD DESDE SUPABASE
// ================================
list($codeAct, $resultadoAct) =
    supabase_get("actividades?id_actividad=eq.$id_actividad&select=*");

if ($codeAct !== 200 || empty($resultadoAct)) {
    echo "<script>
        alert('⚠️ Actividad no encontrada.');
        window.location = 'actividades.php';
    </script>";
    exit;
}

$actividad = $resultadoAct[0];

// ================================
// 🔎 OBTENER INSTITUCIONES
// ================================
list($codeInst, $instituciones) =
    supabase_get("instituciones?select=id_institucion,nombre_institucion&order=nombre_institucion.asc");

// ================================
// 🔎 OBTENER GÉNEROS
// ================================
list($codeGen, $generos) =
    supabase_get("genero?select=id_genero,genero&order=genero.asc");

// ================================
// 🔎 OBTENER PAÍSES
// ================================
list($codePais, $paises) =
    supabase_get("pais?select=id,pais&order=pais.asc");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Individual</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<?php include('../includes/header.php'); ?>

<main class="py-10">

<section class="max-w-3xl mx-auto bg-white shadow-xl rounded-xl p-8 border-t-4 border-green-700">

    <h2 class="text-3xl font-bold text-green-700 text-center mb-2 flex items-center justify-center gap-2">
        🌿 Reserva Individual
    </h2>

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
                class="w-full border border-green-400 rounded-lg p-3">
        </div>

        <!-- DATOS DEL VISITANTE -->
        <div>
            <h3 class="text-xl font-semibold text-green-700 mb-2">🧾 Datos del visitante</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label>Tipo de documento:</label>
                    <select name="tipo_documento" required class="border w-full p-3 rounded-lg">
                        <option value="CC">Cédula de Ciudadanía</option>
                        <option value="TI">Tarjeta de Identidad</option>
                        <option value="CE">Cédula de Extranjería</option>
                        <option value="PEP">PEP</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label>Número de identificación:</label>
                    <input type="text" name="numero_identificacion" required
                           class="border w-full p-3 rounded-lg solo-numeros">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label>Fecha de nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" required
                           class="border w-full p-3 rounded-lg">
                </div>

                <div>
                    <label>Género:</label>
                    <select name="sexo" required class="border w-full p-3 rounded-lg">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($generos as $g): ?>
                            <option value="<?= $g['id_genero'] ?>"><?= $g['genero'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label>País:</label>
                    <select id="paisSelect" name="pais" class="border w-full p-3 rounded-lg">
                        <?php foreach ($paises as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['pais'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Ciudad:</label>
                    <select id="ciudadSelect" name="id_ciudad" class="border w-full p-3 rounded-lg">
                        <option value="">Seleccionar país primero...</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label>Teléfono:</label>
                <input type="text" name="telefono" required
                       class="border w-full p-3 rounded-lg solo-numeros">
            </div>
        </div>

        <!-- INSTITUCIÓN -->
        <div>
            <label>Institución (opcional):</label>
            <select name="institucion" class="border w-full p-3 rounded-lg">
                <option value="">Seleccionar...</option>
                <?php foreach ($instituciones as $i): ?>
                    <option value="<?= $i['id_institucion'] ?>">
                        <?= $i['nombre_institucion'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- OBSERVACIONES -->
        <textarea name="observaciones" rows="3"
                  class="border w-full p-3 rounded-lg mt-1"
                  placeholder="Comentarios (opcional)"></textarea>

        <div class="flex justify-between pt-6">
            <a href="actividades.php" class="px-5 py-3 bg-gray-300 text-gray-800 rounded-lg">
                ← Volver
            </a>

            <button type="submit" class="px-6 py-3 bg-green-700 text-white rounded-lg">
                Confirmar Reserva ✔
            </button>
        </div>

    </form>

</section>

</main>

<?php include('../includes/footer.php'); ?>

<script>
// Solo números
document.querySelectorAll(".solo-numeros").forEach(input => {
    input.addEventListener("input", e => {
        e.target.value = e.target.value.replace(/[^0-9]/g, "");
    });
});
</script>

<script>
// AJAX País → Ciudad
document.getElementById("paisSelect").addEventListener("change", function () {
    fetch("ajax_ciudades.php?pais=" + this.value)
        .then(res => res.json())
        .then(data => {
            let ciudadSelect = document.getElementById("ciudadSelect");
            ciudadSelect.innerHTML = "";
            data.forEach(c => ciudadSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`);
        });
});
</script>

</body>
</html>
