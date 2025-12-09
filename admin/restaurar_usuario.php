<?php
session_start();
include('../includes/verificar_admin.php');
include('../includes/supabase.php');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<script>alert('ID inválido'); location.href='usuarios.php';</script>";
    exit;
}

// ===============================
// ⭐ Restaurar usuario en Supabase
// ===============================
$endpoint = "usuarios?id_usuario=eq.$id";
$data = ["usuario_activo" => true];

list($code, $response) = supabase_update($endpoint, $data);

if ($code !== 200 && $code !== 204) {
    echo "<script>alert('❌ Error al restaurar el usuario'); location.href='usuarios.php';</script>";
    exit;
}

// Éxito
header("Location: usuarios.php?filtro=inactivos");
exit;
?>
