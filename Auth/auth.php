<?php
// auth.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si no hay usuario logueado, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/login.php");
    exit();
}
?>
