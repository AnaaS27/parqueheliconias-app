<?php 
include('header_admin.php'); 
require_once('../includes/supabase.php'); 

/* ======================================================
   🔢 1) ACTIVIDADES — Conteo manual
====================================================== */
list($codeAct, $actividades) = supabase_get("actividades?select=id_actividad");

$total_actividades = ($codeAct === 200 && is_array($actividades))
    ? count($actividades)
    : 0;

/* ======================================================
   🔢 2) RESERVAS TOTALES — Conteo manual
====================================================== */
list($codeRes, $reservas) = supabase_get("reservas?select=id_reserva");

$total_reservas = ($codeRes === 200 && is_array($reservas))
    ? count($reservas)
    : 0;

/* ======================================================
   🔢 3) USUARIOS ACTIVOS — Conteo manual
====================================================== */
list($codeUsers, $usuarios) = supabase_get("usuarios?select=id_usuario,usuario_activo");

$total_usuarios = 0;
if ($codeUsers === 200 && is_array($usuarios)) {
    foreach ($usuarios as $u) {
        if (!empty($u["usuario_activo"])) {
            $total_usuarios++;
        }
    }
}

/* ======================================================
   🔢 4) RESERVAS PENDIENTES — Conteo manual
====================================================== */
list($codePend, $resPend) = supabase_get("reservas?select=id_reserva,estado");

$reservas_pendientes = 0;
if ($codePend === 200 && is_array($resPend)) {
    foreach ($resPend as $r) {
        if ($r["estado"] === "pendiente") {
            $reservas_pendientes++;
        }
    }
}
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
