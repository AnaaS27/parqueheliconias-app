<?php
include('../includes/conexion.php');

header('Content-Type: application/json');

$id_actividad = intval($_GET['id_actividad'] ?? 0);
$fecha        = $_GET['fecha'] ?? '';
$horario      = $_GET['horario'] ?? '';

if (!$id_actividad || !$fecha || !$horario) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Datos incompletos",
        "cupos" => 0
    ]);
    exit;
}

// ---------------------------------------------
// 1️⃣ Verificar si existe el registro de cupos
// ---------------------------------------------
$sql_check = "
    SELECT 
        cupos_maximos,
        cupos_reservados,
        (cupos_maximos - cupos_reservados) AS disponibles
    FROM cupos_actividad
    WHERE id_actividad = $1
      AND fecha = $2
      AND horario = $3
";

$res_check = pg_query_params($conn, $sql_check, [
    $id_actividad,
    $fecha,
    $horario
]);

// ---------------------------------------------
// 2️⃣ Si no existe → crear el registro con 20 cupos
// ---------------------------------------------
if (!$res_check || pg_num_rows($res_check) === 0) {

    $sql_insert = "
        INSERT INTO cupos_actividad (id_actividad, fecha, horario, cupos_maximos, cupos_reservados)
        VALUES ($1, $2, $3, 20, 0)
        RETURNING cupos_maximos, cupos_reservados,
                  (cupos_maximos - cupos_reservados) AS disponibles
    ";

    $res_insert = pg_query_params($conn, $sql_insert, [
        $id_actividad,
        $fecha,
        $horario
    ]);

    if ($res_insert && pg_num_rows($res_insert) > 0) {
        $data = pg_fetch_assoc($res_insert);

        echo json_encode([
            "error" => false,
            "cupos_maximos"    => (int)$data['cupos_maximos'],
            "cupos_reservados" => (int)$data['cupos_reservados'],
            "cupos_disponibles"=> (int)$data['disponibles']
        ]);
        exit;
    } else {
        echo json_encode([
            "error" => true,
            "mensaje" => "No se pudo crear registro de cupos"
        ]);
        exit;
    }
}

// ---------------------------------------------
// 3️⃣ Si sí existe → devolver la información
// ---------------------------------------------
$data = pg_fetch_assoc($res_check);

echo json_encode([
    "error" => false,
    "cupos_maximos"    => (int)$data['cupos_maximos'],
    "cupos_reservados" => (int)$data['cupos_reservados'],
    "cupos_disponibles"=> (int)$data['disponibles']
]);
