<?php
include('header_admin.php');
include('../includes/conexion.php');

// 🔹 ID del administrador (puede venir de session si lo deseas después)
$id_admin = 1;

// =============================
// 🗑 ELIMINAR UNA NOTIFICACIÓN
// =============================
if (isset($_GET['borrar'])) {
    $id_notif = intval($_GET['borrar']);
    pg_query_params($conn, "DELETE FROM notificaciones WHERE id_notificacion = $1 AND id_usuario = $2", [$id_notif, $id_admin]);
    header("Location: notificaciones_admin.php");
    exit;
}

// =============================
// 🗑 ELIMINAR TODAS
// =============================
if (isset($_GET['borrar_todas'])) {
    pg_query_params($conn, "DELETE FROM notificaciones WHERE id_usuario = $1", [$id_admin]);
    header("Location: notificaciones_admin.php");
    exit;
}

// =============================
// 📌 AGRUPACIÓN DE NOTIFICACIONES POR FECHA
// =============================
$sql = "
    SELECT 
        DATE(fecha_creacion) AS fecha,
        STRING_AGG(id_notificacion::text, ',') AS ids,
        STRING_AGG(titulo, '||') AS titulos,
        STRING_AGG(mensaje, '||') AS mensajes,
        STRING_AGG(tipo, '||') AS tipos
    FROM notificaciones
    WHERE id_usuario = $1
    GROUP BY DATE(fecha_creacion)
    ORDER BY fecha DESC
";

$result = pg_query_params($conn, $sql, [$id_admin]);

// =============================
// 🔔 MARCAR COMO LEÍDAS
// =============================
pg_query_params($conn, "UPDATE notificaciones SET leida = TRUE WHERE id_usuario = $1", [$id_admin]);
?>


<section class="admin-reservas">
<h2 class="titulo-dashboard">🔔 Notificaciones del Administrador</h2>
<p class="subtitulo-dashboard">Historial de actividades relevantes y eventos.</p>

<style>
body { background:#f5f9f5; font-family:"Poppins",sans-serif; }
.detalle-card {
  max-width:900px; margin:40px auto; background:#fff;
  padding:30px; border-radius:15px;
  box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
.acordeon-dia {
  background:#cfe6d1; border-radius:10px; margin-bottom:10px;
  padding:12px 18px; cursor:pointer; font-weight:600;
  color:#2f6930; transition:0.2s; font-size:1rem;
}
.acordeon-dia:hover { background:#badaae; }
.contenido-dia { display:none; padding:10px 10px; }
.notificacion-card {
  border-radius:10px; padding:12px; margin:8px 0;
  box-shadow:0 2px 4px rgba(0,0,0,0.05);
  position:relative;
}
.notificacion-card.info { background:#e8f4ff; border-left:5px solid #1e88e5; }
.notificacion-card.exito { background:#e6f9ed; border-left:5px solid #2e7d32; }
.notificacion-card.error { background:#fdecea; border-left:5px solid #c62828; }
.notificacion-card.alerta { background:#fff7e6; border-left:5px solid #f9a825; }
.borrar-btn {
  position:absolute; top:10px; right:10px;
  background:none; border:none; color:#b02a2a;
  font-weight:bold; font-size:1.3rem; cursor:pointer;
}
.borrar-btn:hover { color:#7a1a1a; }
.btn-borrar-todo {
  background:#b02a2a; color:#fff;
  padding:8px 14px; border-radius:8px;
  font-weight:600; text-decoration:none;
  display:inline-block; margin-bottom:15px;
}
.btn-borrar-todo:hover { background:#7a1a1a; }
</style>

<div class="detalle-card">

<?php if (pg_num_rows($result) > 0): ?>
  <div style="text-align:right;">
    <a href="?borrar_todas=1" class="btn-borrar-todo" onclick="return confirm('¿Eliminar TODAS las notificaciones?');">🗑 Borrar todas</a>
  </div>

  <?php while ($grupo = pg_fetch_assoc($result)):
    $fecha = date("d/m/Y", strtotime($grupo['fecha']));
    $titulos = explode('||', $grupo['titulos']);
    $mensajes = explode('||', $grupo['mensajes']);
    $tipos = explode('||', $grupo['tipos']);
    $ids = explode(',', $grupo['ids']);
  ?>

    <div class="acordeon-dia" onclick="toggleAcordeon(this)">
      📅 <?= $fecha ?> (<?= count($titulos) ?>)
    </div>
    <div class="contenido-dia">
      <?php foreach ($titulos as $i => $titulo): ?>
        <div class="notificacion-card <?= htmlspecialchars($tipos[$i]) ?>">
          <button class="borrar-btn" onclick="borrarNotificacion(<?= $ids[$i] ?>)">×</button>
          <h4><?= htmlspecialchars($titulo) ?></h4>
          <p><?= htmlspecialchars($mensajes[$i]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endwhile; ?>

<?php else: ?>
  <p style="text-align:center; color:#666; margin-top:30px;">No hay notificaciones registradas 🌿.</p>
<?php endif; ?>

</div>
</section>

<script>
function toggleAcordeon(elem) {
  const content = elem.nextElementSibling;
  content.style.display = content.style.display === "block" ? "none" : "block";
}
function borrarNotificacion(id) {
  if (confirm("¿Eliminar esta notificación?"))
    window.location = "?borrar=" + id;
}
</script>

<?php include('footer_admin.php'); ?>
