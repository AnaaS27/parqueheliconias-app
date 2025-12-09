<?php
require_once('../includes/verificar_admin.php');
require_once("../includes/supabase.php");
include('header_admin.php');

// =============================
// VALIDAR ID
// =============================
if (!isset($_GET['id'])) {
    echo "ID de usuario no proporcionado";
    exit;
}

$id_usuario = intval($_GET['id']);

// =============================
// 1️⃣ OBTENER USUARIO CORRECTAMENTE
// =============================
$endpointUser = "usuarios?id_usuario=eq.$id_usuario&select=*";
list($codeUser, $userData) = supabase_get($endpointUser);

if ($codeUser !== 200 || empty($userData)) {
    echo "<script>alert('❌ Usuario no encontrado'); window.location='usuarios.php';</script>";
    exit;
}

$usuario = $userData[0];

// =============================
// 2️⃣ OBTENER PAÍS SEGÚN CIUDAD
// =============================
$id_ciudad_usuario = $usuario["id_ciudad"] ?? null;
$id_pais = 1; // valor por defecto

if ($id_ciudad_usuario) {
    $endpointCiudad = "ciudades?id=eq.$id_ciudad_usuario&select=pais_id";
    list($codeCiudad, $ciudadData) = supabase_get($endpointCiudad);

    if ($codeCiudad === 200 && !empty($ciudadData)) {
        $id_pais = $ciudadData[0]["pais_id"] ?? 1;
    }
}

// =============================
// 3️⃣ OBTENER LISTAS DESDE SUPABASE
// =============================
list($codeRoles, $roles) = supabase_get("roles?select=*");
list($codeInst, $instituciones) = supabase_get("instituciones?select=*");
list($codeGen, $generos) = supabase_get("genero?select=*");
list($codePaises, $paises) = supabase_get("pais?select=*");
list($codeCiu, $ciudades) = supabase_get("ciudades?pais_id=eq.$id_pais&select=*");
?>

<div class="max-w-5xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Editar Usuario</h1>
        <a href="usuarios.php" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">Volver</a>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-8 border border-gray-100">

        <form action="procesar_editar_usuario.php" method="POST" class="space-y-6">

            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required class="input">
                </div>

                <div>
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required class="input">
                </div>

                <div>
                    <label>Correo</label>
                    <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required class="input">
                </div>

                <div>
                    <label>Documento</label>
                    <input type="text" name="documento" value="<?= htmlspecialchars($usuario['documento']) ?>" required class="input">
                </div>

                <div>
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" class="input">
                </div>

                <div>
                    <label>Fecha nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento'] ?>" class="input">
                </div>

                <div>
                    <label>Nueva contraseña</label>
                    <input type="password" name="contrasena" placeholder="Dejar vacío para NO cambiarla" class="input">
                </div>

                <div>
                    <label>Género</label>
                    <select name="id_genero" class="input">
                        <?php foreach ($generos as $g): ?>
                            <option value="<?= $g['id_genero'] ?>" <?= $usuario['id_genero'] == $g['id_genero'] ? 'selected' : '' ?>>
                                <?= $g['genero'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Rol</label>
                    <select name="id_rol" class="input">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id_rol'] ?>" <?= $usuario['id_rol'] == $r['id_rol'] ? 'selected' : '' ?>>
                                <?= $r['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Institución</label>
                    <select name="id_institucion" class="input">
                        <?php foreach ($instituciones as $inst): ?>
                            <option value="<?= $inst['id_institucion'] ?>" <?= $usuario['id_institucion'] == $inst['id_institucion'] ? 'selected' : '' ?>>
                                <?= $inst['nombre_institucion'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>País</label>
                    <select name="pais" onchange="cargarCiudades(this.value)" class="input">
                        <?php foreach ($paises as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $id_pais == $p['id'] ? 'selected' : '' ?>>
                                <?= $p['pais'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Ciudad</label>
                    <select name="id_ciudad" class="input">
                        <?php foreach ($ciudades as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $usuario['id_ciudad'] == $c['id'] ? 'selected' : '' ?>>
                                <?= $c['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Estado</label>
                    <select name="usuario_activo" class="input">
                        <option value="1" <?= $usuario['usuario_activo'] ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= !$usuario['usuario_activo'] ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

            </div>

            <div class="text-right">
                <button class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Guardar cambios</button>
            </div>

        </form>

    </div>
</div>

<style>
.input {
    @apply w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500;
}
</style>

<?php include('footer_admin.php'); ?>
