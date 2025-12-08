<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

// ----------------------
// ✔ Validar parámetro ID
// ----------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
        alert('⚠️ Parámetro inválido.');
        window.location = 'mis_reservas.php';
    </script>";
    exit;
}

$id_reserva = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];

// -----------------------------------------------------------
// 🔍 CONSULTA PRINCIPAL DE LA RESERVA
// -----------------------------------------------------------
$endpoint =
    "reservas?"
    . "select=id_reserva,id_usuario,id_actividad,id_institucion,fecha_reserva,fecha_visita,estado,"
    . "tipo_reserva,numero_participantes,"
    . "actividades(nombre,descripcion,duracion_minutos),"
    . "instituciones(nombre_institucion)"
    . "&id_reserva=eq.$id_reserva"
    . "&id_usuario=eq.$id_usuario";

list($codeReserva, $dataReserva) = supabase_get($endpoint);

// validar
if ($codeReserva !== 200 || empty($dataReserva)) {
    echo "<script>
        alert('⚠️ Reserva no encontrada o no pertenece al usuario.');
        window.location = 'mis_reservas.php';
    </script>";
    exit;
}

$reserva = $dataReserva[0];

// -----------------------------------------------------------
// 🔍 CONSULTAR ASISTENCIA (si existe)
// -----------------------------------------------------------
list($codeAsis, $asisData) =
    supabase_get("asistencia?id_reserva=eq.$id_reserva&select=*");

$asistencia = (!empty($asisData)) ? $asisData[0] : null;

// -----------------------------------------------------------
// 🔍 CONSULTAR PARTICIPANTES
// -----------------------------------------------------------
list($codePart, $participantes) =
    supabase_get("participantes_reserva?id_reserva=eq.$id_reserva&select=*");

