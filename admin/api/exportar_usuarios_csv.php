<?php
include "../../includes/verificar_admin.php";
include "../../includes/supabase.php";

// ------------------------------
//   HEADERS CSV (UTF-8 + Excel)
// ------------------------------
while (ob_get_level()) ob_end_clean();

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=usuarios.csv");

// BOM para Excel
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen("php://output", "w");

// Encabezados
fputcsv($out, [
    "ID Usuario",
    "Nombre",
    "Apellido",
    "Correo",
    "Documento",
    "Telefono",
    "Rol ID",
    "Fecha Creacion"
], ";");

// ------------------------------
//   CONSULTA A SUPABASE
// ------------------------------

$endpoint = "usuarios?select="
           ."id_usuario,nombre,apellido,correo,documento,telefono,id_rol,fecha_creacion"
           ."&order=fecha_creacion.desc";

list($code, $data) = supabase_get($endpoint);

if ($code !== 200 || empty($data)) {
    fclose($out);
    exit; 
}

// ------------------------------
//   ESCRIBIR FILAS CSV
// ------------------------------
foreach ($data as $u) {

    fputcsv($out, [
        $u["id_usuario"],
        $u["nombre"],
        $u["apellido"],
        $u["correo"],
        $u["documento"],
        $u["telefono"],
        $u["id_rol"],
        $u["fecha_creacion"]
    ], ";");
}

fclose($out);
exit;
?>
