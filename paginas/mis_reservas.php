<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');

$id_usuario = $_SESSION['usuario_id'];

// ✅ Mostrar solo reservas pendientes (activas)
$sql = "SELECT r.id_reserva, a.nombre AS actividad, a.descripcion,
               r.fecha_reserva, r.fecha_cancelacion, r.estado, 
               r.tipo_reserva, r.numero_participantes
        FROM reservas r
        INNER JOIN actividades a ON r.id_actividad = a.id_actividad
        WHERE r.id_usuario = $1 AND r.estado = 'pendiente'
        ORDER BY r.fecha_reserva DESC";

$result = pg_query_params($conn, $sql, array($id_usuario));

if (!$result) {
    die("❌ Error en la consulta: " . pg_last_error($conn));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Reservas - Parque de las Heliconias</title>

  <link rel="stylesheet" href="../assets/css/estilos.css">
  <link rel="stylesheet" href="../assets/css/modal.css">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

  <style>
    /* 🌿 Barra de búsqueda + filtro */
    .busqueda-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin: 20px auto;
      max-width: 700px;
    }
    .busqueda-container input,
    .busqueda-container select {
      padding: 10px 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    .busqueda-container input {
      flex: 1;
      min-width: 250px;
    }
    .busqueda-container select {
      background-color: #fff;
      cursor: pointer;
    }
    .grid-reservas {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1rem;
      margin-top: 15px;
    }
    .boton-historial {
      display: inline-block;
      background-color: #2e7d32;
      color: #fff;
      padding: 10px 18px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
    }
    .boton-historial:hover {
      background-color: #256428;
    }
  </style>
</head>

<body>
  <?php include('../includes/header.php'); ?>

  <main class="contenedor-panel reservas-wrapper">
    <h2 class="titulo-bienvenida">📅 Mis Reservas</h2>
    <p class="texto-subtitulo">Consulta tus reservas activas y gestiona tus pendientes.</p>

    <!-- 🔗 Enlace al historial -->
    <div style="text-align: center; margin: 15px 0;">
      <a href="historial_reservas.php" class="boton-historial">📜 Ver historial de reservas</a>
    </div>

    <!-- 🔎 Barra de búsqueda + filtro -->
    <div class="busqueda-container">
      <input type="text" id="buscador" placeholder="Buscar por nombre o fecha (dd/mm/aaaa)...">
    </div>

    <?php if (pg_num_rows($result) > 0): ?>
      <section class="grid-reservas" id="listaReservas">
        <?php while ($reserva = pg_fetch_assoc($result)): ?>
          <?php
            $estadoClase = strtolower($reserva['estado']);
            $icono = [
              'pendiente' => '⏳',
              'confirmada' => '✅',
              'cancelada' => '❌'
            ][$estadoClase] ?? '🟢';
          ?>
          <article class="card-reserva <?php echo $estadoClase; ?>" data-estado="<?php echo $estadoClase; ?>">
            <div class="reserva-header">
              <h3 class="nombre-actividad"><?php echo htmlspecialchars($reserva['actividad']); ?></h3>
              <span class="estado <?php echo $estadoClase; ?>">
                <?php echo $icono . " " . ucfirst($reserva['estado']); ?>
              </span>
            </div>

            <p><b>Tipo:</b> <?php echo ucfirst($reserva['tipo_reserva']); ?></p>
            <p><b>Participantes:</b> <?php echo $reserva['numero_participantes']; ?></p>
            <p><b>Fecha reserva:</b> 
              <span class="fecha-reserva">
                <?php echo date("d/m/Y", strtotime($reserva['fecha_reserva'])); ?>
              </span>
            </p>

            <p class="descripcion"><?php echo htmlspecialchars($reserva['descripcion']); ?></p>

            <div class="acciones-reserva">
              <a href="cancelar_reserva.php?id=<?php echo $reserva['id_reserva']; ?>" 
                 class="btn-cancelar" 
                 onclick="return confirm('¿Cancelar esta reserva?');">
                Cancelar
              </a>

              <a href="detalle_reserva.php?id=<?php echo $reserva['id_reserva']; ?>" 
                 class="btn-detalle">
                Ver Detalle
              </a>
            </div>
          </article>
        <?php endwhile; ?>
      </section>
    <?php else: ?>
      <div class="sin-reservas">
        <img src="../assets/img/no_reservas.svg" alt="Sin reservas" class="img-sin-reservas">
        <h3>¡Aún no tienes reservas activas!</h3>
        <p>Puedes explorar las actividades disponibles y reservar tu próxima experiencia 🌿.</p>
        <a href="actividades.php" class="btn boton-verde">Ver Actividades</a>
      </div>
    <?php endif; ?>
  </main>

  <?php include('../includes/footer.php'); ?>

  <script>
    // 🔍 Filtro combinado (nombre + fecha + estado)
    const buscador = document.getElementById("buscador");
    const filtroEstado = document.getElementById("filtroEstado");
    const reservas = document.querySelectorAll("#listaReservas article");

    function filtrarReservas() {
      const texto = buscador.value.toLowerCase();
      const estado = filtroEstado.value.toLowerCase();

      reservas.forEach(card => {
        const nombre = card.querySelector(".nombre-actividad").textContent.toLowerCase();
        const fecha = card.querySelector(".fecha-reserva").textContent.toLowerCase();
        const estadoCard = card.dataset.estado.toLowerCase();

        const coincideTexto = nombre.includes(texto) || fecha.includes(texto);
        const coincideEstado = !estado || estadoCard === estado;

        card.style.display = (coincideTexto && coincideEstado) ? "block" : "none";
      });
    }

    buscador.addEventListener("keyup", filtrarReservas);
    filtroEstado.addEventListener("change", filtrarReservas);
  </script>
</body>
</html>



