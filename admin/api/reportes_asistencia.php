<?php
include('../../includes/verificar_admin.php');
include('../../includes/supabase.php');

header('Content-Type: application/json');

// ===============================
//  CAPTURA DE PARÁMETROS
// ===============================
$tipo      = $_GET['tipo'] ?? 'por_actividad';
$inicio    = $_GET['inicio'] ?? date('Y-m-01');
$fin       = $_GET['fin']    ?? date('Y-m-t');
$actividad = isset($_GET['actividad']) && $_GET['actividad'] !== '' ? intval($_GET['actividad']) : null;


// ===============================
//  FUNCIONES AUXILIARES
// ===============================

// Obtener reservas filtradas
function cargarReservas($inicio, $fin, $actividad)
{
    $query = "reservas?select=*&fecha_reserva=gte.$inicio&fecha_reserva=lte.$fin";

    if ($actividad)
        $query .= "&id_actividad=eq.$actividad";

    list($code, $data) = supabase_get($query);

    return ($code === 200 && is_array($data)) ? $data : [];
}

// Obtener asistencias completas
function cargarAsistencias()
{
    list($code, $data) = supabase_get("asistencia?select=id_asistencia,id_reserva,fecha_asistencia");

    return ($code === 200 && is_array($data)) ? $data : [];
}

// Obtener actividades
function cargarActividades()
{
    list($code, $data) = supabase_get("actividades?select=id_actividad,nombre");

    return ($code === 200 && is_array($data)) ? $data : [];
}


// ===============================
//  CARGAR DATA BASE
// ===============================
$reservas    = cargarReservas($inicio, $fin, $actividad);
$asistencias = cargarAsistencias();
$actividades = cargarActividades();


// ====================================================
//  TIPO A) REPORTE POR ACTIVIDAD
// ====================================================
if ($tipo === 'por_actividad') {

    $out = [];

    foreach ($actividades as $a) {

        $id = $a["id_actividad"];
        $nombre = $a["nombre"];

        // Filtrar reservas por actividad
        $resAct = array_filter($reservas, fn($r) => $r["id_actividad"] == $id);

        if (!$resAct) continue;

        // Reservadas
        $reservadas = 0;
        foreach ($resAct as $r) {
            if (($r["estado"] ?? "") !== "cancelada") {
                $reservadas += $r["numero_participantes"] ?? 1;
            }
        }

        // Asistidas
        $asisCount = 0;
        foreach ($asistencias as $a2) {
            foreach ($resAct as $r) {
                if ($a2["id_reserva"] == $r["id_reserva"]) {
                    $asisCount++;
                }
            }
        }

        $porcentaje = ($reservadas > 0)
            ? round(($asisCount / $reservadas) * 100, 2)
            : 0;

        $out[] = [
            "actividad"  => $nombre,
            "reservadas" => $reservadas,
            "asistidas"  => $asisCount,
            "porcentaje" => $porcentaje
        ];
    }

    usort($out, fn($a, $b) => $b["asistidas"] <=> $a["asistidas"]);

    echo json_encode($out);
    exit;
}



// ====================================================
//  TIPO B) TENDENCIA MENSUAL
// ====================================================
if ($tipo === 'trend') {

    $months = [];

    foreach ($reservas as $r) {

        $fecha = $r["fecha_visita"] ?: $r["fecha_reserva"];
        $mes = substr($fecha, 0, 7); // YYYY-MM

        if (!isset($months[$mes])) {
            $months[$mes] = ["reservadas" => 0, "asistidas" => 0];
        }

        if (($r["estado"] ?? "") !== "cancelada") {
            $months[$mes]["reservadas"] += $r["numero_participantes"] ?? 1;
        }

        foreach ($asistencias as $a) {
            if ($a["id_reserva"] == $r["id_reserva"]) {
                $months[$mes]["asistidas"]++;
            }
        }
    }

    $out = [];
    foreach ($months as $mes => $vals) {
        $out[] = [
            "mes"        => $mes,
            "reservadas" => $vals["reservadas"],
            "asistidas"  => $vals["asistidas"]
        ];
    }

    echo json_encode($out);
    exit;
}



// ====================================================
//  TIPO C) DÍA DE LA SEMANA (0-6)
// ====================================================
if ($tipo === 'weekday') {

    $dias = [
        0 => "Dom",
        1 => "Lun",
        2 => "Mar",
        3 => "Mié",
        4 => "Jue",
        5 => "Vie",
        6 => "Sáb"
    ];

    $out = [];

    foreach ($reservas as $r) {
        $fecha = $r["fecha_visita"] ?: $r["fecha_reserva"];
        $dow   = date("w", strtotime($fecha)); // 0=Domingo

        if (!isset($out[$dow])) {
            $out[$dow] = ["dow" => $dow, "dow_nombre" => $dias[$dow], "asistidas" => 0];
        }

        foreach ($asistencias as $a) {
            if ($a["id_reserva"] == $r["id_reserva"]) {
                $out[$dow]["asistidas"]++;
            }
        }
    }

    echo json_encode(array_values($out));
    exit;
}



// ====================================================
//  TIPO D) HORA DEL DÍA
// ====================================================
if ($tipo === 'hour') {

    $hours = [];

    foreach ($reservas as $r) {

        $hora = intval(substr($r["fecha_reserva"], 11, 2)); // HH

        if (!isset($hours[$hora])) {
            $hours[$hora] = ["hora" => $hora, "asistidas" => 0];
        }

        foreach ($asistencias as $a) {
            if ($a["id_reserva"] == $r["id_reserva"]) {
                $hours[$hora]["asistidas"]++;
            }
        }
    }

    echo json_encode(array_values($hours));
    exit;
}

echo json_encode([]);
?>
