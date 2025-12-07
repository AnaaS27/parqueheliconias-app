<?php
include('../includes/verificar_admin.php');
include('../includes/conexion.php');
include('header_admin.php');

if (!isset($_GET['id'])) {
    echo "ID de usuario no proporcionado";
    exit;
}

$id_usuario = $_GET['id'];

// Obtener datos del usuario
$query = pg_query($conn, "SELECT * FROM usuarios WHERE id_usuario = $id_usuario");
$usuario = pg_fetch_assoc($query);

$id_ciudad_usuario = $usuario['id_ciudad'] ?? null;

if ($id_ciudad_usuario) {
    $buscarPais = pg_query($conn, "SELECT pais_id FROM ciudades WHERE id = $id_ciudad_usuario");
    $paisData = pg_fetch_assoc($buscarPais);
    $id_pais = $paisData['pais_id'];
} else {
    // Si el usuario no tiene ciudad registrada, asigna un país por defecto
    $id_pais = 1; 
}
// Consultas para selects
$roles = pg_query($conn, "SELECT id_rol, nombre FROM roles ORDER BY id_rol ASC");
$instituciones = pg_query($conn, "SELECT id_institucion, nombre_institucion FROM instituciones ORDER BY nombre_institucion ASC");
$generos = pg_query($conn, "SELECT id_genero, genero FROM genero ORDER BY id_genero ASC");
$ciudades = pg_query($conn, "SELECT id, nombre FROM ciudades WHERE pais_id = $id_pais ORDER BY nombre ASC");
$paises = pg_query($conn, "SELECT id, pais FROM pais ORDER BY pais ASC");
?>

<div class="max-w-5xl mx-auto p-6">

    <!-- Título -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
            Editar Usuario
        </h1>

        <a href="usuarios.php" 
           class="px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 transition">
           Volver
        </a>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-md p-8 border border-gray-100">

        <form action="procesar_editar_usuario.php" method="POST" class="space-y-6">

            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

            <!-- Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Nombre -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Nombre</label>
                    <input type="text" name="nombre"
                        value="<?= htmlspecialchars($usuario['nombre']) ?>"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Apellido -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Apellido</label>
                    <input type="text" name="apellido"
                        value="<?= htmlspecialchars($usuario['apellido']) ?>"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Correo -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Correo</label>
                    <input type="email" name="correo"
                        value="<?= htmlspecialchars($usuario['correo']) ?>"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Documento -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Documento</label>
                    <input type="text" name="documento"
                        value="<?= htmlspecialchars($usuario['documento']) ?>"
                        required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Teléfono</label>
                    <input type="text" name="telefono"
                        value="<?= htmlspecialchars($usuario['telefono']) ?>"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Fecha nacimiento -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento"
                        value="<?= $usuario['fecha_nacimiento'] ?>"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Contraseña -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Nueva contraseña</label>
                    <input type="password" name="contrasena"
                        placeholder="Dejar vacío para no cambiarla"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Género -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Género</label>
                    <select name="id_genero" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione...</option>
                        <?php while ($g = pg_fetch_assoc($generos)): ?>
                            <option value="<?= $g['id_genero'] ?>" <?= $usuario['id_genero']==$g['id_genero'] ? 'selected' : '' ?>>
                                <?= $g['genero'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Rol -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Rol</label>
                    <select name="id_rol"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <?php while ($r = pg_fetch_assoc($roles)): ?>
                            <option value="<?= $r['id_rol'] ?>" <?= $usuario['id_rol']==$r['id_rol'] ? 'selected':'' ?>>
                                <?= $r['nombre'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Institución -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Institución</label>
                    <select name="id_institucion" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione...</option>
                        <?php while ($inst = pg_fetch_assoc($instituciones)): ?>
                            <option value="<?= $inst['id_institucion'] ?>" 
                                <?= $usuario['id_instituciones']==$inst['id_institucion'] ? 'selected':'' ?>>
                                <?= $inst['nombre_institucion'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- País -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">País</label>
                    <select name="pais" class="w-full px-4 py-2 rounded-lg border border-gray-300"
                        onchange="cargarCiudades(this.value)">
                        <?php while ($p = pg_fetch_assoc($paises)): ?>
                            <option value="<?= $p['id'] ?>" <?= $id_pais==$p['id'] ? 'selected' : '' ?>>
                                <?= $p['pais'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Ciudad -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Ciudad</label>
                    <select name="id_ciudad" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccione...</option>
                        <?php while ($c = pg_fetch_assoc($ciudades)): ?>
                            <option value="<?= $c['id'] ?>" <?= $usuario['id_ciudad']==$c['id'] ? 'selected':'' ?>>
                                <?= $c['nombre'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Estado del usuario</label>
                    <select name="usuario_activo"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="1" <?= $usuario['usuario_activo'] ? 'selected':'' ?>>Activo</option>
                        <option value="0" <?= !$usuario['usuario_activo'] ? 'selected':'' ?>>Inactivo</option>
                    </select>
                </div>

            </div>

            <!-- Botón Guardar -->
            <div class="text-right">
                <button class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

<?php include('footer_admin.php'); ?>
