<?php
include('../includes/verificar_admin.php');
require_once('../includes/supabase.php');
include('header_admin.php');

// =====================================
// 🔧 CONFIG PAGINACIÓN
// =====================================
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// =====================================
// 🔎 FILTROS
// =====================================
$filtro   = $_GET['filtro']  ?? 'activos';
$busqueda = trim($_GET['buscar'] ?? '');

$queryParams = [];

// Estado
if ($filtro === 'activos') {
    $queryParams[] = "usuario_activo=eq.true";
} elseif ($filtro === 'inactivos') {
    $queryParams[] = "usuario_activo=eq.false";
}

// Búsqueda
if ($busqueda !== "") {
    $texto = "%{$busqueda}%";
    $textoURL = urlencode($texto);
    $queryParams[] = "or=(nombre.ilike.$textoURL,correo.ilike.$textoURL)";
}

// Convertir filtros a cadena
$filtrosQuery = "";
if (!empty($queryParams)) {
    $filtrosQuery = "&" . implode("&", $queryParams);
}

// =====================================
// 📌 CONSULTA USUARIOS (CON LIMIT + OFFSET)
// =====================================
$endpoint = "usuarios?select=*&order=id_usuario.asc&limit=$registrosPorPagina&offset=$offset" . $filtrosQuery;

list($code, $usuarios, $totalRegistros) = supabase_get($endpoint);

if ($code !== 200 || !is_array($usuarios)) {
    $usuarios = [];
    $totalRegistros = 0;
}

// Total páginas
$totalPaginas = max(1, ceil($totalRegistros / $registrosPorPagina));
?>

<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">

        <!-- Header -->
        <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Gestión de Usuarios
            </h2>

            <div class="flex gap-3 items-center">

                <a href="crear_usuario.php"
                   class="px-4 py-2 bg-white text-green-700 rounded-lg shadow hover:bg-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Crear Usuario
                </a>

                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="filtro" value="<?= $filtro ?>">

                    <input type="text"
                           name="buscar"
                           placeholder="Buscar nombre o correo..."
                           value="<?= htmlspecialchars($busqueda) ?>"
                           class="px-3 py-2 rounded-lg border w-64">

                    <button class="px-3 py-2 bg-white text-green-700 rounded-lg hover:bg-gray-100">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <form method="GET">
                    <select name="filtro"
                            onchange="this.form.submit()"
                            class="px-3 py-2 rounded-lg border">
                        <option value="activos"   <?= $filtro == 'activos' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivos" <?= $filtro == 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                        <option value="todos"     <?= $filtro == 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </form>

            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-green-50 border-b">
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
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4"><?= $u['id_usuario'] ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($u['nombre']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($u['correo']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-white text-xs <?= $u['id_rol']==1?'bg-blue-600':'bg-gray-600' ?>">
                                    <?= $u['id_rol']==1?"Administrador":"Usuario" ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($u['usuario_activo']): ?>
                                    <span class="px-3 py-1 bg-green-600 rounded-full text-white text-xs">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-600 rounded-full text-white text-xs">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <a href="editar_usuario.php?id=<?= $u['id_usuario'] ?>"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs">✏️</a>

                                <?php if ($u['usuario_activo']): ?>
                                    <a href="eliminar_usuario.php?id=<?= $u['id_usuario'] ?>"
                                        class="px-3 py-2 bg-red-600 text-white rounded-lg text-xs">🗑️</a>
                                <?php else: ?>
                                    <a href="restaurar_usuario.php?id=<?= $u['id_usuario'] ?>"
                                        class="px-3 py-2 bg-green-600 text-white rounded-lg text-xs">↩️</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-6 text-gray-500">No hay usuarios para mostrar.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="px-6 py-4 flex justify-between items-center text-sm">
            <span>Mostrando <?= count($usuarios) ?> de <?= $totalRegistros ?> usuarios</span>

            <div class="flex gap-2">

                <?php if ($paginaActual > 1): ?>
                    <a href="?pagina=<?= $paginaActual-1 ?>&buscar=<?= urlencode($busqueda) ?>&filtro=<?= $filtro ?>"
                       class="px-3 py-1 border rounded-lg bg-gray-100">←</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">←</span>
                <?php endif; ?>

                <?php for ($i=1;$i<=$totalPaginas;$i++): ?>
                    <a href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>&filtro=<?= $filtro ?>"
                       class="px-3 py-1 border rounded-lg <?= $i==$paginaActual?'bg-green-600 text-white':'bg-gray-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="?pagina=<?= $paginaActual+1 ?>&buscar=<?= urlencode($busqueda) ?>&filtro=<?= $filtro ?>"
                       class="px-3 py-1 border rounded-lg bg-gray-100">→</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">→</span>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<?php include('footer_admin.php'); ?>
