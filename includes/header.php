<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rutaBase = (strpos($_SERVER['PHP_SELF'], '/paginas/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
    ? '../'
    : '';

if (!isset($conn)) {
    include(__DIR__ . '/../includes/conexion.php');
}

// ---------------- Notificaciones ----------------
$notiCount = 0;

if (isset($_SESSION['usuario_id'])) {

    $idUsuario = intval($_SESSION['usuario_id']);

    // Consulta PostgreSQL para contar notificaciones NO leídas
    $sql = "SELECT COUNT(*) AS total 
            FROM notificaciones
            WHERE id_usuario = $1
            AND leida = FALSE";  // notificaciiones no leídas

    $result = pg_query_params($conn, $sql, [$idUsuario]);

    if ($result) {
        $row = pg_fetch_assoc($result);
        $notiCount = $row["total"] ?? 0;
    }
}
?>

<!-- 🔗 Enlaces CSS -->
<link rel="stylesheet" href="<?= $rutaBase ?>assets/css/header.css">

<header class="user-header">

    <div class="top-bar">
        <div class="perfil">

            <?php if (isset($_SESSION['usuario_id'])): ?>

                <a href="<?= $rutaBase ?>paginas/logout.php" class="perfil-link cerrar-sesion">
                    <img src="<?= $rutaBase ?>assets/img/perfil.png" class="icono-perfil">
                    <span>Cerrar sesión</span>
                </a>

            <?php else: ?>

                <a href="<?= $rutaBase ?>paginas/login.php" class="perfil-link">
                    <img src="<?= $rutaBase ?>assets/img/perfil.png" class="icono-perfil">
                    <span>Ingresar al perfil</span>
                </a>

            <?php endif; ?>

        </div>
    </div>

    <nav>
        <div class="logo-title">
            <img src="<?= $rutaBase ?>assets/img/logoo.png" class="logo">
            <span class="titulo">CEA PARQUE DE LAS HELICONIAS</span>
        </div>

        <ul>
            <li><a href="<?= $rutaBase ?>index.php">Inicio</a></li>
            <li><a href="<?= $rutaBase ?>paginas/actividades.php">Actividades</a></li>
            <li><a href="<?= $rutaBase ?>paginas/contacto.php">Contacto</a></li>

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <li class="noti-nav">
                    <a href="<?= $rutaBase ?>paginas/notificaciones.php" class="noti-link">
                        <img src="<?= $rutaBase ?>assets/img/bell.svg" class="icono-perfil">

                        <?php if ($notiCount > 0): ?>
                            <span class="badge"><?= $notiCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>

</header>
