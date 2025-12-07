<?php
session_start();
include('../includes/header.php');

// =========================
// CONFIG SUPABASE
// =========================
$supabase_url = getenv("DATABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

if (!$supabase_url || !$supabase_key) {
    die("❌ ERROR: Variables de entorno de Supabase no configuradas.");
}

// -----------------------
// Función GET Supabase
// -----------------------
function supabase_get($endpoint) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}

// =========================
// OBTENER RECORRIDOS
// =========================
$endpoint_recorridos =
    "actividades?activo=eq.true&categoria=eq.recorrido&order=id_actividad";

[$code1, $recorridos] = supabase_get($endpoint_recorridos);

if ($code1 !== 200) {
    echo "<p>Error cargando recorridos ($code1)</p>";
    $recorridos = [];
}

// =========================
// OBTENER TALLERES
// =========================
$endpoint_talleres =
    "actividades?activo=eq.true&categoria=eq.taller&order=id_actividad";

[$code2, $talleres] = supabase_get($endpoint_talleres);

if ($code2 !== 200) {
    echo "<p>Error cargando talleres ($code2)</p>";
    $talleres = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Actividades - Parque Heliconias</title>

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;background:#f8f9fa}
.container{max-width:1400px;margin:0 auto;padding:60px 20px}
.seccion-titulo{font-size:2.8rem;color:#2e7d32;text-align:center;margin-bottom:50px;font-weight:bold}

.carrusel{position:relative;overflow:hidden;border-radius:25px;box-shadow:0 12px 35px rgba(0,0,0,.18);background:#fff}
.carrusel-contenedor{display:flex;transition:transform 0.6s ease-in-out}
.tarjeta-chicaque{min-width:100%;background:#1a4d2e;padding:30px;display:flex;flex-direction:column}

.tarjeta-superior{display:flex;gap:30px;flex:1}
@media(max-width:900px){.tarjeta-superior{flex-direction:column}}

.imagen-container{flex:1;min-height:350px;overflow:hidden;border-radius:20px;box-shadow:0 8px 20px rgba(0,0,0,.3)}
.imagen-container img{width:100%;height:100%;object-fit:cover;transition:transform .5s}

.contenido{flex:1;color:white;display:flex;flex-direction:column;justify-content:center}
.globo-titulo{background:white;color:#1a4d2e;display:inline-block;padding:16px 35px;border-radius:50px;font-weight:bold;font-size:1.3rem;margin-bottom:25px}
.descripcion{font-size:1.1rem;line-height:1.7;margin-bottom:35px}
.btn-ver-mas{background:#ffca28;color:#1a4d2e;border:none;padding:16px 45px;border-radius:50px;font-weight:bold;font-size:1.2rem;cursor:pointer;text-decoration:none;width:max-content}

.flecha{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:white;border:none;width:60px;height:60px;border-radius:50%;font-size:2.8rem;cursor:pointer}
.flecha-izq{left:20px}
.flecha-der{right:20px}
</style>
</head>
<body>

<div class="container">

<h2 class="seccion-titulo">Recorridos</h2>
<div class="carrusel">
<button class="flecha flecha-izq" onclick="moverCarrusel(this.parentElement,-1)">‹</button>

<div class="carrusel-contenedor">
<?php foreach ($recorridos as $item): ?>
    <div class="tarjeta-chicaque">
        <div class="tarjeta-superior">
            <div class="imagen-container">
                <img src="<?= $item['imagen_url'] ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
            </div>
            <div class="contenido">
                <div class="globo-titulo"><?= htmlspecialchars($item['nombre']) ?></div>
                <p class="descripcion"><?= htmlspecialchars($item['breve']) ?></p>
                <a href="detalle_actividad.php?id=<?= $item['id_actividad'] ?>" class="btn-ver-mas">Ver más</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<button class="flecha flecha-der" onclick="moverCarrusel(this.parentElement,1)">›</button>
</div>


<h2 class="seccion-titulo" style="margin-top:100px">Talleres Educativos</h2>
<div class="carrusel">

<button class="flecha flecha-izq" onclick="moverCarrusel(this.parentElement,-1)">‹</button>

<div class="carrusel-contenedor">
<?php foreach ($talleres as $item): ?>
    <div class="tarjeta-chicaque">
        <div class="tarjeta-superior">
            <div class="imagen-container">
                <img src="<?= $item['imagen_url'] ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
            </div>
            <div class="contenido">
                <div class="globo-titulo"><?= htmlspecialchars($item['nombre']) ?></div>
                <p class="descripcion"><?= htmlspecialchars($item['breve']) ?></p>
                <a href="detalle_actividad.php?id=<?= $item['id_actividad'] ?>" class="btn-ver-mas">Ver más</a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<button class="flecha flecha-der" onclick="moverCarrusel(this.parentElement,1)">›</button>
</div>

</div>

<script>
function moverCarrusel(carrusel,direccion){
    const contenedor=carrusel.querySelector('.carrusel-contenedor');
    const ancho=carrusel.clientWidth;
    let posicion=parseInt(contenedor.dataset.pos||0)+direccion;
    const total=contenedor.children.length;
    if(posicion<0)posicion=total-1;
    if(posicion>=total)posicion=0;
    contenedor.style.transform=`translateX(${-posicion*ancho}px)`;
    contenedor.dataset.pos=posicion;
}
</script>

<?php include('../includes/footer.php'); ?>
</body>
</html>
