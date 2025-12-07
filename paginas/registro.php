<?php
include('../includes/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $documento = $_POST['documento'];
    $telefono = $_POST['telefono'];
    $contrasena = $_POST['contrasena']; // sin hash aquí

    // Query para insertar usando bcrypt en PostgreSQL
    $sql = "INSERT INTO usuarios 
            (nombre, apellido, correo, documento, telefono, contrasena, id_rol)
            VALUES ($1, $2, $3, $4, $5, crypt($6, gen_salt('bf')), 2)";

    $result = pg_query_params($conn, $sql, [
        $nombre, $apellido, $correo, $documento, $telefono, $contrasena
    ]);

    if ($result) {
        header("Location: login.php?registro=ok");
        exit;
    } else {
        $error = "Error al registrarse: " . pg_last_error($conn);
    }
}
?>


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
