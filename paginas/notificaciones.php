<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /parqueheliconias/paginas/login.php");
    exit();
}

include(__DIR__ . '/../includes/conexion.php');
$id_usuario = intval($_SESSION['usuario_id']);

// ------------------------------
// 🗑 ELIMINAR UNA NOTIFICACIÓN
// ------------------------------
if (isset($_GET['borrar'])) {
    pg_query_params($conn, "DELETE FROM notificaciones WHERE id_notificacion = $1 AND id_usuario = $2", [intval($_GET['borrar']), $id_usuario]);
    header("Location: notificaciones.php");
    exit;
}

// ------------------------------
// 🗑 ELIMINAR TODAS
// ------------------------------
if (isset($_GET['borrar_todas'])) {
    pg_query_params($conn, "DELETE FROM notificaciones WHERE id_usuario = $1", [$id_usuario]);
    header("Location: notificaciones.php");
    exit;
}

// ------------------------------
// 📌 CONSULTA AGRUPADA POR FECHA
// ------------------------------
$sql = "
    SELECT 
        DATE(fecha_creacion) AS fecha,
        STRING_AGG(id_notificacion::text, ',') AS ids,
        STRING_AGG(titulo, '||') AS titulos,
        STRING_AGG(mensaje, '||') AS mensajes,
        STRING_AGG(tipo, '||') AS tipos
    FROM notificaciones
    WHERE id_usuario = $1
    GROUP BY DATE(fecha_creacion)
    ORDER BY fecha DESC
";
$result = pg_query_params($conn, $sql, [$id_usuario]);

// ------------------------------
// 🔔 MARCAR COMO LEÍDAS
// ------------------------------
pg_query_params($conn, "UPDATE notificaciones SET leida = TRUE WHERE id_usuario = $1", [$id_usuario]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Notificaciones</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-xl">
    <h2 class="text-3xl font-bold text-green-700 text-center mb-2">🔔 Mis Notificaciones</h2>
    <p class="text-gray-600 text-center mb-6">Consulta tus avisos importantes sobre reservas y actividades.</p>

    <?php if (pg_num_rows($result) > 0): ?>

        <div class="text-right mb-4">
            <a href="?borrar_todas=1" 
               class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition"
               onclick="return confirm('¿Borrar todas las notificaciones?');">
                🗑️ Borrar todas
            </a>
        </div>

        <?php while ($grupo = pg_fetch_assoc($result)): 
            $fecha = date("d/m/Y", strtotime($grupo['fecha']));
            $titulos = explode("||", $grupo["titulos"]);
            $mensajes = explode("||", $grupo["mensajes"]);
            $tipos = explode("||", $grupo["tipos"]);
            $ids = explode(",", $grupo["ids"]);
        ?>

        <!-- ACORDEÓN -->
        <button onclick="toggleAcordeon(this)" 
                class="w-full text-left px-5 py-3 mb-2 bg-green-100 hover:bg-green-200 text-green-800 font-semibold rounded-lg transition flex justify-between">
            <span>📅 <?= $fecha ?> (<?= count($titulos) ?>)</span>
            <span>▼</span>
        </button>

        <div class="hidden mb-4 px-4">

            <?php foreach ($titulos as $i => $titulo): 
                $tipo = $tipos[$i];
                $color = match($tipo) {
                    "error"  => "bg-red-100 border-red-500",
                    "alerta" => "bg-yellow-100 border-yellow-500",
                    "exito"  => "bg-green-100 border-green-500",
                    default  => "bg-blue-100 border-blue-500"
                };
            ?>

            <div class="relative p-4 border-l-4 <?= $color ?> rounded-lg shadow-sm mb-3 animation-slide">
                <button onclick="borrarNotificacion(<?= $ids[$i] ?>)"
                        class="absolute top-2 right-3 text-red-600 font-bold hover:text-red-800 text-lg">
                    ×
                </button>

                <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($titulo) ?></h4>
                <p class="text-gray-700 text-sm"><?= htmlspecialchars($mensajes[$i]) ?></p>
            </div>

            <?php endforeach; ?>

        </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p class="text-center text-gray-600 mt-10">No tienes notificaciones por ahora 🌿.</p>

    <?php endif; ?>
</div>

<script>
function toggleAcordeon(btn) {
    const content = btn.nextElementSibling;
    content.classList.toggle("hidden");
}

function borrarNotificacion(id) {
    if (confirm("¿Deseas eliminar esta notificación?")) {
        window.location = "?borrar=" + id;
    }
}
</script>

<style>
/* Animación suave */
.animation-slide {
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<?php include(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>
