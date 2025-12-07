<?php
session_start();

// ===========================
// CONFIG - SUPABASE
// ===========================
$supabase_url = getenv("SUPABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

// Función para enviar datos a Supabase REST
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
        "Prefer: return=representation"  // devuelve el usuario recién creado
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}

// ===========================
// PROCESAR REGISTRO
// ===========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $documento = $_POST["documento"];
    $telefono = $_POST["telefono"];
    $contrasena = $_POST["contrasena"];

    // Validación simple
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($documento) || empty($contrasena)) {
        $_SESSION["toast"] = ["tipo" => "warning", "mensaje" => "Todos los campos son obligatorios ⚠️"];
        header("Location: registro.php");
        exit;
    }

    // 1 — Verificar si el correo ya existe
    $url = "usuarios?correo=eq." . urlencode($correo) . "&select=id_usuario";
    $url_encoded = str_replace("+", "%20", $url);

    $ch = curl_init($supabase_url . "/rest/v1/" . $url_encoded);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json"
    ]);

    $existing = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($existing)) {
        $_SESSION["toast"] = ["tipo" => "error", "mensaje" => "El correo ya está registrado ❌"];
        header("Location: registro.php");
        exit;
    }

    // 2 — Encriptar contraseña (bcrypt)
    $password_hash = password_hash($contrasena, PASSWORD_BCRYPT);

    // 3 — Insertar usuario por REST
    [$status, $data] = supabase_post("usuarios", [
        "nombre" => $nombre,
        "apellido" => $apellido,
        "correo" => $correo,
        "documento" => $documento,
        "telefono" => $telefono,
        "contrasena" => $password_hash,
        "id_rol" => 2, // usuario normal
        "usuario_activo" => true,
        "fecha_registro" => date("c")
    ]);

    // 4 — Ver resultado
    if ($status === 201) {
        $_SESSION["toast"] = [
            "tipo" => "success",
            "mensaje" => "¡Registro exitoso! Ahora puedes iniciar sesión 🌿"
        ];
        header("Location: login.php");
        exit;
    } else {
        $_SESSION["toast"] = [
            "tipo" => "error",
            "mensaje" => "Error al registrar usuario ❌"
        ];
        header("Location: registro.php");
        exit;
    }
}
?>

<!-- AQUI SIGUE TU HTML DE REGISTRO NORMAL --> 



<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - Parque Las Heliconias</title>
<link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body class="fondo-verde">
<div class="contenedor-login">
    <img src="../assets/img/logo.png" class="logo" alt="Logo Parque Las Heliconias">
    <h2>Registro de Visitante</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" placeholder="Apellido" required>
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="text" name="documento" placeholder="Documento de identidad" required>
        <input type="text" name="telefono" placeholder="Teléfono" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Registrarse</button>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </form>
</div>
</body>
</html>
