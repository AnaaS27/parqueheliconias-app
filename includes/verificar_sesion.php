<?php
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../paginas/login.php");
    exit;
}
?>
