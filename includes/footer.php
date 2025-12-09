<?php
// Detectar ruta base solo si no existe
if (!isset($rutaBase)) {
    $rutaBase = (strpos($_SERVER['PHP_SELF'], '/paginas/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
        ? '../'
        : '';
}
?>

<footer class="bg-[#2E7D32] text-white pt-16 pb-20 mt-20">

    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 px-6">

        <!-- 🌿 LOGO CENTRAL -->
        <div class="flex flex-col items-center">
            <img src="<?= $rutaBase ?>assets/img/logoo.png"
                 class="w-52 h-auto object-contain"
                 alt="Logo Parque de las Heliconias">

            <p class="text-[#F4C542] text-base font-semibold mt-4 uppercase tracking-wide text-center">
                Parque de las Heliconias
            </p>
        </div>

        <!-- 📍 UBICACIÓN -->
        <div class="text-center md:text-left">
            <h3 class="text-[#F4C542] font-bold text-xl mb-4 uppercase tracking-wide">Ubicación</h3>

            <div class="flex md:flex-row flex-col items-center md:items-start gap-3">
                <img src="<?= $rutaBase ?>assets/img/ubicacion.png" class="w-6 h-6">

                <p class="leading-tight">
                    Parque de las Heliconias<br>
                    Kilómetro 7 Vía Sevilla – Caicedonia<br>
                    Valle del Cauca, Colombia
                </p>
            </div>
        </div>

        <!-- ⏰ HORARIOS -->
        <div class="text-center md:text-left">
            <h3 class="text-[#F4C542] font-bold text-xl mb-4 uppercase tracking-wide">Horarios</h3>

            <p class="leading-tight">
                <span class="font-bold">Instituciones Educativas:</span><br>
                Lunes a Domingo<br>
                8:00 a.m. – 5:00 p.m.
            </p>

            <p class="mt-4 leading-tight">
                <span class="font-bold">Público General:</span><br>
                Miércoles a Domingo y Festivos<br>
                8:00 a.m. – 5:00 p.m.
            </p>
        </div>

        <!-- ☎ CONTACTO + REDES -->
        <div class="text-center md:text-left">
            <h3 class="text-[#F4C542] font-bold text-xl mb-4 uppercase tracking-wide">Contáctanos</h3>

            <div class="flex items-center justify-center md:justify-start gap-3 mb-3">
                <img src="<?= $rutaBase ?>assets/img/whatsapp.png" class="w-6 h-6">
                <span>+57 315 734 1432</span>
            </div>

            <div class="flex items-center justify-center md:justify-start gap-3">
                <img src="<?= $rutaBase ?>assets/img/correo.png" class="w-6 h-6">
                <span>cea-heliconias@cvc.gov.co</span>
            </div>

            <h3 class="text-[#F4C542] font-bold text-lg mt-6 mb-3 uppercase tracking-wide">Síguenos</h3>

            <div class="flex gap-6 justify-center md:justify-start">
                <img src="<?= $rutaBase ?>assets/img/instagram.png"
                     class="w-10 h-10 object-contain hover:scale-110 transition">
                     
                <img src="<?= $rutaBase ?>assets/img/facebook.png"
                     class="w-10 h-10 object-contain hover:scale-110 transition">
            </div>
        </div>

    </div>

    <!-- BOTÓN FLOTANTE -->
    <a href="https://wa.me/573157341432?text=Hola!%20Quiero%20más%20información%20sobre%20el%20Parque."
       class="fixed bottom-6 right-6 bg-red-600 text-white py-3 px-6 rounded-full shadow-xl hover:bg-red-700 transition flex items-center gap-2 z-50">
       💬 ¡Vamos a chatear!
    </a>

</footer>