// -----------------------------------------------------------
// 📅 Función día en español
// -----------------------------------------------------------
function diaEnEspanol($fecha) {
    $dias = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return $dias[date('l', strtotime($fecha))] ?? $fecha;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detalle de Reserva - Parque de las Heliconias</title>
<link rel="stylesheet" href="../assets/css/estilos.css">

<style>
<?php /* (Mantenemos tu CSS tal cual) */ ?>
body {
  background: #f5f9f5;
  font-family: "Poppins", sans-serif;
}
.detalle-card {
  max-width: 850px;
  margin: 30px auto;
  background: #fff;
  padding: 30px 40px;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-top: 5px solid #3a7a3b;
}
h2.titulo-bienvenida {
  text-align: center;
  color: #2f6930;
}
.texto-subtitulo {
  text-align: center;
  color: #555;
  margin-bottom: 25px;
}
.detalle-card h3 {
  color: #2e6a30;
  margin-bottom: 10px;
  border-bottom: 2px solid #cde0ce;
  padding-bottom: 5px;
}
.form-reserva {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 15px;
  margin-top: 10px;
}
.form-reserva label { font-weight: 600; color: #333; margin-bottom: 5px; }
.form-reserva input {
  width: 100%; padding: 10px; border: 1.5px solid #4b8b3b;
  border-radius: 8px; background: #f8fff8;
}
.tabla-admin {
  width: 100%; border-collapse: collapse; margin-top: 10px;
}
.tabla-admin th {
  background: #2f6930; color: #fff; padding: 8px; text-align: left;
}
.tabla-admin td {
  border: 1px solid #ccc; padding: 8px;
}
.acciones-detalle {
  display: flex; justify-content: space-between;
  align-items: center; margin: 30px auto; max-width: 850px;
}
.btn-cancelar {
  background: #c0392b; color: #fff; padding: 10px 16px;
  border-radius: 8px; text-decoration: none; font-weight: 600;
}
</style>
</head>

<body>
<?php include('../includes/header.php'); ?>

<main class="contenedor-panel detalle-wrapper">

  <h2 class="titulo-bienvenida">🪶 Detalle de la Reserva</h2>
  <p class="texto-subtitulo">Consulta toda la información de tu visita al Parque de las Heliconias.</p>

  <!-- 🌿 Información General -->
  <section class="detalle-card">
    <h3>🌿 Información General</h3>

    <p><b>Actividad:</b> <?= htmlspecialchars($reserva["actividades"]["nombre"]) ?></p>
    <p><b>Descripción:</b> <?= htmlspecialchars($reserva["actividades"]["descripcion"]) ?></p>
    <p><b>Duración:</b> <?= $reserva["actividades"]["duracion_minutos"] ?> minutos</p>
    <p><b>Tipo de reserva:</b> <?= ucfirst($reserva["tipo_reserva"]) ?></p>
    <p><b>Participantes:</b> <?= $reserva["numero_participantes"] ?></p>

    <p><b>Estado:</b>
      <span style="font-weight:600; color:
            <?= $reserva['estado']==='pendiente'?'#d4a017':
               ($reserva['estado']==='confirmada'?'#28a745':'#c0392b'); ?>">
        <?= ucfirst($reserva["estado"]) ?>
      </span>
    </p>

    <p><b>Fecha Reserva:</b>
      <?= date("d/m/Y H:i", strtotime($reserva["fecha_reserva"])) ?>
    </p>

    <p><b>📅 Día de visita:</b>
      <?= diaEnEspanol($reserva["fecha_visita"]) ?>,
      <?= date("d/m/Y", strtotime($reserva["fecha_visita"])) ?>
    </p>

    <?php if (!empty($reserva["instituciones"]["nombre_institucion"])): ?>
      <p><b>Institución:</b> <?= $reserva["instituciones"]["nombre_institucion"] ?></p>
    <?php endif; ?>
  </section>

  <!-- 🧾 Datos del Visitante (Asistencia) -->
  <?php if ($asistencia): ?>
  <section class="detalle-card">
    <h3>🧾 Datos del Visitante</h3>

    <div class="form-reserva">
      <div>
        <label>Tipo de documento:</label>
        <input type="text" value="<?= $asistencia['tipo_documento'] ?>" readonly>
      </div>

      <div>
        <label>Número de identificación:</label>
        <input type="text" value="<?= $asistencia['numero_identificacion'] ?>" readonly>
      </div>

      <div>
        <label>Nacionalidad:</label>
        <input type="text" value="<?= $asistencia['nacionalidad'] ?>" readonly>
      </div>

      <?php if (!empty($asistencia["nombre_grupo"])): ?>
      <div>
        <label>Grupo / Centro educativo:</label>
        <input type="text" value="<?= $asistencia['nombre_grupo'] ?>" readonly>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- 👥 Participantes -->
  <?php if ($reserva["tipo_reserva"] === "grupal" && !empty($participantes)): ?>
  <section class="detalle-card">
      <h3>👥 Participantes</h3>

      <table class="tabla-admin">
        <thead>
          <tr>
            <th>#</th><th>Nombre</th><th>Apellido</th><th>Documento</th><th>Teléfono</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach ($participantes as $p): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($p["nombre"]) ?></td>
            <td><?= htmlspecialchars($p["apellido"]) ?></td>
            <td><?= htmlspecialchars($p["documento"]) ?></td>
            <td><?= htmlspecialchars($p["telefono"]) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
  </section>
  <?php endif; ?>

  <!-- BOTONES -->
  <div class="acciones-detalle">
    <a href="mis_reservas.php" class="boton-verde">← Volver</a>

    <?php if ($reserva["estado"] === "pendiente"): ?>
      <a href="cancelar_reserva.php?id=<?= $reserva["id_reserva"] ?>"
         class="btn-cancelar"
         onclick="return confirm('¿Deseas cancelar esta reserva?');">
         Cancelar Reserva
      </a>
    <?php endif; ?>
  </div>

</main>

<?php include('../includes/footer.php'); ?>
</body>
</html>
