<?php
include "../../includes/verificar_admin.php";
include "../../includes/conexion.php";

// filtros opcionales
$inicio    = $_GET['inicio'] ?? null;
$fin       = $_GET['fin'] ?? null;
$actividad = (isset($_GET['actividad']) && $_GET['actividad'] !== '') ? (int)$_GET['actividad'] : null;

while (ob_get_level()) ob_end_clean();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=asistencia.csv');
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen('php://output','w');
fputcsv($out, [
    "ID Reserva","Actividad","Fecha Visita","Usuario Nombre","Usuario Apellido",
    "Institucion","Tipo Usuario","Participantes","Estado Reserva","Asistió"
], ';');

$sql = "
SELECT 
    r.id_reserva,
    a.nombre AS actividad,
    COALESCE(r.fecha_visita, DATE(r.fecha_reserva)) AS fecha_visita,
    u.nombre AS usuario_nombre,
    u.apellido AS usuario_apellido,
    inst.nombre_institucion AS institucion,
    u.id_rol AS tipo_usuario,
    COALESCE(r.numero_participantes,1) AS participantes,
    r.estado,
    CASE WHEN asi.id_asistencia IS NULL THEN 'No' ELSE 'Sí' END AS asistio
FROM reservas r
LEFT JOIN actividades a ON r.id_actividad = a.id_actividad
LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario
LEFT JOIN instituciones inst ON r.id_institucion = inst.id_institucion
LEFT JOIN asistencia asi ON asi.id_reserva = r.id_reserva
WHERE 1=1
";

$params = []; $types = '';
if ($inicio && $fin) {
  $sql .= " AND COALESCE(r.fecha_visita, DATE(r.fecha_reserva)) BETWEEN ? AND ? ";
  $params[] = $inicio; $params[] = $fin; $types .= 'ss';
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
        $row['usuario_nombre'],
        $row['usuario_apellido'],
        $row['institucion'],
        $row['tipo_usuario'],
        $row['participantes'],
        $row['estado'],
        $row['asistio']
    ], ';');
}

fclose($out);
exit;
