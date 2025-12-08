<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');
include_once('../includes/enviarCorreo.php');

// ===============================
//  🔐 VALIDAR SESIÓN
// ===============================
if (!isset($_SESSION['usuario_id'])) {
    echo "<script>alert('⚠️ Debes iniciar sesión.'); window.location='../login.php';</script>";
    exit;
}

$id_usuario = intval($_SESSION['usuario_id']);

// ===============================
//  🔎 OBTENER DATOS DEL USUARIO
// ===============================
list($codeUser, $userData) = supabase_get("usuarios?id_usuario=eq.$id_usuario&select=*");

if ($codeUser !== 200 || empty($userData)) {
    echo "<script>alert('❌ No se encontró el usuario.'); window.location='actividades.php';</script>";
    exit;
}

$user = $userData[0];

$nombre_usuario  = $user["nombre"];
$apellido_usuario= $user["apellido"];
$doc_usuario     = $user["documento"];
$correo_usuario  = $user["correo"];

// ===============================
//  VALIDACIONES BÁSICAS
// ===============================
if (!isset($_GET['id_actividad']) || !isset($_GET['cantidad'])) {
    echo "<script>alert('❌ Faltan datos para la reserva.'); window.location='actividades.php';</script>";
    exit;
}

$id_actividad = intval($_GET['id_actividad']);
$cantidad     = intval($_GET['cantidad']);

if ($cantidad < 2) {
    echo "<script>alert('⚠️ Una reserva grupal requiere mínimo 2 participantes.'); window.location='actividades.php';</script>";
    exit;
}

// ===============================
//  FUNCIONES AUXILIARES
// ===============================
function calcularEdad($fecha) {
    $hoy = new DateTime();
    $nac = new DateTime($fecha);
    return $hoy->diff($nac)->y;
}

function log_error_email($texto) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents("$logDir/email_errors.log", "[".date("Y-m-d H:i:s")."] $texto\n", FILE_APPEND);
}

