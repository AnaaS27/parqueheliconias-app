<?php
session_start();
include('../includes/conexion.php');

// FORZAMOS el usuario 25 directamente
$id_usuario = 25;

$sql = "
SELECT r.id_reserva, a.nombre AS actividad, a.descripcion,
       r.fecha_reserva, r.estado, r.tipo_reserva, r.numero_participantes
FROM reservas r
LEFT JOIN actividades a ON r.id_actividad = a.id_actividad
WHERE r.id_usuario = $1
ORDER BY r.fecha_reserva DESC
";

$result = pg_query_params($conn, $sql, [$id_usuario]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>PRUEBA FINAL RESERVAS</title>
<style>
body {
  font-family: Arial, sans-serif;
  background: #f2f2f2;
  padding: 40px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  border: 2px solid green;
}
</style>
</head>
<body>

<h1>✅ PRUEBA FINAL DE RESERVAS</h1>
<h3>Total: <?php echo pg_num_rows($result); ?></h3>

<div class="grid">
<?php while ($reserva = pg_fetch_assoc($result)): ?>
  <div class="card">
    <h3><?php echo $reserva['actividad']; ?></h3>
    <p><b>ID:</b> <?php echo $reserva['id_reserva']; ?></p>
    <p><b>Estado:</b> <?php echo $reserva['estado']; ?></p>
    <p><b>Fecha:</b> <?php echo $reserva['fecha_reserva']; ?></p>
    <p><b>Tipo:</b> <?php echo $reserva['tipo_reserva']; ?></p>
    <p><b>Participantes:</b> <?php echo $reserva['numero_participantes']; ?></p>
  </div>
<?php endwhile; ?>
</div>

</body>
</html>
