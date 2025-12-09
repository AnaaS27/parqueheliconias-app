<?php
// Solo calcular la ruta base si no existe
if (!isset($rutaBase)) {
    $rutaBase = (strpos($_SERVER['PHP_SELF'], '/paginas/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
        ? '../'
        : '';
}
?>

<!-- Enlace al CSS del footer -->
<link rel="stylesheet" href="<?= $rutaBase ?>assets\css\footer.css">

<footer class="footer">
    <div class="footer-container">
        
        <!-- 🗺️ Columna 1 - Ubicación -->
        <div class="footer-section ubicacion">
            <img src="<?= $rutaBase ?>assets/img/logoo.png" alt="Logo Parque de las Heliconias" class="footer-logo">
            <h3>Ubicación</h3>
            <a href="https://maps.app.goo.gl/h5fW21CPUkmmKE8Q9" target="_blank" class="ubicacion-link">
                <img src="<?= $rutaBase ?>assets/img/ubicacion.png" alt="Ubicación" class="social-icon">
                <span>
                    Parque de las Heliconias<br>
                    Kilómetro 7 Vía Sevilla - Caicedonia<br>
                    Valle del Cauca, Colombia
                </span>
            </a>
        </div>

        <!-- ⏰ Columna 2 - Horarios -->
        <div class="footer-section">
            <h3>Horarios Disponibles</h3>
            <p><strong>Instituciones Educativas:</strong><br>
            Lunes a Domingo<br>
            8:00 a.m. – 5:00 p.m.</p>
            <p><strong>Público General:</strong><br>
            Miércoles a Domingo y Festivos<br>
            8:00 a.m. – 5:00 p.m.</p>
        </div>

        <!-- ☎️ Columna 3 - Contáctanos -->
        <div class="footer-section contacto">
            <h3>Contáctanos</h3>
            <a href="https://wa.me/573157341432?text=¡Hola!%20Estoy%20interesado(a)%20en%20visitar%20el%20Parque%20de%20las%20Heliconias.%20¿Podrían%20darme%20más%20información?" 
               target="_blank" class="whatsapp-link">
                <img src="<?= $rutaBase ?>assets/img/whatsapp.png" alt="WhatsApp" class="social-icon">
                <span>+57 3157341432</span>
            </a>

            <a href="mailto:cea-heliconias@cvc.gov.co" class="correo-link">
               <img src="<?= $rutaBase ?>assets/img/correo.png" alt="Correo" class="social-icon">
                <span>cea-heliconias@cvc.gov.co</span>
            </a> 
        </div>

        <!-- 📱 Columna 4 - Redes sociales -->
        <div class="footer-section redes">
            <h3>Síguenos en redes sociales</h3>
            <div class="social-icons">
                <a href="https://www.instagram.com/parquedelasheliconias/?hl=es" target="_blank">
                    <img src="<?= $rutaBase ?>assets/img/instagram.png" alt="Instagram" class="social-icon">
                </a>
                <a href="https://www.facebook.com/ParqueHeliconias/" target="_blank">
                    <img src="<?= $rutaBase ?>assets/img/facebook.png" alt="Facebook" class="social-icon">
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