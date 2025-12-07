<?php
include "../../includes/verificar_admin.php";
include "../../includes/conexion.php";

while (ob_get_level()) ob_end_clean();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=usuarios.csv');
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen('php://output','w');
fputcsv($out, ['ID Usuario','Nombre','Apellido','Correo','Documento','Telefono','Rol ID','Fecha Creacion'], ';');

$sql = "SELECT id_usuario, nombre, apellido, correo, documento, telefono, id_rol, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
  fputcsv($out, [
    $r['id_usuario'],
    $r['nombre'],
    $r['apellido'],
    $r['correo'],
    $r['documento'],
    $r['telefono'],
    $r['id_rol'],
    $r['fecha_creacion']
  ], ';');
}
fclose($out);
exit;
