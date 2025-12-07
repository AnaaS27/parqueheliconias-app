<?php
session_start();
include('../includes/verificar_admin.php');
include('../includes/conexion.php');

$id = $_GET['id'] ?? null;

if (!$id) {
    die("<script>alert('ID Inválido');location.href='usuarios.php';</script>");
}

$sql = "UPDATE usuarios SET activo = TRUE WHERE id_usuario = $1";
pg_query_params($conn, $sql, [$id]);

header("Location: usuarios.php?filtro=inactivos");
exit;
?>
