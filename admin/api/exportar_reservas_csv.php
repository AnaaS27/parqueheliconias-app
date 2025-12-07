<?php
include "../../includes/verificar_admin.php";
include "../../includes/conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_exception_handler(fn($e)=> exit(json_encode(['error'=>$e->getMessage()])));

// filtros opcionales
$inicio    = $_GET['inicio'] ?? null;
$fin       = $_GET['fin'] ?? null;
$actividad = (isset($_GET['actividad']) && $_GET['actividad'] !== '') ? (int)$_GET['actividad'] : null;

// headers CSV (BOM + sep=;)
while (ob_get_level()) ob_end_clean();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=reservas.csv');
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen('php://output','w');
fputcsv($out, [
  'ID Reserva','Actividad','Fecha Visita','Fecha Reserva','Tipo Reserva',
  'Estado','Participantes','Usuario ID','Usuario Nombre','Usuario Apellido',
  'Institucion ID','Institucion Nombre'
], ';');

// Query con joins y filtros
$sql = "
  SELECT r.id_reserva, a.nombre AS actividad,
         COALESCE(r.fecha_visita, DATE(r.fecha_reserva)) AS fecha_visita,
         r.fecha_reserva, r.tipo_reserva, r.estado, r.numero_participantes,
         u.id_usuario AS usuario_id, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido,
         r.id_institucion, inst.nombre_institucion
  FROM reservas r
  LEFT JOIN actividades a ON r.id_actividad = a.id_actividad
  LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario
  LEFT JOIN instituciones inst ON r.id_institucion = inst.id_institucion
  WHERE 1=1
";

$params = [];
$types = '';
if ($inicio && $fin) {
  $sql .= " AND COALESCE(r.fecha_visita, DATE(r.fecha_reserva)) BETWEEN ? AND ? ";
  $params[] = $inicio; $params[] = $fin;
  $types .= 'ss';
}
if ($actividad) {
  $sql .= " AND r.id_actividad = ? ";
  $params[] = $actividad; $types .= 'i';
}
$sql .= " ORDER BY COALESCE(r.fecha_visita, DATE(r.fecha_reserva)) DESC, r.id_reserva DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
  fputcsv($out, [
    $row['id_reserva'],
    $row['actividad'],
    $row['fecha_visita'],
    $row['fecha_reserva'],
    $row['tipo_reserva'],
    $row['estado'],
    $row['numero_participantes'],
    $row['usuario_id'],
    $row['usuario_nombre'],
    $row['usuario_apellido'],
    $row['id_institucion'],
    $row['nombre_institucion']
  ], ';');
}

fclose($out);
exit;
