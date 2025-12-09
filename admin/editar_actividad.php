<?php
include('header_admin.php');
require_once("../includes/supabase.php"); // ← CONEXIÓN SUPABASE ÚNICA

// Verificar si llega el ID de la actividad
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ ID de actividad no especificado'); window.location='actividades.php';</script>";
    exit;
}

$id_actividad = intval($_GET['id']);

// =====================================
// CONSULTAR ACTIVIDAD DESDE SUPABASE
// =====================================

list($code, $data) = supabase_get("actividades", ["id_actividad" => $id_actividad]);

if ($code !== 200 || empty($data)) {
    echo "<script>alert('❌ Actividad no encontrada'); window.location='actividades.php';</script>";
    exit;
}

// Supabase devuelve array → tomamos el primer resultado
$actividad = $data[0];
?>

<section class="admin-editar-actividad">
  <h2 class="titulo-dashboard">✏ Editar Actividad</h2>
  <p class="subtitulo-dashboard">Modifica los datos de la actividad seleccionada.</p>

  <div class="formulario-admin">
    <form action="procesar_editar_actividad.php" method="POST">
      <input type="hidden" name="id_actividad" value="<?php echo $actividad['id_actividad']; ?>">

      <label>Nombre:</label>
      <input type="text" name="nombre" value="<?php echo htmlspecialchars($actividad['nombre']); ?>" required>

      <label>Descripción:</label>
      <textarea name="descripcion" rows="3" required><?php echo htmlspecialchars($actividad['descripcion']); ?></textarea>

      <label>Duración (minutos):</label>
      <input type="number" name="duracion_minutos" min="10" value="<?php echo $actividad['duracion_minutos']; ?>" required>

      <label>Cupo máximo:</label>
      <input type="number" name="cupo_maximo" min="1" value="<?php echo $actividad['cupo_maximo']; ?>" required>

      <label>Estado:</label>
      <select name="activo">
        <option value="1" <?php if ($actividad['activo'] == true || $actividad['activo'] == "1") echo 'selected'; ?>>Activa</option>
        <option value="0" <?php if ($actividad['activo'] == false || $actividad['activo'] == "0") echo 'selected'; ?>>Inactiva</option>
      </select>

      <div class="acciones-formulario">
        <button type="submit" class="btn-admin">💾 Guardar Cambios</button>
        <a href="actividades.php" class="btn-cancelar">↩ Volver</a>
      </div>
    </form>
  </div>
</section>

<?php include('footer_admin.php'); ?>