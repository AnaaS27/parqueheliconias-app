<?php
session_start();
require_once('../includes/verificar_sesion.php');

// ===========================
//  CONFIG DE SUPABASE
// ===========================
$supabase_url = getenv("DATABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

// Función para hacer peticiones REST a Supabase
function supabase_get($endpoint) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}

// ===========================
//  OBTENER DATOS DEL USUARIO
// ===========================

$id_usuario = $_SESSION['usuario_id'];

[$code, $data] = supabase_get("usuarios?id_usuario=eq.$id_usuario&select=nombre,apellido");

if ($code !== 200 || empty($data)) {
    // Error crítico
    $usuario = ["nombre" => "Usuario", "apellido" => ""];
} else {
    $usuario = $data[0];
}

// Manejo de toast de bienvenida
$mostrar_toast = isset($_SESSION['login_exitoso']) && $_SESSION['login_exitoso'] === true;
unset($_SESSION['login_exitoso']);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - CEA Parque de las Heliconias</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include('../includes/header.php'); ?>

    <!-- ✅ Toast de bienvenida -->
    <?php if ($mostrar_toast): ?>
        <div class="toast" id="toast">
            ✅ ¡Inicio de sesión exitoso! Bienvenido(a) <?php echo htmlspecialchars($usuario['nombre']); ?> 🌿
        </div>
    <?php endif; ?>

    <!-- Carrusel con texto fijo -->
    <section class="carousel">
        <div class="slides">
            <div class="slide active">
                <img src="../assets/img/parque1.JPG" alt="Parque de las Heliconias 1">
            </div>
            <div class="slide"><img src="../assets/img/parque2.jpg" alt="Parque de las Heliconias 2"></div>
            <div class="slide"><img src="../assets/img/parque3.jpg" alt="Parque de las Heliconias 3"></div>
            <div class="slide"><img src="../assets/img/parque4.jpg" alt="Parque de las Heliconias 4"></div>
        </div>

        <!-- Texto fijo sobre el carrusel -->
        <div class="carousel-text">
            <h1>🌿 Bienvenido(a), <?php echo htmlspecialchars($usuario['nombre']); ?>!</h1>
            <p>Explora las actividades, reserva experiencias y disfruta del contacto con la naturaleza en el <b>CEA Parque de las Heliconias</b>.</p>
            <div class="botones-inicio">
                <a href="actividades.php" class="btn boton-verde">Ver Actividades</a>
                <a href="mis_reservas.php" class="btn boton-rojo">Mis Reservas</a>
                <a href="perfil.php" class="btn boton-azul">Mi Perfil</a>
            </div>
        </div>

        <!-- Puntos de navegación (opcional) -->
        <div class="carousel-dots"></div>
    </section>

    <?php include('../includes/footer.php'); ?>
    <script src="../assets/js/carrusel.js"></script>

    <!-- Script del toast -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toast = document.getElementById("toast");
            if (toast) {
                setTimeout(() => toast.classList.add("hide"), 3500);
            }
        });
    </script>
</body>
</html>



