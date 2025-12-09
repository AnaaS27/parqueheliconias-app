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

    // 🔍 Verificar si correo existe
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

    // 🔐 Hash contraseña
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // ➕ Insertar usuario
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

    echo json_encode([
        "ok"  => $status === 201,
        "msg" => $status === 201 ? "Registro exitoso." : "Error registrando usuario."
    ]);
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

    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-green-50 min-h-screen flex items-center justify-center p-4">

<!-- 🟢 Toast → Notificación -->
<div id="toast" class="fixed top-5 right-5 z-50 hidden px-4 py-3 rounded-lg shadow-lg text-white"></div>

<!-- 🟢 Tarjeta de registro -->
<div class="bg-white shadow-2xl rounded-2xl p-10 w-full max-w-2xl border border-green-200">

    <h2 class="text-3xl font-bold text-center text-green-700 mb-8">
        🌿 Crear Cuenta
    </h2>

    <form id="registerForm" class="space-y-5">

        <!-- Nombre / Apellido -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-gray-700 font-medium">Nombre *</label>
                <input type="text" name="nombre" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="text-gray-700 font-medium">Apellido *</label>
                <input type="text" name="apellido" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
        </div>

        <!-- Documento -->
        <div>
            <label class="text-gray-700 font-medium">Documento de identidad *</label>
            <input type="number" name="documento" required
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Email -->
        <div>
            <label class="text-gray-700 font-medium">Correo electrónico *</label>
            <input type="email" name="correo" required
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Teléfono -->
        <div>
            <label class="text-gray-700 font-medium">Teléfono (opcional)</label>
            <input type="tel" name="telefono"
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Fecha Nacimiento -->
        <div>
            <label class="text-gray-700 font-medium">Fecha de nacimiento *</label>
            <input type="date" name="fecha_nacimiento" required
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Género / Ciudad -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-gray-700 font-medium">Género *</label>
                <select name="genero" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seleccione</option>
                    <option>Femenino</option>
                    <option>Masculino</option>
                    <option>Otro</option>
                </select>
            </div>

            <div>
                <label class="text-gray-700 font-medium">Ciudad *</label>
                <select name="ciudad" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seleccione</option>
                    <option>Pereira</option>
                    <option>Dosquebradas</option>
                    <option>Manizales</option>
                </select>
            </div>
        </div>

        <!-- Contraseña -->
        <div>
            <label class="text-gray-700 font-medium">Contraseña *</label>
            <input type="password" name="password" minlength="6" required
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Confirmar contraseña -->
        <div>
            <label class="text-gray-700 font-medium">Confirmar contraseña *</label>
            <input type="password" name="confirmPassword" minlength="6" required
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <!-- Botón -->
        <button type="submit"
                id="registerBtn"
                class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition flex items-center justify-center gap-2">

            <span>Registrarme</span>

            <div id="loadingSpinner"
                 class="hidden w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin">
            </div>
        </button>

        <p class="text-center text-gray-600 text-sm mt-3">
            ¿Ya tienes cuenta?
            <a href="login.php" class="text-green-700 font-medium hover:underline">Inicia sesión</a><br>
            <a href="index.php" class="text-green-700 font-medium hover:underline">Volver al inicio</a>
        </p>

    </form>
</div>

<!-- 🟢 Notificaciones -->
<script>
function mostrarNotificacion(tipo, mensaje) {
    const toast = document.getElementById("toast");

    toast.className =
        "px-4 py-3 rounded-lg shadow-lg text-white fixed top-5 right-5 " +
        (tipo === "success" ? "bg-green-600" : "bg-red-600");

    toast.textContent = mensaje;
    toast.classList.remove("hidden");

    setTimeout(() => toast.classList.add("hidden"), 3000);
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