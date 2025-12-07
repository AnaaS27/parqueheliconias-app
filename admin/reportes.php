<?php
include('header_admin.php');
include('../includes/conexion.php');
include('../includes/verificar_admin.php');

// ==========================
//   CONFIGURACIÓN BASE
// ==========================
$QTY     = "COALESCE(r.numero_participantes, 1)";
$DATECOL = "COALESCE(r.fecha_visita, DATE(r.fecha_reserva))";

// ==========================
//   FILTROS DEL REPORTE
// ==========================
$inicio    = $_GET['inicio'] ?? date('Y-m-01');
$fin       = $_GET['fin']    ?? date('Y-m-t');
$actividad = isset($_GET['actividad']) && $_GET['actividad'] !== '' ? (int)$_GET['actividad'] : null;

// Actividades para el select
$acts = pg_query($conn, "SELECT id_actividad, nombre FROM actividades ORDER BY nombre ASC");

// ==========================
//          KPIs
// ==========================

$DATECOL = "COALESCE(p.fecha_visita, DATE(r.fecha_reserva))";

$sqlResumen = "
  SELECT
    COALESCE(rsum.reservadas,0) AS reservadas,
    COALESCE(asum.asistidas,0)  AS asistidas,
    CASE WHEN COALESCE(rsum.reservadas,0)=0 THEN 0
         ELSE ROUND(COALESCE(asum.asistidas,0)::numeric/rsum.reservadas*100,2) END AS tasa_asistencia,
    CASE WHEN COALESCE(rsum.reservadas,0)=0 THEN 0
         ELSE ROUND((1-COALESCE(asum.asistidas,0)::numeric/rsum.reservadas)*100,2) END AS tasa_no_show
  FROM
    ( SELECT SUM(CASE WHEN r.estado <> 'cancelada' OR r.estado IS NULL THEN COALESCE(r.numero_participantes,1) ELSE 0 END) AS reservadas
      FROM reservas r
      LEFT JOIN participantes_reserva p ON p.id_reserva = r.id_reserva
      WHERE $DATECOL BETWEEN $1 AND $2
            " . ($actividad ? " AND r.id_actividad = $3 " : "") . "
    ) rsum
  CROSS JOIN
    ( SELECT COUNT(a.id_asistencia) AS asistidas
      FROM reservas r
      LEFT JOIN participantes_reserva p ON p.id_reserva = r.id_reserva
      LEFT JOIN asistencia a ON a.id_reserva = r.id_reserva
      WHERE $DATECOL BETWEEN $1 AND $2
            " . ($actividad ? " AND r.id_actividad = $3 " : "") . "
    ) asum
";



// Construcción de parámetros según si hay actividad
$params = $actividad ? [$inicio, $fin, $actividad] : [$inicio, $fin];

$stmt = pg_query_params($conn, $sqlResumen, $params);

if (!$stmt) {
    echo "<p style='color:red'>❌ Error en la consulta: " . pg_last_error($conn) . "</p>";
    $resumen = ['reservadas'=>0,'asistidas'=>0,'tasa_asistencia'=>0,'tasa_no_show'=>0];
} else {
    $resumen = pg_fetch_assoc($stmt);
}

// ==========================
//   TUS REPORTES ORIGINALES
// ==========================

// 1. Reservas por actividad
$sql_actividades = "
  SELECT a.nombre AS actividad, COUNT(r.id_reserva) AS total_reservas
  FROM reservas r
  INNER JOIN actividades a ON r.id_actividad = a.id_actividad
  GROUP BY a.nombre
  ORDER BY total_reservas DESC";
$result_actividades = pg_query($conn, $sql_actividades);

// 2. Reservas por estado
$sql_estados = "
  SELECT estado, COUNT(*) AS total
  FROM reservas
  GROUP BY estado";
$result_estados = pg_query($conn, $sql_estados);

// 3. Usuarios más activos
$sql_usuarios = "
  SELECT u.nombre, u.apellido, COUNT(r.id_reserva) AS total_reservas
  FROM reservas r
  INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
  GROUP BY u.id_usuario, u.nombre, u.apellido
  ORDER BY total_reservas DESC
  LIMIT 5";
$result_usuarios = pg_query($conn, $sql_usuarios);
?>

<link rel="stylesheet" href="../css/admin.css">

