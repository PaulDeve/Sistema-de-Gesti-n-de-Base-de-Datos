<?php
/**
 * Archivo Principal
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';

// Verificar si el sistema está instalado
if (!file_exists('config/installed.txt')) {
    redirigir('install.php');
}

// Si el usuario está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    redirigir('dashboard.php');
}

// Si no está autenticado, redirigir al login
redirigir('login.php');
?>
