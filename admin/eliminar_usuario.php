<?php
session_start();
include('../includes/verificar_admin.php');
include('../includes/conexion.php');
require_once('../correo/enviarCorreo.php');

$id_usuario = $_GET['id'] ?? null;

if (!$id_usuario) {
    echo "<script>
        Swal.fire('⚠️', 'ID inválido', 'warning').then(() => location.href='usuarios.php');
    </script>";
    exit;
}

$id_usuario = intval($id_usuario);

// Verificar usuario
$sqlCheck = "SELECT nombre, correo, activo FROM usuarios WHERE id_usuario = $1";
$resCheck = pg_query_params($conn, $sqlCheck, [$id_usuario]);

if (!$resCheck || pg_num_rows($resCheck) === 0) {
    echo "<script>
        Swal.fire('❌', 'El usuario no existe', 'error').then(() => location.href='usuarios.php');
    </script>";
    exit;
}

$user = pg_fetch_assoc($resCheck);

if (!$user['activo']) {
    echo "<script>
        Swal.fire('ℹ️', 'Este usuario ya está desactivado.', 'info').then(() => location.href='usuarios.php');
    </script>";
    exit;
}

$nombreUsuario = $user['nombre'];
$correoUsuario = $user['correo'];

// Verificar dependencias
$sqlDep = "SELECT COUNT(*) AS total FROM reservas WHERE id_usuario = $1";
$resDep = pg_query_params($conn, $sqlDep, [$id_usuario]);
$depCount = pg_fetch_assoc($resDep)['total'];

if ($depCount > 0) {
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'No se puede eliminar',
            html: \"Este usuario tiene <strong>$depCount</strong> reservas registradas y no puede ser eliminado.\",
        }).then(() => location.href='usuarios.php');
    </script>";
    exit;
}

// Desactivar usuario
$sqlUpdate = "UPDATE usuarios SET activo = FALSE WHERE id_usuario = $1";
pg_query_params($conn, $sqlUpdate, [$id_usuario]);

// Registrar historial
$sqlHist = "INSERT INTO historial_modificaciones 
(id_usuario_modificado, accion, fecha_modificacion, realizado_por) 
VALUES ($1, $2, NOW(), $3)";
pg_query_params($conn, $sqlHist, [$id_usuario, 'Usuario desactivado', $_SESSION['usuario_id']]);

// Opcional: Correo
/*
$mensaje = "<p>Hola <strong>$nombreUsuario</strong>,</p>
<p>Tu cuenta ha sido desactivada del sistema.</p>";
enviarCorreo($correoUsuario, $nombreUsuario, "Cuenta desactivada", $mensaje);
*/

pg_close($conn);
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Usuario desactivado',
    html: 'El usuario <strong><?= htmlspecialchars($nombreUsuario) ?></strong> ha sido desactivado correctamente.',
    confirmButtonText: 'Aceptar',
    confirmButtonColor: '#2e6a30'
}).then(() => {
    window.location = 'usuarios.php';
});
</script>
