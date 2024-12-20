<?php
session_start();
// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir al inicio de sesión si no está autenticado
    header("Location: login.html?error=not_authenticated");
    exit;
}
?>