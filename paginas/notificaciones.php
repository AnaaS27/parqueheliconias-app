<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /parqueheliconias/paginas/login.php");
    exit();
}

include('../includes/verificar_sesion.php');
include('../includes/supabase.php');

$id_usuario = intval($_SESSION['usuario_id']);


// ======================================================
// 🗑 ELIMINAR UNA NOTIFICACIÓN
// ======================================================
if (isset($_GET["borrar"])) {
    $id_notificacion = intval($_GET["borrar"]);
    $endpoint = "notificaciones?id_notificacion=eq.$id_notificacion&id_usuario=eq.$id_usuario";

    list($codeDel, $respDel) = supabase_update($endpoint, ["deleted" => true]);

    // Como no manejas borrado lógico, usamos DELETE real:
    if ($codeDel !== 200) {
        // enforce delete using RPC alternative:
        $ch = curl_init($supabase_url . "/rest/v1/notificaciones?id_notificacion=eq.$id_notificacion&id_usuario=eq.$id_usuario");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key",
            "Authorization: Bearer $supabase_key"
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    header("Location: notificaciones.php");
    exit;
}


// ======================================================
// 🗑 ELIMINAR TODAS LAS NOTIFICACIONES
// ======================================================
if (isset($_GET["borrar_todas"])) {
    $url = $supabase_url . "/rest/v1/notificaciones?id_usuario=eq.$id_usuario";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key"
    ]);
    curl_exec($ch);
    curl_close($ch);

    header("Location: notificaciones.php");
    exit;
}


// ======================================================
// 📌 TRAER NOTIFICACIONES AGRUPADAS POR FECHA
// ======================================================
// Supabase no soporta aggregates complejos directamente → agrupamos en PHP
$endpoint = "notificaciones?select=*&id_usuario=eq.$id_usuario&order=fecha_creacion.desc";
list($code, $notificaciones) = supabase_get($endpoint);

if ($code !== 200) {
    die("<h3>Error cargando notificaciones.</h3>");
}

// Agrupar por fecha (Y-m-d)
$grupos = [];
foreach ($notificaciones as $n) {
    $fecha = substr($n["fecha_creacion"], 0, 10);
    if (!isset($grupos[$fecha])) $grupos[$fecha] = [];
    $grupos[$fecha][] = $n;
}


// ======================================================
// 🔔 MARCAR TODAS COMO LEÍDAS
// ======================================================
supabase_update("notificaciones?id_usuario=eq.$id_usuario", ["leida" => true]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Notificaciones</title>
</head>

<body class="bg-gray-100">
<?php include('../includes/header.php'); ?>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-xl">
    <h2 class="text-3xl font-bold text-green-700 text-center mb-2">🔔 Mis Notificaciones</h2>
    <p class="text-gray-600 text-center mb-6">Consulta tus avisos importantes sobre reservas y actividades.</p>

    <?php if (!empty($grupos)): ?>

        <div class="text-right mb-4">
            <a href="?borrar_todas=1" 
               class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition"
               onclick="return confirm('¿Borrar todas las notificaciones?');">
                🗑️ Borrar todas
            </a>
        </div>

        <?php foreach ($grupos as $fecha => $items): ?>

        <?php
            $fechaMostrada = date("d/m/Y", strtotime($fecha));
        ?>

        <!-- ACORDEÓN -->
        <button onclick="toggleAcordeon(this)" 
                class="w-full text-left px-5 py-3 mb-2 bg-green-100 hover:bg-green-200 text-green-800 font-semibold rounded-lg transition flex justify-between">
            <span>📅 <?= $fechaMostrada ?> (<?= count($items) ?>)</span>
            <span>▼</span>
        </button>

        <div class="hidden mb-4 px-4">

            <?php foreach ($items as $n): 

                $color = match($n["tipo"]) {
                    "error"  => "bg-red-100 border-red-500",
                    "alerta" => "bg-yellow-100 border-yellow-500",
                    "exito"  => "bg-green-100 border-green-500",
                    default  => "bg-blue-100 border-blue-500"
                };
            ?>

            <div class="relative p-4 border-l-4 <?= $color ?> rounded-lg shadow-sm mb-3 animation-slide">

                <button onclick="borrarNotificacion(<?= $n['id_notificacion'] ?>)"
                        class="absolute top-2 right-3 text-red-600 font-bold hover:text-red-800 text-lg">
                    ×
                </button>

                <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($n["titulo"]) ?></h4>
                <p class="text-gray-700 text-sm"><?= htmlspecialchars($n["mensaje"]) ?></p>
            </div>

            <?php endforeach; ?>

        </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p class="text-center text-gray-600 mt-10">No tienes notificaciones por ahora 🌿.</p>

    <?php endif; ?>
</div>

<script>
function toggleAcordeon(btn) {
    const panel = btn.nextElementSibling;
    panel.classList.toggle("hidden");
}

function borrarNotificacion(id) {
    if (confirm("¿Deseas eliminar esta notificación?")) {
        window.location = "?borrar=" + id;
    }
}
</script>

<style>
.animation-slide { animation: slideIn 0.3s ease-out; }
@keyframes slideIn {
    from { opacity:0; transform: translateY(6px); }
    to   { opacity:1; transform: translateY(0); }
}
</style>

<?php include('../includes/footer.php'); ?>
</body>
</html>
