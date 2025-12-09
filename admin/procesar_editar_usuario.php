<?php
require_once("../includes/supabase.php");
require_once("../includes/email_api.php"); // usa Brevo (ya migrado)

// ===============================
// CAPTURA DE DATOS DEL FORMULARIO
// ===============================
$id             = intval($_POST['id_usuario']);
$nombre         = $_POST['nombre'];
$apellido       = $_POST['apellido'];
$correo         = $_POST['correo'];
$documento      = $_POST['documento'];
$telefono       = $_POST['telefono'];
$rol            = intval($_POST['id_rol']);
$genero         = intval($_POST['id_genero']);
$institucion    = intval($_POST['id_institucion']);
$ciudad         = intval($_POST['id_ciudad']);
$usuario_activo = ($_POST['usuario_activo'] == "1") ? true : false;

$contrasena     = $_POST['contrasena'];
$cambioPassword = false;

// =======================================
// 🔐 1️⃣ GENERAR PASSWORD HASH SI CAMBIÓ
// =======================================
$dataUpdate = [
    "nombre"           => $nombre,
    "apellido"         => $apellido,
    "correo"           => $correo,
    "documento"        => $documento,
    "telefono"         => $telefono,
    "id_rol"           => $rol,
    "id_genero"        => $genero,
    "id_institucion"   => $institucion,
    "id_ciudad"        => $ciudad,
    "usuario_activo"   => $usuario_activo
];

if (!empty($contrasena)) {

    // Crear salt compatible con bcrypt
    $salt = '$2a$10$' . substr(str_replace('+', '.', base64_encode(random_bytes(16))), 0, 22);

    // Hash final
    $passwordHash = crypt($contrasena, $salt);

    $dataUpdate["contrasena"] = $passwordHash;
    $cambioPassword = true;
}

// =======================================
// 🔄 2️⃣ ACTUALIZAR USUARIO EN SUPABASE
// =======================================
list($codeUpdate, $respUpdate) = supabase_update(
    "usuarios",
    ["id_usuario" => $id],
    $dataUpdate
);

if ($codeUpdate !== 200 && $codeUpdate !== 204) {

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
    exit;
}

// =======================================
// 📩 3️⃣ SI SE CAMBIÓ CONTRASEÑA → ENVIAR EMAIL
// =======================================
if ($cambioPassword) {
    enviarCorreoPassword($correo, $nombre);
}

// =======================================
// ✅ 4️⃣ RESPUESTA FINAL EXITOSA
// =======================================
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
?>