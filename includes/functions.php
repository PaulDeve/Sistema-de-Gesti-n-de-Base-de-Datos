<?php
/**
 * Funciones utilitarias del sistema
 * Sistema de Gestión de Ventas
 */

// Función para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para validar teléfono
function validarTelefono($telefono) {
    return preg_match('/^[\d\s\-\+\(\)]+$/', $telefono);
}

// Función para validar precio
function validarPrecio($precio) {
    return is_numeric($precio) && $precio >= 0;
}

// Función para validar cantidad
function validarCantidad($cantidad) {
    return is_numeric($cantidad) && $cantidad > 0 && is_int($cantidad + 0);
}

// Función para sanitizar entrada de texto
function sanitizarTexto($texto) {
    return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
}

// Función para formatear precio
function formatearPrecio($precio) {
    return number_format($precio, 2, '.', ',');
}

// Función para formatear moneda
function formatearMoneda($cantidad) {
    return '$' . number_format($cantidad, 2, '.', ',');
}

// Función para formatear fecha
function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

// Función para generar token CSRF
function generarTokenCSRF() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Función para verificar token CSRF
function verificarTokenCSRF($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Función para validar token CSRF
function validarTokenCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !verificarTokenCSRF($_POST[CSRF_TOKEN_NAME])) {
            http_response_code(403);
            die('Token CSRF inválido');
        }
    }
}

// Función para redirigir
function redirigir($url) {
    header("Location: $url");
    exit();
}

// Función para mostrar mensaje de éxito
function mostrarExito($mensaje) {
    return "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle'></i> $mensaje
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Función para mostrar mensaje de error
function mostrarError($mensaje) {
    return "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle'></i> $mensaje
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Función para mostrar mensaje de advertencia
function mostrarAdvertencia($mensaje) {
    return "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-triangle'></i> $mensaje
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Función para mostrar mensaje de información
function mostrarInfo($mensaje) {
    return "<div class='alert alert-info alert-dismissible fade show' role='alert'>
                <i class='fas fa-info-circle'></i> $mensaje
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Función para validar sesión activa
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        redirigir('login.php');
    }
}

// Función para verificar permisos de administrador
function verificarAdmin() {
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
        redirigir('dashboard.php');
    }
}

// Función para obtener nombre del mes
function obtenerNombreMes($numero_mes) {
    $meses = array(
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    );
    return $meses[$numero_mes] ?? 'Desconocido';
}

// Función para calcular días entre fechas
function calcularDiasEntreFechas($fecha_inicio, $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $diferencia = $inicio->diff($fin);
    return $diferencia->days;
}

// Función para generar código de venta
function generarCodigoVenta() {
    return 'V-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
}

// Función para validar archivo de imagen
function validarImagen($archivo) {
    $tipos_permitidos = array('image/jpeg', 'image/png', 'image/gif');
    $tamano_maximo = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        return false;
    }
    
    if ($archivo['size'] > $tamano_maximo) {
        return false;
    }
    
    return true;
}

// Función para subir imagen
function subirImagen($archivo, $directorio_destino) {
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid() . '.' . $extension;
    $ruta_completa = $directorio_destino . '/' . $nombre_archivo;
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return $nombre_archivo;
    }
    
    return false;
}

// Función para generar paginación
function generarPaginacion($total_registros, $registros_por_pagina, $pagina_actual, $url_base) {
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    if ($total_paginas <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center">';
    
    // Botón anterior
    if ($pagina_actual > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . '?pagina=' . ($pagina_actual - 1) . '">Anterior</a></li>';
    }
    
    // Números de página
    for ($i = max(1, $pagina_actual - 2); $i <= min($total_paginas, $pagina_actual + 2); $i++) {
        $clase = ($i == $pagina_actual) ? 'page-item active' : 'page-item';
        $html .= '<li class="' . $clase . '"><a class="page-link" href="' . $url_base . '?pagina=' . $i . '">' . $i . '</a></li>';
    }
    
    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url_base . '?pagina=' . ($pagina_actual + 1) . '">Siguiente</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

// Función para limpiar datos de entrada
function limpiarDatos($datos) {
    if (is_array($datos)) {
        foreach ($datos as $clave => $valor) {
            $datos[$clave] = limpiarDatos($valor);
        }
    } else {
        $datos = sanitizarTexto($datos);
    }
    return $datos;
}

// Función para validar fecha
function validarFecha($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

// Función para obtener estadísticas del dashboard
function obtenerEstadisticasDashboard() {
    try {
        require_once 'models/Cliente.php';
        require_once 'models/Producto.php';
        require_once 'models/Venta.php';
        
        $cliente = new Cliente();
        $producto = new Producto();
        $venta = new Venta();
        
        return array(
            'total_clientes' => $cliente->contar(),
            'total_productos' => $producto->contar(),
            'total_ventas' => $venta->contar(),
            'valor_inventario' => $producto->valorInventario(),
            'productos_stock_bajo' => $producto->stockBajo()->rowCount(),
            'estadisticas_ventas' => $venta->obtenerEstadisticas()
        );
    } catch (Exception $e) {
        // Si hay error, retornar estadísticas por defecto
        error_log("Error obteniendo estadísticas del dashboard: " . $e->getMessage());
        return array(
            'total_clientes' => 0,
            'total_productos' => 0,
            'total_ventas' => 0,
            'valor_inventario' => 0,
            'productos_stock_bajo' => 0,
            'estadisticas_ventas' => array()
        );
    }
}

// Función para limpiar datos de entrada
function limpiarEntrada($datos) {
    if (is_array($datos)) {
        foreach ($datos as $clave => $valor) {
            $datos[$clave] = limpiarEntrada($valor);
        }
    } else {
        $datos = htmlspecialchars(strip_tags(trim($datos)), ENT_QUOTES, 'UTF-8');
    }
    return $datos;
}

// Función para verificar si es una petición AJAX
function esAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Función para responder JSON
function responderJSON($datos, $codigo = 200) {
    http_response_code($codigo);
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit();
}

// Función para generar código único
function generarCodigoUnico($prefijo = '') {
    return $prefijo . uniqid() . '_' . time();
}

// Función para validar archivo
function validarArchivo($archivo, $tipos_permitidos = [], $tamano_maximo = null) {
    if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($tamano_maximo && $archivo['size'] > $tamano_maximo) {
        return false;
    }
    
    if (!empty($tipos_permitidos) && !in_array($archivo['type'], $tipos_permitidos)) {
        return false;
    }
    
    return true;
}

// Función para obtener extensión de archivo
function obtenerExtension($nombre_archivo) {
    return strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
}

// Función para generar nombre de archivo seguro
function generarNombreArchivo($nombre_original, $prefijo = '') {
    $extension = obtenerExtension($nombre_original);
    $nombre_base = uniqid($prefijo);
    return $nombre_base . '.' . $extension;
}

// Función para mostrar mensaje
function mostrarMensaje($tipo, $mensaje) {
    $clases = [
        'exito' => 'alert-success',
        'error' => 'alert-danger',
        'advertencia' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $iconos = [
        'exito' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'advertencia' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle'
    ];
    
    $clase = $clases[$tipo] ?? 'alert-info';
    $icono = $iconos[$tipo] ?? 'fas fa-info-circle';
    
    return "<div class='alert $clase alert-dismissible fade show' role='alert'>
                <i class='$icono'></i> $mensaje
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}
?>
