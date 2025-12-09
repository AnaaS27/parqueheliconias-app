<?php
include('header_admin.php');
require_once("../includes/supabase.php");

// =====================================
// 🔸 ID del administrador (puede venir de session)
$id_admin = 1;
// =====================================


// =============================
// 🗑 1️⃣ ELIMINAR UNA NOTIFICACIÓN
// =============================
if (isset($_GET['borrar'])) {
    $id_notif = intval($_GET['borrar']);

    // Solo eliminar si pertenece al admin
    supabase_delete("notificaciones", [
        "id_notificacion" => $id_notif,
        "id_usuario" => $id_admin
    ]);

    header("Location: notificaciones_admin.php");
    exit;
}


// =============================
// 🗑 2️⃣ BORRAR TODAS LAS NOTIFICACIONES DEL ADMIN
// =============================
if (isset($_GET['borrar_todas'])) {

    supabase_delete("notificaciones", [
        "id_usuario" => $id_admin
    ]);

    header("Location: notificaciones_admin.php");
    exit;
}


// =============================
// 📌 3️⃣ OBTENER NOTIFICACIONES DEL ADMIN
// =============================
list($codeNotif, $notifs) = supabase_get(
    "notificaciones",
    ["id_usuario" => $id_admin],
    0,
    1000
);

if ($codeNotif !== 200) {
    $notifs = [];
}


// =============================
// 🔔 4️⃣ MARCAR TODAS COMO LEÍDAS
// =============================
supabase_update(
    "notificaciones",
    ["id_usuario" => $id_admin],
    ["leida" => true]
);


// =============================
// 📅 5️⃣ AGRUPAR NOTIFICACIONES POR FECHA (en PHP)
// =============================
$grupos = [];

foreach ($notifs as $n) {
    $fecha = date("Y-m-d", strtotime($n["fecha_creacion"]));

    if (!isset($grupos[$fecha])) {
        $grupos[$fecha] = [
            "titulos" => [],
            "mensajes" => [],
            "tipos"   => [],
            "ids"     => []
        ];
    }

    $grupos[$fecha]["titulos"][]  = $n["titulo"];
    $grupos[$fecha]["mensajes"][] = $n["mensaje"];
    $grupos[$fecha]["tipos"][]    = $n["tipo"];
    $grupos[$fecha]["ids"][]      = $n["id_notificacion"];
}

krsort($grupos); // Ordenar como SQL ORDER DESC
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

<?php if (!empty($grupos)): ?>
  <div style="text-align:right;">
    <a href="?borrar_todas=1" class="btn-borrar-todo" onclick="return confirm('¿Eliminar TODAS las notificaciones?');">🗑 Borrar todas</a>
  </div>

  <?php foreach ($grupos as $fecha => $datos):
      $fecha_formateada = date("d/m/Y", strtotime($fecha));
      $count = count($datos["titulos"]);
  ?>

    <div class="acordeon-dia" onclick="toggleAcordeon(this)">
      📅 <?= $fecha_formateada ?> (<?= $count ?>)
    </div>

    <div class="contenido-dia">
      <?php for ($i = 0; $i < $count; $i++): ?>
        <div class="notificacion-card <?= htmlspecialchars($datos['tipos'][$i]) ?>">
          <button class="borrar-btn" onclick="borrarNotificacion(<?= $datos['ids'][$i] ?>)">×</button>
          <h4><?= htmlspecialchars($datos['titulos'][$i]) ?></h4>
          <p><?= htmlspecialchars($datos['mensajes'][$i]) ?></p>
        </div>
      <?php endfor; ?>
    </div>

  <?php endforeach; ?>

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