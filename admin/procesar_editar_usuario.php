<?php
include('../includes/conexion.php');
require_once "../includes/enviarCorreo.php";  // PHPMailer

$id = $_POST['id_usuario'];
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$correo = $_POST['correo'];
$documento = $_POST['documento'];
$telefono = $_POST['telefono'];
$rol = $_POST['id_rol'];
$genero = $_POST['id_genero'];
$institucion = $_POST['id_institucion'];
$ciudad = $_POST['id_ciudad'];

$usuario_activo  = ($_POST['usuario_activo'] == "1") ? 'true' : 'false';

$contrasena = $_POST['contrasena'];
$actualizarPass = "";
$cambioPassword = false;

// --------------------------------------------------
// 🔐 SI EL USUARIO INGRESÓ UNA NUEVA CONTRASEÑA
// --------------------------------------------------
if (!empty($contrasena)) {

    // SALT BCRYPT COMPATIBLE CON PostgreSQL
    $salt = '$2a$10$' . substr(str_replace('+', '.', base64_encode(random_bytes(16))), 0, 22);

    // Generar password BCRYPT con crypt() (igual a pgcrypto)
    $nueva_contrasena = crypt($contrasena, $salt);

    $actualizarPass = ", contrasena = '$nueva_contrasena'";
    $cambioPassword = true;
}

// --------------------------------------------------
// 🔄 ACTUALIZAR USUARIO
// --------------------------------------------------
$query = "
UPDATE usuarios SET
    nombre='$nombre',
    apellido='$apellido',
    correo='$correo',
    documento='$documento',
    telefono='$telefono',
    id_rol=$rol,
    id_genero=$genero,
    id_instituciones=$institucion,
    id_ciudad=$ciudad,
    usuario_activo=$usuario_activo
    $actualizarPass
WHERE id_usuario=$id
";

$resultado = pg_query($conn, $query);

// --------------------------------------------------
// 📩 SI CONTRASEÑA CAMBIÓ → ENVIAR NOTIFICACIÓN
// --------------------------------------------------
if ($resultado) {

    if ($cambioPassword) {
        enviarCorreoPassword($correo, $nombre);
    }

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Usuario actualizado',
        text: 'Los cambios fueron guardados correctamente',
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        window.location = 'usuarios.php';
    });
    </script>";
    exit;
}

// ❌ ERROR
echo "
<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'Ocurrió un problema al actualizar el usuario'
});
</script>";
?>
