<?php
session_start();

// =====================================
//  CONFIG DE SUPABASE
// =====================================
$supabase_url = getenv("SUPABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

// Función genérica para peticiones REST
function supabase_request($method, $endpoint, $data = null) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json"
    ];

    if ($method === "PATCH") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ["Prefer: return=minimal"]));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}

// =====================================
//  PROCESAR LOGIN
// =====================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    // ---------------------------------------
    // 1. Buscar usuario por correo
    // ---------------------------------------
    $endpoint = "usuarios?correo=eq." . urlencode($correo)
              . "&select=id_usuario,nombre,apellido,documento,correo,contrasena,id_rol,usuario_activo";

    [$code, $resultado] = supabase_request("GET", $endpoint);

    // Si no devuelve nada → correo no existe
    if ($code !== 200 || empty($resultado)) {
        $_SESSION['toast'] = [
            'tipo' => 'warning',
            'mensaje' => 'No existe una cuenta con ese correo ⚠️'
        ];
        header("Location: login.php");
        exit;
    }

    $usuario = $resultado[0];

    // ---------------------------------------
    // 2. Verificar si el usuario está activo
    // ---------------------------------------
    if (isset($usuario["usuario_activo"]) && !$usuario["usuario_activo"]) {
        $_SESSION['toast'] = [
            'tipo' => 'warning',
            'mensaje' => 'Tu cuenta está inactiva. Comunícate con el administrador. ⚠️'
        ];
        header("Location: login.php");
        exit;
    }

    // ---------------------------------------
    // 3. Verificar contraseña con bcrypt
    // ---------------------------------------
    if (!password_verify($contrasena, $usuario["contrasena"])) {
        $_SESSION['toast'] = [
            'tipo' => 'error',
            'mensaje' => 'Contraseña incorrecta ❌'
        ];
        header("Location: login.php");
        exit;
    }

    // ---------------------------------------
    // 4. Guardar datos en sesión
    // ---------------------------------------
    $_SESSION["usuario_id"] = $usuario["id_usuario"];
    $_SESSION["rol"] = $usuario["id_rol"];
    $_SESSION["usuario_nombre"] = $usuario["nombre"];
    $_SESSION["usuario_apellido"] = $usuario["apellido"];
    $_SESSION["usuario_documento"] = $usuario["documento"];
    $_SESSION["usuario_correo"] = $usuario["correo"];
    $_SESSION["login_exitoso"] = true;

    // ---------------------------------------
    // 5. Actualizar último login
    // ---------------------------------------
    supabase_request("PATCH", "usuarios?id_usuario=eq." . $usuario["id_usuario"], [
        "ultimo_login" => date("c")
    ]);

    // ---------------------------------------
    // 6. Redirigir según rol
    // ---------------------------------------
    if ($usuario["id_rol"] == 1) {
        header("Location: ../admin/index.php");
    } else {
        header("Location: inicio.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar Sesión - Parque Las Heliconias</title>
<link rel="stylesheet" href="../assets/css/estilos.css">
<style>
body.fondo-verde {
    background: linear-gradient(135deg, #e2f5e7, #b6e4b8);
    font-family: "Poppins", sans-serif;
}
.contenedor-login {
    max-width: 400px;
    margin: 80px auto;
    background: #fff;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
}
.contenedor-login .logo {
    width: 90px;
    margin-bottom: 15px;
}
.contenedor-login input {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}
.contenedor-login button {
    width: 100%;
    padding: 10px;
    background-color: #3a7a3b;
    border: none;
    color: #fff;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
}
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 14px 18px;
    border-radius: 10px;
    color: #fff;
    font-weight: 500;
    z-index: 9999;
    opacity: 0.95;
    animation: fadein 0.5s, fadeout 0.5s 3s forwards;
}
.toast.success { background: #3a7a3b; }
.toast.error { background: #d43f3a; }
.toast.warning { background: #f0ad4e; }
</style>
</head>
<body class="fondo-verde">

<?php if (isset($_SESSION['toast'])): ?>
    <div class="toast <?php echo $_SESSION['toast']['tipo']; ?>" id="toast">
        <?php echo htmlspecialchars($_SESSION['toast']['mensaje']); ?>
    </div>
    <script>
        setTimeout(() => {
            document.getElementById("toast").style.display = "none";
        }, 3500);
    </script>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<div class="contenedor-login">
    <img src="../assets/img/logoo.png" class="logo" alt="Logo Parque Las Heliconias">
    <h2>Iniciar Sesión</h2>

    <form method="POST">
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </form>
</div>

</body>
</html>
