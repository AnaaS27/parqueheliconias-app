<?php 
include('header_admin.php'); 
require_once('../includes/supabase.php'); 
?>

<?php
// ========================================
// 🔢 CONTADOR: ACTIVIDADES
// ========================================
list($codeAct, $resAct) = supabase_get("actividades?select=count:id");

$total_actividades = ($codeAct === 200 && !empty($resAct))
    ? intval($resAct[0]['count'])
    : 0;


// ========================================
// 🔢 CONTADOR: RESERVAS TOTALES
// ========================================
list($codeRes, $resReservas) = supabase_get("reservas?select=count:id");

$total_reservas = ($codeRes === 200 && !empty($resReservas))
    ? intval($resReservas[0]['count'])
    : 0;


// ========================================
// 🔢 CONTADOR: USUARIOS ACTIVOS
// ========================================
list($codeUsers, $resUsers) = supabase_get("usuarios?usuario_activo=eq.true&select=count:id");

$total_usuarios = ($codeUsers === 200 && !empty($resUsers))
    ? intval($resUsers[0]['count'])
    : 0;


// ========================================
// 🔢 CONTADOR: RESERVAS PENDIENTES
// ========================================
$estado = urlencode("pendiente");
list($codePend, $resPend) = supabase_get("reservas?estado=eq.$estado&select=count:id");

$reservas_pendientes = ($codePend === 200 && !empty($resPend))
    ? intval($resPend[0]['count'])
    : 0;

?>

<section class="admin-dashboard">
  <h2 class="titulo-dashboard">🌿 Panel de Control</h2>
  <p class="subtitulo-dashboard">Bienvenido, administrador. Aquí puedes gestionar y monitorear las principales actividades del sistema.</p>

  <div class="tarjetas-dashboard">

    <div class="tarjeta-admin">
      <div class="icono-tarjeta">🎫</div>
      <div class="info-tarjeta">
        <h3><?= $total_actividades ?></h3>
        <p>Actividades registradas</p>
      </div>
    </div>

    <div class="tarjeta-admin">
      <div class="icono-tarjeta">📅</div>
      <div class="info-tarjeta">
        <h3><?= $total_reservas ?></h3>
        <p>Total de reservas</p>
      </div>
    </div>

    <div class="tarjeta-admin">
      <div class="icono-tarjeta">👥</div>
      <div class="info-tarjeta">
        <h3><?= $total_usuarios ?></h3>
        <p>Usuarios activos</p>
      </div>
    </div>

    <div class="tarjeta-admin pendiente">
      <div class="icono-tarjeta">⏳</div>
      <div class="info-tarjeta">
        <h3><?= $reservas_pendientes ?></h3>
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
