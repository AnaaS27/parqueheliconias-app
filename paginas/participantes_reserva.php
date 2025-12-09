<?php
session_start();
include('../includes/verificar_sesion.php');
include('../includes/supabase.php');
include_once('../includes/email_api.php');

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

$nombre_usuario   = $user["nombre"] ?? "";
$apellido_usuario = $user["apellido"] ?? "";
$doc_usuario      = $user["documento"] ?? "";
$correo_usuario   = $user["correo"] ?? "";

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
//  🔎 CONSULTAR LISTAS DESDE SUPABASE
// ===============================
list($codePais, $paises) = supabase_get("pais?select=id,pais&order=pais.asc");
if ($codePais !== 200) $paises = [];

list($codeGen, $generos) = supabase_get("genero?select=id_genero,genero&order=genero.asc");
if ($codeGen !== 200) $generos = [];

list($codeInst, $instituciones) = supabase_get("instituciones?select=id_institucion,nombre_institucion&order=nombre_institucion.asc");
if ($codeInst !== 200) $instituciones = [];

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
    // 1️⃣ CREAR RESERVA GRUPAL
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

    $creador = [
        "id_reserva"          => $id_reserva,
        "nombre"              => $nombre_usuario,
        "apellido"            => $apellido_usuario,
        "documento"           => $doc_usuario,
        "telefono"            => $_POST['telefono_creador'] ?? null,
        "id_genero"           => $_POST['sexo_creador'] ?? null,
        "id_ciudad"           => $_POST['ciudad_creador'] ?? null,
        "fecha_nacimiento"    => $fecha_nac_creador,
        "es_usuario_registrado" => true,
        "fecha_visita"        => $fecha_visita
    ];

    list($codeCreador, $resCreador) = supabase_insert("participantes_reserva", $creador);

    if ($codeCreador !== 201) {
        echo "<script>alert('❌ No se pudo registrar al creador del grupo.'); window.location='actividades.php';</script>";
        exit;
    }

    // ===============================
    // 3️⃣ PARTICIPANTES ADICIONALES
    // ===============================
    $listaParticipantes = [];

    for ($i = 0; $i < $cantidad - 1; $i++) {

        if (empty($_POST['nombre'][$i])) continue;

        $participante = [
            "id_reserva"        => $id_reserva,
            "nombre"            => $_POST['nombre'][$i],
            "apellido"          => $_POST['apellido'][$i],
            "documento"         => $_POST['documento'][$i],
            "telefono"          => $_POST['telefono'][$i] ?? null,
            "id_genero"         => $_POST['sexo'][$i] ?? null,
            "id_ciudad"         => $_POST['ciudad_origen'][$i] ?? null,
            "fecha_nacimiento"  => $_POST['fecha_nacimiento'][$i],
            "es_usuario_registrado" => false,
            "fecha_visita"      => $fecha_visita
        ];

        supabase_insert("participantes_reserva", $participante);

        // Para el correo:
        $listaParticipantes[] = [
            "nombre"   => $_POST['nombre'][$i],
            "apellido" => $_POST['apellido'][$i],
            "documento"=> $_POST['documento'][$i],
            "genero"   => $_POST['sexo'][$i],
            "ciudad"   => $_POST['ciudad_origen'][$i]
        ];
    }

    // ===========================================
    // 4️⃣ NOTIFICACIONES INTERNAS
    // ===========================================
    // Notificación para admin (ID=1)
    supabase_insert("notificaciones", [
        "id_usuario"      => 1,
        "id_reserva"      => $id_reserva,
        "titulo"          => "Nueva reserva grupal",
        "mensaje"         => "El usuario $nombre_usuario ha creado la reserva grupal #$id_reserva",
        "tipo"            => "info",
        "fecha_creacion"  => date("Y-m-d H:i:s"),
        "leida"           => false
    ]);

    // Notificación para el usuario
    supabase_insert("notificaciones", [
        "id_usuario"      => $id_usuario,
        "id_reserva"      => $id_reserva,
        "titulo"          => "Reserva registrada",
        "mensaje"         => "Tu reserva grupal para la fecha $fecha_visita fue creada con éxito.",
        "tipo"            => "exito",
        "fecha_creacion"  => date("Y-m-d H:i:s"),
        "leida"           => false
    ]);

    // ===========================================
    // 5️⃣ ENVIAR CORREO DETALLADO
    // ===========================================

    // Obtener nombre de actividad
    list($codeAct, $actData) = supabase_get("actividades?id_actividad=eq.$id_actividad&select=nombre");
    $actividadNombre = $actData[0]["nombre"] ?? "Actividad";

    // Datos del responsable para el correo
    $responsableInfo = [
        "nombre"   => $nombre_usuario,
        "apellido" => $apellido_usuario,
        "documento"=> $doc_usuario,
        "telefono" => $_POST['telefono_creador'] ?? "",
        "ciudad"   => $_POST['ciudad_creador'] ?? ""
    ];

    enviarCorreoReservaGrupal(
        $correo_usuario,
        $nombre_usuario,
        $id_reserva,
        $fecha_visita,
        $actividadNombre,
        $cantidad,
        $responsableInfo,
        $listaParticipantes
    );

    // ===========================================
    // 🎉 6️⃣ FIN
    // ===========================================
    echo "<script>alert('🎉 ¡Reserva grupal registrada correctamente! Se envió confirmación al correo.'); window.location='mis_reservas.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reserva Grupal</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
