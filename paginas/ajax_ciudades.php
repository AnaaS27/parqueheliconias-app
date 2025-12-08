<?php
header("Content-Type: application/json");
include('../includes/supabase.php');

// Validar parámetro
if (!isset($_GET['pais']) || empty($_GET['pais'])) {
    echo json_encode([]);
    exit;
}

$pais_id = intval($_GET['pais']);

// ==========================================
// 🔎 CONSULTAR CIUDADES EN SUPABASE
// ==========================================
list($code, $ciudades) = supabase_get("ciudades?pais_id=eq.$pais_id&select=id,nombre&order=nombre.asc");

// Si la consulta falla, enviamos una lista vacía
if ($code !== 200 || !is_array($ciudades)) {
    echo json_encode([]);
    exit;
}

// Responder JSON
echo json_encode($ciudades);
exit;
?>
