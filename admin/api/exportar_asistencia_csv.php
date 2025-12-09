<?php
require_once "../../includes/verificar_admin.php";
require_once "../../includes/supabase.php";

// ============================
//  FILTROS
// ============================
$inicio    = $_GET['inicio'] ?? null;
$fin       = $_GET['fin'] ?? null;
$actividad = isset($_GET['actividad']) && $_GET['actividad'] !== '' ? (int)$_GET['actividad'] : null;


// ============================
//  CONFIGURACIÓN CSV
// ============================
while (ob_get_level()) ob_end_clean();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=asistencia.csv');

echo "\xEF\xBB\xBF"; // BOM Excel
echo "sep=;\r\n";

$out = fopen('php://output', 'w');

fputcsv($out, [
    "ID Reserva","Actividad","Fecha Visita","Usuario Nombre","Usuario Apellido",
    "Institucion","Tipo Usuario","Participantes","Estado Reserva","Asistió"
], ';');


// ============================
//  OBTENER TABLAS NECESARIAS
// ============================

// Reservas filtradas
$filters = [];

if ($actividad) {
    $filters["id_actividad"] = $actividad;
}

// Obtener todas las reservas (ya filtradas)
list($codeRes, $reservas) = supabase_get("reservas", $filters, 0, 5000);
if ($codeRes !== 200) $reservas = [];

// Actividades
list($codeAct, $actividades) = supabase_get("actividades", [], 0, 5000);
$mapActividades = [];
foreach ($actividades as $a) {
    $mapActividades[$a["id_actividad"]] = $a;
}

// Usuarios
list($codeUsr, $usuarios) = supabase_get("usuarios", [], 0, 5000);
$mapUsuarios = [];
foreach ($usuarios as $u) {
    $mapUsuarios[$u["id_usuario"]] = $u;
}

// Instituciones
list($codeInst, $insts) = supabase_get("instituciones", [], 0, 5000);
$mapInst = [];
foreach ($insts as $i) {
    $mapInst[$i["id_institucion"]] = $i;
}

// Asistencia
list($codeAsi, $asis) = supabase_get("asistencia", [], 0, 5000);
$mapAsistencia = [];
foreach ($asis as $a) {
    $mapAsistencia[$a["id_reserva"]] = $a;
}


// ============================
//  FILTRAR FECHAS EN PHP
// ============================

$reservasFiltradas = [];

foreach ($reservas as $r) {

    // Obtener fecha_visita o fallback
    $fecha_visita = $r["fecha_visita"] ?: substr($r["fecha_reserva"], 0, 10);

    // Filtrar por rango de fechas
    if ($inicio && $fin) {
        if ($fecha_visita < $inicio || $fecha_visita > $fin) continue;
    }

    $reservasFiltradas[] = $r;
}


// ============================
//  ORDENAR POR FECHA DESC
// ============================

usort($reservasFiltradas, function($a, $b) {
    $fechaA = $a["fecha_visita"] ?: substr($a["fecha_reserva"], 0, 10);
    $fechaB = $b["fecha_visita"] ?: substr($b["fecha_reserva"], 0, 10);
    return strcmp($fechaB, $fechaA);
});


// ============================
//  GENERAR CSV
// ============================

foreach ($reservasFiltradas as $r) {

    $id = $r["id_reserva"];

    $actividad_data = $mapActividades[$r["id_actividad"]] ?? [];
    $usuario_data   = $mapUsuarios[$r["id_usuario"]] ?? [];
    $inst_data      = $mapInst[$usuario_data["id_institucion"] ?? null] ?? [];

    $asistio = isset($mapAsistencia[$id]) ? "Sí" : "No";

    $fecha_visita = $r["fecha_visita"] ?: substr($r["fecha_reserva"], 0, 10);

    fputcsv($out, [
        $id,
        $actividad_data["nombre"] ?? "",
        $fecha_visita,
        $usuario_data["nombre"] ?? "",
        $usuario_data["apellido"] ?? "",
        $inst_data["nombre_institucion"] ?? "",
        $usuario_data["id_rol"] ?? "",
        $r["numero_participantes"] ?? 1,
        $r["estado"] ?? "",
        $asistio
    ], ';');
}

fclose($out);
exit;
?>