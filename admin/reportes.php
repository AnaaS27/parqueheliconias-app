<?php
include('header_admin.php');
include('../includes/verificar_admin.php');
include('../includes/supabase.php');

// ==========================
//   FILTROS DEL REPORTE
// ==========================
$inicio    = $_GET['inicio'] ?? date('Y-m-01');
$fin       = $_GET['fin']    ?? date('Y-m-t');
$actividad = isset($_GET['actividad']) && $_GET['actividad'] !== '' ? intval($_GET['actividad']) : null;

// ==========================
//   CARGAR ACTIVIDADES
// ==========================
list($codeActs, $acts) = supabase_get("actividades?select=id_actividad,nombre&order=nombre.asc");
if ($codeActs !== 200) $acts = [];

// ==========================
//   KPIs
// ==========================

// 🔹 Total reservas (excepto canceladas)
$query = "reservas?select=id_reserva,fecha_reserva,fecha_visita,estado,numero_participantes&id_actividad=neq.null"
       . "&fecha_reserva=gte.$inicio&fecha_reserva=lte.$fin";

if ($actividad) $query .= "&id_actividad=eq.$actividad";

list($codeR, $reservas) = supabase_get($query);
if ($codeR !== 200) $reservas = [];

// 🔹 Total reservadas
$total_reservadas = 0;
foreach ($reservas as $r) {
    if ($r['estado'] !== 'cancelada') {
        $total_reservadas += ($r['numero_participantes'] ?? 1);
    }
}

// 🔹 Total asistencias
list($codeA, $asistencias) = supabase_get("asistencia?select=id_asistencia,id_reserva&fecha_asistencia=gte.$inicio&fecha_asistencia=lte.$fin");
if ($codeA !== 200) $asistencias = [];

$total_asistidas = count($asistencias);

// 🔹 KPI cálculos
$tasa_asistencia = $total_reservadas > 0 ? round(($total_asistidas / $total_reservadas) * 100, 2) : 0;
$tasa_no_show    = $total_reservadas > 0 ? round((1 - ($total_asistidas / $total_reservadas)) * 100, 2) : 0;

// ===============================
//   REPORTES ORIGINALES
// ===============================

