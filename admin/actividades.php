<?php
include('header_admin.php');
include('../includes/supabase.php'); // <-- Ahora usamos Supabase
?>

<section class="admin-actividades">
  <h2 class="titulo-dashboard">🎫 Gestión de Actividades</h2>
  <p class="subtitulo-dashboard">Administra las actividades disponibles en el Parque Las Heliconias.</p>

  <!-- BOTÓN NUEVA ACTIVIDAD -->
  <div class="acciones-superiores">
    <button class="btn-admin" onclick="abrirModal()">➕ Nueva Actividad</button>
  </div>

  <!-- TABLA DE ACTIVIDADES -->
  <div class="tabla-contenedor">
    <table class="tabla-admin">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Duración</th>
          <th>Cupos</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>

        <?php
        // ===============================
        //   Consultar actividades
        // ===============================
        list($codeAct, $actividades) = supabase_get("actividades?select=*&order=id_actividad.asc");

        if ($codeAct === 200 && !empty($actividades)):
            foreach ($actividades as $row):
        ?>

        <tr>
          <td><?= $row['id_actividad']; ?></td>

          <td><?= htmlspecialchars($row['nombre']); ?></td>

          <td><?= htmlspecialchars($row['descripcion']); ?></td>

          <td><?= $row['duracion_minutos']; ?> min</td>

          <td><?= $row['cupo_maximo']; ?></td>

          <td>
            <?php if ($row['activo'] == true): ?>
              <span class="estado-activo">Activa</span>
            <?php else: ?>
              <span class="estado-inactivo">Inactiva</span>
            <?php endif; ?>
          </td>

          <td>
            <a href="editar_actividad.php?id=<?= $row['id_actividad'] ?>" 
               class="btn-accion editar">✏️</a>

            <a href="eliminar_actividad.php?id=<?= $row['id_actividad'] ?>" 
               class="btn-accion eliminar"
               onclick="return confirm('¿Seguro que deseas eliminar esta actividad?');">🗑️</a>
          </td>
        </tr>

        <?php
            endforeach;
        else:
            echo "<tr><td colspan='7' class='sin-registros'>No hay actividades registradas.</td></tr>";
        endif;
        ?>

      </tbody>
    </table>
  </div>
</section>

<!-- === MODAL NUEVA ACTIVIDAD === -->
<div id="modalActividad" class="modal-oculto">
  <div class="modal-contenido">
    <button class="btn-cerrar" onclick="cerrarModal()">✖</button>
    <h3>➕ Agregar Nueva Actividad</h3>

    <form action="procesar_actividad.php" method="POST">

      <label>Nombre:</label>
      <input type="text" name="nombre" required>

      <label>Descripción:</label>
      <textarea name="descripcion" rows="3" required></textarea>

      <label>Duración (minutos):</label>
      <input type="number" name="duracion_minutos" min="10" required>

      <label>Cupo máximo:</label>
      <input type="number" name="cupo_maximo" min="1" required>

      <label>Estado:</label>
      <select name="activo">
        <option value="true" selected>Activa</option>
        <option value="false">Inactiva</option>
      </select>

      <button type="submit" class="btn-admin">Guardar Actividad</button>
    </form>

  </div>
</div>

<script>
function abrirModal() {
  document.getElementById('modalActividad').classList.remove('modal-oculto');
}
function cerrarModal() {
  document.getElementById('modalActividad').classList.add('modal-oculto');
}
</script>

<?php include('footer_admin.php'); ?>
