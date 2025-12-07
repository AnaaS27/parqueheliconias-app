<?php
header("Content-Type: application/json");

// ❗ Asegurar que cargue las funciones de Supabase
include('../includes/supabase.php');  // Asegúrate que este archivo contiene supabase_get()

// Validar parámetro
if (!isset($_GET['pais'])) {
    echo json_encode([]);
    exit;
}

$pais_id = intval($_GET['pais']);

// ==============================
// 🔹 Consulta a Supabase REST API
// ==============================
$endpoint = "ciudades?pais_id=eq.$pais_id&select=id,nombre&order=nombre";

[$status, $data] = supabase_get($endpoint);

// Si hay error o no devuelve datos
if ($status !== 200 || !is_array($data)) {
    echo json_encode([]);
    exit;
}

// Respuesta correcta
echo json_encode($data);

