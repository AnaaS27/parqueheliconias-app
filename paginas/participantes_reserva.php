<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/conexion.php');
include_once('../includes/enviarCorreo.php');

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('⚠️ Debes iniciar sesión para realizar una reserva.'); window.location='../login.php';</script>";
    exit;
}

$id_usuario = intval($_SESSION['usuario_id']);

// ------------------
// 🔹 Obtener datos del usuario
// ------------------
$sql_user = "SELECT nombre, apellido, documento, correo FROM usuarios WHERE id_usuario = $1";
$res_user = pg_query_params($conn, $sql_user, [$id_usuario]);
$user = pg_fetch_assoc($res_user);

if (!$user) {
    echo "<script>alert('Error obteniendo datos del usuario.'); window.location='../login.php';</script>";
    exit;
}

$nombre_usuario = $user["nombre"];
$apellido_usuario = $user["apellido"];
$doc_usuario = $user["documento"];
$correo_usuario = $user["correo"];

// ------------------
// Validaciones básicas
// ------------------
if (!isset($_GET['id_actividad']) || !isset($_GET['cantidad'])) {
    echo "<script>alert('❌ Faltan datos para realizar la reserva.'); window.location='actividades.php';</script>";
    exit;
}

$id_actividad = intval($_GET['id_actividad']);
$cantidad = intval($_GET['cantidad']);

if ($cantidad < 2) {
    echo "<script>alert('⚠️ Una reserva grupal requiere mínimo 2 participantes.'); window.location='actividades.php';</script>";
    exit;
}

// ------------------
// Funciones
// ------------------
function calcularEdad($fecha_nac) {
    $hoy = new DateTime();
    $nacimiento = new DateTime($fecha_nac);
    return $hoy->diff($nacimiento)->y;
}

function obtenerCuposDisponibles($conn, $id_actividad, $fecha_visita) {
    $res_act = pg_query_params($conn, "SELECT cupo_maximo FROM actividades WHERE id_actividad=$1", [$id_actividad]);
    $row_act = pg_fetch_assoc($res_act);
    $cupo_maximo = $row_act['cupo_maximo'] ?? 0;

    $res_cupos = pg_query_params($conn, "SELECT cupos_reservados FROM cupos_actividad WHERE id_actividad=$1 AND fecha=$2", [$id_actividad, $fecha_visita]);
    $row_cupos = pg_fetch_assoc($res_cupos);

    $ocupados = $row_cupos['cupos_reservados'] ?? 0;
    return max($cupo_maximo - $ocupados, 0);
}

function log_error_email($texto) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents("$logDir/email_errors.log", "[".date("Y-m-d H:i:s")."] $texto\n", FILE_APPEND);
}

// ------------------
// 🚨 Cuando envían el formulario
// ------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fecha_visita = $_POST['fecha_visita'] ?? null;

    if (!$fecha_visita) {
        echo "<script>alert('⚠️ Debes seleccionar una fecha.'); history.back();</script>";
        exit;
    }

    $cupos = obtenerCuposDisponibles($conn, $id_actividad, $fecha_visita);

    if ($cupos < $cantidad) {
        echo "<script>alert('⚠️ Solo hay $cupos cupos disponibles en esa fecha.'); history.back();</script>";
        exit;
    }

    // -------------------------
    // 📝 Crear Reserva Grupal
    // -------------------------
    $sql_reserva = "INSERT INTO reservas (id_usuario, id_actividad, fecha_reserva, fecha_visita, estado, tipo_reserva, numero_participantes)
                    VALUES ($1,$2,CURRENT_TIMESTAMP,$3,'pendiente','grupal',$4)
                    RETURNING id_reserva";

    $data_reserva = pg_query_params($conn, $sql_reserva, [$id_usuario, $id_actividad, $fecha_visita, $cantidad]);
    $id_reserva = pg_fetch_assoc($data_reserva)['id_reserva'];

    // -------------------------
    // 🧍 Registrar primer participante
    // -------------------------
    $fecha_nacimiento_creador = $_POST['fecha_nacimiento_creador'];
    $edad_creador = calcularEdad($fecha_nacimiento_creador);

    $sql_participante = "INSERT INTO participantes_reserva
        (id_reserva,nombre,apellido,documento,telefono,edad,sexo,ciudad_origen,observaciones,fecha_nacimiento,es_usuario_registrado)
        VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,1)";

    pg_query_params($conn, $sql_participante, [
        $id_reserva,
        $nombre_usuario,
        $apellido_usuario,
        $doc_usuario,
        $_POST['telefono_creador'],
        $edad_creador,
        $_POST['sexo_creador'],
        $_POST['ciudad_creador'],
        $_POST['observaciones_creador'] ?? null,
        $fecha_nacimiento_creador
    ]);

    // -------------------------
    // 👥 Registrar participantes adicionales
    // -------------------------
    for ($i = 0; $i < $cantidad - 1; $i++) {
        $edad = calcularEdad($_POST['fecha_nacimiento'][$i]);

        pg_query_params($conn, $sql_participante, [
            $id_reserva,
            $_POST['nombre'][$i],
            $_POST['apellido'][$i],
            $_POST['documento'][$i],
            $_POST['telefono'][$i] ?? null,
            $edad,
            $_POST['sexo'][$i],
            $_POST['ciudad_origen'][$i],
            $_POST['observaciones'][$i] ?? null,
            $_POST['fecha_nacimiento'][$i]
        ]);
    }

    // -------------------------
    // 📌 Actualizar cupos
    // -------------------------
    $sql_cupo = "INSERT INTO cupos_actividad (id_actividad, fecha, cupos_reservados)
                 VALUES ($1,$2,$3)
                 ON CONFLICT (id_actividad, fecha)
                 DO UPDATE SET cupos_reservados = cupos_actividad.cupos_reservados + EXCLUDED.cupos_reservados";

    pg_query_params($conn, $sql_cupo, [$id_actividad, $fecha_visita, $cantidad]);

    // -------------------------
    // 🔔 Crear notificaciones
    // -------------------------
    pg_query_params($conn,
        "INSERT INTO notificaciones (id_usuario,id_reserva,titulo,mensaje,tipo,fecha_creacion,leida)
         VALUES ($1,$2,$3,$4,$5,CURRENT_TIMESTAMP,0)",
        [1, $id_reserva, "Nueva reserva grupal", "Un usuario hizo una reserva grupal #$id_reserva", "info"]
    );

    pg_query_params($conn,
        "INSERT INTO notificaciones (id_usuario,id_reserva,titulo,mensaje,tipo,fecha_creacion,leida)
         VALUES ($1,$2,$3,$4,$5,CURRENT_TIMESTAMP,0)",
        [$id_usuario, $id_reserva, "Reserva registrada", "Tu reserva grupal para $fecha_visita fue creada con éxito", "exito"]
    );

    echo "<script>alert('🎉 ¡Reserva grupal registrada correctamente!'); window.location='mis_reservas.php';</script>";
    exit;
}

