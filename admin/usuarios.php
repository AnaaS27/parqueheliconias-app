<?php
include('../includes/verificar_admin.php');
include('../includes/supabase.php');
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

$where = [];

// Estado
if ($filtro === 'activos') {
    $where[] = "usuario_activo=eq.true";
} elseif ($filtro === 'inactivos') {
    $where[] = "usuario_activo=eq.false";
}

// Búsqueda
if ($busqueda !== "") {
    $texto = urlencode("%$busqueda%");
    $where[] = "(nombre=ilike.$texto,correo=ilike.$texto)";
}

// Convertir condiciones a formato Supabase
$filtrosQuery = "";
if (!empty($where)) {
    foreach ($where as $cond) {
        $filtrosQuery .= "&" . $cond;
    }
}

// =====================================
// 📌 OBTENER LISTA DE USUARIOS LIMITADA (PÁGINA)
// =====================================
$endpoint = "usuarios?select=*&order=id_usuario.asc&limit=$registrosPorPagina&offset=$offset" . $filtrosQuery;

list($codeUsers, $usuarios) = supabase_get($endpoint);

if ($codeUsers !== 200) {
    $usuarios = [];
}

// =====================================
// 📌 CONTAR TOTAL DE REGISTROS FILTRADOS
// Supabase: select=count:id
// =====================================
$countEndpoint = "usuarios?select=count:id" . $filtrosQuery;

list($codeCount, $countData) = supabase_get($countEndpoint);

$totalRegistros = ($codeCount === 200 && !empty($countData))
    ? intval($countData[0]["count"])
    : 0;

$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

?>

<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Mensaje -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="mb-4 bg-green-100 text-green-800 px-4 py-3 rounded-lg shadow flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2"></i> <?= $_SESSION['mensaje_exito']; ?></span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <?php unset($_SESSION['mensaje_exito']); endif; ?>

    <!-- Card principal -->
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">

        <!-- Header -->
        <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-users"></i> Gestión de Usuarios
            </h2>

            <div class="flex gap-3 items-center">

                <!-- CREAR USUARIO -->
                <a href="crear_usuario.php"
                class="px-4 py-2 bg-white text-green-700 rounded-lg shadow hover:bg-gray-100 font-medium text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Crear Usuario
                </a>

                <!-- BUSCADOR -->
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="filtro" value="<?= $filtro ?>">

                    <input type="text" name="buscar" placeholder="Buscar nombre o correo..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                        class="px-3 py-2 rounded-lg bg-white text-gray-700 border border-gray-300 text-sm w-64 focus:ring">

                    <button class="px-3 py-2 bg-white text-green-700 rounded-lg hover:bg-gray-100 text-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <!-- SELECT FILTRO -->
                <form method="GET">
                    <select name="filtro" onchange="this.form.submit()"
                        class="px-3 py-2 rounded-lg bg-white text-gray-800 border border-gray-300 text-sm focus:ring">
                        <option value="activos"   <?= $filtro === 'activos' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivos" <?= $filtro === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
                        <option value="todos"     <?= $filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </form>

            </div>

        </div>

        <!-- Tabla -->
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
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4"><?= $usuario['id_usuario'] ?></td>

                            <td class="px-6 py-4 font-medium text-gray-700">
                                <?= htmlspecialchars($usuario['nombre']) ?>
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                <?= htmlspecialchars($usuario['correo']) ?>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-white text-xs
                                    <?= $usuario['id_rol'] == 1 ? 'bg-blue-600' : 'bg-gray-600' ?>">
                                    <?= $usuario['id_rol'] == 1 ? 'Administrador' : 'Usuario' ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if ($usuario['usuario_activo']): ?>
                                    <span class="px-3 py-1 rounded-full bg-green-600 text-white text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full bg-red-600 text-white text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-circle-xmark"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center flex justify-center gap-2">

                                <!-- Editar -->
                                <a href="editar_usuario.php?id=<?= $usuario['id_usuario'] ?>"
                                   class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs flex items-center gap-1">
                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                </a>

                                <!-- Activar/Desactivar -->
                                <?php if ($usuario['usuario_activo']): ?>
                                    <button onclick="confirmarEliminacion(<?= $usuario['id_usuario'] ?>,'<?= $usuario['nombre'] ?>')"
                                            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-trash"></i> Desactivar
                                    </button>
                                <?php else: ?>
                                    <button onclick="confirmarRestauracion(<?= $usuario['id_usuario'] ?>,'<?= $usuario['nombre'] ?>')"
                                            class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-rotate-left"></i> Restaurar
                                    </button>
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
            <div class="px-6 py-4 flex justify-between items-center text-sm">

                <span class="text-gray-600">
                    Mostrando <?= count($usuarios) ?> de <?= $totalRegistros ?> usuarios
                </span>

                <div class="flex gap-2">

                    <!-- Anterior -->
                    <?php if ($paginaActual > 1): ?>
                        <a href="?pagina=<?= $paginaActual - 1 ?>&filtro=<?= $filtro ?>&buscar=<?= $busqueda ?>"
                            class="px-3 py-1 border rounded-lg bg-gray-100 hover:bg-gray-200">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">
                            <i class="fa-solid fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>

                    <!-- Números -->
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <a href="?pagina=<?= $i ?>&filtro=<?= $filtro ?>&buscar=<?= $busqueda ?>"
                            class="px-3 py-1 border rounded-lg 
                            <?= $i == $paginaActual ? 'bg-green-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Siguiente -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?pagina=<?= $paginaActual + 1 ?>&filtro=<?= $filtro ?>&buscar=<?= $busqueda ?>"
                            class="px-3 py-1 border rounded-lg bg-gray-100 hover:bg-gray-200">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1 border rounded-lg bg-gray-200 text-gray-400">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarEliminacion(id, nombre) {
    Swal.fire({
        title: "¿Desactivar usuario?",
        html: `Se desactivará la cuenta de <b>${nombre}</b>.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, desactivar",
        cancelButtonColor: "#d33"
    }).then((r) => { if (r.isConfirmed) location.href = `eliminar_usuario.php?id=${id}`; });
}

function confirmarRestauracion(id, nombre) {
    Swal.fire({
        title: "¿Restaurar usuario?",
        html: `El usuario <b>${nombre}</b> volverá a estar activo.`,
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Sí, restaurar",
        confirmButtonColor: "#198754"
    }).then((r) => { if (r.isConfirmed) location.href = `restaurar_usuario.php?id=${id}`; });
}
</script>

<?php include('footer_admin.php'); ?>
