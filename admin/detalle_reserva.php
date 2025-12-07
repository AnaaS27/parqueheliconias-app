<?php
include('header_admin.php');
include('../includes/conexion.php');

// Validar parámetro ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ No se especificó una reserva válida'); window.location='reservas.php';</script>";
    exit;
}

$id_reserva = intval($_GET['id']);

// Consulta versión PostgreSQL
$sql = "
    SELECT r.id_reserva, r.fecha_reserva, r.estado, r.tipo_reserva, r.numero_participantes,
           r.fecha_cancelacion,
           u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, u.correo,
           a.nombre AS actividad, a.descripcion, a.duracion_minutos
    FROM reservas r
    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
    INNER JOIN actividades a ON r.id_actividad = a.id_actividad
    WHERE r.id_reserva = $1
";

$result = pg_query_params($conn, $sql, [$id_reserva]);

if (!$result || pg_num_rows($result) == 0) {
    echo "<script>alert('❌ La reserva no existe'); window.location='reservas.php';</script>";
    exit;
}

$reserva = pg_fetch_assoc($result);
?>

<section class="detalle-reserva">
  <h2 class="titulo-dashboard">🔍 Detalle de la Reserva #<?php echo $reserva['id_reserva']; ?></h2>
  <p class="subtitulo-dashboard">Consulta la información completa de la reserva seleccionada.</p>

  <div class="tarjeta-detalle">
    <div class="detalle-columna">
      <h3>👤 Información del Usuario</h3>
      <p><b>Nombre:</b> <?= htmlspecialchars($reserva['nombre_usuario'] . " " . $reserva['apellido_usuario']); ?></p>
      <p><b>Correo:</b> <?= htmlspecialchars($reserva['correo']); ?></p>
    </div>

    <div class="detalle-columna">
      <h3>🎫 Información de la Actividad</h3>
      <p><b>Actividad:</b> <?= htmlspecialchars($reserva['actividad']); ?></p>
      <p><b>Descripción:</b> <?= htmlspecialchars($reserva['descripcion']); ?></p>
      <p><b>Duración:</b> <?= $reserva['duracion_minutos']; ?> minutos</p>
    </div>

    <div class="detalle-columna">
      <h3>📅 Información de la Reserva</h3>
      <p><b>Fecha de reserva:</b> <?= date("d/m/Y H:i", strtotime($reserva['fecha_reserva'])); ?></p>
      <p><b>Tipo:</b> <?= ucfirst($reserva['tipo_reserva']); ?></p>
      <p><b>N° Participantes:</b> <?= $reserva['numero_participantes']; ?></p>

      <p><b>Estado:</b>
        <?php if ($reserva['estado'] == 'pendiente'): ?>
          <span class="estado-pendiente">🕒 Pendiente</span>
        <?php elseif ($reserva['estado'] == 'confirmada'): ?>
          <span class="estado-confirmada">✅ Confirmada</span>
        <?php elseif ($reserva['estado'] == 'cancelada'): ?>
          <span class="estado-cancelada">❌ Cancelada</span>
        <?php endif; ?>
      </p>

      <?php if (!empty($reserva['fecha_cancelacion'])): ?>
        <p><b>Fecha de cancelación:</b> <?= date("d/m/Y H:i", strtotime($reserva['fecha_cancelacion'])); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="acciones-detalle">
    <?php if ($reserva['estado'] == 'pendiente'): ?>
      <a href="actualizar_estado.php?id=<?= $reserva['id_reserva']; ?>&estado=confirmada" class="btn-accion confirmar">✅ Confirmar</a>
      <a href="actualizar_estado.php?id=<?= $reserva['id_reserva']; ?>&estado=cancelada" class="btn-accion cancelar">❌ Cancelar</a>
    <?php endif; ?>

    <a href="reservas.php" class="btn-volver">↩️ Volver</a>
  </div>
</section>

<?php include('footer_admin.php'); ?>