?>

<!-- Formulario HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reserva Grupal - Parque Las Heliconias</title>
<link rel="stylesheet" href="../assets/css/estilos.css">
<style>
/* Igual que antes */
</style>
</head>
<body>
<?php include('../includes/header.php'); ?>

<main class="contenedor-panel detalle-wrapper">
<section class="detalle-card">
<h2>👥 Reserva Grupal</h2>
<p>Completa los datos para los <b><?php echo $cantidad; ?></b> participantes.</p>

<form method="POST">
<label>📅 Fecha de visita:</label>
<input type="date" name="fecha_visita" required min="<?php echo date('Y-m-d'); ?>">

<h3>🏫 Datos del grupo</h3>
<div class="form-grid">
  <div><label>Nacionalidad:</label><input type="text" name="nacionalidad"></div>
  <div><label>Nombre del grupo:</label><input type="text" name="nombre_grupo"></div>
  <div><label>Institución / Organización:</label><input type="text" name="institucion"></div>
</div>

<h3>🧍 Participante 1 (Usuario logueado)</h3>
<div class="form-grid">
  <div><label>Nombre:</label><input type="text" value="<?php echo htmlspecialchars($nombre_usuario); ?>" disabled></div>
  <div><label>Apellido:</label><input type="text" value="<?php echo htmlspecialchars($apellido_usuario); ?>" disabled></div>
  <div><label>Documento:</label><input type="text" value="<?php echo htmlspecialchars($doc_usuario); ?>" disabled></div>
  <div><label>Sexo:</label>
    <select name="sexo_creador" required>
      <option value="">Seleccionar...</option>
      <option value="Femenino">Femenino</option>
      <option value="Masculino">Masculino</option>
      <option value="Otro">Otro</option>
    </select>
  </div>
  <div><label>Ciudad / País de origen:</label><input type="text" name="ciudad_creador" required></div>
  <div><label>Teléfono:</label><input type="text" name="telefono_creador"></div>
  <div><label>Fecha de nacimiento:</label><input type="date" name="fecha_nacimiento_creador" required></div>
  <div><label>Observaciones:</label><textarea name="observaciones_creador" rows="2"></textarea></div>
</div>

<h3>👥 Participantes adicionales</h3>
<div id="carruselParticipantes">
<?php for ($i = 1; $i < $cantidad; $i++): ?>
<fieldset class="participante-card <?php echo $i === 1 ? 'active' : ''; ?>">
<legend>Participante <?php echo $i + 1; ?></legend>
<div class="form-grid">
  <div><label>Nombre:</label><input type="text" name="nombre[]" required></div>
  <div><label>Apellido:</label><input type="text" name="apellido[]" required></div>
  <div><label>Documento:</label><input type="text" name="documento[]" required></div>
  <div><label>Sexo:</label>
    <select name="sexo[]" required>
      <option value="">Seleccionar...</option>
      <option value="Femenino">Femenino</option>
      <option value="Masculino">Masculino</option>
      <option value="Otro">Otro</option>
    </select>
  </div>
  <div><label>Ciudad / País de origen:</label><input type="text" name="ciudad_origen[]" required></div>
  <div><label>Teléfono:</label><input type="text" name="telefono[]"></div>
  <div><label>Fecha de nacimiento:</label><input type="date" name="fecha_nacimiento[]" required></div>
  <div><label>Observaciones:</label><textarea name="observaciones[]" rows="2"></textarea></div>
</div>
</fieldset>
<?php endfor; ?>
</div>

<div style="text-align:center;margin-top:15px;">
  <button type="button" onclick="moverCarrusel(-1)">⬅ Anterior</button>
  <button type="button" onclick="moverCarrusel(1)">Siguiente ➡</button>
</div>

<div style="display:flex;justify-content:space-between;margin-top:20px;">
  <a href="actividades.php" style="background:#ccc;color:#333;padding:8px 14px;border-radius:8px;text-decoration:none;">← Volver</a>
  <button type="submit">✅ Confirmar Reserva</button>
</div>
</form>
</section>
</main>

<script>
let indice = 0;
const tarjetas = document.querySelectorAll(".participante-card");
function moverCarrusel(dir) {
  tarjetas[indice].classList.remove("active");
  indice = (indice + dir + tarjetas.length) % tarjetas.length;
  tarjetas[indice].classList.add("active");
}
</script>

<?php include('../includes/footer.php'); ?>
</body>
</html>
