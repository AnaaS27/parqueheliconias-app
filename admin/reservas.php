<?php
include('header_admin.php');
require_once("../includes/supabase.php");

// ================================
// 1️⃣ OBTENER TODAS LAS RESERVAS
// ================================
list($codeRes, $reservas) = supabase_get("reservas", [], 0, 500);

if ($codeRes !== 200) {
    echo "<p style='color:red; text-align:center;'>Error cargando reservas desde Supabase</p>";
    $reservas = [];
}

// ================================
// 2️⃣ OBTENER USUARIOS
// ================================
list($codeUser, $usuarios) = supabase_get("usuarios", [], 0, 1000);
$usuariosPorID = [];

if ($codeUser === 200) {
    foreach ($usuarios as $u) {
        $usuariosPorID[$u["id_usuario"]] = $u;
    }
}

// ================================
// 3️⃣ OBTENER ACTIVIDADES
// ================================
list($codeAct, $actividades) = supabase_get("actividades", [], 0, 500);
$actividadesPorID = [];

if ($codeAct === 200) {
    foreach ($actividades as $a) {
        $actividadesPorID[$a["id_actividad"]] = $a;
    }
}

// ================================
// 4️⃣ CONSTRUIR LISTA COMPLETA (Simulación de JOIN)
// ================================
$lista = [];

foreach ($reservas as $r) {
    $idU = $r["id_usuario"];
    $idA = $r["id_actividad"];

    $lista[] = [
        "id_reserva"          => $r["id_reserva"],
        "usuario_nombre"      => $usuariosPorID[$idU]["nombre"] ?? "N/A",
        "usuario_apellido"    => $usuariosPorID[$idU]["apellido"] ?? "",
        "actividad"           => $actividadesPorID[$idA]["nombre"] ?? "N/A",
        "fecha_reserva"       => $r["fecha_reserva"],
        "estado"              => $r["estado"],
        "tipo_reserva"        => $r["tipo_reserva"],
        "participantes"       => $r["numero_participantes"],
        "fecha_cancelacion"   => $r["fecha_cancelacion"] ?? null
    ];
}

// Ordenar por fecha como en SQL DESC
usort($lista, function($a, $b) {
    return strtotime($b["fecha_reserva"]) - strtotime($a["fecha_reserva"]);
});
?>

<section class="admin-reservas">
  <h2 class="titulo-dashboard">📅 Gestión de Reservas</h2>
  <p class="subtitulo-dashboard">Administra, confirma o cancela las reservas realizadas por los usuarios.</p>

  <div class="tabla-contenedor">
    <table class="tabla-admin">
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuario</th>
          <th>Actividad</th>
          <th>Tipo</th>
          <th>Participantes</th>
          <th>Fecha</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>

        <?php if (!empty($lista)): ?>
          <?php foreach ($lista as $reserva): ?>
            <tr>
              <td>#<?= $reserva['id_reserva']; ?></td>
              <td><?= htmlspecialchars($reserva['usuario_nombre'] . " " . $reserva['usuario_apellido']); ?></td>
              <td><?= htmlspecialchars($reserva['actividad']); ?></td>
              <td><?= ucfirst($reserva['tipo_reserva']); ?></td>
              <td><?= $reserva['participantes']; ?></td>
              <td><?= date("d/m/Y H:i", strtotime($reserva['fecha_reserva'])); ?></td>

              <td>
                <?php 
                  if ($reserva['estado'] == 'pendiente') echo '<span class="estado-pendiente">🕒 Pendiente</span>';
                  elseif ($reserva['estado'] == 'confirmada') echo '<span class="estado-confirmada">✅ Confirmada</span>';
                  elseif ($reserva['estado'] == 'cancelada') echo '<span class="estado-cancelada">❌ Cancelada</span>';
                ?>
              </td>

              <td>

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

                <a href="detalle_reserva.php?id=<?= $reserva['id_reserva']; ?>" class="btn-accion detalle" title="Ver detalle">🔍</a>

                <a href="eliminar_reserva.php?id=<?= $reserva['id_reserva']; ?>" 
                   class="btn-accion eliminar"
                   onclick="return confirm('¿Deseas eliminar esta reserva definitivamente?');"
                   title="Eliminar reserva">🗑</a>
              </td>
            </tr>
          <?php endforeach; ?>

        <?php else: ?>
          <tr><td colspan="8" class="sin-registros">No hay reservas registradas aún.</td></tr>
        <?php endif; ?>

      </tbody>
    </table>
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