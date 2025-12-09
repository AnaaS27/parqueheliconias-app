<?php
// Solo calcular la ruta base si no existe
if (!isset($rutaBase)) {
    $rutaBase = (strpos($_SERVER['PHP_SELF'], '/paginas/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
        ? '../'
        : '';
}
?>

<link rel="stylesheet" href="<?= $rutaBase ?>assets/css/footer.css">

<footer class="footer">

    <div class="footer-container">

        <!-- ===========================
             🗺 COLUMNA 1 - UBICACIÓN
        ============================ -->
        <div class="footer-section ubicacion">
            <div class="footer-logo-box">
                <img src="<?= $rutaBase ?>assets/img/logoo.png" alt="Logo Parque de las Heliconias" class="footer-logo">
            </div>

            <h3>Ubicación</h3>

            <a href="https://maps.app.goo.gl/h5fW21CPUkmmKE8Q9" target="_blank"
               class="footer-item-row">
                <img src="<?= $rutaBase ?>assets/img/ubicacion.png" alt="Ubicación" class="social-icon">
                <span>
                    Parque de las Heliconias<br>
                    Kilómetro 7 Vía Sevilla - Caicedonia<br>
                    Valle del Cauca, Colombia
                </span>
            </a>
        </div>

        <!-- ===========================
             ⏰ COLUMNA 2 - HORARIOS
        ============================ -->
        <div class="footer-section">
            <h3>Horarios Disponibles</h3>

            <div class="footer-item-column">
                <p><strong>Instituciones Educativas:</strong></p>
                <p>Lunes a Domingo<br>8:00 a.m. – 5:00 p.m.</p>
            </div>

            <div class="footer-item-column">
                <p><strong>Público General:</strong></p>
                <p>Miércoles a Domingo y Festivos<br>8:00 a.m. – 5:00 p.m.</p>
            </div>
        </div>

        <!-- ===========================
             ☎ COLUMNA 3 - CONTACTO
        ============================ -->
        <div class="footer-section contacto">
            <h3>Contáctanos</h3>

            <div class="footer-item-row">
                <img src="<?= $rutaBase ?>assets/img/whatsapp.png" class="social-icon">
                <a href="https://wa.me/573157341432?text=¡Hola!%20Estoy%20interesado(a)%20en%20visitar%20el%20Parque%20de%20las%20Heliconias."
                   target="_blank">+57 3157341432</a>
            </div>

            <div class="footer-item-row">
                <img src="<?= $rutaBase ?>assets/img/correo.png" class="social-icon">
                <a href="mailto:cea-heliconias@cvc.gov.co">cea-heliconias@cvc.gov.co</a>
            </div>
        </div>

        <!-- ===========================
             📱 COLUMNA 4 - REDES SOCIALES
        ============================ -->
        <div class="footer-section redes">
            <h3>Síguenos en redes sociales</h3>

            <div class="social-icons-row">
                <a href="https://www.instagram.com/parquedelasheliconias/?hl=es" target="_blank">
                    <img src="<?= $rutaBase ?>assets/img/instagram.png" alt="Instagram" class="social-icon">
                </a>

                <a href="https://www.facebook.com/ParqueHeliconias/" target="_blank">
                    <img src="<?= $rutaBase ?>assets/img/facebook.png" alt="Facebook" class="social-icon">
                </a>
            </div>
        </div>

    </div>

    <!-- ===========================
            💬 BOTÓN FLOTANTE
    ============================ -->
    <a href="https://wa.me/573157341432?text=¡Hola!%20Quiero%20más%20información%20sobre%20el%20Parque%20de%20las%20Heliconias."
       class="chat-btn" target="_blank">
       💬 ¡Vamos a chatear!
    </a>

</footer>