/* Animación suave de transición */
.paso {
    display: none;
    animation: fade 0.3s ease-in-out;
}
.paso.activo {
    display: block;
}

@keyframes fade {
  from { opacity: 0; transform: translateY(8px);}
  to   { opacity: 1; transform: translateY(0);}
}
</style>
</head>
<body class="bg-gray-100">

<?php include('../includes/header.php'); ?>

<div class="max-w-3xl mx-auto mt-8 bg-white p-8 shadow-xl rounded-2xl">
    
    <h2 class="text-3xl font-bold text-green-700 text-center mb-2">👥 Reserva Grupal</h2>
    <p class="text-center text-gray-600 mb-6">
        Completa los datos para <b><?= $cantidad ?></b> participantes.
    </p>

    <!-- Barra de progreso -->
    <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
        <div id="barraProgreso"
             class="bg-green-600 h-3 rounded-full transition-all duration-300"
             style="width: 0%;">
        </div>
    </div>

    <!-- FORMULARIO -->
    <form method="POST" class="space-y-8">

        <!-- ============================
             1️⃣ FECHA DE VISITA
        ============================= -->
        <div class="paso activo" id="paso-0">
            <h3 class="text-xl font-semibold text-green-700 mb-4">📅 Selecciona la fecha de visita</h3>

            <input type="date"
                   name="fecha_visita"
                   required
                   min="<?= date('Y-m-d'); ?>"
                   class="w-full p-3 border rounded-lg">

            <div class="flex justify-end mt-6">
                <button type="button"
                        class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800"
                        onclick="siguientePaso()">
                    Siguiente →
                </button>
            </div>
        </div>

        <!-- ============================
             2️⃣ DATOS DEL CREADOR
        ============================= -->
        <div class="paso" id="paso-1">
            <h3 class="text-xl font-semibold text-green-700 mb-4">🧍 Datos del responsable del grupo</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="font-semibold">Nombre</label>
                    <input type="text" value="<?= htmlspecialchars($nombre_usuario) ?>" disabled
                           class="w-full p-3 border rounded-lg bg-gray-100">
                </div>

                <div>
                    <label class="font-semibold">Apellido</label>
                    <input type="text" value="<?= htmlspecialchars($apellido_usuario) ?>" disabled
                           class="w-full p-3 border rounded-lg bg-gray-100">
                </div>

                <div>
                    <label class="font-semibold">Documento</label>
                    <input type="text" value="<?= htmlspecialchars($doc_usuario) ?>" disabled
                           class="w-full p-3 border rounded-lg bg-gray-100">
                </div>

                <div>
                    <label class="font-semibold">Género</label>
                    <select name="sexo_creador" required class="w-full p-3 border rounded-lg">
                        <option value="">Seleccionar...</option>
                        <option value="1">Femenino</option>
                        <option value="2">Masculino</option>
                        <option value="3">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">País</label>
                    <select id="pais_creador" class="w-full p-3 border rounded-lg">
                        <option>Seleccionar...</option>
                        <?php foreach ($paises as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['pais'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">Ciudad</label>
                    <select id="ciudad_creador" name="ciudad_creador"
                            class="w-full p-3 border rounded-lg">
                        <option value="">Seleccione país...</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">Teléfono</label>
                    <input type="text" name="telefono_creador"
                           class="w-full p-3 border rounded-lg">
                </div>

                <div>
                    <label class="font-semibold">Fecha nacimiento</label>
                    <input type="date" name="fecha_nacimiento_creador" required
                           class="w-full p-3 border rounded-lg">
                </div>

            </div>

            <div class="flex justify-between mt-6">
                <button type="button" onclick="anteriorPaso()"
                        class="px-6 py-3 bg-gray-300 rounded-lg">
                    ← Atrás
                </button>
                <button type="button" onclick="siguientePaso()"
                        class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800">
                    Siguiente →
                </button>
            </div>
        </div>

        <!-- ================================
            3️⃣ PARTICIPANTES ADICIONALES
        ================================ -->

        <?php for ($i = 1; $i < $cantidad; $i++): ?>
        <div class="paso" id="paso-<?= $i + 1 ?>">

            <h3 class="text-xl font-semibold text-green-700 mb-4">
                👤 Participante <?= $i + 1 ?> de <?= $cantidad ?>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="font-semibold">Nombre</label>
                    <input type="text" name="nombre[]" required
                           class="w-full p-3 border rounded-lg">
                </div>

                <div>
                    <label class="font-semibold">Apellido</label>
                    <input type="text" name="apellido[]" required
                           class="w-full p-3 border rounded-lg">
                </div>

                <div>
                    <label class="font-semibold">Documento</label>
                    <input type="text" name="documento[]" required
                           class="w-full p-3 border rounded-lg">
                </div>

                <div>
                    <label class="font-semibold">Género</label>
                    <select name="sexo[]" required class="w-full p-3 border rounded-lg">
                        <option value="">Seleccionar...</option>
                        <option value="1">Femenino</option>
                        <option value="2">Masculino</option>
                        <option value="3">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">País</label>
                    <select class="pais w-full p-3 border rounded-lg">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($paises as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['pais'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">Ciudad</label>
                    <select name="ciudad_origen[]" class="ciudad w-full p-3 border rounded-lg">
                        <option>Seleccione país…</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">Teléfono</label>
                    <input type="text" name="telefono[]" class="w-full p-3 border rounded-lg">
                </div>

                <div>
                    <label class="font-semibold">Fecha nacimiento</label>
                    <input type="date" name="fecha_nacimiento[]" required
                           class="w-full p-3 border rounded-lg">
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold">Observaciones</label>
                    <textarea name="observaciones[]" rows="2"
                              class="w-full p-3 border rounded-lg"></textarea>
                </div>

            </div>

            <div class="flex justify-between mt-6">
                <button type="button" onclick="anteriorPaso()"
                        class="px-6 py-3 bg-gray-300 rounded-lg">
                    ← Atrás
                </button>

                <?php if ($i == $cantidad - 1): ?>
                    <!-- Último paso -->
                    <button type="submit"
                            class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800">
                        ✔ Confirmar Reserva
                    </button>
                <?php else: ?>
                    <button type="button" onclick="siguientePaso()"
                            class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800">
                        Siguiente →
                    </button>
                <?php endif; ?>
            </div>

        </div>
        <?php endfor; ?>

    </form>
</div>


<script>
let pasoActual = 0;
const pasos = document.querySelectorAll(".paso");
const barra = document.getElementById("barraProgreso");

function actualizarProgressBar() {
    const porcentaje = (pasoActual / (pasos.length - 1)) * 100;
    barra.style.width = porcentaje + "%";
}

function mostrarPaso(i) {
    pasos.forEach(p => p.classList.remove("activo"));
    pasos[i].classList.add("activo");
    pasoActual = i;
    actualizarProgressBar();
}

function siguientePaso() {
    if (pasoActual < pasos.length - 1) {
        mostrarPaso(pasoActual + 1);
    }
}

function anteriorPaso() {
    if (pasoActual > 0) {
        mostrarPaso(pasoActual - 1);
    }
}

mostrarPaso(0);

// AJAX País → Ciudades
document.addEventListener("change", e => {
    if (e.target.classList.contains("pais")) {
        const paisID = e.target.value;
        const ciudadSelect = e.target.closest("div").nextElementSibling.querySelector(".ciudad");
        ciudadSelect.innerHTML = "<option>Cargando...</option>";

        fetch("ajax_ciudades.php?pais=" + paisID)
            .then(r => r.json())
            .then(ciudades => {
                ciudadSelect.innerHTML = "";
                ciudades.forEach(c => {
                    ciudadSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
                });
            });
    }
});

// CREATOR: País → Ciudad
document.getElementById("pais_creador").addEventListener("change", function () {
    const pais = this.value;
    const ciudadSel = document.getElementById("ciudad_creador");
    ciudadSel.innerHTML = "<option>Cargando...</option>";

    fetch("ajax_ciudades.php?pais=" + pais)
        .then(r => r.json())
        .then(data => {
            ciudadSel.innerHTML = "";
            data.forEach(c => ciudadSel.innerHTML += `<option value="${c.id}">${c.nombre}</option>`);
        });
});
</script>

</body>
</html>
