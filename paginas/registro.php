<?php
session_start();

// ===========================
// CONFIG - SUPABASE
// ===========================
$supabase_url = getenv("DATABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

function supabase_post($endpoint, $data) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

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
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}

// ===========================
// PROCESAR FORMULARIO POST (AJAX)
// ===========================
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

    // Verificar correo existente
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

    // Encriptar
    $hash = password_hash($password, PASSWORD_BCRYPT);

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

<!-- ============================
      🔥 FORMULARIO MODERNO
============================= -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Parque Las Heliconias</title>

    <link rel="icon" href="../assets/img/logoo.png">

    <!-- Tus estilos -->
    <link rel="stylesheet" href="css/notificacion.css">
    <link rel="stylesheet" href="css/register.css">

</head>
<body>

    <div class="register-container">
        <div class="register-form">
            <h2>Crear Cuenta</h2>

            <form id="registerForm">

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" required placeholder="Tu nombre">
                    </div>

                    <div class="form-group">
                        <label>Apellido:</label>
                        <input type="text" name="apellido" required placeholder="Tu apellido">
                    </div>
                </div>

                <div class="form-group">
                    <label>Documento de identidad:</label>
                    <input type="number" name="documento" required placeholder="Tu documento">
                </div>

                <div class="form-group">
                    <label>Correo electrónico:</label>
                    <input type="email" name="correo" required placeholder="ejemplo@correo.com">
                </div>

                <div class="form-group">
                    <label>Teléfono (opcional):</label>
                    <input type="tel" name="telefono" placeholder="Teléfono">
                </div>

                <div class="form-group">
                    <label>Fecha nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Género:</label>
                        <select name="genero" required>
                            <option value="">Seleccione</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ciudad:</label>
                        <select name="ciudad" required>
                            <option value="">Seleccione</option>
                            <option value="Pereira">Pereira</option>
                            <option value="Dosquebradas">Dosquebradas</option>
                            <option value="Manizales">Manizales</option>
                            <!-- Puedes cargar dinámico con AJAX -->
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label>Confirmar contraseña:</label>
                    <input type="password" name="confirmPassword" required minlength="6" placeholder="Repite tu contraseña">
                </div>

                <button type="submit" class="register-btn" id="registerBtn">
                    <span class="btn-text">Registrarme</span>
                    <div class="loading-spinner" id="loadingSpinner"></div>
                </button>

                <div class="login-link">
                    ¿Ya tienes cuenta?
                    <a href="login.php">Inicia sesión</a><br>
                    <a href="index.php">Volver al inicio</a>
                </div>

            </form>
        </div>
    </div>

<script src="js/notificacion.js"></script>

<script>
// =========================
//   Envío AJAX moderno
// =========================
document.getElementById("registerForm").addEventListener("submit", async function(e){
    e.preventDefault();

    const btn = document.getElementById("registerBtn");
    const spinner = document.getElementById("loadingSpinner");
    btn.disabled = true;
    spinner.style.display = "inline-block";

    const formData = new FormData(this);
    formData.append("ajax", "1");

    const req = await fetch("registro.php", {
        method: "POST",
        body: formData
    });

    const res = await req.json();

    btn.disabled = false;
    spinner.style.display = "none";

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
