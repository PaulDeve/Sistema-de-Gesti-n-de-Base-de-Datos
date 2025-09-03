<?php
/**
 * Archivo de Logout
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';

// Registrar actividad antes de cerrar sesión
if (estaAutenticado()) {
    registrarActividad('Cierre de sesión', 'Logout exitoso');
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también eliminar la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al login
redirigir('login.php');
?>
