<?php
session_start();

$mensaje = $_SESSION["mensaje_reserva"] ?? "Operación finalizada.";
$tipo    = $_SESSION["tipo_reserva"] ?? "info";

// Limpia mensaje para siguientes peticiones
unset($_SESSION["mensaje_reserva"]);
unset($_SESSION["tipo_reserva"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultado de la Reserva</title>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="max-w-lg w-full bg-white shadow-xl rounded-xl p-8 text-center">

    <?php if ($tipo === "exito"): ?>
        <h2 class="text-2xl font-bold text-green-600 mb-4">🎉 Reserva Exitosa</h2>
    <?php else: ?>
        <h2 class="text-2xl font-bold text-red-600 mb-4">❌ Error al Registrar</h2>
    <?php endif; ?>

    <p class="text-lg text-gray-700 mb-6"><?= htmlspecialchars($mensaje) ?></p>

    <a href="mis_reservas.php" 
       class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-900 transition">
       Ir a mis reservas →
    </a>

</div>

</body>
</html>
