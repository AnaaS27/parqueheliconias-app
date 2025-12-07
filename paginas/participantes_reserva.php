<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');
include_once('../includes/enviarCorreo.php');

if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('⚠️ Debes iniciar sesión para realizar una reserva.'); window.location='../login.php';</script>";
    exit;
}

$id_usuario = intval($_SESSION['usuario_id']);

/* ===========================================================
   🔹 1. OBTENER DATOS DEL USUARIO DESDE SUPABASE
   =========================================================== */
$endpoint_user = "usuarios?select=nombre,apellido,documento,correo&id_usuario=eq.$id_usuario";
[$codeU, $dataU] = supabase_get($endpoint_user);

if ($codeU !== 200 || empty($dataU)) {
    echo "<script>alert('Error obteniendo datos del usuario.'); window.location='../login.php';</script>";
    exit;
}

$user = $dataU[0];

$nombre_usuario  = $user["nombre"];
$apellido_usuario = $user["apellido"];
$doc_usuario = $user["documento"];
$correo_usuario = $user["correo"];

/* ===========================================================
   🔹 2. VALIDACIONES
   =========================================================== */
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

/* ===========================================================
   🔹 FUNCIONES
   =========================================================== */
function calcularEdad($fecha_nac) {
    $hoy = new DateTime();
    $n = new DateTime($fecha_nac);
    return $hoy->diff($n)->y;
}

function obtenerCuposDisponibles($id_actividad, $fecha_visita) {
    // cupo máximo
    $endpoint_act = "actividades?select=cupo_maximo&id_actividad=eq.$id_actividad";
    [$c1, $d1] = supabase_get($endpoint_act);

    if ($c1 !== 200 || empty($d1)) return 0;
    $cupo_maximo = $d1[0]["cupo_maximo"];

    // cupos ya reservados
    $endpoint_cupos = "cupos_actividad?select=cupos_reservados&id_actividad=eq.$id_actividad&fecha=eq.$fecha_visita";
    [$c2, $d2] = supabase_get($endpoint_cupos);

    $ocupados = (!empty($d2)) ? $d2[0]["cupos_reservados"] : 0;

    return max($cupo_maximo - $ocupados, 0);
}

/* ===========================================================
   🚨 3. SI ENVIARON EL FORMULARIO
   =========================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fecha_visita = $_POST['fecha_visita'] ?? null;

    if (!$fecha_visita) {
        echo "<script>alert('⚠️ Debes seleccionar una fecha.'); history.back();</script>";
        exit;
    }

    // ✔ Validar cupos
    $cupos = obtenerCuposDisponibles($id_actividad, $fecha_visita);

    if ($cupos < $cantidad) {
        echo "<script>alert('⚠️ Solo hay $cupos cupos disponibles en esa fecha.'); history.back();</script>";
        exit;
    }

    /* ===========================================================
       📝 4. CREAR RESERVA GRUPAL EN SUPABASE
       =========================================================== */
    $reservaData = [
        "id_usuario" => $id_usuario,
        "id_actividad" => $id_actividad,
        "fecha_reserva" => date("Y-m-d H:i:s"),
        "fecha_visita" => $fecha_visita,
        "estado" => "pendiente",
        "tipo_reserva" => "grupal",
        "numero_participantes" => $cantidad
    ];

    [$codeR, $dataR] = supabase_insert("reservas", $reservaData);

    if ($codeR !== 201) {
        echo "<script>alert('Error creando la reserva.'); history.back();</script>";
        exit;
    }

    $id_reserva = $dataR[0]["id_reserva"];

    /* ===========================================================
       🧍 5. REGISTRAR PARTICIPANTE PRINCIPAL
       =========================================================== */
    $fecha_nacimiento_creador = $_POST['fecha_nacimiento_creador'];
    $edad_creador = calcularEdad($fecha_nacimiento_creador);

    $partPrincipal = [
        "id_reserva" => $id_reserva,
        "nombre" => $nombre_usuario,
        "apellido" => $apellido_usuario,
        "documento" => $doc_usuario,
        "telefono" => $_POST['telefono_creador'],
        "edad" => $edad_creador,
        "sexo" => $_POST['sexo_creador'],
        "ciudad_origen" => $_POST['ciudad_creador'],
        "observaciones" => $_POST['observaciones_creador'] ?? null,
        "fecha_nacimiento" => $fecha_nacimiento_creador,
        "es_usuario_registrado" => true
    ];

    supabase_insert("participantes_reserva", $partPrincipal);

    /* ===========================================================
       👥 6. PARTICIPANTES ADICIONALES
       =========================================================== */
    for ($i = 0; $i < $cantidad - 1; $i++) {

        $edad = calcularEdad($_POST['fecha_nacimiento'][$i]);

        $p = [
            "id_reserva" => $id_reserva,
            "nombre" => $_POST['nombre'][$i],
            "apellido" => $_POST['apellido'][$i],
            "documento" => $_POST['documento'][$i],
            "telefono" => $_POST['telefono'][$i] ?? null,
            "edad" => $edad,
            "sexo" => $_POST['sexo'][$i],
            "ciudad_origen" => $_POST['ciudad_origen'][$i],
            "observaciones" => $_POST['observaciones'][$i] ?? null,
            "fecha_nacimiento" => $_POST['fecha_nacimiento'][$i],
            "es_usuario_registrado" => false
        ];

        supabase_insert("participantes_reserva", $p);
    }

    /* ===========================================================
       📌 7. ACTUALIZAR CUPOS (upsert)
       =========================================================== */
    $endpoint_cupos = "cupos_actividad?id_actividad=eq.$id_actividad&fecha=eq.$fecha_visita";

    // Verificar si existe registro
    [$codeC, $dataC] = supabase_get($endpoint_cupos);

    if (!empty($dataC)) {
        // actualizar
        $nuevo = $dataC[0]["cupos_reservados"] + $cantidad;

        supabase_insert("cupos_actividad", [
            "id_actividad" => $id_actividad,
            "fecha" => $fecha_visita,
            "cupos_reservados" => $nuevo
        ]);
    } else {
        // crear
        supabase_insert("cupos_actividad", [
            "id_actividad" => $id_actividad,
            "fecha" => $fecha_visita,
            "cupos_reservados" => $cantidad
        ]);
    }

    /* ===========================================================
       🔔 8. NOTIFICACIONES
       =========================================================== */

    // notificación al admin
    supabase_insert("notificaciones", [
        "id_usuario" => 1,
        "id_reserva" => $id_reserva,
        "titulo" => "Nueva reserva grupal",
        "mensaje" => "Se creó la reserva grupal #$id_reserva",
        "tipo" => "info",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida" => false
    ]);

    // notificación al usuario
    supabase_insert("notificaciones", [
        "id_usuario" => $id_usuario,
        "id_reserva" => $id_reserva,
        "titulo" => "Reserva registrada",
        "mensaje" => "Tu reserva grupal fue creada para el día $fecha_visita",
        "tipo" => "exito",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida" => false
    ]);

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
