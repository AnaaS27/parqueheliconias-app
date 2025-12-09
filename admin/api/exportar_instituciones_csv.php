<?php
include "../../includes/verificar_admin.php";
include "../../includes/supabase.php";

// Limpia buffers
while (ob_get_level()) ob_end_clean();

// Cabeceras del CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=instituciones.csv");

// BOM UTF-8 para Excel
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

// Abrir salida CSV
$out = fopen("php://output", "w");

// Escribir encabezados
fputcsv($out, ["ID Institución", "Nombre Institución", "Tipo", "Contacto"], ";");

// ==================================
//  CONSULTA A SUPABASE
// ==================================
list($code, $data) = supabase_get("instituciones?select=id_institucion,nombre_institucion,tipo,contacto&order=nombre_institucion.asc");

// Validación
if ($code !== 200 || empty($data)) {
    // CSV vacío con solo encabezado
    fclose($out);
    exit;
}

// Escribir datos al CSV
foreach ($data as $r) {
    fputcsv($out, [
        $r["id_institucion"],
        $r["nombre_institucion"],
        $r["tipo"],
        $r["contacto"]
    ], ";");
}

// Cerrar
fclose($out);
exit;
?>
