<?php
session_start();


// Si el usuario ya inició sesión, lo enviamos a su panel correspondiente
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 1) {
        header("Location: admin/index.php");
    } else {
        header("Location: paginas/inicio.php");
    }
    exit;
}

if (isset($_GET['logout']) && $_GET['logout'] == 'ok') {
    echo "<script>alert('👋 Sesión cerrada correctamente');</script>";
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parque de las Heliconias</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include('includes/header.php'); ?>

    <!-- Carrusel -->
    <!-- Carrusel -->
    <section class="carousel">
        <div class="slides">
            <div class="slide active">
                <img src="assets/img/parque1.JPG" alt="Parque de las Heliconias 1">
            </div>
            <div class="slide">
                <img src="assets/img/parque2.jpg" alt="Parque de las Heliconias 2">
            </div>
            <div class="slide">
                <img src="assets/img/parque3.jpg" alt="Parque de las Heliconias 3">
            </div>
            <div class="slide">
                <img src="assets/img/parque4.jpg" alt="Parque de las Heliconias 4">
            </div>
        </div>

        <!-- Texto centrado, ahora fijo -->
        <div class="carousel-text">
            <h1>Bienvenido al Parque de las Heliconias</h1>
            <p>Disfruta todo el encanto natural de la biodiversidad</p>
            <p>Visítanos y vive el encanto del Paisaje Cultural Cafetero</p>
            <p>Ubicado en el kilómetro 7 Vía Sevilla - Caicedonia</p>
            <a href="paginas/registro.php">
                <button class="plan-btn">RESERVA TU VISITA</button>
            </a>
        </div>

        <div class="carousel-dots"></div>

    </section>


    <?php include('includes/footer.php'); ?>
    <script src="assets/js/carrusel.js"></script>

    <?php if (isset($_GET['logout']) && $_GET['logout'] === 'ok'): ?>
        <div id="toast-logout" class="toast-logout">👋 Sesión cerrada correctamente</div>
        <script>
        // Aparece suavemente y se desvanece luego
        const toast = document.getElementById('toast-logout');
        setTimeout(() => toast.classList.add('show'), 100); // aparece
        setTimeout(() => toast.classList.remove('show'), 4000); // desaparece
        </script>
    <?php endif; ?>


</body>
</html>

