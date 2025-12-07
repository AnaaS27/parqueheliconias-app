<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contacto - Parque de las Heliconias</title>
<link rel="stylesheet" href="../assets/css/estilos.css">
<style>
body {
  background: #f5f9f5;
  font-family: "Poppins", sans-serif;
}

main.contacto {
  max-width: 1100px;
  margin: 50px auto;
  padding: 20px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
}

@media (min-width: 992px) {
  main.contacto {
    grid-template-columns: 7fr 5fr;
    align-items: start;
  }
}

.contacto-info {
  background: #fff;
  border: 1px solid #dfe7df;
  border-radius: 15px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border-top: 5px solid #3a7a3b;
}

.contacto-info h2 {
  font-size: 1.8rem;
  color: #2a7a3b;
  margin-bottom: .5rem;
}

.contacto-info p {
  color: #4b5563;
  margin-bottom: 1.5rem;
}

.contacto-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.contacto-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #fff;
  border: 1px solid #dfe7df;
  border-radius: 12px;
  padding: 0.9rem 1rem;
  text-decoration: none;
  color: #1f2937;
  transition: all .2s ease;
}

.contacto-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  border-color: #3a7a3b;
}

.contacto-icon {
  background: #f0fdf4;
  border: 1px solid #dfe7df;
  border-radius: 10px;
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2a7a3b;
  font-size: 20px;
}

.contacto-cta {
  margin-top: 1.8rem;
  display: flex;
  flex-wrap: wrap;
  gap: .8rem;
}

.contacto-btn {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .8rem 1.3rem;
  border-radius: 999px;
  font-weight: 600;
  text-decoration: none;
  box-shadow: 0 8px 20px rgba(0,0,0,.08);
  transition: all .2s ease;
}

.contacto-btn--wa {
  background: #2a7a3b;
  color: #fff;
}

.contacto-btn--wa:hover {
  background: #256f34;
}

.contacto-btn--mail {
  background: #8cc63f;
  color: #fff;
}

.contacto-btn--mail:hover {
  background: #7ab92f;
}

.contacto-map {
  border: 1px solid #dfe7df;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border-top: 5px solid #3a7a3b;
}

iframe {
  width: 100%;
  height: 100%;
  min-height: 360px;
  border: 0;
}
</style>
</head>

<body>
<?php include('../includes/header.php'); ?>

<main class="contacto">
  <section class="contacto-info">
    <h2>📞 Contáctanos</h2>
    <p>Estamos encantados de ayudarte a planear tu visita al Parque de las Heliconias.</p>

    <div class="contacto-list">
      <a class="contacto-item" href="mailto:info@parqueheliconias.com">
        <span class="contacto-icon">📧</span>
        <span><strong>Correo:</strong> info@parqueheliconias.com</span>
      </a>

      <a class="contacto-item" href="https://wa.me/573173034970?text=Hola%20Parque%20de%20Heliconias" target="_blank">
        <span class="contacto-icon">💬</span>
        <span><strong>WhatsApp:</strong> +57 317 3034970</span>
      </a>

      <a class="contacto-item" href="https://www.instagram.com/parqueheliconias" target="_blank">
        <span class="contacto-icon">📸</span>
        <span><strong>Instagram:</strong> @parqueheliconias</span>
      </a>

      <a class="contacto-item" href="https://www.facebook.com/parqueheliconias" target="_blank">
        <span class="contacto-icon">📘</span>
        <span><strong>Facebook:</strong> /parqueheliconias</span>
      </a>
    </div>

    <div class="contacto-cta">
      <a class="contacto-btn contacto-btn--wa" href="https://wa.me/573173034970?text=Hola%20quiero%20informaci%C3%B3n" target="_blank">💬 Escribir por WhatsApp</a>
      <a class="contacto-btn contacto-btn--mail" href="mailto:info@parqueheliconias.com">✉️ Escribir por correo</a>
    </div>
  </section>

  <section class="contacto-map">
    <iframe 
      src="https://www.google.com/maps?q=Parque%20Nacional%20Las%20Heliconias&output=embed" 
      allowfullscreen="" loading="lazy">
    </iframe>
  </section>
</main>

<?php include('../includes/footer.php'); ?>
</body>
</html>
