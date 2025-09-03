<?php
/**
 * Archivo de instalación del sistema
 * Sistema de Gestión de Ventas
 */

require_once 'config/database.php';

// Verificar si ya está instalado
if (file_exists('config/installed.txt')) {
    echo "<h2>El sistema ya está instalado</h2>";
    echo "<p>Si deseas reinstalar, elimina el archivo 'config/installed.txt'</p>";
    echo "<a href='index.php'>Ir al sistema</a>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .install-container { max-width: 800px; margin: 50px auto; }
        .step { background: white; border-radius: 10px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step-header { border-bottom: 2px solid #007bff; padding-bottom: 15px; margin-bottom: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container install-container">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cogs text-primary"></i> Instalación del Sistema</h1>
            <p class="lead">Sistema de Gestión de Ventas v1.0</p>
        </div>

        <div class="step">
            <div class="step-header">
                <h3><i class="fas fa-database text-primary"></i> Paso 1: Verificación del Servidor</h3>
            </div>
            
            <?php
            $errores = array();
            $advertencias = array();
            
            // Verificar versión de PHP
            if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
                echo "<p class='success'><i class='fas fa-check-circle'></i> Versión de PHP: " . PHP_VERSION . " ✓</p>";
            } else {
                $errores[] = "Se requiere PHP 7.4 o superior. Versión actual: " . PHP_VERSION;
                echo "<p class='error'><i class='fas fa-times-circle'></i> Versión de PHP: " . PHP_VERSION . " ✗</p>";
            }
            
            // Verificar extensiones necesarias
            $extensiones_requeridas = array('pdo', 'pdo_mysql', 'mbstring', 'json');
            foreach ($extensiones_requeridas as $ext) {
                if (extension_loaded($ext)) {
                    echo "<p class='success'><i class='fas fa-check-circle'></i> Extensión $ext ✓</p>";
                } else {
                    $errores[] = "Extensión $ext no está disponible";
                    echo "<p class='error'><i class='fas fa-times-circle'></i> Extensión $ext ✗</p>";
                }
            }
            
            // Verificar permisos de escritura
            if (is_writable('.')) {
                echo "<p class='success'><i class='fas fa-check-circle'></i> Permisos de escritura en el directorio ✓</p>";
            } else {
                $advertencias[] = "No hay permisos de escritura en el directorio actual";
                echo "<p class='warning'><i class='fas fa-exclamation-triangle'></i> Permisos de escritura en el directorio ⚠</p>";
            }
            ?>
        </div>

        <?php if (empty($errores)): ?>
            <div class="step">
                <div class="step-header">
                    <h3><i class="fas fa-database text-primary"></i> Paso 2: Configuración de la Base de Datos</h3>
                </div>
                
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="host" class="form-label">Servidor MySQL:</label>
                                <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario MySQL:</label>
                                <input type="text" class="form-control" id="username" name="username" value="root" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña MySQL:</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="database" class="form-label">Nombre de la Base de Datos:</label>
                                <input type="text" class="form-control" id="database" name="database" value="gestion_ventas" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg" name="instalar">
                            <i class="fas fa-play"></i> Instalar Sistema
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="step">
                <div class="step-header">
                    <h3><i class="fas fa-exclamation-triangle text-danger"></i> Errores Detectados</h3>
                </div>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <p>Por favor, corrige estos errores antes de continuar con la instalación.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($advertencias)): ?>
            <div class="step">
                <div class="step-header">
                    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Advertencias</h3>
                </div>
                <div class="alert alert-warning">
                    <ul class="mb-0">
                        <?php foreach ($advertencias as $advertencia): ?>
                            <li><?php echo $advertencia; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php
        if (isset($_POST['instalar']) && empty($errores)) {
            echo "<div class='step'>";
            echo "<div class='step-header'>";
            echo "<h3><i class='fas fa-cog text-primary'></i> Paso 3: Instalación en Progreso</h3>";
            echo "</div>";
            
            try {
                // Crear instancia de base de datos
                $database = new Database();
                
                // Actualizar configuración si se proporcionó
                if (!empty($_POST['host'])) {
                    // Aquí podrías actualizar la configuración si es necesario
                }
                
                // Crear base de datos y tablas
                $database->createDatabase();
                
                echo "<div class='alert alert-success'>";
                echo "<h4><i class='fas fa-check-circle'></i> ¡Instalación Completada!</h4>";
                echo "<p>El sistema se ha instalado correctamente.</p>";
                echo "<hr>";
                echo "<h5>Credenciales de Acceso:</h5>";
                echo "<p><strong>Email:</strong> admin@sistema.com</p>";
                echo "<p><strong>Contraseña:</strong> admin123</p>";
                echo "<p class='text-warning'><i class='fas fa-exclamation-triangle'></i> <strong>IMPORTANTE:</strong> Cambia esta contraseña después del primer inicio de sesión.</p>";
                echo "</div>";
                
                echo "<div class='text-center'>";
                echo "<a href='index.php' class='btn btn-success btn-lg'>";
                echo "<i class='fas fa-sign-in-alt'></i> Ir al Sistema";
                echo "</a>";
                echo "</div>";
                
                // Crear archivo de instalación completada
                file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
                
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>";
                echo "<h4><i class='fas fa-times-circle'></i> Error en la Instalación</h4>";
                echo "<p>Se produjo un error durante la instalación:</p>";
                echo "<p><strong>" . $e->getMessage() . "</strong></p>";
                echo "</div>";
            }
            
            echo "</div>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
