<?php
include('../includes/verificar_admin.php');
require_once('../includes/supabase.php');
include('header_admin.php');

// =====================================
// 🔧 CONFIGURACIÓN PAGINACIÓN
// =====================================
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// =====================================
// 🔎 FILTROS
// =====================================
$filtro   = $_GET['filtro']  ?? 'activos';
$busqueda = trim($_GET['buscar'] ?? '');

$filtrosQuery = "";

// Estado
if ($filtro === 'activos') {
    $filtrosQuery .= "&usuario_activo=eq.true";
} elseif ($filtro === 'inactivos') {
    $filtrosQuery .= "&usuario_activo=eq.false";
}

// Busqueda OR
if ($busqueda !== "") {
    $texto = urlencode("%$busqueda%");
    $filtrosQuery .= "&or=(nombre.ilike.$texto,correo.ilike.$texto)";
}

// =====================================
// 📌 OBTENER LISTA PÁGINA + TOTAL
// =====================================
$endpoint = "usuarios?select=*&order=id_usuario.asc&limit=$registrosPorPagina&offset=$offset" . $filtrosQuery;

list($codeUsers, $usuarios, $totalRegistros) = supabase_get($endpoint);

if ($codeUsers !== 200 || !is_array($usuarios)) {
    $usuarios = [];
}

$totalRegistros = $totalRegistros ?? 0;
$totalPaginas   = max(1, ceil($totalRegistros / $registrosPorPagina));
?>

<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">

        <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Gestión de Usuarios
            </h2>

            <div class="flex gap-3 items-center">

                <a href="crear_usuario.php"
                   class="px-4 py-2 bg-white text-green-700 rounded-lg shadow hover:bg-gray-100 text-sm">
                    <i class="fa-solid fa-user-plus"></i> Crear Usuario
                </a>

                <form method="GET" class="flex items-center gap-2">
                    <input type="text" name="buscar" placeholder="Buscar por nombre o correo..."
                           value="<?= htmlspecialchars($busqueda) ?>"
                           class="px-3 py-2 rounded-lg bg-white text-gray-700 border border-gray-300 text-sm w-64">

                    <button class="px-3 py-2 bg-white text-green-700 rounded-lg hover:bg-gray-200 text-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <form method="GET">
                    <select name="filtro" onchange="this.form.submit()"
                            class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-sm">
                        <option value="activos"   <?= $filtro=='activos'?'selected':'' ?>>Activos</option>
                        <option value="inactivos" <?= $filtro=='inactivos'?'selected':'' ?>>Inactivos</option>
                        <option value="todos"     <?= $filtro=='todos'?'selected':'' ?>>Todos</option>
                    </select>
                </form>

            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-green-50 text-green-800 border-b">
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Correo</th>
                        <th class="px-6 py-3">Rol</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4"><?= $usuario['id_usuario'] ?></td>

                            <td class="px-6 py-4 font-medium">
                                <?= htmlspecialchars($usuario['nombre'] . " " . ($usuario['apellido'] ?? '')) ?>
                            </td>

                            <td class="px-6 py-4"><?= htmlspecialchars($usuario['correo']) ?></td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-white rounded-full text-xs 
                                    <?= $usuario['id_rol']==1 ? 'bg-blue-600':'bg-gray-600' ?>">
                                    <?= $usuario['id_rol']==1 ? 'Administrador':'Usuario' ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($usuario['usuario_activo']): ?>
                                    <span class="px-3 py-1 bg-green-600 text-white rounded-full text-xs">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 flex justify-center gap-2">

                                <a href="editar_usuario.php?id=<?= $usuario['id_usuario'] ?>"
                                   class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                <?php if ($usuario['usuario_activo']): ?>
                                    <a href="eliminar_usuario.php?id=<?= $usuario['id_usuario'] ?>"
                                       class="px-3 py-2 bg-red-600 text-white rounded-lg text-xs">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="restaurar_usuario.php?id=<?= $usuario['id_usuario'] ?>"
                                       class="px-3 py-2 bg-green-600 text-white rounded-lg text-xs">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </a>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500">No hay usuarios para mostrar.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- PAGINACIÓN -->
            <div class="px-6 py-4 flex justify-between text-sm">
                <span class="text-gray-600">
                    Mostrando <?= count($usuarios) ?> de <?= $totalRegistros ?> usuarios
                </span>

                <div class="flex gap-2">

                    <!-- Anterior -->
                    <?php if ($paginaActual > 1): ?>
                        <a href="?pagina=<?= $paginaActual-1 ?>&filtro=<?= urlencode($filtro) ?>&buscar=<?= urlencode($busqueda) ?>"
                           class="px-3 py-1 bg-gray-100 border rounded-lg hover:bg-gray-200">
                           <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-gray-200 border rounded-lg text-gray-400">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>

                    <!-- Números -->
                    <?php for ($i=1; $i<=$totalPaginas; $i++): ?>
                        <a href="?pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&buscar=<?= urlencode($busqueda) ?>"
                           class="px-3 py-1 border rounded-lg 
                           <?= $i==$paginaActual ? 'bg-green-600 text-white':'bg-gray-100 hover:bg-gray-200' ?>">
                           <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Siguiente -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?pagina=<?= $paginaActual+1 ?>&filtro=<?= urlencode($filtro) ?>&buscar=<?= urlencode($busqueda) ?>"
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
    </div>
</div>

<?php include('footer_admin.php'); ?>