// 1️⃣ Reservas por actividad
list($codeRa, $resAct) = supabase_get("
    reservas?select=id_reserva,id_actividad,actividades(nombre)
    &actividades!inner=id_actividad
");
if ($codeRa !== 200) $resAct = [];

$report_actividades = [];
foreach ($resAct as $r) {
    $nom = $r['actividades']['nombre'];
    if (!isset($report_actividades[$nom])) $report_actividades[$nom] = 0;
    $report_actividades[$nom]++;
}

// 2️⃣ Reservas por estado
list($codeRe, $resEst) = supabase_get("
    reservas?select=estado
");
if ($codeRe !== 200) $resEst = [];

$report_estados = [];
foreach ($resEst as $r) {
    $e = $r["estado"] ?? "sin estado";
    if (!isset($report_estados[$e])) $report_estados[$e] = 0;
    $report_estados[$e]++;
}

// 3️⃣ Usuarios más activos
list($codeU, $users) = supabase_get("
    reservas?select=id_reserva,usuarios(id_usuario,nombre,apellido)
    &usuarios!inner=id_usuario
");
if ($codeU !== 200) $users = [];

$report_usuarios = [];
foreach ($users as $r) {
    $u = $r['usuarios'];
    $id = $u['id_usuario'];
    if (!isset($report_usuarios[$id])) {
        $report_usuarios[$id] = [
            "nombre" => $u["nombre"],
            "apellido" => $u["apellido"],
            "total" => 0
        ];
    }
    $report_usuarios[$id]["total"]++;
}

// Top 5
usort($report_usuarios, fn($a,$b) => $b["total"] - $a["total"]);
$report_usuarios = array_slice($report_usuarios, 0, 5);
?>

<link rel="stylesheet" href="../css/admin.css">

<section class="admin-reportes">
  <h2 class="titulo-dashboard">📊 Reportes del Sistema</h2>

  <!-- ========================= -->
  <!--     FILTROS DE REPORTE    -->
  <!-- ========================= -->
  <form method="get" class="filtros" 
        style="display:grid;grid-template-columns:repeat(8,1fr);gap:.75rem;margin-bottom:2rem;">

    <label style="grid-column:span 2;">Desde:
      <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
    </label>

    <label style="grid-column:span 2;">Hasta:
      <input type="date" name="fin" value="<?= htmlspecialchars($fin) ?>">
    </label>

    <label style="grid-column:span 3;">Actividad:
      <select name="actividad">
        <option value="">Todas</option>
        <?php foreach($acts as $a): ?>
          <option value="<?= $a['id_actividad'] ?>"
            <?= $actividad === (int)$a['id_actividad'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['nombre']) ?>
          </option>
        <?php endforeach ?>
      </select>
    </label>

    <button type="submit" 
      style="grid-column:span 1;background:#c62828;color:#fff;border:0;border-radius:8px;padding:.6rem 1rem;">
      Aplicar
    </button>
  </form>

  <!-- ========================= -->
  <!--            KPIs           -->
  <!-- ========================= -->
  <div class="kpis" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin:1.5rem 0;">
    <div class="kpi"><h3>Reservadas</h3><p><?= $total_reservadas ?></p></div>
    <div class="kpi"><h3>Asistidas</h3><p><?= $total_asistidas ?></p></div>
    <div class="kpi"><h3>% Asistencia</h3><p><?= $tasa_asistencia ?>%</p></div>
    <div class="kpi"><h3>% No-Show</h3><p><?= $tasa_no_show ?>%</p></div>
  </div>

  <!-- ========================= -->
  <!--    REPORTES ORIGINALES    -->
  <!-- ========================= -->

  <h2 style="margin-top:2rem;">📁 Reportes Generales</h2>

  <div class="reportes-grid">

    <!-- 1️⃣ Reservas por actividad -->
    <div class="reporte-card">
      <h3>🎫 Reservas por Actividad</h3>
      <table class="tabla-admin">
        <thead><tr><th>Actividad</th><th>Total</th></tr></thead>
        <tbody>
          <?php foreach($report_actividades as $act => $total): ?>
            <tr><td><?= htmlspecialchars($act) ?></td><td><?= $total ?></td></tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <!-- 2️⃣ Reservas por estado -->
    <div class="reporte-card">
      <h3>📅 Reservas por Estado</h3>
      <table class="tabla-admin">
        <thead><tr><th>Estado</th><th>Total</th></tr></thead>
        <tbody>
          <?php foreach($report_estados as $estado => $total): ?>
            <tr><td><?= ucfirst($estado) ?></td><td><?= $total ?></td></tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <!-- 3️⃣ Usuarios más activos -->
    <div class="reporte-card">
      <h3>👥 Usuarios Más Activos</h3>
      <table class="tabla-admin">
        <thead><tr><th>Usuario</th><th>Total Reservas</th></tr></thead>
        <tbody>
          <?php foreach($report_usuarios as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u["nombre"]." ".$u["apellido"]) ?></td>
              <td><?= $u["total"] ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>

  </div>

  <!-- ========================= -->
  <!--      GRÁFICOS (igual)     -->
  <!-- ========================= -->

  <h3 style="margin-top:2rem;">Gráficos de asistencia</h3>

  <div class="chart-container"><canvas id="chartMes"></canvas></div>
  <div class="chart-container"><canvas id="chartWeekday"></canvas></div>
  <div class="chart-container"><canvas id="chartHour"></canvas></div>

</section>

<?php include('footer_admin.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
async function fetchJSON(url) {
  const r = await fetch(url, { cache: "no-store" });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

const API = "./api/reportes_asistencia.php";

const params = new URLSearchParams({
  inicio: "<?= $inicio ?>",
  fin: "<?= $fin ?>",
  actividad: "<?= $actividad ?? '' ?>"
}).toString();

// Gráficos
fetchJSON(`${API}?tipo=trend&${params}`).then(data=>{
  if (!Array.isArray(data) || data.length===0) return;
  new Chart(document.getElementById('chartMes'), {
    type:"line",
    data:{ labels:data.map(r=>r.mes),
           datasets:[{label:"Asistidas",data:data.map(r=>r.asistidas),borderWidth:2,tension:.25}] },
    options:{ responsive:true, maintainAspectRatio:false }
  });
}).catch(e=>console.error(e));

fetchJSON(`${API}?tipo=weekday&${params}`).then(data=>{
  if (!Array.isArray(data) || data.length===0) return;
  new Chart(document.getElementById('chartWeekday'), {
    type:"bar",
    data:{ labels:data.map(r=>r.dow_nombre),
           datasets:[{label:"Asistidas",data:data.map(r=>r.asistidas)}] },
    options:{ responsive:true, maintainAspectRatio:false }
  });
}).catch(e=>console.error(e));

fetchJSON(`${API}?tipo=hour&${params}`).then(data=>{
  if (!Array.isArray(data) || data.length===0) return;
  new Chart(document.getElementById('chartHour'), {
    type:"bar",
    data:{ labels:data.map(r=>String(r.hora).padStart(2,'0')+':00'),
           datasets:[{label:"Asistidas",data:data.map(r=>r.asistidas)}] },
    options:{ responsive:true, maintainAspectRatio:false }
  });
}).catch(e=>console.error(e));
</script>
