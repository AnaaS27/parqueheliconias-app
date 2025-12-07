<?php
include "../../includes/verificar_admin.php";
include "../../includes/conexion.php";

while (ob_get_level()) ob_end_clean();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=actividades.csv');
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen('php://output','w');
fputcsv($out, [
  'ID Actividad','Nombre','Descripcion','Nivel Dificultad','Horario',
  'Duracion (min)','Cupo Maximo','Recomendaciones','Activo'
], ';');

$sql = "SELECT id_actividad, nombre, descripcion, nivel_dificultad, horario, duracion_minutos, cupo_maximo, recomendaciones, activo FROM actividades ORDER BY nombre";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
  fputcsv($out, [
    $r['id_actividad'],
    $r['nombre'],
    $r['descripcion'],
    $r['nivel_dificultad'],
    $r['horario'],
    $r['duracion_minutos'],
    $r['cupo_maximo'],
    $r['recomendaciones'],
    $r['activo']
  ], ';');
}
fclose($out);
exit;
