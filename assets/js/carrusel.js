// ================================
//  Carrusel automático - Parque Las Heliconias
// ================================

document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".slide");
    const dotsContainer = document.querySelector(".carousel-dots"); // Si existen los puntos
    let index = 0;
    const intervalo = 5000; // Tiempo entre imágenes (ms)

    // Crear puntos dinámicamente si no existen
    if (dotsContainer && slides.length > 1) {
        slides.forEach((_, i) => {
            const dot = document.createElement("span");
            dot.classList.add("dot");
            if (i === 0) dot.classList.add("active");
            dot.addEventListener("click", () => cambiarSlideManual(i));
            dotsContainer.appendChild(dot);
        });
    }

    const dots = dotsContainer ? dotsContainer.querySelectorAll(".dot") : [];

    // Cambia automáticamente entre imágenes
    function cambiarSlide() {
        slides[index].classList.remove("active");
        if (dots.length) dots[index].classList.remove("active");

        index = (index + 1) % slides.length;

        slides[index].classList.add("active");
        if (dots.length) dots[index].classList.add("active");
    }

    // Permitir cambiar manualmente al hacer clic en un punto
    function cambiarSlideManual(nuevoIndex) {
        slides[index].classList.remove("active");
        if (dots.length) dots[index].classList.remove("active");

        index = nuevoIndex;

        slides[index].classList.add("active");
        if (dots.length) dots[index].classList.add("active");
    }

    // Iniciar carrusel
    setInterval(cambiarSlide, intervalo);
});

