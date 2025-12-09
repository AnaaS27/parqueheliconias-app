<?php
include('header_admin.php');
require_once("../includes/supabase.php");

// ===============================
// CONFIGURACIÓN DE PAGINACIÓN
// ===============================
$porPagina = 10;
$pagina = isset($_GET["pagina"]) ? max(1, intval($_GET["pagina"])) : 1;
$offset = ($pagina - 1) * $porPagina;

// ===============================
// FILTROS Y BÚSQUEDA
// ===============================
$buscar = trim($_GET["buscar"] ?? "");
$estado = $_GET["estado"] ?? "todas";

$filtros = "";

// Filtro por estado
if ($estado !== "todas") {
    $filtros .= "&estado=eq." . urlencode($estado);
}

// Búsqueda
if ($buscar !== "") {
    $b = strtolower($buscar);
    $filtros .= "&or=(id_reserva.eq.$b, tipo_reserva.ilike.%$b%)";
}

// ===============================
// CONSULTAR RESERVAS (PÁGINA ACTUAL)
// ===============================
$endpoint = "reservas?select=*&order=fecha_reserva.desc&limit=$porPagina&offset=$offset" . $filtros;
list($codeRes, $reservas) = supabase_get($endpoint);
if ($codeRes !== 200) $reservas = [];

// ===============================
// CONTAR TOTAL DE REGISTROS
// ===============================
$countEndpoint = "reservas?select=count:id" . $filtros;
list($codeCount, $countData) = supabase_get($countEndpoint);
$totalRegistros = ($codeCount === 200 && !empty($countData)) ? intval($countData[0]["count"]) : 0;
$totalPaginas = ceil($totalRegistros / $porPagina);

// ===============================
// OBTENER LISTA DE IDs PARA JOIN
// ===============================
$idsUsuarios = array_column($reservas, "id_usuario");
$idsActividades = array_column($reservas, "id_actividad");

$lista = [];

// ===============================
// OBTENER USUARIOS POR ID
// ===============================
$usuariosPorID = [];
if (!empty($idsUsuarios)) {
    $idList = implode(",", array_unique($idsUsuarios));
    list($codeUser, $usuarios) = supabase_get("usuarios?id_usuario=in.($idList)");
    if ($codeUser === 200)
        foreach ($usuarios as $u) $usuariosPorID[$u["id_usuario"]] = $u;
}

// ===============================
// OBTENER ACTIVIDADES POR ID
// ===============================
$actividadesPorID = [];
if (!empty($idsActividades)) {
    $idListA = implode(",", array_unique($idsActividades));
    list($codeA, $acts) = supabase_get("actividades?id_actividad=in.($idListA)");
    if ($codeA === 200)
        foreach ($acts as $a) $actividadesPorID[$a["id_actividad"]] = $a;
}

// ===============================
// CONSTRUIR LISTA FINAL
// ===============================
foreach ($reservas as $r) {
    $uid = $r["id_usuario"];
    $aid = $r["id_actividad"];

    $lista[] = [
        "id_reserva"      => $r["id_reserva"],
        "usuario"         => ($usuariosPorID[$uid]["nombre"] ?? "N/A") . " " . ($usuariosPorID[$uid]["apellido"] ?? ""),
        "actividad"       => $actividadesPorID[$aid]["nombre"] ?? "N/A",
        "tipo_reserva"    => $r["tipo_reserva"],
        "estado"          => $r["estado"],
        "participantes"   => $r["numero_participantes"],
        "fecha_reserva"   => $r["fecha_reserva"]
    ];
}

?>

