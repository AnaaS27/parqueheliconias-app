<?php
include('../includes/verificar_admin.php');
require_once('../includes/supabase.php');
include('header_admin.php');

/* =======================================
   🔧 CONFIGURACIÓN PAGINACIÓN
======================================= */
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

/* =======================================
   🔎 FILTROS
======================================= */
$filtro   = $_GET['filtro']  ?? 'activos';
$busqueda = trim($_GET['buscar'] ?? '');

$queryFiltros = "";

/* ---- Estado ---- */
if ($filtro === 'activos') {
    $queryFiltros .= "&usuario_activo=eq.true";
} elseif ($filtro === 'inactivos') {
    $queryFiltros .= "&usuario_activo=eq.false";
}

/* ---- Búsqueda OR nombre/correo ---- */
if ($busqueda !== "") {
    $texto = "*". urlencode($busqueda) . "*";
    $queryFiltros .= "&or=(nombre.ilike.$texto,correo.ilike.$texto)";
}

/* =======================================
   📌 OBTENER USUARIOS (con paginación)
======================================= */
$endpoint = "usuarios?select=*&order=id_usuario.asc&limit=$registrosPorPagina&offset=$offset$queryFiltros";

list($codeUsers, $usuarios, $totalRegistros) = supabase_get($endpoint);

if ($codeUsers !== 200 || !is_array($usuarios)) {
    $usuarios = [];
}

$totalRegistros = $totalRegistros ?? 0;
$totalPaginas   = max(1, ceil($totalRegistros / $registrosPorPagina));
?>

<!-- ==================  HTML  ================== -->

<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Mensaje -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="mb-4 bg-green-100 text-green-800 px-4 py-3 rounded-lg shadow flex items-center justify-between">
            <span><?= $_SESSION['mensaje_exito']; ?></span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php unset($_SESSION['mensaje_exito']); endif; ?>


    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">

        <!-- HEADER -->
        <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Gestión de Usuarios
            </h2>

            <div class="flex gap-3 items-center">

                <!-- CREAR USUARIO -->
                <a href="crear_usuario.php"
                    class="px-4 py-2 bg-white text-green-700 rounded-lg shadow hover:bg-gray-100 text-sm">
                    <i class="fa-solid fa-user-plus"></i> Crear Usuario
                </a>

                <!-- BUSCADOR -->
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="filtro" value="<?= htmlspecialchars($filtro) ?>">

                    <input type="text" name="buscar"
                        placeholder="Buscar nombre o correo..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                        class="px-3 py-2 rounded-lg bg-white border text-gray-700 w-64 text-sm">

                    <button class="px-3 py-2 bg-white text-green-700 rounded-lg">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </form>

                <!-- FILTRO ESTADO -->
                <form method="GET">
                    <select name="filtro" onchange="this.form.submit()"
                        class="px-3 py-2 rounded-lg bg-white border text-gray-800 text-sm">
                        <option value="activos"   <?= $filtro==='activos'?'selected':'' ?>>Activos</option>
                        <option value="inactivos" <?= $filtro==='inactivos'?'selected':'' ?>>Inactivos</option>
                        <option value="todos"     <?= $filtro==='todos'?'selected':'' ?>>Todos</option>
                    </select>
                </form>
            </div>
        </div>


        <!-- TABLA -->
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
                            <td class="px-6 py-4"><?= $usuario["id_usuario"] ?></td>

                            <td class="px-6 py-4"><?= htmlspecialchars($usuario["nombre"]) ?></td>

                            <td class="px-6 py-4"><?= htmlspecialchars($usuario["correo"]) ?></td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-white text-xs rounded-full 
                                    <?= $usuario["id_rol"]==1 ? 'bg-blue-600' : 'bg-gray-600' ?>">
                                    <?= $usuario["id_rol"]==1 ? "Administrador" : "Usuario" ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($usuario["usuario_activo"]): ?>
                                    <span class="px-3 py-1 rounded-full bg-green-600 text-white text-xs">Activo</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full bg-red-600 text-white text-xs">Inactivo</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center flex justify-center gap-2">

                                <a href="editar_usuario.php?id=<?= $usuario["id_usuario"] ?>"
                                   class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs">✏️ Editar</a>

                                <?php if ($usuario["usuario_activo"]): ?>
                                    <a href="eliminar_usuario.php?id=<?= $usuario["id_usuario"] ?>"
                                       class="px-3 py-2 bg-red-600 text-white rounded-lg text-xs">
                                        ❌ Desactivar
                                    </a>
                                <?php else: ?>
                                    <a href="restaurar_usuario.php?id=<?= $usuario["id_usuario"] ?>"
                                       class="px-3 py-2 bg-green-600 text-white rounded-lg text-xs">
                                        🔄 Restaurar
                                    </a>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500">
                            No hay usuarios para mostrar.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>


        <!-- PAGINACIÓN -->
        <div class="px-6 py-4 flex justify-between items-center text-sm">
            <span class="text-gray-600">
                Mostrando <?= count($usuarios) ?> de <?= $totalRegistros ?> usuarios
            </span>

            <div class="flex gap-2">
                <?php if ($paginaActual > 1): ?>
                    <a href="?pagina=<?= $paginaActual - 1 ?>&filtro=<?= urlencode($filtro) ?>&buscar=<?= urlencode($busqueda) ?>"
                        class="px-3 py-1 border rounded-lg bg-gray-100 hover:bg-gray-200">
                        ‹
                    </a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">‹</span>
                <?php endif; ?>

                <?php for ($i=1;$i<=$totalPaginas;$i++): ?>
                    <a href="?pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>&buscar=<?= urlencode($busqueda) ?>"
                       class="px-3 py-1 border rounded-lg <?= $i==$paginaActual?'bg-green-600 text-white':'bg-gray-100 hover:bg-gray-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="?pagina=<?= $paginaActual + 1 ?>&filtro=<?= urlencode($filtro) ?>&buscar=<?= urlencode($busqueda) ?>"
                        class="px-3 py-1 border rounded-lg bg-gray-100 hover:bg-gray-200">
                        ›
                    </a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">›</span>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include('footer_admin.php'); ?>
