<?php
session_start();

// ================================
// 🔧 CONFIGURACIÓN SUPABASE
// ================================
$supabase_url = getenv("DATABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

function supabase_post($endpoint, $data) {
    global $supabase_url, $supabase_key;

    $url = rtrim($supabase_url, "/") . "/rest/v1/" . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode($response, true)];
}

// ================================
// 🔄 PROCESAR PETICIÓN AJAX
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajax"])) {

    $nombre     = trim($_POST["nombre"]);
    $apellido   = trim($_POST["apellido"]);
    $documento  = trim($_POST["documento"]);
    $correo     = trim($_POST["correo"]);
    $telefono   = trim($_POST["telefono"]);
    $genero     = trim($_POST["genero"]);
    $ciudad     = trim($_POST["ciudad"]);
    $fecha_nac  = trim($_POST["fecha_nacimiento"]);
    $password   = trim($_POST["password"]);

    if (!$nombre || !$apellido || !$correo || !$documento || !$password) {
        echo json_encode(["ok" => false, "msg" => "Todos los campos obligatorios deben llenarse."]);
        exit;
    }

    // ================================
    // 🔍 Verificar si el correo existe
    // ================================
    $query = "usuarios?correo=eq." . urlencode($correo);
    $query = str_replace("+", "%20", $query);

    $ch = curl_init($supabase_url . "/rest/v1/" . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key"
    ]);

    $exists = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($exists)) {
        echo json_encode(["ok" => false, "msg" => "El correo ya existe en el sistema."]);
        exit;
    }

    // ================================
    // 🔐 Encriptar contraseña
    // ================================
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // ================================
    // ➕ Insertar usuario
    // ================================
    [$status, $insert] = supabase_post("usuarios", [
        "nombre"        => $nombre,
        "apellido"      => $apellido,
        "correo"        => $correo,
        "documento"     => $documento,
        "telefono"      => $telefono,
        "genero"        => $genero,
        "ciudad"        => $ciudad,
        "fecha_nac"     => $fecha_nac,
        "contrasena"    => $hash,
        "id_rol"        => 2,
        "usuario_activo"=> true,
        "fecha_registro"=> date("c")
    ]);

    if ($status === 201) {
        echo json_encode(["ok" => true, "msg" => "Registro exitoso."]);
    } else {
        echo json_encode(["ok" => false, "msg" => "Error registrando usuario."]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrarse - Parque Las Heliconias</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="../assets/img/logoo.png">
</head>

<body class="bg-green-50 min-h-screen flex items-center justify-center p-4">

<!-- 🟢 Notificación flotante -->
<div id="toast" class="fixed top-5 right-5 z-50 hidden"></div>

<!-- 🟢 Tarjeta de registro -->
<div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-2xl">

    <h2 class="text-3xl font-bold text-center text-green-700 mb-6">
        🌿 Crear Cuenta
    </h2>

    <form id="registerForm" class="space-y-5">

        <!-- Nombre / Apellido -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Nombre</label>
                <input type="text" name="nombre" required class="input-box">
            </div>

            <div>
                <label class="label">Apellido</label>
                <input type="text" name="apellido" required class="input-box">
            </div>
        </div>

        <!-- Documento -->
        <div>
            <label class="label">Documento de identidad</label>
            <input type="number" name="documento" required class="input-box">
        </div>

        <!-- Email -->
        <div>
            <label class="label">Correo electrónico</label>
            <input type="email" name="correo" required class="input-box">
        </div>

        <!-- Teléfono -->
        <div>
            <label class="label">Teléfono (opcional)</label>
            <input type="tel" name="telefono" class="input-box">
        </div>

        <!-- Fecha Nacimiento -->
        <div>
            <label class="label">Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" required class="input-box">
        </div>

        <!-- Género / Ciudad -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Género</label>
                <select name="genero" required class="input-box">
                    <option value="">Seleccione</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <div>
                <label class="label">Ciudad</label>
                <select name="ciudad" required class="input-box">
                    <option value="">Seleccione</option>
                    <option value="Pereira">Pereira</option>
                    <option value="Dosquebradas">Dosquebradas</option>
                    <option value="Manizales">Manizales</option>
                </select>
            </div>
        </div>

        <!-- Contraseña -->
        <div>
            <label class="label">Contraseña</label>
            <input type="password" name="password" required minlength="6" class="input-box">
        </div>

        <!-- Confirmar contraseña -->
        <div>
            <label class="label">Confirmar contraseña</label>
            <input type="password" name="confirmPassword" required minlength="6" class="input-box">
        </div>

        <!-- Botón -->
        <button type="submit"
            id="registerBtn"
            class="w-full py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition flex items-center justify-center gap-2">
            
            <span>Registrarme</span>
            <div id="loadingSpinner"
                 class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
        </button>

        <p class="text-center text-gray-600 text-sm mt-3">
            ¿Ya tienes cuenta?
            <a href="login.php" class="text-green-700 hover:underline font-medium">Inicia sesión</a><br>
            <a href="index.php" class="text-green-700 hover:underline font-medium">Volver al inicio</a>
        </p>
    </form>
</div>

<!-- 🟢 ESTILOS INPUT -->
<style>
.label {
    @apply block text-sm font-medium text-gray-700 mb-1;
}
.input-box {
    @apply w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition;
}
</style>

<!-- 🟢 Notificaciones -->
<script>
function mostrarNotificacion(tipo, mensaje) {
    const toast = document.getElementById("toast");

    toast.className =
        "fixed top-5 right-5 px-4 py-3 rounded-lg shadow-xl text-white " +
        (tipo === "success" ? "bg-green-600" : "bg-red-600");

    toast.textContent = mensaje;
    toast.style.display = "block";

    setTimeout(() => toast.style.display = "none", 3000);
}
</script>

<!-- 🟢 Enviar formulario AJAX -->
<script>
document.getElementById("registerForm").addEventListener("submit", async function(e){
    e.preventDefault();

    const pass = this.password.value;
    const conf = this.confirmPassword.value;

    if (pass !== conf) {
        mostrarNotificacion("error", "Las contraseñas no coinciden");
        return;
    }

    const btn = document.getElementById("registerBtn");
    const spinner = document.getElementById("loadingSpinner");

    btn.disabled = true;
    spinner.classList.remove("hidden");

    const formData = new FormData(this);
    formData.append("ajax", "1");

    const req = await fetch("registro.php", {
        method: "POST",
        body: formData
    });

    const res = await req.json();

    btn.disabled = false;
    spinner.classList.add("hidden");

    if (res.ok) {
        mostrarNotificacion("success", "Registro exitoso, redirigiendo...");
        setTimeout(() => window.location = "login.php", 1500);
    } else {
        mostrarNotificacion("error", res.msg);
    }
});
</script>

</body>
</html>
