<?php
require_once "../../includes/verificar_admin.php";
require_once "../../includes/supabase.php";

// ============================
//  CONFIGURAR EXPORTACIÓN CSV
// ============================
while (ob_get_level()) ob_end_clean();
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=actividades.csv');

// BOM para Excel
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen('php://output', 'w');

// Encabezados
fputcsv($out, [
  'ID Actividad','Nombre','Descripcion','Nivel Dificultad',
  'Horario','Duracion (min)','Cupo Maximo','Recomendaciones','Activo'
], ';');

// ============================
//  CONSULTAR ACTIVIDADES EN SUPABASE
// ============================

list($code, $actividades) = supabase_get("actividades", [], 0, 2000); 
// puedes ajustar el límite según sea necesario

if ($code !== 200 || empty($actividades)) {
    fclose($out);
    exit;
}

// Ordenar por nombre (Supabase no soporta ORDER por API REST)
usort($actividades, function($a, $b) {
    return strcmp($a["nombre"], $b["nombre"]);
});

// ============================
//  EXPORTAR CADA FILA
// ============================

foreach ($actividades as $r) {
    fputcsv($out, [
        $r['id_actividad'] ?? "",
        $r['nombre'] ?? "",
        $r['descripcion'] ?? "",
        $r['nivel_dificultad'] ?? "",
        $r['horario'] ?? "",
        $r['duracion_minutos'] ?? "",
        $r['cupo_maximo'] ?? "",
        $r['recomendaciones'] ?? "",
        $r['activo'] ? "1" : "0"
    ], ';');
}

fclose($out);
exit;
?>