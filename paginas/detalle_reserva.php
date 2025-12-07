<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');

// ✅ Validar parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
        alert('⚠️ Parámetro inválido.');
        window.location = 'mis_reservas.php';
    </script>";
    exit;
}

$id_reserva = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];

// ✅ Consulta de detalles de la reserva
$sql = "SELECT r.*, a.nombre AS actividad, a.descripcion, a.duracion_minutos, i.nombre_institucion
        FROM reservas r
        INNER JOIN actividades a ON r.id_actividad = a.id_actividad
        LEFT JOIN instituciones i ON r.id_institucion = i.id_institucion
        WHERE r.id_reserva = $1 AND r.id_usuario = $2";

$result = pg_query_params($conn, $sql, [$id_reserva, $id_usuario]);

if (!$result || pg_num_rows($result) === 0) {
    echo "<script>
        alert('⚠️ Reserva no encontrada o no pertenece a este usuario.');
        window.location = 'mis_reservas.php';
    </script>";
    exit;
}

$reserva = pg_fetch_assoc($result);

// ✅ Obtener información de asistencia
$sql_asistencia = "SELECT * FROM asistencia WHERE id_reserva = $1";
$result_asistencia = pg_query_params($conn, $sql_asistencia, [$id_reserva]);
$asistencia = pg_fetch_assoc($result_asistencia);

// ✅ Obtener participantes (si existen)
$sql_participantes = "SELECT * FROM participantes_reserva WHERE id_reserva = $1";
$participantes = pg_query_params($conn, $sql_participantes, [$id_reserva]);

// ✅ Día en español
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
    $ingles = date('l', strtotime($fecha));
    return $dias[$ingles] ?? $ingles;
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
.form-reserva label {
  font-weight: 600;
  color: #333;
  margin-bottom: 5px;
}
.form-reserva input {
  width: 100%;
  padding: 10px;
  border: 1.5px solid #4b8b3b;
  border-radius: 8px;
  background: #f8fff8;
  transition: all 0.2s;
}
.form-reserva input:focus {
  border-color: #2e7031;
  box-shadow: 0 0 5px rgba(46,112,49,0.3);
  outline: none;
}
.tabla-admin {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
.tabla-admin th {
  background: #2f6930;
  color: #fff;
  padding: 8px;
  text-align: left;
}
.tabla-admin td {
  border: 1px solid #ccc;
  padding: 8px;
}
.tabla-admin input {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 5px;
}
.acciones-detalle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 30px auto;
  max-width: 850px;
}
.btn-cancelar {
  background: #c0392b;
  color: #fff;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: background 0.3s;
}
.btn-cancelar:hover {
  background: #a93226;
}
.boton-verde {
  background: #3a7a3b;
  color: #fff;
  padding: 10px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
}
.boton-verde:hover { background: #2d5f2e; }
</style>
</head>
<body>
<?php include('../includes/header.php'); ?>

<main class="contenedor-panel detalle-wrapper">
  <h2 class="titulo-bienvenida">🪶 Detalle de la Reserva</h2>
  <p class="texto-subtitulo">Consulta toda la información de tu visita al Parque de las Heliconias.</p>

  <!-- 🌿 Información general -->
  <section class="detalle-card">
    <h3>🌿 Información General</h3>
    <p><b>Actividad:</b> <?php echo htmlspecialchars($reserva['actividad']); ?></p>
    <p><b>Descripción:</b> <?php echo htmlspecialchars($reserva['descripcion']); ?></p>
    <p><b>Duración:</b> <?php echo $reserva['duracion_minutos']; ?> minutos</p>
    <p><b>Tipo de reserva:</b> <?php echo ucfirst($reserva['tipo_reserva']); ?></p>
    <p><b>Número de participantes:</b> <?php echo $reserva['numero_participantes']; ?></p>
    <p><b>Estado:</b>
      <span style="color:<?php echo $reserva['estado']==='pendiente'?'#d4a017':($reserva['estado']==='confirmada'?'#28a745':'#c0392b'); ?>; font-weight:600;">
        <?php echo ucfirst($reserva['estado']); ?>
      </span>
    </p>
    <p><b>Fecha de reserva:</b> <?php echo date("d/m/Y H:i", strtotime($reserva['fecha_reserva'])); ?></p>
    <p><b>📅 Día de visita:</b> <?php echo diaEnEspanol($reserva['fecha_visita']) . ", " . date("d/m/Y", strtotime($reserva['fecha_visita'])); ?></p>
  </section>

  <!-- 🧾 Datos del visitante -->
  <?php if ($asistencia): ?>
  <section class="detalle-card">
    <h3>🧾 Datos del Visitante</h3>
    <div class="form-reserva">
      <div>
        <label>Tipo de documento:</label>
        <input type="text" value="<?php echo htmlspecialchars($asistencia['tipo_documento']); ?>" readonly>
      </div>
      <div>
        <label>Número de identificación:</label>
        <input type="text" value="<?php echo htmlspecialchars($asistencia['numero_identificacion']); ?>" readonly>
      </div>
      <div>
        <label>Nacionalidad:</label>
        <input type="text" value="<?php echo htmlspecialchars($asistencia['nacionalidad']); ?>" readonly>
      </div>
      <?php if (!empty($asistencia['nombre_grupo'])): ?>
      <div>
        <label>Nombre del grupo / centro educativo:</label>
        <input type="text" value="<?php echo htmlspecialchars($asistencia['nombre_grupo']); ?>" readonly>
      </div>
      <?php endif; ?>
      <?php if (!empty($reserva['nombre_institucion'])): ?>
      <div>
        <label>Institución / Organización:</label>
        <input type="text" value="<?php echo htmlspecialchars($reserva['nombre_institucion']); ?>" readonly>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- 👥 Participantes -->
  <?php if ($reserva['tipo_reserva'] === 'grupal' && pg_num_rows($participantes) > 0): ?>
  <section class="detalle-card">
      <h3>👥 Participantes</h3>
      <table class="tabla-admin">
          <thead>
              <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Documento</th>
                  <th>Teléfono</th>
              </tr>
          </thead>
          <tbody>
              <?php $i = 1; while ($p = pg_fetch_assoc($participantes)): ?>
              <tr>
                  <td><?php echo $i++; ?></td>
                  <td><input type="text" value="<?php echo htmlspecialchars($p['nombre']); ?>" readonly></td>
                  <td><input type="text" value="<?php echo htmlspecialchars($p['apellido']); ?>" readonly></td>
                  <td><input type="text" value="<?php echo htmlspecialchars($p['documento']); ?>" readonly></td>
                  <td><input type="text" value="<?php echo htmlspecialchars($p['telefono']); ?>" readonly></td>
              </tr>
              <?php endwhile; ?>
          </tbody>
      </table>
  </section>
  <?php endif; ?>


  <div class="acciones-detalle">
    <a href="mis_reservas.php" class="boton-verde">← Volver</a>
    <?php if ($reserva['estado'] === 'pendiente'): ?>
      <a href="cancelar_reserva.php?id=<?php echo $reserva['id_reserva']; ?>" 
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






