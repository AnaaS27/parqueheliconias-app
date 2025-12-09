<?php
include('header_admin.php');
require_once("../includes/supabase.php");

// ================================
// Validar parámetro ID
// ================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ No se especificó una reserva válida'); window.location='reservas.php';</script>";
    exit;
}

$id_reserva = intval($_GET['id']);

// ================================
// 1️⃣ OBTENER RESERVA DESDE SUPABASE
// ================================
list($codeReserva, $reservaData) = supabase_get("reservas", ["id_reserva" => $id_reserva]);

if ($codeReserva !== 200 || empty($reservaData)) {
    echo "<script>alert('❌ La reserva no existe'); window.location='reservas.php';</script>";
    exit;
}

$reserva = $reservaData[0];

// ================================
// 2️⃣ OBTENER USUARIO RELACIONADO
// ================================
$id_usuario = $reserva["id_usuario"];

list($codeUser, $userData) = supabase_get("usuarios", ["id_usuario" => $id_usuario]);
$usuario = $userData[0] ?? null;

// Seguridad adicional
if (!$usuario) {
    echo "<script>alert('❌ No se encontró el usuario asociado a la reserva'); window.location='reservas.php';</script>";
    exit;
}

// ================================
// 3️⃣ OBTENER ACTIVIDAD ASOCIADA
// ================================
$id_actividad = $reserva["id_actividad"];

list($codeAct, $actividadData) = supabase_get("actividades", ["id_actividad" => $id_actividad]);
$actividad = $actividadData[0] ?? null;

if (!$actividad) {
    echo "<script>alert('❌ No se encontró la actividad asociada a esta reserva'); window.location='reservas.php';</script>";
    exit;
}
?>

<section class="detalle-reserva">
  <h2 class="titulo-dashboard">🔍 Detalle de la Reserva #<?php echo $reserva['id_reserva']; ?></h2>
  <p class="subtitulo-dashboard">Consulta la información completa de la reserva seleccionada.</p>

  <div class="tarjeta-detalle">

    <!-- Usuario -->
    <div class="detalle-columna">
      <h3>👤 Información del Usuario</h3>
      <p><b>Nombre:</b> <?= htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido']); ?></p>
      <p><b>Correo:</b> <?= htmlspecialchars($usuario['correo']); ?></p>
    </div>

    <!-- Actividad -->
    <div class="detalle-columna">
      <h3>🎫 Información de la Actividad</h3>
      <p><b>Actividad:</b> <?= htmlspecialchars($actividad['nombre']); ?></p>
      <p><b>Descripción:</b> <?= htmlspecialchars($actividad['descripcion']); ?></p>
      <p><b>Duración:</b> <?= $actividad['duracion_minutos']; ?> minutos</p>
    </div>

    <!-- Reserva -->
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

  <!-- ACCIONES -->
  <div class="acciones-detalle">
    <?php if ($reserva['estado'] == 'pendiente'): ?>
      <form action="actualizar_estado_reserva.php" method="POST" style="display:inline;">
        <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva']; ?>">
        <input type="hidden" name="estado" value="confirmada">
        <button class="btn-accion confirmar">✅ Confirmar</button>
      </form>

      <form action="actualizar_estado_reserva.php" method="POST" style="display:inline;">
        <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva']; ?>">
        <input type="hidden" name="estado" value="cancelada">
        <button class="btn-accion cancelar">❌ Cancelar</button>
      </form>
    <?php endif; ?>

    <a href="reservas.php" class="btn-volver">↩ Volver</a>
  </div>
</section>

<?php include('footer_admin.php'); ?>