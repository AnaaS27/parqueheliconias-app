<?php
include('../includes/conexion.php');
include('../includes/verificar_admin.php');

// -----------------------------
// Validación del parámetro ID
// -----------------------------
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ ID de actividad no especificado'); window.location='actividades.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// -----------------------------
// Verificar si la actividad existe
// -----------------------------
$sqlCheck = "SELECT nombre FROM actividades WHERE id_actividad = $1";
$resultCheck = pg_query_params($conn, $sqlCheck, [$id]);

if (!$resultCheck || pg_num_rows($resultCheck) === 0) {
    echo "<script>alert('❌ La actividad no existe'); window.location='actividades.php';</script>";
    exit;
}

$actividad = pg_fetch_assoc($resultCheck)['nombre'];

// -----------------------------
// Verificar dependencias (reservas)
// -----------------------------
$sqlDep = "SELECT COUNT(*) AS total FROM reservas WHERE id_actividad = $1";
$resultDep = pg_query_params($conn, $sqlDep, [$id]);
$depCount = pg_fetch_assoc($resultDep)['total'];

if ($depCount > 0) {
    echo "<script>
        alert('⚠ No puedes eliminar esta actividad porque tiene $depCount reservas asociadas.');
        window.location='actividades.php';
    </script>";
    exit;
}

// -----------------------------
// Eliminar actividad (sin dependencias)
// -----------------------------
$sqlDelete = "DELETE FROM actividades WHERE id_actividad = $1";
$resultDelete = pg_query_params($conn, $sqlDelete, [$id]);

if ($resultDelete) {
    echo "<script>
        alert('✅ La actividad \"$actividad\" fue eliminada exitosamente');
        window.location='actividades.php';
    </script>";
} else {
    echo "<script>
        alert('❌ No se pudo eliminar la actividad. Intenta nuevamente.');
        window.history.back();
    </script>";
}

pg_close($conn);
?>

