<?php
session_start();
require_once('../includes/verificar_admin.php');
require_once("../includes/supabase.php");

// ===============================
// Validar ID
// ===============================
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<script>alert('ID Inválido'); location.href='usuarios.php';</script>";
    exit;
}

$id = intval($id);

// ===============================
// 1️⃣ ACTIVAR USUARIO EN SUPABASE
// ===============================

list($codeUpdate, $respUpdate) = supabase_update(
    "usuarios",
    ["id_usuario" => $id],
    ["usuario_activo" => true]
);

if ($codeUpdate !== 200 && $codeUpdate !== 204) {
    echo "<script>alert('❌ Error al restaurar usuario'); location.href='usuarios.php';</script>";
    exit;
}

// ===============================
// 2️⃣ REDIRIGIR A USUARIOS INACTIVOS
// ===============================
header("Location: usuarios.php?filtro=inactivos");
exit;
?>