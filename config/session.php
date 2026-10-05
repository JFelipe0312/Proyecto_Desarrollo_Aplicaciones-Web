<?php
// =============================================
// Control de sesión y autenticación
// Archivo: config/session.php
// =============================================

session_start();

function verificarSesion() {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: ../index.php');
        exit();
    }
}

function verificarAdmin() {
    verificarSesion();
    if ($_SESSION['rol'] !== 'administrador') {
        header('Location: ../dashboard.php?error=acceso_denegado');
        exit();
    }
}

function estaLogueado() {
    return isset($_SESSION['id_usuario']);
}
?>
