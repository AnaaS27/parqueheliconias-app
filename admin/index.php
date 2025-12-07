<?php include('header_admin.php'); ?>
<?php include('../includes/conexion.php'); ?>

<?php
// === CONSULTAS RÁPIDAS PARA LOS CONTADORES ===

// Total actividades
$sql1 = "SELECT COUNT(*) AS total FROM actividades";
$res1 = pg_query($conn, $sql1);
$total_actividades = ($res1 && pg_num_rows($res1)) ? pg_fetch_assoc($res1)['total'] : 0;

// Total reservas
$sql2 = "SELECT COUNT(*) AS total FROM reservas";
$res2 = pg_query($conn, $sql2);
$total_reservas = ($res2 && pg_num_rows($res2)) ? pg_fetch_assoc($res2)['total'] : 0;

// Total usuarios
$sql3 = "SELECT COUNT(*) AS total FROM usuarios WHERE usuario_activo = TRUE"; // mejor práctica
$res3 = pg_query($conn, $sql3);
$total_usuarios = ($res3 && pg_num_rows($res3)) ? pg_fetch_assoc($res3)['total'] : 0;

// Reservas pendientes
$sql4 = "SELECT COUNT(*) AS total FROM reservas WHERE estado = 'pendiente'";
$res4 = pg_query($conn, $sql4);
$reservas_pendientes = ($res4 && pg_num_rows($res4)) ? pg_fetch_assoc($res4)['total'] : 0;
?>

<section class="admin-dashboard">
  <h2 class="titulo-dashboard">🌿 Panel de Control</h2>
  <p class="subtitulo-dashboard">Bienvenido, administrador. Aquí puedes gestionar y monitorear las principales actividades del sistema.</p>

  <div class="tarjetas-dashboard">
    <div class="tarjeta-admin">
      <div class="icono-tarjeta">🎫</div>
      <div class="info-tarjeta">
        <h3><?php echo $total_actividades; ?></h3>
        <p>Actividades registradas</p>
      </div>
    </div>

    <div class="tarjeta-admin">
      <div class="icono-tarjeta">📅</div>
      <div class="info-tarjeta">
        <h3><?php echo $total_reservas; ?></h3>
        <p>Total de reservas</p>
      </div>
    </div>

    <div class="tarjeta-admin">
      <div class="icono-tarjeta">👥</div>
      <div class="info-tarjeta">
        <h3><?php echo $total_usuarios; ?></h3>
        <p>Usuarios activos</p>
      </div>
    </div>

    <div class="tarjeta-admin pendiente">
      <div class="icono-tarjeta">⏳</div>
      <div class="info-tarjeta">
        <h3><?php echo $reservas_pendientes; ?></h3>
        <p>Reservas pendientes</p>
      </div>
    </div>
  </div>

  <div class="acciones-rapidas">
    <h3>⚙️ Accesos rápidos</h3>
    <div class="botones-rapidos">
      <a href="actividades.php" class="btn-admin">Gestionar Actividades</a>
      <a href="reservas.php" class="btn-admin">Ver Reservas</a>
      <a href="usuarios.php" class="btn-admin">Usuarios</a>
      <a href="reportes.php" class="btn-admin">Reportes</a>
    </div>
  </div>
</section>

<?php include('footer_admin.php'); ?>
