<?php
require_once("../includes/supabase.php"); // ← Única conexión permitida
require_once('../includes/verificar_admin.php');

// -----------------------------
// Validación de ID
// -----------------------------
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('❌ ID de actividad no especificado'); window.location='actividades.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// -----------------------------
// Verificar si la actividad existe
// -----------------------------
list($codeCheck, $actividadData) = supabase_get("actividades", ["id_actividad" => $id]);

if ($codeCheck !== 200 || empty($actividadData)) {
    echo "<script>alert('❌ La actividad no existe'); window.location='actividades.php';</script>";
    exit;
}

$actividadNombre = $actividadData[0]["nombre"];

// -----------------------------
// Verificar dependencias (reservas)
// -----------------------------
list($codeDep, $reservas) = supabase_get("reservas", ["id_actividad" => $id]);

$depCount = is_array($reservas) ? count($reservas) : 0;

if ($depCount > 0) {
    echo "<script>
        alert('⚠ No puedes eliminar esta actividad porque tiene $depCount reservas asociadas.');
        window.location='actividades.php';
    </script>";
    exit;
}

// -----------------------------
// Eliminar actividad
// -----------------------------
list($codeDelete, $respDelete) = supabase_delete("actividades", ["id_actividad" => $id]);

if ($codeDelete === 200 || $codeDelete === 204) {
    echo "<script>
        alert('✅ La actividad \"{$actividadNombre}\" fue eliminada exitosamente');
        window.location='actividades.php';
    </script>";
} else {
    echo "<script>
        alert('❌ No se pudo eliminar la actividad. Intenta nuevamente.');
        window.history.back();
    </script>";
    exit;
}
?>