<?php
include "../../includes/verificar_admin.php";
include "../../includes/conexion.php";

while (ob_get_level()) ob_end_clean();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=instituciones.csv');
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen('php://output','w');
fputcsv($out, ['ID Institucion','Nombre Institucion','Tipo','Contacto'], ';');

$sql = "SELECT id_institucion, nombre_institucion, tipo, contacto FROM instituciones ORDER BY nombre_institucion";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
  fputcsv($out, [
    $r['id_institucion'],
    $r['nombre_institucion'],
    $r['tipo'],
    $r['contacto']
  ], ';');
}
fclose($out);
exit;
