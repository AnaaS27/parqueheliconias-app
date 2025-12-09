<?php
include('header_admin.php');
require_once '../includes/supabase.php';

// ==========================
// PAGINACIÓN
// ==========================
$porPagina = 10;
$pagina = isset($_GET["pagina"]) ? max(1, intval($_GET["pagina"])) : 1;
$offset = ($pagina - 1) * $porPagina;

// ==========================
// BUSCADOR
// ==========================
$buscar = trim($_GET["buscar"] ?? "");

$filtros = "";
if ($buscar !== "") {
    $txt = strtolower($buscar);
    // OR real para Supabase
    $filtros .= "&or=(nombre.ilike.%$txt%,descripcion.ilike.%$txt%)";
}

// ==========================
// CONSULTAR ACTIVIDADES PÁGINA
// ==========================
$endpoint = "actividades?select=*&order=id_actividad.asc&limit=$porPagina&offset=$offset" . $filtros;
list($code, $actividades) = supabase_get($endpoint);
if ($code !== 200) $actividades = [];

// ==========================
// CONTAR ACTIVIDADES
// ==========================
$countEndpoint = "actividades?select=count:id" . $filtros;
list($codeCount, $countData) = supabase_get($countEndpoint);

$total = ($codeCount === 200 && !empty($countData)) ? intval($countData[0]["count"]) : 0;
$totalPaginas = ceil($total / $porPagina);

?>

<div class="max-w-7xl mx-auto px-6 py-8">

    <h2 class="text-3xl font-bold text-green-700 mb-2">🎫 Gestión de Actividades</h2>
    <p class="text-gray-700 mb-6">Administra las actividades del Parque Las Heliconias.</p>

    <!-- ACCIONES SUPERIORES -->
    <div class="flex justify-between mb-4">

        <!-- BUSCADOR -->
        <form method="GET" class="flex items-center gap-2">
            <input type="text"
                   name="buscar"
                   placeholder="Buscar actividad..."
                   value="<?= htmlspecialchars($buscar) ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 w-64">
            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                Buscar
            </button>
        </form>

        <!-- NUEVA ACTIVIDAD -->
        <button onclick="abrirModal()"
                class="bg-green-700 text-white px-5 py-2 rounded-lg shadow hover:bg-green-800 flex items-center gap-2">
            ➕ Nueva Actividad
        </button>
    </div>

    <!-- TABLA DE ACTIVIDADES -->
    <div class="bg-white shadow-xl border border-gray-200 rounded-xl overflow-hidden">

        <table class="w-full text-sm text-left">
            <thead class="bg-green-50 text-green-800 border-b">
                <tr>
                    <th class="px-5 py-3">ID</th>
                    <th class="px-5 py-3">Nombre</th>
                    <th class="px-5 py-3">Descripción</th>
                    <th class="px-5 py-3 text-center">Duración</th>
                    <th class="px-5 py-3 text-center">Cupos</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php if (!empty($actividades)): ?>
                <?php foreach ($actividades as $a): ?>
                    <tr class="border-b hover:bg-gray-50">

                        <td class="px-5 py-3"><?= $a["id_actividad"] ?></td>

                        <td class="px-5 py-3 font-medium"><?= htmlspecialchars($a["nombre"]) ?></td>

                        <td class="px-5 py-3 text-gray-600"><?= htmlspecialchars($a["descripcion"]) ?></td>

                        <td class="px-5 py-3 text-center"><?= $a["duracion_minutos"] ?> min</td>

                        <td class="px-5 py-3 text-center"><?= $a["cupo_maximo"] ?></td>

                        <td class="px-5 py-3 text-center">
                            <?php if ($a["activo"]): ?>
                                <span class="px-3 py-1 bg-green-600 text-white rounded-full text-xs">Activa</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs">Inactiva</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-5 py-3 text-center flex justify-center gap-2">

                            <a href="editar_actividad.php?id=<?= $a['id_actividad'] ?>"
                               class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs">
                                ✏️ Editar
                            </a>

                            <a href="eliminar_actividad.php?id=<?= $a['id_actividad'] ?>"
                               onclick="return confirm('¿Eliminar esta actividad?')"
                               class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs">
                                🗑️ Eliminar
                            </a>
                        </td>

                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-500">
                        No hay actividades registradas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- PAGINACIÓN -->
        <div class="flex justify-between items-center px-5 py-4 bg-gray-50 text-sm">

            <span class="text-gray-600">
                Mostrando <?= count($actividades) ?> de <?= $total ?> actividades
            </span>

            <div class="flex gap-2">

                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>&buscar=<?= $buscar ?>"
                       class="px-3 py-1 border rounded hover:bg-gray-200">⬅</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded text-gray-400 bg-gray-200">⬅</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>&buscar=<?= $buscar ?>"
                       class="px-3 py-1 border rounded 
                       <?= $i == $pagina ? 'bg-green-600 text-white' : 'hover:bg-gray-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&buscar=<?= $buscar ?>"
                       class="px-3 py-1 border rounded hover:bg-gray-200">➡</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded text-gray-400 bg-gray-200">➡</span>
                <?php endif; ?>

            </div>

        </div>

    </div>
</div>

<!-- MODAL -->
<div id="modalActividad"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg relative">

        <button class="absolute right-3 top-3 text-gray-600 text-xl" onclick="cerrarModal()">✖</button>

        <h3 class="text-xl font-semibold text-green-700 mb-4">➕ Nueva Actividad</h3>

        <form action="procesar_actividad.php" method="POST" class="space-y-3">

            <div>
                <label class="font-semibold">Nombre:</label>
                <input type="text" name="nombre" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="font-semibold">Descripción:</label>
                <textarea name="descripcion" rows="3" required
                          class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>

            <div>
                <label class="font-semibold">Duración (minutos):</label>
                <input type="number" name="duracion_minutos" min="10" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="font-semibold">Cupo máximo:</label>
                <input type="number" name="cupo_maximo" min="1" required
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="font-semibold">Estado:</label>
                <select name="activo" class="w-full px-3 py-2 border rounded-lg">
                    <option value="true" selected>Activa</option>
                    <option value="false">Inactiva</option>
                </select>
            </div>

            <button class="bg-green-700 text-white w-full py-2 rounded-lg hover:bg-green-800">
                Guardar Actividad
            </button>
        </form>
    </div>

</div>

<script>
function abrirModal() {
    document.getElementById("modalActividad").classList.remove("hidden");
}
function cerrarModal() {
    document.getElementById("modalActividad").classList.add("hidden");
}
</script>

<?php include('footer_admin.php'); ?>
