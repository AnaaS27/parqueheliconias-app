<?php
if (!isset($rutaBase)) {
    $rutaBase = (strpos($_SERVER['PHP_SELF'], '/paginas/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
        ? '../'
        : '';
}
?>

<link rel="stylesheet" href="<?= $rutaBase ?>assets/css/footer_tailwind_free.css">

<footer class="footer">
    <div class="footer-grid">

        <!-- LOGO -->
        <div class="footer-logo-box">
            <img src="<?= $rutaBase ?>assets/img/logoo.png" class="footer-logo">
            <p class="footer-logo-text">Parque de las Heliconias</p>
        </div>

        <!-- UBICACIÓN -->
        <div class="footer-section">
            <h3 class="footer-title">Ubicación</h3>

            <div class="footer-row">
                <img src="<?= $rutaBase ?>assets/img/ubicacion.png" class="icon-sm">
                <p>
                    Parque de las Heliconias<br>
                    Kilómetro 7 Vía Sevilla – Caicedonia<br>
                    Valle del Cauca, Colombia
                </p>
            </div>
        </div>

        <!-- HORARIOS -->
        <div class="footer-section">
            <h3 class="footer-title">Horarios</h3>

            <p><strong>Instituciones Educativas:</strong><br>
               Lunes a Domingo<br>
               8:00 a.m. – 5:00 p.m.</p>

            <p class="mt">
               <strong>Público General:</strong><br>
               Miércoles a Domingo y Festivos<br>
               8:00 a.m. – 5:00 p.m.
            </p>
        </div>

        <!-- CONTACTO -->
        <div class="footer-section">
            <h3 class="footer-title">Contáctanos</h3>

            <div class="footer-row">
                <img src="<?= $rutaBase ?>assets/img/whatsapp.png" class="icon-sm">
                <span>+57 315 734 1432</span>
            </div>

            <div class="footer-row">
                <img src="<?= $rutaBase ?>assets/img/correo.png" class="icon-sm">
                <span>cea-heliconias@cvc.gov.co</span>
            </div>

            <h3 class="footer-title mt">Síguenos</h3>

            <div class="social-box">
                <img src="<?= $rutaBase ?>assets/img/instagram.png" class="icon-md">
                <img src="<?= $rutaBase ?>assets/img/facebook.png" class="icon-md">
            </div>
        </div>

    </div>

    <!-- BOTÓN FLOTANTE -->
    <a href="https://wa.me/573157341432?text=Hola!%20Quiero%20más%20información%20sobre%20el%20Parque."
       class="chat-btn">
       💬 ¡Vamos a chatear!
    </a>

</footer>
