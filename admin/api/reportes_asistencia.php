<?php
include('../../includes/verificar_admin.php');
include('../../includes/conexion.php');
header('Content-Type: application/json');

// Manejo de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_exception_handler(function($e){
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
  exit;
});

if (!$conn) {
  http_response_code(500);
  echo json_encode(['error'=>'Sin conexión a la base de datos']);
  exit;
}

// PARÁMETROS
$tipo      = $_GET['tipo'] ?? 'por_actividad';
$inicio    = $_GET['inicio'] ?? date('Y-m-01');
$fin       = $_GET['fin']    ?? date('Y-m-t');
$actividad = isset($_GET['actividad']) && $_GET['actividad'] !== '' ? (int)$_GET['actividad'] : null;

// COLUMNAS CALCULADAS
$QTY     = "COALESCE(r.numero_participantes,1)";
$DATECOL = "COALESCE(r.fecha_visita, DATE(r.fecha_reserva))";

// FUNCION PARA CONSULTAS
function runQuery(mysqli $conn, string $sql, array $params) {
  $stmt = $conn->prepare($sql);
  if ($params) {
    $types = "";
    foreach ($params as $p) $types .= is_int($p) ? "i" : "s";
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* ============================
   REPORTE POR ACTIVIDAD
============================ */
if ($tipo === 'por_actividad') {

  $sql_ids = "
    SELECT DISTINCT r.id_actividad
    FROM reservas r
    WHERE $DATECOL BETWEEN ? AND ?
    " . ($actividad ? " AND r.id_actividad = ?" : "") . "
  ";

  $params = [$inicio,$fin];
  if ($actividad) $params[] = $actividad;

  $ids = runQuery($conn, $sql_ids, $params);
  if (empty($ids)) { echo json_encode([]); exit; }

  $ids_only = array_map(fn($r)=> (int)$r['id_actividad'], $ids);
  $placeholders = implode(',', array_fill(0, count($ids_only), '?'));

  // Reservadas
  $sql_res = "
    SELECT r.id_actividad,
           SUM(CASE WHEN r.estado <> 'cancelada' THEN $QTY ELSE 0 END) AS reservadas
    FROM reservas r
    WHERE $DATECOL BETWEEN ? AND ?
      AND r.id_actividad IN ($placeholders)
    GROUP BY r.id_actividad
  ";
  $params_res = array_merge([$inicio,$fin], $ids_only);
  $reservas = runQuery($conn, $sql_res, $params_res);
  $map_res = [];
  foreach ($reservas as $r) $map_res[(int)$r['id_actividad']] = (float)$r['reservadas'];

  // Asistidas
  $sql_as = "
    SELECT r.id_actividad,
           COUNT(a.id_asistencia) AS asistidas
    FROM reservas r
    LEFT JOIN asistencia a ON a.id_reserva = r.id_reserva
    WHERE $DATECOL BETWEEN ? AND ?
      AND r.id_actividad IN ($placeholders)
    GROUP BY r.id_actividad
  ";
  $params_as = array_merge([$inicio,$fin], $ids_only);
  $asis = runQuery($conn, $sql_as, $params_as);
  $map_as = [];
  foreach ($asis as $r) $map_as[(int)$r['id_actividad']] = (int)$r['asistidas'];

  // Nombres
  $sql_n = "SELECT id_actividad, nombre FROM actividades WHERE id_actividad IN ($placeholders)";
  $names = runQuery($conn, $sql_n, $ids_only);
  $map_nm = [];
  foreach ($names as $r) $map_nm[(int)$r['id_actividad']] = $r['nombre'];

  $out = [];
  foreach ($ids_only as $id) {
    $res = $map_res[$id] ?? 0;
    $asi = $map_as[$id] ?? 0;
    $por = $res ? round($asi / $res * 100, 2) : 0;

    $out[] = [
      'actividad'  => $map_nm[$id] ?? ('Actividad '.$id),
      'reservadas' => $res,
      'asistidas'  => $asi,
      'porcentaje' => $por
    ];
  }

  usort($out, fn($a,$b)=> $b['asistidas'] <=> $a['asistidas']);

  echo json_encode($out);
  exit;
}

/* ============================
   TENDENCIA MENSUAL
============================ */
if ($tipo === 'trend') {

  $params = [$inicio,$fin];
  if ($actividad) $params[] = $actividad;

  $sql = "
    SELECT DATE_FORMAT($DATECOL,'%Y-%m') AS mes
    FROM reservas r
    WHERE $DATECOL BETWEEN ? AND ?
    " . ($actividad ? " AND r.id_actividad = ?" : "") . "
    GROUP BY mes ORDER BY mes
  ";

  $meses = runQuery($conn,$sql,$params);
  if (empty($meses)) { echo json_encode([]); exit; }

  $labels = array_column($meses,'mes');

  // reservadas
  $sqlR = "
    SELECT DATE_FORMAT($DATECOL,'%Y-%m') AS mes,
           SUM(CASE WHEN r.estado <> 'cancelada' THEN $QTY ELSE 0 END) AS reservadas
    FROM reservas r
    WHERE $DATECOL BETWEEN ? AND ?
    " . ($actividad ? " AND r.id_actividad = ?" : "") . "
    GROUP BY mes
  ";

  $resR = runQuery($conn,$sqlR,$params);
  $map_res = [];
  foreach ($resR as $r) $map_res[$r['mes']] = $r['reservadas'];

  // asistidas
  $sqlA = "
    SELECT DATE_FORMAT($DATECOL,'%Y-%m') AS mes,
           COUNT(a.id_asistencia) AS asistidas
    FROM reservas r
    LEFT JOIN asistencia a ON a.id_reserva = r.id_reserva
    WHERE $DATECOL BETWEEN ? AND ?
    " . ($actividad ? " AND r.id_actividad = ?" : "") . "
    GROUP BY mes
  ";

  $resA = runQuery($conn,$sqlA,$params);
  $map_asi = [];
  foreach ($resA as $r) $map_asi[$r['mes']] = $r['asistidas'];

  // salida final
  $out = [];
  foreach ($labels as $m) {
    $out[] = [
      'mes'        => $m,
      'reservadas' => $map_res[$m] ?? 0,
      'asistidas'  => $map_asi[$m] ?? 0
    ];
  }

  echo json_encode($out);
  exit;
}

/* ============================
   DÍA DE LA SEMANA
============================ */
if ($tipo === 'weekday') {

  $params = [$inicio,$fin];
  if ($actividad) $params[] = $actividad;

  $sql = "
    SELECT
      (DAYOFWEEK($DATECOL)-1) AS dow,
      CASE (DAYOFWEEK($DATECOL)-1)
        WHEN 0 THEN 'Dom'
        WHEN 1 THEN 'Lun'
        WHEN 2 THEN 'Mar'
        WHEN 3 THEN 'Mié'
        WHEN 4 THEN 'Jue'
        WHEN 5 THEN 'Vie'
        ELSE 'Sáb'
      END AS dow_nombre,
      COUNT(a.id_asistencia) AS asistidas
    FROM reservas r
    LEFT JOIN asistencia a ON a.id_reserva = r.id_reserva
    WHERE $DATECOL BETWEEN ? AND ?
    " . ($actividad ? " AND r.id_actividad = ?" : "") . "
    GROUP BY dow, dow_nombre
    ORDER BY dow
  ";

  echo json_encode(runQuery($conn,$sql,$params));
  exit;
}

/* ============================
   HORA DEL DÍA
============================ */
if ($tipo === 'hour') {

  $params = [$inicio,$fin];
  if ($actividad) $params[] = $actividad;

  $sql = "
    SELECT
      HOUR(r.fecha_reserva) AS hora,
      COUNT(a.id_asistencia) AS asistidas
    FROM reservas r
    LEFT JOIN asistencia a ON a.id_reserva = r.id_reserva
    WHERE $DATECOL BETWEEN ? AND ?
    " . ($actividad ? " AND r.id_actividad = ?" : "") . "
    GROUP BY hora
    ORDER BY hora
  ";

  echo json_encode(runQuery($conn,$sql,$params));
  exit;
}

echo json_encode([]);
