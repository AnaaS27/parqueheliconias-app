<?php
include('header_admin.php');
require_once("../includes/supabase.php");

// ================================
// 🔧 PAGINACIÓN
// ================================
$registrosPorPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// ================================
// 1️⃣ OBTENER TODAS LAS RESERVAS
//    (hasta 1000 por seguridad)
// ================================
list($codeRes, $reservas) = supabase_get("reservas?select=*&order=fecha_reserva.desc&limit=1000");
if ($codeRes !== 200 || !is_array($reservas)) {
    $reservas = [];
}

// ================================
// 2️⃣ OBTENER USUARIOS
// ================================
list($codeUser, $usuarios) = supabase_get("usuarios?select=id_usuario,nombre,apellido");
$usuariosPorID = [];
if ($codeUser === 200 && is_array($usuarios)) {
    foreach ($usuarios as $u) {
        $usuariosPorID[$u["id_usuario"]] = $u;
    }
}

// ================================
// 3️⃣ OBTENER ACTIVIDADES
// ================================
list($codeAct, $actividades) = supabase_get("actividades?select=id_actividad,nombre");
$actividadesPorID = [];
if ($codeAct === 200 && is_array($actividades)) {
    foreach ($actividades as $a) {
        $actividadesPorID[$a["id_actividad"]] = $a;
    }
}

// ================================
// 4️⃣ CONSTRUIR LISTA COMPLETA
// ================================
$lista = [];
foreach ($reservas as $r) {
    $idU = $r["id_usuario"];
    $idA = $r["id_actividad"];

    $lista[] = [
        "id_reserva"        => $r["id_reserva"],
        "usuario_nombre"    => $usuariosPorID[$idU]["nombre"]   ?? "N/A",
        "usuario_apellido"  => $usuariosPorID[$idU]["apellido"] ?? "",
        "actividad"         => $actividadesPorID[$idA]["nombre"] ?? "N/A",
        "fecha_reserva"     => $r["fecha_reserva"],
        "estado"            => $r["estado"],
        "tipo_reserva"      => $r["tipo_reserva"],
        "participantes"     => $r["numero_participantes"],
        "fecha_cancelacion" => $r["fecha_cancelacion"] ?? null
    ];
}

// Ya están ordenadas por fecha_reserva.desc desde Supabase,
// pero si quieres reforzar:
usort($lista, function($a, $b) {
    return strtotime($b["fecha_reserva"]) - strtotime($a["fecha_reserva"]);
});

$totalRegistros = count($lista);
$totalPaginas   = max(1, ceil($totalRegistros / $registrosPorPagina));

// Cortar solo la página actual
$listaPagina = array_slice($lista, $offset, $registrosPorPagina);
?>

<section class="max-w-7xl mx-auto px-4 py-6 admin-reservas">
  <h2 class="text-2xl font-bold text-green-700 mb-1">📅 Gestión de Reservas</h2>
  <p class="text-gray-600 mb-4">Administra, confirma o cancela las reservas realizadas por los usuarios.</p>

  <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm tabla-admin">
        <thead>
          <tr class="bg-green-50 text-green-800 border-b">
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
        <?php if (!empty($listaPagina)): ?>
          <?php foreach ($listaPagina as $reserva): ?>
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="px-6 py-4">#<?= $reserva['id_reserva']; ?></td>
              <td class="px-6 py-4">
                <?= htmlspecialchars($reserva['usuario_nombre'] . " " . $reserva['usuario_apellido']); ?>
              </td>
              <td class="px-6 py-4"><?= htmlspecialchars($reserva['actividad']); ?></td>
              <td class="px-6 py-4"><?= ucfirst($reserva['tipo_reserva']); ?></td>
              <td class="px-6 py-4"><?= intval($reserva['participantes']); ?></td>
              <td class="px-6 py-4">
                <?= date("d/m/Y H:i", strtotime($reserva['fecha_reserva'])); ?>
              </td>
              <td class="px-6 py-4">
                <?php 
                  if ($reserva['estado'] == 'pendiente') {
                    echo '<span class="estado-pendiente">🕒 Pendiente</span>';
                  } elseif ($reserva['estado'] == 'confirmada') {
                    echo '<span class="estado-confirmada">✅ Confirmada</span>';
                  } elseif ($reserva['estado'] == 'cancelada') {
                    echo '<span class="estado-cancelada">❌ Cancelada</span>';
                  }
                ?>
              </td>
              <td class="px-6 py-4 text-center flex justify-center gap-2">

                <?php if ($reserva['estado'] == 'pendiente'): ?>
                  <!-- Confirmar -->
                  <form action="actualizar_estado_reserva.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva']; ?>">
                    <input type="hidden" name="estado" value="confirmada">
                    <button type="submit" class="btn-accion confirmar" title="Confirmar reserva">✔</button>
                  </form>

                  <!-- Cancelar -->
                  <form action="actualizar_estado_reserva.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva']; ?>">
                    <input type="hidden" name="estado" value="cancelada">
                    <button type="submit" class="btn-accion cancelar" title="Cancelar reserva">🚫</button>
                  </form>
                <?php endif; ?>

                <a href="detalle_reserva.php?id=<?= $reserva['id_reserva']; ?>"
                   class="btn-accion detalle" title="Ver detalle">🔍</a>

                <a href="eliminar_reserva.php?id=<?= $reserva['id_reserva']; ?>"
                   class="btn-accion eliminar"
                   onclick="return confirm('¿Deseas eliminar esta reserva definitivamente?');"
                   title="Eliminar reserva">🗑</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-6 text-gray-500">No hay reservas registradas aún.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINACIÓN -->
    <div class="px-6 py-4 flex justify-between items-center text-sm">
      <span class="text-gray-600">
        Mostrando <?= count($listaPagina) ?> de <?= $totalRegistros ?> reservas
      </span>

      <div class="flex gap-2">
        <!-- Anterior -->
        <?php if ($paginaActual > 1): ?>
          <a href="?pagina=<?= $paginaActual - 1 ?>"
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
          <a href="?pagina=<?= $i ?>"
             class="px-3 py-1 border rounded-lg
             <?= $i == $paginaActual ? 'bg-green-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <!-- Siguiente -->
        <?php if ($paginaActual < $totalPaginas): ?>
          <a href="?pagina=<?= $paginaActual + 1 ?>"
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
</section>

<?php include('footer_admin.php'); ?>

<!-- TOAST -->
<?php if (isset($_SESSION['toast'])): ?>
  <div class="toast <?= $_SESSION['toast']['tipo']; ?>" id="toast">
      <?= htmlspecialchars($_SESSION['toast']['mensaje']); ?>
  </div>
  <script>
      document.addEventListener("DOMContentLoaded", () => {
          const toast = document.getElementById("toast");
          if (toast) setTimeout(() => toast.classList.add("hide"), 3500);
      });
  </script>
  <?php unset($_SESSION['toast']); ?>
<?php endif; ?>
