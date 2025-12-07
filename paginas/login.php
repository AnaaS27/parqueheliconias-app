<?php
session_start();
include('../includes/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Buscar usuario
    $query = "SELECT * FROM usuarios WHERE correo = $1";
    $result = pg_query_params($conn, $query, [$correo]);

    if (pg_num_rows($result) > 0) {
        $usuario = pg_fetch_assoc($result);

        // Verificar contraseña con bcrypt
        $checkQuery = "SELECT 1 FROM usuarios WHERE correo = $1 AND contrasena = crypt($2, contrasena)";
        $verify = pg_query_params($conn, $checkQuery, [$correo, $contrasena]);

        if (pg_num_rows($verify) > 0) {

            // Guardar info en sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['rol'] = $usuario['id_rol'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_apellido'] = $usuario['apellido'];
            $_SESSION['usuario_documento'] = $usuario['documento'];
            $_SESSION['usuario_correo'] = $usuario['correo'];

            // Actualizar último login
            pg_query_params($conn, "UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = $1", [$usuario['id_usuario']]);

            $_SESSION['toast'] = [
                'tipo' => 'success',
                'mensaje' => '¡Inicio de sesión exitoso! Bienvenido(a) ' . htmlspecialchars($usuario['nombre']) . ' 🌿'
            ];

            header("Location: " . ($usuario['id_rol'] == 1 ? "../admin/index.php" : "inicio.php"));
            exit;
        } else {
            $_SESSION['toast'] = [
                'tipo' => 'error',
                'mensaje' => 'Contraseña incorrecta ❌'
            ];
        }
    } else {
        $_SESSION['toast'] = [
            'tipo' => 'warning',
            'mensaje' => 'No existe una cuenta con ese correo ⚠️'
        ];
    }
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
    transition: background .3s;
}
.contenedor-login button:hover {
    background-color: #2d5f2e;
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
@keyframes fadein { from {opacity: 0; transform: translateY(-10px);} to {opacity: 1; transform: translateY(0);} }
@keyframes fadeout { to {opacity: 0; transform: translateY(-10px);} }
</style>
</head>
<body class="fondo-verde">

<?php if (isset($_SESSION['toast'])): ?>
    <div class="toast <?php echo $_SESSION['toast']['tipo']; ?>" id="toast">
        <?php echo htmlspecialchars($_SESSION['toast']['mensaje']); ?>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toast = document.getElementById("toast");
            if (toast) {
                setTimeout(() => toast.classList.add("hide"), 3500);
            }
        });
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