// ===============================
//  🚨 PROCESAR FORMULARIO
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fecha_visita = $_POST['fecha_visita'] ?? null;

    if (!$fecha_visita) {
        echo "<script>alert('⚠️ Debes seleccionar una fecha.'); history.back();</script>";
        exit;
    }

    // ===============================
    // 1️⃣ INSERTAR RESERVA GRUPAL
    // ===============================
    $nuevaReserva = [
        "id_usuario"           => $id_usuario,
        "id_actividad"         => $id_actividad,
        "fecha_reserva"        => date("Y-m-d H:i:s"),
        "fecha_visita"         => $fecha_visita,
        "estado"               => "pendiente",
        "tipo_reserva"         => "grupal",
        "numero_participantes" => $cantidad
    ];

    list($codeRes, $dataRes) = supabase_insert("reservas", $nuevaReserva);

    if ($codeRes !== 201) {
        echo "<script>alert('❌ Error al crear la reserva.'); window.location='actividades.php';</script>";
        exit;
    }

    $id_reserva = $dataRes[0]["id_reserva"];

    // ===============================
    // 2️⃣ REGISTRAR PARTICIPANTE CREADOR
    // ===============================
    $fecha_nac_creador = $_POST['fecha_nacimiento_creador'];
    $edad_creador = calcularEdad($fecha_nac_creador);

    $creador = [
        "id_reserva"        => $id_reserva,
        "nombre"            => $nombre_usuario,
        "apellido"          => $apellido_usuario,
        "documento"         => $doc_usuario,
        "telefono"          => $_POST['telefono_creador'] ?? null,
        "edad"              => $edad_creador,
        "sexo"              => $_POST['sexo_creador'] ?? null,
        "ciudad_origen"     => $_POST['ciudad_creador'] ?? null,
        "observaciones"     => $_POST['observaciones_creador'] ?? null,
        "fecha_nacimiento"  => $fecha_nac_creador,
        "es_usuario_registrado" => true,
        "fecha_visita"      => $fecha_visita
    ];

    list($codeCreador, $resCreador) = supabase_insert("participantes_reserva", $creador);

    if ($codeCreador !== 201) {
        echo "<script>alert('❌ No se pudo registrar al creador del grupo.'); window.location='actividades.php';</script>";
        exit;
    }

    // ===============================
    // 3️⃣ PARTICIPANTES ADICIONALES
    // ===============================
    for ($i = 0; $i < $cantidad - 1; $i++) {

        if (!isset($_POST['nombre'][$i]) || empty($_POST['nombre'][$i]))
            continue;

        $edad = calcularEdad($_POST['fecha_nacimiento'][$i]);

        $p = [
            "id_reserva"        => $id_reserva,
            "nombre"            => $_POST['nombre'][$i],
            "apellido"          => $_POST['apellido'][$i],
            "documento"         => $_POST['documento'][$i],
            "telefono"          => $_POST['telefono'][$i] ?? null,
            "edad"              => $edad,
            "sexo"              => $_POST['sexo'][$i],
            "ciudad_origen"     => $_POST['ciudad_origen'][$i],
            "observaciones"     => $_POST['observaciones'][$i] ?? null,
            "fecha_nacimiento"  => $_POST['fecha_nacimiento'][$i],
            "es_usuario_registrado" => false,
            "fecha_visita"      => $fecha_visita
        ];

        supabase_insert("participantes_reserva", $p);
    }

    // ===============================
    // 4️⃣ NOTIFICACIONES
    // ===============================
    $notif_admin = [
        "id_usuario"   => 1,
        "id_reserva"   => $id_reserva,
        "titulo"       => "Nueva reserva grupal",
        "mensaje"      => "El usuario $nombre_usuario creó la reserva grupal #$id_reserva",
        "tipo"         => "info",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida"        => false
    ];

    supabase_insert("notificaciones", $notif_admin);

    $notif_user = [
        "id_usuario"   => $id_usuario,
        "id_reserva"   => $id_reserva,
        "titulo"       => "Reserva registrada",
        "mensaje"      => "Tu reserva grupal para la fecha $fecha_visita fue creada con éxito.",
        "tipo"         => "exito",
        "fecha_creacion" => date("Y-m-d H:i:s"),
        "leida"        => false
    ];

    supabase_insert("notificaciones", $notif_user);

    // ===============================
    // 🎉 TODO OK
    // ===============================
    echo "<script>alert('🎉 ¡Reserva grupal registrada correctamente!'); window.location='mis_reservas.php';</script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reserva Grupal</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-4xl mx-auto mt-8 bg-white p-6 rounded-xl shadow-lg">

    <h2 class="text-3xl font-bold text-green-700 text-center mb-4">
        👨‍👩‍👧‍👦 Reserva Grupal
    </h2>

    <p class="text-center text-gray-600 mb-6">
        Actividad ID: <?= $id_actividad ?> — Personas: <?= $cantidad ?>
    </p>

    <!-- FORMULARIO -->
    <form action="" method="POST">

        <!-- FECHA -->
        <h3 class="text-xl font-semibold text-green-700">📅 Fecha de la visita</h3>
        <input type="date" name="fecha_visita" required 
               min="<?= date('Y-m-d') ?>"
               class="border p-3 mt-2 w-full rounded-lg mb-6">

        <!-- DATOS DEL CREADOR -->
        <h3 class="text-xl font-semibold text-green-700 mb-2">👤 Datos del creador del grupo</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label>Fecha de nacimiento:</label>
                <input type="date" name="fecha_nacimiento_creador" required
                       class="border p-3 w-full rounded-lg">
            </div>
            <div>
                <label>Teléfono:</label>
                <input type="text" name="telefono_creador"
                       class="border p-3 w-full rounded-lg">
            </div>
            <div>
                <label>Sexo:</label>
                <select name="sexo_creador" class="border p-3 w-full rounded-lg">
                    <option value="">Seleccionar...</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="O">Otro</option>
                </select>
            </div>
            <div>
                <label>Ciudad de origen:</label>
                <input type="text" name="ciudad_creador"
                       class="border p-3 w-full rounded-lg">
            </div>
        </div>

        <textarea name="observaciones_creador"
                  class="border p-3 w-full rounded-lg mt-4"
                  placeholder="Observaciones del creador (opcional)"></textarea>


        <!-- PARTICIPANTES ADICIONALES -->
        <h3 class="text-xl font-semibold text-green-700 mt-8">👥 Participantes adicionales</h3>

        <?php for ($i = 0; $i < $cantidad - 1; $i++): ?>
            <div class="border rounded-lg p-4 mt-4 bg-gray-50">
                <h4 class="font-semibold mb-2">Participante <?= $i + 2 ?></h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label>Nombre:</label>
                        <input type="text" name="nombre[]" class="border p-3 w-full rounded-lg">
                    </div>
                    <div>
                        <label>Apellido:</label>
                        <input type="text" name="apellido[]" class="border p-3 w-full rounded-lg">
                    </div>
                    <div>
                        <label>Documento:</label>
                        <input type="text" name="documento[]" class="border p-3 w-full rounded-lg">
                    </div>
                    <div>
                        <label>Teléfono:</label>
                        <input type="text" name="telefono[]" class="border p-3 w-full rounded-lg">
                    </div>
                    <div>
                        <label>Fecha de nacimiento:</label>
                        <input type="date" name="fecha_nacimiento[]" class="border p-3 w-full rounded-lg">
                    </div>
                    <div>
                        <label>Sexo:</label>
                        <select name="sexo[]" class="border p-3 w-full rounded-lg">
                            <option value="">Seleccionar...</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label>Ciudad de origen:</label>
                        <input type="text" name="ciudad_origen[]" class="border p-3 w-full rounded-lg">
                    </div>
                </div>

                <textarea name="observaciones[]" placeholder="Observaciones"
                          class="border p-3 w-full rounded-lg mt-2"></textarea>

            </div>
        <?php endfor; ?>

        <button type="submit"
                class="mt-8 bg-green-700 text-white px-6 py-3 rounded-lg w-full hover:bg-green-800">
            ✔ Registrar reserva grupal
        </button>

    </form>

</div>

</body>
</html>