<div class="max-w-7xl mx-auto px-6 py-8">

    <h2 class="text-3xl font-bold text-green-700 mb-2">📅 Gestión de Reservas</h2>
    <p class="text-gray-700 mb-6">Administra, confirma o cancela las reservas realizadas por los usuarios.</p>

    <!-- FILTROS -->
    <div class="flex justify-between items-center mb-5">

        <!-- BUSCADOR -->
        <form method="GET" class="flex items-center gap-3">
            <input type="text"
                   name="buscar"
                   placeholder="Buscar por ID o tipo..."
                   value="<?= htmlspecialchars($buscar) ?>"
                   class="px-3 py-2 border rounded-lg shadow-sm bg-white w-64">

            <select name="estado" class="px-3 py-2 border rounded-lg bg-white shadow-sm">
                <option value="todas" <?= $estado === "todas" ? "selected" : "" ?>>Todas</option>
                <option value="pendiente" <?= $estado === "pendiente" ? "selected" : "" ?>>Pendientes</option>
                <option value="confirmada" <?= $estado === "confirmada" ? "selected" : "" ?>>Confirmadas</option>
                <option value="cancelada" <?= $estado === "cancelada" ? "selected" : "" ?>>Canceladas</option>
            </select>

            <button class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800">
                Filtrar
            </button>
        </form>

    </div>

    <!-- TABLA DE RESERVAS -->
    <div class="bg-white shadow-xl border rounded-xl overflow-hidden">

        <table class="w-full text-left text-sm">
            <thead class="bg-green-50 border-b text-green-800">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Usuario</th>
                    <th class="px-6 py-3">Actividad</th>
                    <th class="px-6 py-3">Tipo</th>
                    <th class="px-6 py-3">Participantes</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($lista)): ?>
                    <?php foreach ($lista as $r): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4">#<?= $r["id_reserva"] ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($r["usuario"]) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($r["actividad"]) ?></td>
                            <td class="px-6 py-4"><?= ucfirst($r["tipo_reserva"]) ?></td>
                            <td class="px-6 py-4"><?= $r["participantes"] ?></td>
                            <td class="px-6 py-4"><?= date("d/m/Y H:i", strtotime($r["fecha_reserva"])) ?></td>

                            <td class="px-6 py-4">
                                <?php
                                    $badge = [
                                        "pendiente"  => "bg-yellow-500",
                                        "confirmada" => "bg-green-600",
                                        "cancelada"  => "bg-red-600"
                                    ][$r["estado"]];
                                ?>
                                <span class="px-3 py-1 text-white rounded-full text-xs <?= $badge ?>">
                                    <?= ucfirst($r["estado"]) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center flex gap-2 justify-center">

                                <?php if ($r["estado"] === "pendiente"): ?>
                                    <!-- Confirmar -->
                                    <form action="actualizar_estado_reserva.php" method="POST">
                                        <input type="hidden" name="id_reserva" value="<?= $r["id_reserva"] ?>">
                                        <input type="hidden" name="estado" value="confirmada">
                                        <button class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-xs">✔</button>
                                    </form>

                                    <!-- Cancelar -->
                                    <form action="actualizar_estado_reserva.php" method="POST">
                                        <input type="hidden" name="id_reserva" value="<?= $r["id_reserva"] ?>">
                                        <input type="hidden" name="estado" value="cancelada">
                                        <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-xs">✖</button>
                                    </form>
                                <?php endif; ?>

                                <!-- Detalles -->
                                <a href="detalle_reserva.php?id=<?= $r["id_reserva"] ?>"
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs">
                                    🔍
                                </a>

                                <!-- Eliminar -->
                                <a href="eliminar_reserva.php?id=<?= $r["id_reserva"] ?>"
                                   onclick="return confirm('¿Eliminar esta reserva?')"
                                   class="bg-red-700 text-white px-3 py-1 rounded hover:bg-red-800 text-xs">
                                    🗑
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-6 text-gray-500">No hay reservas para mostrar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- PAGINACIÓN -->
        <div class="flex justify-between items-center px-6 py-4 bg-gray-50">

            <span class="text-gray-600 text-sm">
                Mostrando <?= count($lista) ?> de <?= $totalRegistros ?> reservas
            </span>

            <div class="flex gap-2 text-sm">

                <!-- Prev -->
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>&buscar=<?= $buscar ?>&estado=<?= $estado ?>"
                       class="px-3 py-1 border rounded hover:bg-gray-200">⬅</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded text-gray-400 bg-gray-200">⬅</span>
                <?php endif; ?>

                <!-- Pages -->
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>&buscar=<?= $buscar ?>&estado=<?= $estado ?>"
                       class="px-3 py-1 border rounded 
                       <?= $i == $pagina ? 'bg-green-600 text-white' : 'hover:bg-gray-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Next -->
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&buscar=<?= $buscar ?>&estado=<?= $estado ?>"
                       class="px-3 py-1 border rounded hover:bg-gray-200">➡</a>
                <?php else: ?>
                    <span class="px-3 py-1 border rounded text-gray-400 bg-gray-200">➡</span>
                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php include('footer_admin.php'); ?>
