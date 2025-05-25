<?php
session_start();

// Verificar si la sesión del usuario está activa
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirigir al login si no hay sesión activa
    exit;
}
?>
