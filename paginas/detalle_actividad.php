<?php
include('../includes/header.php'); // Aquí ya está supabase_get()

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    die("<h2 style='text-align:center;margin:100px;color:red;'>ID inválido</h2>");
}

/* ----------------------------------------------------------
   🔍 CONSULTAR ACTIVIDAD DESDE SUPABASE
-----------------------------------------------------------*/
list($status, $data) = supabase_get("actividades?id_actividad=eq.$id&select=*");

if ($status !== 200 || empty($data)) {
    die("<h2 style='text-align:center;margin:100px;color:red;'>Actividad no encontrada</h2>");
}

$item = $data[0];

// Evitar NULLs
$descripcion = $item['descripcion'] ?? 'Descripción no disponible';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($item['nombre']) ?></title>

<style>
body{font-family:Arial;background:#f8f9fa;padding:40px}
.detalle-container{max-width:1200px;margin:auto;background:white;border-radius:25px;box-shadow:0 15px 50px rgba(0,0,0,.2);overflow:hidden}
.header-grid{display:grid;grid-template-columns:1fr 1fr;gap:50px;padding:50px}

.detalle-img img{width:100%;border-radius:20px}
.detalle-texto h1{font-size:2.5rem;color:#1a4d2e}
.detalle-texto p{font-size:1.2rem;line-height:1.8}

.tabla{margin:40px;border-collapse:collapse;width:calc(100% - 80px)}
.tabla td{border:1px solid #ddd;padding:20px}

.botones{text-align:center;margin-bottom:50px}
.btn-reserva{
    background:#ffca28;
    color:#1a4d2e;
    padding:20px 60px;
    border-radius:50px;
    font-size:1.5rem;
    font-weight:bold;
    border:none;
    cursor:pointer;
}

.form-reserva-moderna {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.tipo-reserva-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.card-reserva {
    cursor: pointer;
    position: relative;
}

.card-reserva input {
    display: none;
}

.card-contenido {
    padding: 35px;
    border-radius: 25px;
    border: 2px solid #ddd;
    text-align: center;
    transition: all 0.35s ease;
    background: #fafafa;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.card-contenido .icono {
    font-size: 3rem;
    margin-bottom: 12px;
}

.card-contenido h3 {
    margin: 0;
    font-size: 1.3rem;
    color: #1a4d2e;
}

.card-contenido p {
    margin-top: 8px;
    color: #666;
}

.card-reserva input:checked + .card-contenido {
    border-color: #1a4d2e;
    background: linear-gradient(135deg, #d4edda, #f6fff8);
    transform: scale(1.05);
    box-shadow: 0 15px 30px rgba(0,0,0,0.18);
}

.grupo-input {
    margin-bottom: 30px;
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transition: all 0.4s ease;
}

.grupo-input.activo {
    max-height: 100px;
    opacity: 1;
}

.mensaje-validacion {
    margin-top: 15px;
    font-weight: bold;
    color: crimson;
}

.btn-reserva-moderna {
    background: linear-gradient(135deg, #1a4d2e, #2f7d4a);
    color: white;
    padding: 18px 65px;
    border-radius: 50px;
    font-size: 1.4rem;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-reserva-moderna:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}

.btn-reserva-moderna.cargando {
    pointer-events: none;
    opacity: 0.8;
}

.btn-reserva-moderna.cargando::after {
    content: " ⏳ Procesando...";
}
</style>
</head>
<body>

<div class="detalle-container">

    <div class="header-grid">
        <div class="detalle-img">
            <img src="<?= htmlspecialchars($item['imagen_url']) ?>">
        </div>

        <div class="detalle-texto">
            <h1><?= htmlspecialchars($item['nombre']) ?></h1>
            <p><?= nl2br(htmlspecialchars($descripcion)) ?></p>
        </div>
    </div>

    <table class="tabla">
        <tr><td><strong>Lugar:</strong> <?= htmlspecialchars($item['lugar']) ?></td></tr>
        <tr><td><strong>Duración:</strong> <?= htmlspecialchars($item['duracion_texto']) ?></td></tr>
        <tr><td><strong>Horarios:</strong> <?= htmlspecialchars($item['horarios']) ?></td></tr>
        <tr><td><strong>Capacidad:</strong> <?= htmlspecialchars($item['capacidad_texto']) ?></td></tr>
        <tr><td><strong>Recomendaciones:</strong> <?= htmlspecialchars($item['recomendaciones']) ?></td></tr>
        <tr><td><strong>Seguridad:</strong> <?= htmlspecialchars($item['seguridad']) ?></td></tr>
    </table>

    <div class="botones">
        <form action="" method="POST" class="form-reserva-moderna" id="formReserva">

            <input type="hidden" name="id_actividad" value="<?= $item['id_actividad'] ?>">

            <div class="tipo-reserva-grid">

                <!-- INDIVIDUAL -->
                <label class="card-reserva">
                    <input type="radio" name="tipo_reserva" value="individual" checked>
                    <div class="card-contenido">
                        <div class="icono">🙋</div>
                        <h3>Reserva Individual</h3>
                        <p>Una persona</p>
                    </div>
                </label>

                <!-- GRUPAL -->
                <label class="card-reserva">
                    <input type="radio" name="tipo_reserva" value="grupal">
                    <div class="card-contenido">
                        <div class="icono">👨‍👩‍👧‍👦</div>
                        <h3>Reserva Grupal</h3>
                        <p>2 o más personas</p>
                    </div>
                </label>

            </div>

            <!-- Input animado para grupal -->
            <div class="grupo-input" id="grupoInput">
                <input type="number" name="cantidad" min="2" placeholder="Cantidad de personas">
            </div>

            <button type="submit" class="btn-reserva-moderna" id="btnReserva">
                🌿 Reservar ahora
            </button>

            <p class="mensaje-validacion" id="mensajeValidacion"></p>

        </form>
    </div>

</div>

<?php include('../includes/footer.php'); ?>

<script>
const radios = document.querySelectorAll("input[name='tipo_reserva']");
const grupoInput = document.getElementById("grupoInput");
const form = document.getElementById("formReserva");
const mensaje = document.getElementById("mensajeValidacion");
const boton = document.getElementById("btnReserva");

radios.forEach(radio => {
    radio.addEventListener("change", () => {
        if (radio.value === "grupal" && radio.checked) {
            grupoInput.classList.add("activo");
        } else {
            grupoInput.classList.remove("activo");
        }
    });
});

form.addEventListener("submit", function (e) {
    const tipo = document.querySelector("input[name='tipo_reserva']:checked").value;
    const cantidad = document.querySelector("input[name='cantidad']").value;

    mensaje.textContent = "";

    if (tipo === "grupal") {

        if (!cantidad || cantidad < 2) {
            e.preventDefault();
            mensaje.textContent = "⚠️ Debes ingresar al menos 2 personas.";
            return;
        }

        // 👉 MUY IMPORTANTE: USAR GET
        form.method = "GET";
        form.action = "participantes_reserva.php?id_actividad=<?= $item['id_actividad'] ?>&cantidad=" + cantidad;

    } else {
        // Individual usa POST
        form.method = "POST";
        form.action = "fecha_reserva.php";
    }

    boton.classList.add("cargando");
});

</script>

</body>
</html>
