<?php
// === VERIFICACIÓN DE SESIÓN PARA ADMINISTRADOR ===

// Iniciamos la sesión (si no está iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario no ha iniciado sesión, lo redirigimos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../paginas/login.php");
    exit;
}

// Si el usuario no tiene rol de administrador (rol = 1), lo redirigimos al inicio general
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: ../paginas/inicio.php");
    exit;
}

// Si llega aquí, el usuario tiene sesión y rol de admin ✔️
?>
