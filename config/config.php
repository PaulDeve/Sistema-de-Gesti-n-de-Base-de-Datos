<?php
/**
 * Configuración principal del sistema
 * Sistema de Gestión de Ventas
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_ventas');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Gestión de Ventas');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/gdb');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_NAME', 'gdb_session');

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// Configuración de archivos
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de paginación
define('ITEMS_PER_PAGE', 20);

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Configuración de headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Función para cargar clases automáticamente
spl_autoload_register(function ($class_name) {
    $paths = array(
        'models/',
        'controllers/',
        'includes/'
    );
    
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Función para verificar si el usuario está autenticado
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Función para obtener el rol del usuario actual
function obtenerRolUsuario() {
    return $_SESSION['usuario_rol'] ?? 'invitado';
}

// Función para verificar permisos
function tienePermiso($permiso) {
    if (!estaAutenticado()) {
        return false;
    }
    
    $rol = obtenerRolUsuario();
    
    switch ($permiso) {
        case 'admin':
            return $rol === 'admin';
        case 'vendedor':
            return $rol === 'vendedor' || $rol === 'admin';
        case 'cliente':
            return $rol === 'cliente' || $rol === 'vendedor' || $rol === 'admin';
        default:
            return false;
    }
}

// Función para registrar actividad del usuario
function registrarActividad($accion, $detalles = '') {
    if (estaAutenticado()) {
        $usuario_id = $_SESSION['usuario_id'];
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
        
        // Aquí podrías guardar en una tabla de logs si la necesitas
        error_log("Actividad: Usuario $usuario_id - $accion - $detalles - $fecha - IP: $ip");
    }
}














?>