<section class="admin-reportes">
  <h2 class="titulo-dashboard">📊 Reportes del Sistema</h2>

  <!-- ========================= -->
  <!--     FILTROS DE REPORTE    -->
  <!-- ========================= -->
  <form method="get" class="filtros" style="display:grid;grid-template-columns:repeat(8,1fr);gap:.75rem;margin-bottom:2rem;">

    <label style="grid-column:span 2;">Desde:
      <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
    </label>

    <label style="grid-column:span 2;">Hasta:
      <input type="date" name="fin" value="<?= htmlspecialchars($fin) ?>">
    </label>

    <label style="grid-column:span 3;">Actividad:
      <select name="actividad">
        <option value="">Todas</option>
        <?php while($a = pg_fetch_assoc($acts)): ?>
          <option value="<?= $a['id_actividad'] ?>" <?= $actividad === (int)$a['id_actividad'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['nombre']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label>

    <button type="submit" style="grid-column:span 1;background:#c62828;color:#fff;border:0;border-radius:8px;padding:.6rem 1rem;">Aplicar</button>

  </form>

  <!-- ========================= -->
  <!--  BOTONES DE EXPORTACIÓN   -->
  <!-- ========================= -->
  <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    <a class="btn-exportar"
      href="api/exportar_reservas_csv.php?inicio=<?= urlencode($inicio) ?>&fin=<?= urlencode($fin) ?>&actividad=<?= urlencode($actividad ?? '') ?>"
      style="display:inline-block;background:#0F172A;color:#fff;padding:.7rem 1rem;border-radius:8px;text-decoration:none;font-weight:600;">
      📥 Exportar reservas
    </a>

    <a class="btn-exportar"
      href="api/exportar_actividades_csv.php?inicio=<?= urlencode($inicio) ?>&fin=<?= urlencode($fin) ?>"
      style="display:inline-block;background:#2e7d32;color:#fff;padding:.7rem 1rem;border-radius:8px;text-decoration:none;font-weight:600;">
      📥 Exportar actividades
    </a>

    <a class="btn-exportar"
      href="api/exportar_instituciones_csv.php"
      style="display:inline-block;background:#1565c0;color:#fff;padding:.7rem 1rem;border-radius:8px;text-decoration:none;font-weight:600;">
      📥 Exportar instituciones
    </a>

    <a class="btn-exportar"
      href="api/exportar_usuarios_csv.php"
      style="display:inline-block;background:#c62828;color:#fff;padding:.7rem 1rem;border-radius:8px;text-decoration:none;font-weight:600;">
      📥 Exportar usuarios
    </a>

    <a class="btn-exportar"
      href="api/exportar_asistencia_csv.php?inicio=<?= urlencode($inicio) ?>&fin=<?= urlencode($fin) ?>&actividad=<?= urlencode($actividad ?? '') ?>"
      style="display:inline-block;background:#6A1B9A;color:#fff;padding:.7rem 1rem;border-radius:8px;text-decoration:none;font-weight:600;">
      📥 Exportar asistencia
    </a>

  </div>

  <!-- ========================= -->
  <!--            KPIs           -->
  <!-- ========================= -->
  <div class="kpis" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin:1.5rem 0;">
    <div class="kpi"><h3>Reservadas</h3><p><?= (int)$resumen['reservadas'] ?></p></div>
    <div class="kpi"><h3>Asistidas</h3><p><?= (int)$resumen['asistidas'] ?></p></div>
    <div class="kpi"><h3>% Asistencia</h3><p><?= number_format((float)$resumen['tasa_asistencia'],2) ?>%</p></div>
    <div class="kpi"><h3>% No-Show</h3><p><?= number_format((float)$resumen['tasa_no_show'],2) ?>%</p></div>
  </div>

  <!-- ========================= -->
  <!--   TUS REPORTES ORIGINALES -->
  <!-- ========================= -->

  <h2 style="margin-top:2rem;">📁 Reportes Generales</h2>

  <div class="reportes-grid">

    <div class="reporte-card">
      <h3>🎫 Reservas por Actividad</h3>
      <table class="tabla-admin">
      <thead><tr><th>Actividad</th><th>Total</th></tr></thead>
      <tbody>
        <?php while($row=pg_fetch_assoc($result_actividades)): ?>
        <tr><td><?= htmlspecialchars($row['actividad']) ?></td><td><?= $row['total_reservas'] ?></td></tr>
        <?php endwhile; ?>
      </tbody>
      </table>
    </div>

    <div class="reporte-card">
      <h3>📅 Reservas por Estado</h3>
      <table class="tabla-admin">
      <thead><tr><th>Estado</th><th>Total</th></tr></thead>
      <tbody>
        <?php while($row=pg_fetch_assoc($result_estados)): ?>
        <tr><td><?= ucfirst($row['estado']) ?></td><td><?= $row['total'] ?></td></tr>
        <?php endwhile; ?>
      </tbody>
      </table>
    </div>

    <div class="reporte-card">
      <h3>👥 Usuarios Más Activos</h3>
      <table class="tabla-admin">
      <thead><tr><th>Usuario</th><th>Total Reservas</th></tr></thead>
      <tbody>
        <?php while($row=pg_fetch_assoc($result_usuarios)): ?>
        <tr><td><?= htmlspecialchars($row['nombre']." ".$row['apellido']) ?></td><td><?= $row['total_reservas'] ?></td></tr>
        <?php endwhile; ?>
      </tbody>
      </table>
    </div>

  </div>

    <h3 style="margin-top:2rem;">Gráficos de asistencia</h3>

    <div style="display:grid;gap:1rem;">
      <style>
        canvas {
          width: 100% !important;
          max-width: 700px;
          height: auto !important;
          aspect-ratio: 16/9;
          display: block;
          margin: auto;
        }

        .chart-container {
          width: 100%;
          max-width: 700px;
          height: 380px;
          margin: 0 auto;
        }

        canvas {
          width: 100% !important;
          height: 100% !important;
        }
      </style>

      <div class="chart-container">
          <canvas id="chartMes"></canvas>
      </div>
      <div class="chart-container">
          <canvas id="chartWeekday"></canvas>
      </div>
      <div class="chart-container">
          <canvas id="chartHour"></canvas>
      </div>
    </div>

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
