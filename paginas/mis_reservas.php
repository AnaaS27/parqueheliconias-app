<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

$id_usuario = $_SESSION['usuario_id'];

// -----------------------------------------------------------
// 🔎 CONSULTA A SUPABASE (solo reservas pendientes)
// -----------------------------------------------------------
// Endpoint limpio SIN saltos de línea, sin espacios ilegales
$endpoint =
    "reservas?"
    . "select=id_reserva,fecha_reserva,fecha_cancelacion,estado,tipo_reserva,numero_participantes,"
    . "actividades(nombre,descripcion)"
    . "&id_usuario=eq.$id_usuario"
    . "&estado=eq.pendiente"
    . "&order=fecha_reserva.desc";

// Consumir API REST
list($code, $reservas) = supabase_get($endpoint);

// Mostrar error si falla
if ($code !== 200) {
    echo "<h2>Error consultando Supabase</h2>";
    echo "<pre>";
    var_dump($code);
    var_dump($reservas);
    echo "ENDPOINT: $endpoint";
    echo "</pre>";
    exit;
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
    .busqueda-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin: 20px auto;
      max-width: 700px;
    }
    .busqueda-container input {
      padding: 10px 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
      flex: 1;
      min-width: 250px;
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

<?php if (isset($_SESSION['toast'])): ?>
    <div class="alert bg-green-100 border-l-4 border-green-600 text-green-800 px-4 py-3 mb-4 rounded">
        <?= $_SESSION['toast']['mensaje'] ?>
    </div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>


<main class="contenedor-panel reservas-wrapper">

  <h2 class="titulo-bienvenida">📅 Mis Reservas</h2>
  <p class="texto-subtitulo">Consulta tus reservas activas y gestiona tus pendientes.</p>

  <div style="text-align: center; margin: 15px 0;">
    <a href="historial_reservas.php" class="boton-historial">📜 Ver historial de reservas</a>
  </div>

  <div class="busqueda-container">
    <input type="text" id="buscador" placeholder="Buscar por nombre o fecha...">
  </div>

  <?php if (!empty($reservas)): ?>
    <section class="grid-reservas" id="listaReservas">

      <?php foreach ($reservas as $reserva): ?>

        <?php
          $estadoClase = strtolower($reserva["estado"]);
          $icono = [
            "pendiente" => "⏳",
            "confirmada" => "✅",
            "cancelada" => "❌"
          ][$estadoClase] ?? "🟢";

          $actividad = $reserva["actividades"]["nombre"] ?? "Actividad";
          $descripcion = $reserva["actividades"]["descripcion"] ?? "";
        ?>

        <article class="card-reserva <?= $estadoClase ?>" data-estado="<?= $estadoClase ?>">

          <div class="reserva-header">
            <h3 class="nombre-actividad"><?= htmlspecialchars($actividad) ?></h3>
            <span class="estado <?= $estadoClase ?>">
              <?= $icono . " " . ucfirst($reserva["estado"]) ?>
            </span>
          </div>

          <p><b>Tipo:</b> <?= ucfirst($reserva["tipo_reserva"]) ?></p>
          <p><b>Participantes:</b> <?= $reserva["numero_participantes"] ?></p>

          <p><b>Fecha reserva:</b>
            <span class="fecha-reserva">
              <?= date("d/m/Y", strtotime($reserva["fecha_reserva"])) ?>
            </span>
          </p>

          <p class="descripcion"><?= htmlspecialchars($descripcion) ?></p>

          <div class="acciones-reserva">
            <a href="cancelar_reserva.php?id=<?= $reserva["id_reserva"] ?>" 
               class="btn-cancelar"
               onclick="return confirm('¿Cancelar esta reserva?');">
              Cancelar
            </a>

            <a href="detalle_reserva.php?id=<?= $reserva["id_reserva"] ?>" 
               class="btn-detalle">
              Ver Detalle
            </a>
          </div>

        </article>

      <?php endforeach; ?>

    </section>

  <?php else: ?>

    <div class="sin-reservas">
      <img src="../assets/img/no_reservas.svg" class="img-sin-reservas">
      <h3>¡Aún no tienes reservas activas!</h3>
      <p>Puedes explorar actividades y reservar tu próxima experiencia 🌿.</p>
      <a href="actividades.php" class="btn boton-verde">Ver Actividades</a>
    </div>

  <?php endif; ?>

</main>

<?php include('../includes/footer.php'); ?>

<script>
const buscador = document.getElementById("buscador");
const cards = document.querySelectorAll("#listaReservas article");

buscador.addEventListener("keyup", () => {
    const texto = buscador.value.toLowerCase();

    cards.forEach(card => {
        const nombre = card.querySelector(".nombre-actividad").textContent.toLowerCase();
        const fecha = card.querySelector(".fecha-reserva").textContent.toLowerCase();
        const coincide = nombre.includes(texto) || fecha.includes(texto);

        card.style.display = coincide ? "block" : "none";
    });
});
</script>

</body>
</html>
