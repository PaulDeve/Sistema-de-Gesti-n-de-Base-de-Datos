<?php
/**
 * Gestión de Usuarios
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar autenticación y permisos de administrador
if (!estaAutenticado()) {
    redirigir('login.php');
}

if (!tienePermiso('admin')) {
    redirigir('dashboard.php');
}

// Incluir modelo de usuarios
require_once 'models/Usuario.php';
$usuario = new Usuario();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nuevo usuario
                $usuario->nombre = limpiarEntrada($_POST['nombre']);
                $usuario->correo = limpiarEntrada($_POST['correo']);
                $usuario->contraseña = $_POST['contraseña'];
                $usuario->rol = $_POST['rol'];
                
                if ($usuario->crear()) {
                    $mensaje = 'Usuario creado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Crear usuario', "Usuario: {$usuario->nombre}");
                } else {
                    $mensaje = 'Error al crear el usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'actualizar':
                // Actualizar usuario existente
                $usuario->id_usuario = $_POST['id_usuario'];
                $usuario->nombre = limpiarEntrada($_POST['nombre']);
                $usuario->correo = limpiarEntrada($_POST['correo']);
                $usuario->rol = $_POST['rol'];
                
                if ($usuario->actualizar()) {
                    $mensaje = 'Usuario actualizado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Actualizar usuario', "Usuario: {$usuario->nombre}");
                } else {
                    $mensaje = 'Error al actualizar el usuario';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'cambiar_contraseña':
                // Cambiar contraseña
                $usuario->id_usuario = $_POST['id_usuario'];
                $nueva_contraseña = $_POST['nueva_contraseña'];
                
                if ($usuario->actualizarContraseña($nueva_contraseña)) {
                    $mensaje = 'Contraseña actualizada exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Cambiar contraseña', "Usuario ID: {$usuario->id_usuario}");
                } else {
                    $mensaje = 'Error al actualizar la contraseña';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar usuario
                $usuario->id_usuario = $_POST['id_usuario'];
                
                // No permitir eliminar el usuario actual
                if ($usuario->id_usuario == $_SESSION['usuario_id']) {
                    $mensaje = 'No puede eliminar su propio usuario';
                    $tipo_mensaje = 'error';
                } else {
                    if ($usuario->eliminar()) {
                        $mensaje = 'Usuario eliminado exitosamente';
                        $tipo_mensaje = 'exito';
                        registrarActividad('Eliminar usuario', "ID: {$usuario->id_usuario}");
                    } else {
                        $mensaje = 'Error al eliminar el usuario';
                        $tipo_mensaje = 'error';
                    }
                }
                break;
        }
    }
}

// Obtener parámetros de búsqueda y paginación
$buscar = $_GET['buscar'] ?? '';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$registros_por_pagina = 10;

// Obtener usuarios
if (!empty($buscar)) {
    $resultado = $usuario->buscar($buscar);
    $total_registros = $resultado->rowCount();
} else {
    $resultado = $usuario->leerTodos($registros_por_pagina, ($pagina - 1) * $registros_por_pagina);
    $total_registros = $usuario->contar();
}

$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener usuario para editar
$usuario_editar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $usuario->id_usuario = $_GET['editar'];
    $usuario_editar = $usuario->leerUno();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .rol-admin {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .rol-vendedor {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .usuario-actual {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-chart-line"></i> GDB
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="clientes.php">
                            <i class="fas fa-users"></i> Clientes
                        </a>
                        <a class="nav-link" href="productos.php">
                            <i class="fas fa-box"></i> Productos
                        </a>
                        <a class="nav-link" href="ventas.php">
                            <i class="fas fa-shopping-cart"></i> Ventas
                        </a>
                        <a class="nav-link" href="reportes.php">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                        <a class="nav-link active" href="usuarios.php">
                            <i class="fas fa-user-cog"></i> Usuarios
                        </a>
                        <hr class="my-3">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Gestión de Usuarios</span>
                        
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></div>
                                <small class="text-muted"><?php echo ucfirst($_SESSION['usuario_rol'] ?? 'usuario'); ?></small>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <!-- Mensajes -->
                    <?php if (!empty($mensaje)): ?>
                        <?php echo mostrarMensaje($tipo_mensaje, $mensaje); ?>
                    <?php endif; ?>

                    <!-- Header con búsqueda y botón nuevo -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2><i class="fas fa-user-cog text-primary"></i> Usuarios del Sistema</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                                <i class="fas fa-plus"></i> Nuevo Usuario
                            </button>
                        </div>
                    </div>

                    <!-- Búsqueda -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="buscar" class="form-control me-2" 
                                       placeholder="Buscar por nombre o correo..." 
                                       value="<?php echo htmlspecialchars($buscar); ?>">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="usuarios.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Limpiar
                            </a>
                        </div>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Rol</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($resultado && $resultado->rowCount() > 0): ?>
                                            <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                                                <tr class="<?php echo $row['id_usuario'] == $_SESSION['usuario_id'] ? 'usuario-actual' : ''; ?>">
                                                    <td><?php echo $row['id_usuario']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                                        <?php if ($row['id_usuario'] == $_SESSION['usuario_id']): ?>
                                                            <span class="badge bg-info ms-2">Tú</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $row['rol'] === 'admin' ? 'bg-danger' : 'bg-success'; ?> rounded-pill">
                                                            <?php echo ucfirst($row['rol']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo formatearFecha($row['fecha_creacion']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                                onclick="editarUsuario(<?php echo $row['id_usuario']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-warning btn-action" 
                                                                onclick="cambiarContraseña(<?php echo $row['id_usuario']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>')">
                                                            <i class="fas fa-key"></i>
                                                        </button>
                                                        <?php if ($row['id_usuario'] != $_SESSION['usuario_id']): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                                    onclick="eliminarUsuario(<?php echo $row['id_usuario']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-user-cog text-muted fa-2x mb-2"></i>
                                                    <p class="text-muted mb-0">
                                                        <?php echo empty($buscar) ? 'No hay usuarios registrados' : 'No se encontraron resultados'; ?>
                                                    </p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($total_paginas > 1): ?>
                                <nav aria-label="Paginación" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($pagina > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>">
                                                    Anterior
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                                <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($pagina < $total_paginas): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?><?php echo !empty($buscar) ? '&buscar=' . urlencode($buscar) : ''; ?>">
                                                    Siguiente
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioLabel">
                        <i class="fas fa-user-plus text-primary"></i> 
                        <?php echo $usuario_editar ? 'Editar Usuario' : 'Nuevo Usuario'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formUsuario">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="<?php echo $usuario_editar ? 'actualizar' : 'crear'; ?>">
                        <?php if ($usuario_editar): ?>
                            <input type="hidden" name="id_usuario" value="<?php echo $usuario_editar['id_usuario']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['nombre']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['correo']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">Rol *</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar rol...</option>
                                    <option value="admin" <?php echo $usuario_editar && $usuario_editar['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    <option value="vendedor" <?php echo $usuario_editar && $usuario_editar['rol'] === 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                                </select>
                            </div>
                            <?php if (!$usuario_editar): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="contraseña" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="contraseña" name="contraseña" 
                                           minlength="8" required>
                                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $usuario_editar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="modalContraseña" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="cambiar_contraseña">
                        <input type="hidden" name="id_usuario" id="idUsuarioContraseña">
                        
                        <div class="mb-3">
                            <label for="nueva_contraseña" class="form-label">Nueva Contraseña *</label>
                            <input type="password" class="form-control" id="nueva_contraseña" name="nueva_contraseña" 
                                   minlength="8" required>
                            <small class="form-text text-muted">Mínimo 8 caracteres</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmar_contraseña" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirmar_contraseña" 
                                   minlength="8" required>
                            <small class="form-text text-muted">Debe coincidir con la nueva contraseña</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar al usuario <strong id="nombreUsuarioEliminar"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_usuario" id="idUsuarioEliminar">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para editar usuario
        function editarUsuario(id) {
            window.location.href = 'usuarios.php?editar=' + id;
        }

        // Función para cambiar contraseña
        function cambiarContraseña(id, nombre) {
            document.getElementById('idUsuarioContraseña').value = id;
            new bootstrap.Modal(document.getElementById('modalContraseña')).show();
        }

        // Función para eliminar usuario
        function eliminarUsuario(id, nombre) {
            document.getElementById('idUsuarioEliminar').value = id;
            document.getElementById('nombreUsuarioEliminar').textContent = nombre;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        // Limpiar modal al cerrar
        document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formUsuario').reset();
            window.location.href = 'usuarios.php';
        });

        // Validación del formulario de usuario
        document.getElementById('formUsuario').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const rol = document.getElementById('rol').value;
            const contraseña = document.getElementById('contraseña');
            
            if (!nombre || !correo || !rol) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios.');
                return false;
            }
            
            if (contraseña && contraseña.value.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                return false;
            }
        });

        // Validación del formulario de contraseña
        document.getElementById('modalContraseña').addEventListener('submit', function(e) {
            const nuevaContraseña = document.getElementById('nueva_contraseña').value;
            const confirmarContraseña = document.getElementById('confirmar_contraseña').value;
            
            if (nuevaContraseña.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                return false;
            }
            
            if (nuevaContraseña !== confirmarContraseña) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                return false;
            }
        });
    </script>
</body>
</html>
