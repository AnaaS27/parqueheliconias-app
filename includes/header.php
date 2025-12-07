<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// DETECTAR RUTA BASE
// ==========================================
$rutaBase = (strpos($_SERVER['PHP_SELF'], '/paginas/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
    ? '../'
    : '';


// ==========================================
// CONFIGURACIÓN SUPABASE (REST API)
// ==========================================
// ⚠ Mantenemos tus nombres originales tal cual están funcionando
$supabase_url = getenv("DATABASE_URL");   // URL del proyecto Supabase
$supabase_key = getenv("SUPABASE_KEY");   // Llave pública/anon


// ==========================================
// FUNCIÓN GENERAL PARA CONSULTAR SUPABASE (GET)
// ==========================================
if (!function_exists('supabase_get')) {
    function supabase_get($endpoint) {
        global $supabase_url, $supabase_key;

        // Asegurar que no haya doble slash
        $url = rtrim($supabase_url, "/") . "/rest/v1/" . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key",
            "Authorization: Bearer $supabase_key",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$code, json_decode($response, true)];
    }
}



// ==========================================
// NOTIFICACIONES (SI EL USUARIO ESTÁ LOGUEADO)
// ==========================================
$notiCount = 0;

if (isset($_SESSION['usuario_id'])) {

    $idUsuario = intval($_SESSION['usuario_id']);

    // Supabase: contar notificaciones no leídas
    $endpoint = "notificaciones?select=count&id_usuario=eq.$idUsuario&leida=eq.false";

    [$code, $data] = supabase_get($endpoint);

    if ($code === 200 && !empty($data)) {
        // Supabase devuelve: [ { "count": X } ]
        $notiCount = $data[0]["count"] ?? 0;
    }
}

?>

<!-- 🔗 CSS DEL HEADER -->
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
