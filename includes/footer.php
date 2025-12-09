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

    <div class="footer-container footer-grid">

        <!-- 🟢 Columna 1: Logo + Ubicación -->
        <div class="footer-col logo-col">
            <img src="<?= $rutaBase ?>assets/img/logoo.png" alt="Logo Parque Heliconias" class="footer-logo">

            <div class="ubicacion-box">
                <h3>Ubicación</h3>
                <a href="https://maps.app.goo.gl/h5fW21CPUkmmKE8Q9" target="_blank" class="ubicacion-link">
                    <img src="<?= $rutaBase ?>assets/img/ubicacion.png" class="icon-sm">
                    <span>
                        Parque de las Heliconias<br>
                        Kilómetro 7 Vía Sevilla - Caicedonia<br>
                        Valle del Cauca, Colombia
                    </span>
                </a>
            </div>
        </div>

        <!-- ⏰ Columna 2: Horarios -->
        <div class="footer-col">
            <h3>Horarios Disponibles</h3>
            <p><strong>Instituciones Educativas:</strong><br>
            Lunes a Domingo<br>8:00 a.m. – 5:00 p.m.</p>

            <p><strong>Público General:</strong><br>
            Miércoles a Domingo y Festivos<br>8:00 a.m. – 5:00 p.m.</p>
        </div>

        <!-- 📞 Columna 3: Contacto -->
        <div class="footer-col">
            <h3>Contáctanos</h3>

            <a href="https://wa.me/573157341432?text=¡Hola!%20Estoy%20interesado(a)%20en%20visitar%20el%20Parque%20de%20las%20Heliconias.%20¿Podrían%20darme%20más%20información?"
               target="_blank" class="footer-link">
                <img src="<?= $rutaBase ?>assets/img/whatsapp.png" class="icon-sm">
                <span>+57 315 734 1432</span>
            </a>

            <a href="mailto:cea-heliconias@cvc.gov.co" class="footer-link">
                <img src="<?= $rutaBase ?>assets/img/correo.png" class="icon-sm">
                <span>cea-heliconias@cvc.gov.co</span>
            </a>
        </div>

        <!-- 📱 Columna 4: Redes -->
        <div class="footer-col">
            <h3>Síguenos en redes</h3>
            <div class="redes-box">
                <a href="https://www.instagram.com/parquedelasheliconias/" target="_blank">
                    <img src="<?= $rutaBase ?>assets/img/instagram.png" class="icon-md">
                </a>
                <a href="https://www.facebook.com/ParqueHeliconias/" target="_blank">
                    <img src="<?= $rutaBase ?>assets/img/facebook.png" class="icon-md">
                </a>
            </div>
        </div>

    </div>

    <!-- Botón flotante de WhatsApp -->
    <a href="https://wa.me/573157341432?text=¡Hola!%20Quiero%20más%20información%20sobre%20el%20Parque%20de%20las%20Heliconias."
       class="chat-btn" target="_blank">
       💬 ¡Vamos a chatear!
    </a>

</footer>
