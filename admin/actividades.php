<?php
include('header_admin.php');
require_once('../includes/supabase.php');

// ================================
// 🔧 PAGINACIÓN + BÚSQUEDA
// ================================
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

$busqueda = trim($_GET['buscar'] ?? '');
$filtrosQuery = "";

// Búsqueda OR
if ($busqueda !== "") {
    $texto = urlencode("%$busqueda%");
    $filtrosQuery .= "&or=(nombre.ilike.$texto,descripcion.ilike.$texto)";
}

// ================================
// 📌 OBTENER ACTIVIDADES + TOTAL
// ================================
$endpoint = "actividades?select=*&order=id_actividad.asc&limit=$registrosPorPagina&offset=$offset" . $filtrosQuery;

list($codeAct, $actividades, $totalRegistros) = supabase_get($endpoint);

if ($codeAct !== 200 || !is_array($actividades)) {
    $actividades = [];
}

$totalRegistros = $totalRegistros ?? 0;
$totalPaginas   = max(1, ceil($totalRegistros / $registrosPorPagina));
?>

<section class="max-w-7xl mx-auto px-4 py-6">
  <h2 class="text-2xl font-bold text-green-700 mb-1">🎫 Gestión de Actividades</h2>
  <p class="text-gray-600 mb-4">Administra las actividades disponibles en el Parque Las Heliconias.</p>

  <div class="flex flex-col md:flex-row items-center justify-between mb-4 gap-3">

    <button onclick="abrirModal()"
            class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 text-sm">
        ➕ Nueva Actividad
    </button>

    <form method="GET" class="flex items-center gap-2">
      <input type="text" name="buscar" placeholder="Buscar actividad..."
             value="<?= htmlspecialchars($busqueda) ?>"
             class="px-3 py-2 border rounded-lg w-64 text-sm">
      <button class="px-3 py-2 bg-green-600 text-white rounded-lg">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
    </form>
  </div>

  <div class="bg-white shadow-xl rounded-xl border">

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
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

        <?php if (!empty($actividades)): ?>
            <?php foreach ($actividades as $row): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4"><?= $row['id_actividad'] ?></td>
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($row['nombre']) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($row['descripcion']) ?></td>
                <td class="px-6 py-4"><?= $row['duracion_minutos'] ?> min</td>
                <td class="px-6 py-4"><?= $row['cupo_maximo'] ?></td>

                <td class="px-6 py-4">
                  <?php if ($row['activo']): ?>
                    <span class="px-3 py-1 rounded-full bg-green-600 text-white text-xs">Activa</span>
                  <?php else: ?>
                    <span class="px-3 py-1 rounded-full bg-red-600 text-white text-xs">Inactiva</span>
                  <?php endif; ?>
                </td>

                <td class="px-6 py-4 text-center flex justify-center gap-2">
                  <a href="editar_actividad.php?id=<?= $row['id_actividad'] ?>"
                     class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs">
                    ✏️
                  </a>
                  <a href="eliminar_actividad.php?id=<?= $row['id_actividad'] ?>"
                     onclick="return confirm('¿Eliminar actividad?')"
                     class="px-3 py-2 bg-red-600 text-white rounded-lg text-xs">
                    🗑️
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center py-6 text-gray-500">No hay actividades registradas.</td></tr>
        <?php endif; ?>

        </tbody>
      </table>
    </div>

    <!-- PAGINACIÓN -->
    <div class="px-6 py-4 flex justify-between text-sm">
      <span>Mostrando <?= count($actividades) ?> de <?= $totalRegistros ?> actividades</span>

      <div class="flex gap-2">

        <!-- ANTERIOR -->
        <?php if ($paginaActual > 1): ?>
            <a href="?pagina=<?= $paginaActual-1 ?>&buscar=<?= urlencode($busqueda) ?>"
               class="px-3 py-1 bg-gray-100 border rounded-lg hover:bg-gray-200">
               <i class="fa-solid fa-chevron-left"></i>
            </a>
        <?php else: ?>
            <span class="px-3 py-1 bg-gray-200 border rounded-lg text-gray-400">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        <?php endif; ?>

        <!-- NUMERACIÓN -->
        <?php for ($i=1; $i<=$totalPaginas; $i++): ?>
          <a href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"
             class="px-3 py-1 border rounded-lg 
             <?= $i==$paginaActual?'bg-green-600 text-white':'bg-gray-100 hover:bg-gray-200' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- SIGUIENTE -->
        <?php if ($paginaActual < $totalPaginas): ?>
            <a href="?pagina=<?= $paginaActual+1 ?>&buscar=<?= urlencode($busqueda) ?>"
               class="px-3 py-1 bg-gray-100 border rounded-lg hover:bg-gray-200">
               <i class="fa-solid fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="px-3 py-1 bg-gray-200 border rounded-lg text-gray-400">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        <?php endif; ?>

      </div>
    </div>

  </div>
</section>

<!-- MODAL CREAR NUEVA ACTIVIDAD -->
<div id="modalActividad"
     class="fixed inset-0 bg-black/40 hidden justify-center items-center z-50">

  <div class="bg-white w-full max-w-lg px-6 py-5 rounded-xl shadow-xl relative">

    <button class="absolute top-3 right-3 text-gray-600" onclick="cerrarModal()">✖</button>

    <h3 class="text-lg font-semibold text-green-700 mb-4">➕ Agregar Nueva Actividad</h3>

    <form action="procesar_actividad.php" method="POST" class="space-y-3">

      <div>
        <label class="font-medium">Nombre:</label>
        <input name="nombre" required class="w-full border px-3 py-2 rounded-lg">
      </div>

      <div>
        <label class="font-medium">Descripción:</label>
        <textarea name="descripcion" rows="3" required class="w-full border px-3 py-2 rounded-lg"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label>Duración (minutos):</label>
          <input type="number" min="1" name="duracion_minutos" required class="w-full border px-3 py-2 rounded-lg">
        </div>
        <div>
          <label>Cupo máximo:</label>
          <input type="number" min="1" name="cupo_maximo" required class="w-full border px-3 py-2 rounded-lg">
        </div>
      </div>

      <div>
        <label>Estado:</label>
        <select name="activo" class="w-full border px-3 py-2 rounded-lg">
          <option value="true">Activa</option>
          <option value="false">Inactiva</option>
        </select>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="cerrarModal()" class="px-4 py-2 bg-gray-200 rounded-lg">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">
          Guardar
        </button>
      </div>
    </form>

  </div>

</div>

<script>
function abrirModal() {
  document.getElementById("modalActividad").classList.remove("hidden");
  document.getElementById("modalActividad").classList.add("flex");
}
function cerrarModal() {
  document.getElementById("modalActividad").classList.add("hidden");
}
</script>

<?php include('footer_admin.php'); ?>
