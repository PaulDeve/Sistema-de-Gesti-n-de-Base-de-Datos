<?php
/**
 * Página de Login
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';

// Si ya está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    redirigir('dashboard.php');
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = limpiarEntrada($_POST['correo'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    
    if (empty($correo) || empty($contraseña)) {
        $mensaje = 'Por favor, completa todos los campos.';
        $tipo_mensaje = 'error';
    } else {
        try {
            require_once 'models/Usuario.php';
            $usuario = new Usuario();
            
            if ($usuario->autenticar($correo, $contraseña)) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario->id_usuario;
                $_SESSION['usuario_nombre'] = $usuario->nombre;
                $_SESSION['usuario_correo'] = $usuario->correo;
                $_SESSION['usuario_rol'] = $usuario->rol;
                
                // Registrar actividad
                registrarActividad('Inicio de sesión', 'Login exitoso');
                
                // Redirigir al dashboard
                redirigir('dashboard.php');
            } else {
                $mensaje = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
                $tipo_mensaje = 'error';
            }
        } catch (Exception $e) {
            $mensaje = 'Error en el sistema. Por favor, intenta más tarde.';
            $tipo_mensaje = 'error';
            error_log("Error en login: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-header">
                        <h2><i class="fas fa-chart-line"></i></h2>
                        <h4 class="mb-0">Sistema de Gestión</h4>
                        <p class="mb-0">Inicia sesión para continuar</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje === 'error' ? 'danger' : 'info'; ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-<?php echo $tipo_mensaje === 'error' ? 'exclamation-circle' : 'info-circle'; ?>"></i>
                                <?php echo $mensaje; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="correo" name="correo" placeholder="correo@ejemplo.com" required>
                                <label for="correo">Correo Electrónico</label>
                            </div>
                            
                            <div class="form-floating">
                                <input type="password" class="form-control" id="contraseña" name="contraseña" placeholder="Contraseña" required>
                                <label for="contraseña">Contraseña</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Credenciales por defecto: admin@sistema.com / admin123
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="install.php" class="text-white text-decoration-none">
                        <i class="fas fa-cogs"></i> Instalar Sistema
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
