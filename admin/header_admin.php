<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/verificar_admin.php'); 
require_once('../includes/supabase.php'); // ← reemplaza conexion.php por supabase.php

// ===============================
// 1️⃣ OBTENER ID ADMIN DESDE SESIÓN
// ===============================
$id_admin = $_SESSION['usuario_id'] ?? null;

if (!$id_admin) {
    header("Location: ../login.php");
    exit;
}

// ===============================
// 2️⃣ CONSULTAR NOTIFICACIONES NO LEÍDAS EN SUPABASE
// ===============================
//
// Endpoint:
// notificaciones?id_usuario=eq.{id}&leida=eq.false&select=count(id)
//
list($codeNoti, $notiData) = supabase_get(
    "notificaciones?id_usuario=eq.$id_admin&leida=eq.false&select=count:id"
);

$notiAdminCount = 0;

if ($codeNoti === 200 && !empty($notiData)) {
    // Supabase devuelve count como string → convertirlo
    $notiAdminCount = intval($notiData[0]["count"]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administrativo - Parque Las Heliconias</title>
  <link rel="stylesheet" href="../assets/css/estilos.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="icon" type="image/png" href="../assets/img/logoo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    /* 🔔 Estilo del contador (badge) */
    .noti-admin-link {
      position: relative;
      display: inline-block;
    }
    .icono-notificacion {
      width: 24px;
      height: 24px;
    }
    .badge {
      position: absolute;
      top: -5px;
      right: -8px;
      background-color: #e53935;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
      font-weight: bold;
      box-shadow: 0 0 4px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>

<header class="admin-header">
  <div class="admin-header-content">
    <div class="admin-logo">
      <img src="../assets/img/logoo.png" alt="Logo Parque Las Heliconias">
      <h1>Panel Administrativo</h1>
    </div>

    <nav class="admin-nav">
      <ul>
        <li><a href="index.php">🏠 Inicio</a></li>
        <li><a href="actividades.php">🎫 Actividades</a></li>
        <li><a href="reservas.php">📅 Reservas</a></li>
        <li><a href="usuarios.php">👥 Usuarios</a></li>
        <li><a href="reportes.php">📊 Reportes</a></li>

        <!-- 🔔 Notificaciones -->
        <li>
          <a href="notificaciones_admin.php" class="noti-admin-link" title="Ver notificaciones">
            <img src="../assets/img/bell.svg" alt="Notificaciones" class="icono-notificacion">

            <?php if ($notiAdminCount > 0): ?>
              <span class="badge"><?= $notiAdminCount ?></span>
            <?php endif; ?>

          </a>
        </li>

        <li><a href="logout.php" class="btn-cerrar-sesion">🔒 Cerrar sesión</a></li>
      </ul>
    </nav>
  </div>
</header>

<main class="admin-main">
