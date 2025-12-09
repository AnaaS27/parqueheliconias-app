<?php
include('header_admin.php');
require_once '../includes/supabase.php';

// ================================
// 🔧 PAGINACIÓN MANUAL (como reservas)
// ================================
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// ================================
// 🔍 BÚSQUEDA
// ================================
$busqueda = trim($_GET['buscar'] ?? '');
$where = [];

if ($busqueda !== "") {
    $texto = urlencode("%$busqueda%");
    $where[] = "or=(nombre.ilike.$texto,descripcion.ilike.$texto)";
}

// Construir query completa
$query = "actividades?select=*&order=id_actividad.asc";
foreach ($where as $f) {
    $query .= "&$f";
}

// ================================
// 📌 1) OBTENER TODAS LAS ACTIVIDADES
// ================================
list($code, $actividades) = supabase_get($query);

if ($code !== 200 || !is_array($actividades)) {
    $actividades = [];
}

// ================================
// 📌 2) PAGINAR MANUALMENTE
// ================================
$totalRegistros = count($actividades);
$totalPaginas   = max(1, ceil($totalRegistros / $registrosPorPagina));

$actividadesPagina = array_slice($actividades, $offset, $registrosPorPagina);
?>

<section class="max-w-7xl mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold text-green-700 mb-1">🎫 Gestión de Actividades</h2>
    <p class="text-gray-600 mb-6">Administra las actividades disponibles en el Parque Las Heliconias.</p>

    <!-- ACCIONES SUPERIORES -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <button onclick="abrirModal()" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 text-sm">
            ➕ Nueva Actividad
        </button>

        <!-- BUSCADOR -->
        <form method="GET" class="flex items-center gap-2">
            <input type="text"
                   name="buscar"
                   placeholder="Buscar por nombre o descripción..."
                   value="<?= htmlspecialchars($busqueda) ?>"
                   class="px-3 py-2 rounded-lg border bg-white w-64 focus:ring focus:border-green-500">

            <button class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
    </div>

    <!-- TABLA -->
    <div class="bg-white rounded-xl shadow border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-green-50 text-green-800 border-b">
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Descripción</th>
                        <th class="px-6 py-3">Duración</th>
                        <th class="px-6 py-3">Cupos</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($actividadesPagina)): ?>
                        <?php foreach ($actividadesPagina as $a): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $a['id_actividad'] ?></td>

                                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($a['nombre']); ?></td>

                                <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($a['descripcion']); ?></td>

                                <td class="px-6 py-4"><?= intval($a['duracion_minutos']); ?> min</td>

                                <td class="px-6 py-4"><?= intval($a['cupo_maximo']); ?></td>

                                <td class="px-6 py-4">
                                    <?php if (!empty($a['activo'])): ?>
                                        <span class="px-3 py-1 bg-green-600 text-white text-xs rounded-full">Activa</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-600 text-white text-xs rounded-full">Inactiva</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-center flex justify-center gap-2">

                                    <a href="editar_actividad.php?id=<?= $a['id_actividad'] ?>"
                                       class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs">
                                        ✏️ Editar
                                    </a>

                                    <a href="eliminar_actividad.php?id=<?= $a['id_actividad'] ?>"
                                       onclick="return confirm('¿Seguro que deseas eliminar esta actividad?')"
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
        </div>

        <!-- PAGINACIÓN -->
        <div class="px-6 py-4 flex justify-between items-center text-sm">
            <span class="text-gray-600">
                Mostrando <?= count($actividadesPagina) ?> de <?= $totalRegistros ?> actividades
            </span>

            <div class="flex gap-2">

                <!-- Anterior -->
                <?php if ($paginaActual > 1): ?>
                    <a href="?pagina=<?= $paginaActual - 1 ?>&buscar=<?= urlencode($busqueda) ?>"
                       class="px-3 py-1 border rounded-lg bg-gray-100 hover:bg-gray-200">←</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">←</span>
                <?php endif; ?>

                <!-- Números -->
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"
                       class="px-3 py-1 border rounded-lg 
                       <?= $i == $paginaActual ? 'bg-green-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Siguiente -->
                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="?pagina=<?= $paginaActual + 1 ?>&buscar=<?= urlencode($busqueda) ?>"
                       class="px-3 py-1 border rounded-lg bg-gray-100 hover:bg-gray-200">→</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">→</span>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>


<!-- ======================================================
     MODAL NUEVA ACTIVIDAD
======================================================= -->
<div id="modalActividad"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-50 flex items-center justify-center">

    <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-xl relative">

        <button onclick="cerrarModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✖</button>

        <h3 class="text-lg font-semibold text-green-700 mb-4">➕ Agregar Nueva Actividad</h3>

        <form action="procesar_actividad.php" method="POST" class="space-y-3">

            <div>
                <label class="block text-sm font-medium">Nombre:</label>
                <input type="text" name="nombre" required
                       class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium">Descripción:</label>
                <textarea name="descripcion" rows="3" required
                          class="w-full border rounded-lg px-3 py-2"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium">Duración (min):</label>
                    <input type="number" name="duracion_minutos" min="10" required
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium">Cupo máximo:</label>
                    <input type="number" name="cupo_maximo" min="1" required
                           class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Estado:</label>
                <select name="activo" class="w-full border rounded-lg px-3 py-2">
                    <option value="true">Activa</option>
                    <option value="false">Inactiva</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="cerrarModal()"
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Guardar Actividad
                </button>
            </div>

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
