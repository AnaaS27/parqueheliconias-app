<?php
include('../includes/conexion.php');

header("Content-Type: application/json");

// Validar que viene el ID del país
if (!isset($_GET['pais'])) {
    echo json_encode([]);
    exit;
}

$pais_id = intval($_GET['pais']);

// Consultar ciudades según el país
$sql = "SELECT id, nombre FROM ciudades WHERE pais_id = $1 ORDER BY nombre";
$result = pg_query_params($conn, $sql, [$pais_id]);

$ciudades = [];

while ($row = pg_fetch_assoc($result)) {
    $ciudades[] = $row;
}

// Responder a JavaScript
echo json_encode($ciudades);
