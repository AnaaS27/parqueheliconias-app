<?php
include "../../includes/verificar_admin.php";
include "../../includes/supabase.php";

// ------------------------------
//   CAPTURA DE PARÁMETROS
// ------------------------------
$inicio    = $_GET['inicio'] ?? null;
$fin       = $_GET['fin'] ?? null;
$actividad = isset($_GET['actividad']) && $_GET['actividad'] !== '' ? (int)$_GET['actividad'] : null;

// ------------------------------
//   HEADERS CSV (UTF-8 + Excel)
// ------------------------------
while (ob_get_level()) ob_end_clean();

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=reservas.csv");

// BOM UTF-8
echo "\xEF\xBB\xBF";
echo "sep=;\r\n";

$out = fopen("php://output", "w");

// Encabezados
fputcsv($out, [
    "ID Reserva", "Actividad", "Fecha Visita", "Fecha Reserva",
    "Tipo Reserva", "Estado", "Participantes", "Usuario ID",
    "Usuario Nombre", "Usuario Apellido", "Institucion ID", "Institucion Nombre"
], ";");

// ------------------------------
//   CONSTRUIR FILTRO SUPABASE
// ------------------------------

// Base endpoint con JOINS
$endpoint = "reservas?select="
           ."id_reserva,"
           ."fecha_reserva,"
           ."fecha_visita,"
           ."tipo_reserva,"
           ."estado,"
           ."numero_participantes,"
           ."id_institucion,"
           ."actividades(nombre),"
           ."usuarios(id_usuario,nombre,apellido),"
           ."instituciones(nombre_institucion)";

// Filtro: fecha
// Usamos fecha_visita si existe, sino fecha_reserva
if ($inicio && $fin) {
    $endpoint .= "&fecha_visita=gte.$inicio&fecha_visita=lte.$fin";
}

// Filtro: actividad
if ($actividad) {
    $endpoint .= "&id_actividad=eq.$actividad";
}

// Orden final
$endpoint .= "&order=fecha_visita.desc&order=id_reserva.desc";

// ------------------------------
//   CONSULTA A SUPABASE
// ------------------------------
list($code, $data) = supabase_get($endpoint);

if ($code !== 200 || empty($data)) {
    fclose($out);
    exit; // CSV vacío con solo encabezado
}

// ------------------------------
//   ESCRIBIR REGISTROS CSV
// ------------------------------
foreach ($data as $r) {
    
    // Actividad
    $actividadNombre = $r["actividades"]["nombre"] ?? "N/A";

    // Usuario
    $u = $r["usuarios"] ?? null;
    $usuarioID       = $u["id_usuario"]   ?? "";
    $usuarioNombre   = $u["nombre"]       ?? "";
    $usuarioApellido = $u["apellido"]     ?? "";

    // Institución
    $instNombre = $r["instituciones"]["nombre_institucion"] ?? "";
    $instID     = $r["id_institucion"] ?? "";

    fputcsv($out, [
        $r["id_reserva"],
        $actividadNombre,
        $r["fecha_visita"],
        $r["fecha_reserva"],
        $r["tipo_reserva"],
        $r["estado"],
        $r["numero_participantes"],
        $usuarioID,
        $usuarioNombre,
        $usuarioApellido,
        $instID,
        $instNombre
    ], ";");
}

fclose($out);
exit;
?>
