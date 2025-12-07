<?php
include('../includes/verificar_admin.php');
include('../includes/conexion.php');
include('header_admin.php');

// --- Cargar selects de BD ---
$roles = pg_query($conn, "SELECT id_rol, nombre FROM roles ORDER BY id_rol ASC");
$instituciones = pg_query($conn, "SELECT id_institucion, nombre_institucion FROM instituciones ORDER BY nombre_institucion ASC");
$ciudades = pg_query($conn, "SELECT id, nombre FROM ciudades ORDER BY nombre ASC");


// --- Variables de error ---
$mensajeError = "";

// --- Procesar formulario ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre      = trim($_POST['nombre']);
    $apellido    = trim($_POST['apellido']);
    $correo      = trim($_POST['correo']);
    $documento   = trim($_POST['documento']);
    $telefono    = trim($_POST['telefono']);
    $password    = trim($_POST['password']);
    $rol         = intval($_POST['rol']);
    $genero      = intval($_POST['genero']);
    $institucion = intval($_POST['institucion']);
    $ciudad      = intval($_POST['ciudad']);
    $fecha_nac   = $_POST['fecha_nacimiento'];
    $activo      = isset($_POST['usuario_activo']) ? 'TRUE' : 'FALSE';

    if ($nombre === "" || $apellido === "" || $correo === "" || $password === "") {
        $mensajeError = "Todos los campos obligatorios deben completarse.";
    } else {

        // Verificar correo repetido
        $sqlCheck = "SELECT id_usuario FROM usuarios WHERE correo = $1 LIMIT 1";
        $respCheck = pg_query_params($conn, $sqlCheck, [$correo]);

        if (pg_num_rows($respCheck) > 0) {
            $mensajeError = "El correo ya está registrado.";
        } else {

            // Insertar registro
            $passHash = password_hash($password, PASSWORD_DEFAULT);

            $sqlInsert = "
                INSERT INTO usuarios
                (nombre, apellido, correo, documento, contrasena, telefono, id_rol,
                fecha_nacimiento, id_genero, id_institucion, id_ciudad, usuario_activo, fecha_creacion)
                VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,NOW())
            ";

            $ok = pg_query_params($conn, $sqlInsert, [
                $nombre,
                $apellido,
                $correo,
                $documento,
                $passHash,
                $telefono,
                $rol,
                $fecha_nac,
                $genero,
                $institucion,
                $ciudad,
                $activo
            ]);

            if ($ok) {
                $_SESSION['mensaje_exito'] = "Usuario creado correctamente.";
                header("Location: usuarios.php");
                exit;
            } else {
                $mensajeError = "Error al registrar el usuario.";
            }
        }
    }
}
?>

<!-- CONTENIDO -->
<div class="max-w-4xl mx-auto px-6 py-8">

    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">

        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
            <i class="fa-solid fa-user-plus text-green-600 text-3xl"></i>
            Crear Usuario
        </h2>

        <!-- Error -->
        <?php if ($mensajeError): ?>
            <div class="mb-4 bg-red-100 text-red-800 px-4 py-3 rounded-lg">
                <?= $mensajeError ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Nombre -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Nombre *</label>
                <input type="text" name="nombre" required
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Apellido -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Apellido *</label>
                <input type="text" name="apellido" required
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Correo -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Correo *</label>
                <input type="email" name="correo" required
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Documento -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Documento</label>
                <input type="text" name="documento"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Contraseña -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Contraseña *</label>
                <input type="password" name="password" required
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Teléfono -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Teléfono</label>
                <input type="text" name="telefono"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Fecha nacimiento -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
            </div>

            <!-- Rol -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Rol *</label>
                <select name="rol"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
                    <?php while ($r = pg_fetch_assoc($roles)): ?>
                        <option value="<?= $r['id_rol'] ?>"><?= $r['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Género -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Género *</label>
                <select name="genero"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
                    <option value="1">Masculino</option>
                    <option value="2">Femenino</option>
                    <option value="3">Otro</option>
                    <option value="4">Prefiero no decirlo</option>
                </select>
            </div>

            <!-- Institución -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Institución *</label>
                <select name="institucion"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
                    <?php while ($i = pg_fetch_assoc($instituciones)): ?>
                        <option value="<?= $i['id_institucion'] ?>"><?= $i['nombre_institucion'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Ciudad -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Ciudad *</label>
                <select name="ciudad"
                class="w-full px-4 py-2 border rounded-lg bg-gray-50 focus:ring">
                    <?php while ($c = pg_fetch_assoc($ciudades)): ?>
                        <option value="<?= $c['id'] ?>"><?= $c['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Estado -->
            <div class="flex items-center gap-2">
                <input type="checkbox" name="usuario_activo" checked>
                <label class="text-gray-700 font-medium">Usuario Activo</label>
            </div>

            <!-- BOTONES -->
            <div class="col-span-2 flex justify-between mt-4">

                <a href="usuarios.php"
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fa-solid fa-arrow-left"></i> Regresar
                </a>

                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow">
                    <i class="fa-solid fa-floppy-disk"></i> Crear Usuario
                </button>

            </div>

        </form>
    </div>

</div>

<?php include('footer_admin.php'); ?>
