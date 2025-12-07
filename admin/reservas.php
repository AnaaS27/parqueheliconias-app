<?php
include('header_admin.php');
include('../includes/conexion.php');

// Consulta general adaptada a PostgreSQL
$sql = "
  SELECT r.id_reserva, 
         u.nombre AS usuario, 
         u.apellido,
         a.nombre AS actividad, 
         r.fecha_reserva, 
         r.estado, 
         r.tipo_reserva, 
         r.numero_participantes,
         r.fecha_cancelacion
  FROM reservas r
  INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
  INNER JOIN actividades a ON r.id_actividad = a.id_actividad
  ORDER BY r.fecha_reserva DESC
";

$result = pg_query($conn, $sql);
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

        <?php if ($result && pg_num_rows($result) > 0): ?>
          <?php while ($reserva = pg_fetch_assoc($result)): ?>
            <tr>
              <td>#<?= $reserva['id_reserva']; ?></td>
              <td><?= htmlspecialchars($reserva['usuario'] . " " . $reserva['apellido']); ?></td>
              <td><?= htmlspecialchars($reserva['actividad']); ?></td>
              <td><?= ucfirst($reserva['tipo_reserva']); ?></td>
              <td><?= $reserva['numero_participantes']; ?></td>
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
                    <button type="submit" class="btn-accion confirmar" title="Confirmar reserva">✔️</button>
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
                   title="Eliminar reserva">🗑️</a>
              </td>
            </tr>
          <?php endwhile; ?>

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
