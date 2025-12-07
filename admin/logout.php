<?php
session_start();

// Si el usuario tiene sesión activa, la destruimos
if (isset($_SESSION['usuario_id'])) {
    session_unset();
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cerrando sesión...</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="logout-body">
    <div class="mensaje-logout">
        🌿 Cerrando sesión del administrador...
    </div>

    <script>
        setTimeout(() => {
            window.location.href = "../index.php";
        }, 2500);
    </script>
</body>
</html>
