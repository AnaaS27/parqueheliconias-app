<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

$id_usuario = $_SESSION['usuario_id'];

// 1️⃣ Obtener historial de reservas (confirmadas o canceladas)
$filter = "reservas?id_usuario=eq.$id_usuario&or=(estado.eq.confirmada,estado.eq.cancelada)"
        . "&select=id_reserva,fecha_reserva,fecha_cancelacion,estado,tipo_reserva,numero_participantes,"
        . "actividad:actividades(nombre,descripcion)"
        . "&order=fecha_reserva.desc";

list($code, $reservas) = supabase_get($filter);

if ($code !== 200) {
    die("❌ Error consultando historial de reservas en Supabase.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial de Reservas - Parque de las Heliconias</title>

  <link rel="stylesheet" href="../assets/css/estilos.css">
  <link rel="stylesheet" href="../assets/css/modal.css">

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
    .busqueda-container input, .busqueda-container select {
      padding: 10px 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    .busqueda-container input { flex: 1; min-width: 250px; }
    .grid-reservas {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1rem;
      margin-top: 15px;
    }
  </style>
</head>

<body>

<?php include('../includes/header.php'); ?>

<main class="contenedor-panel reservas-wrapper">
    <h2 class="titulo-bienvenida">📜 Historial de Reservas</h2>
    <p class="texto-subtitulo">Consulta todas tus reservas confirmadas o canceladas.</p>

    <div style="text-align: center; margin: 15px 0;">
        <a href="mis_reservas.php" class="btn boton-verde">← Volver a mis reservas</a>
    </div>

    <div class="busqueda-container">
        <input type="text" id="buscador" placeholder="Buscar por nombre o fecha (dd/mm/aaaa)...">
        <select id="filtroEstado">
            <option value="">Todos los estados</option>
            <option value="confirmada">Confirmada</option>
            <option value="cancelada">Cancelada</option>
        </select>
    </div>

    <?php if (!empty($reservas)): ?>
      <section class="grid-reservas" id="listaReservas">
        <?php foreach ($reservas as $reserva): ?>
            <?php
                $estado = strtolower($reserva["estado"]);
                $icono = [
                    "confirmada" => "✅",
                    "cancelada" => "❌"
                ][$estado] ?? "🟢";

                $actividad = $reserva["actividad"]["nombre"] ?? "Actividad";
                $descripcion = $reserva["actividad"]["descripcion"] ?? "";
            ?>
            <article class="card-reserva <?= $estado ?>" data-estado="<?= $estado ?>">
                <div class="reserva-header">
                    <h3 class="nombre-actividad"><?= htmlspecialchars($actividad) ?></h3>
                    <span class="estado <?= $estado ?>">
                        <?= $icono . " " . ucfirst($estado) ?>
                    </span>
                </div>

                <p><b>Tipo:</b> <?= ucfirst($reserva["tipo_reserva"]) ?></p>
                <p><b>Participantes:</b> <?= $reserva["numero_participantes"] ?></p>

                <p><b>Fecha reserva:</b>
                    <span class="fecha-reserva">
                        <?= date("d/m/Y", strtotime($reserva["fecha_reserva"])) ?>
                    </span>
                </p>

                <?php if (!empty($reserva["fecha_cancelacion"])): ?>
                    <p><b>Fecha cancelación:</b>
                        <?= date("d/m/Y H:i", strtotime($reserva["fecha_cancelacion"])) ?>
                    </p>
                <?php endif; ?>

                <p class="descripcion"><?= htmlspecialchars($descripcion) ?></p>

                <div class="acciones-reserva">
                    <a href="detalle_reserva.php?id=<?= $reserva['id_reserva'] ?>" class="btn-detalle">
                        Ver Detalle
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
      </section>
    <?php else: ?>
      <div class="sin-reservas">
        <img src="../assets/img/no_reservas.svg" class="img-sin-reservas">
        <h3>📭 No hay historial todavía</h3>
        <p>Las reservas confirmadas o canceladas aparecerán aquí.</p>
      </div>
    <?php endif; ?>
</main>

<?php include('../includes/footer.php'); ?>

<script>
const buscador = document.getElementById("buscador");
const filtroEstado = document.getElementById("filtroEstado");
const reservas = document.querySelectorAll("#listaReservas article");

function filtrar() {
    const texto = buscador.value.toLowerCase();
    const estado = filtroEstado.value.toLowerCase();

    reservas.forEach(card => {
        const nombre = card.querySelector(".nombre-actividad").textContent.toLowerCase();
        const fecha = card.querySelector(".fecha-reserva").textContent.toLowerCase();
        const estadoCard = card.dataset.estado.toLowerCase();

        const okTexto = nombre.includes(texto) || fecha.includes(texto);
        const okEstado = !estado || estadoCard === estado;

        card.style.display = (okTexto && okEstado) ? "block" : "none";
    });
}

buscador.addEventListener("keyup", filtrar);
filtroEstado.addEventListener("change", filtrar);
</script>

</body>
</html>
