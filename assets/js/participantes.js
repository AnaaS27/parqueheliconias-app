// ================================
// Carrusel horizontal de Participantes - Parque Las Heliconias
// ================================

document.addEventListener("DOMContentLoaded", () => {
  const carrusel = document.getElementById("carruselParticipantes");
  if (!carrusel) return; // solo se ejecuta si existe el carrusel

  const tarjetas = carrusel.querySelectorAll(".participante-card");
  const contador = document.getElementById("contador");
  const puntosContainer = document.getElementById("puntosCarrusel");

  let indiceActual = 0;

  // Crear puntos dinámicos
  tarjetas.forEach((_, i) => {
    const punto = document.createElement("div");
    punto.classList.add("punto");
    if (i === 0) punto.classList.add("activo");
    puntosContainer.appendChild(punto);
  });

  const puntos = document.querySelectorAll(".punto");

  // Actualizar contador y puntos
  function actualizarIndicador() {
    contador.textContent = `${indiceActual + 1} / ${tarjetas.length}`;
    puntos.forEach((p, i) => {
      p.classList.toggle("activo", i === indiceActual);
    });
  }

  // Mover carrusel
  window.moverCarrusel = (dir) => {
    indiceActual += dir;
    if (indiceActual < 0) indiceActual = 0;
    if (indiceActual >= tarjetas.length) indiceActual = tarjetas.length - 1;

    const ancho = tarjetas[0].offsetWidth + 25;
    carrusel.scrollTo({
      left: indiceActual * ancho,
      behavior: "smooth"
    });
    actualizarIndicador();
  };

  actualizarIndicador();

  // 🧭 Swipe con mouse o dedo (extra)
  let startX = 0;
  carrusel.addEventListener("touchstart", (e) => startX = e.touches[0].clientX);
  carrusel.addEventListener("touchend", (e) => {
    const endX = e.changedTouches[0].clientX;
    if (startX - endX > 50) moverCarrusel(1);
    else if (endX - startX > 50) moverCarrusel(-1);
  });
});
