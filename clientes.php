<?php
/**
 * Gestión de Clientes
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar autenticación
if (!estaAutenticado()) {
    redirigir('login.php');
}

// Incluir modelo de clientes
require_once 'models/Cliente.php';
$cliente = new Cliente();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nuevo cliente
                $cliente->nombre = limpiarEntrada($_POST['nombre']);
                $cliente->apellido = limpiarEntrada($_POST['apellido']);
                $cliente->correo = limpiarEntrada($_POST['correo']);
                $cliente->telefono = limpiarEntrada($_POST['telefono']);
                $cliente->direccion = limpiarEntrada($_POST['direccion']);
                
                if ($cliente->crear()) {
                    $mensaje = 'Cliente creado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Crear cliente', "Cliente: {$cliente->nombre} {$cliente->apellido}");
                } else {
                    $mensaje = 'Error al crear el cliente';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'actualizar':
                // Actualizar cliente existente
                $cliente->id_cliente = $_POST['id_cliente'];
                $cliente->nombre = limpiarEntrada($_POST['nombre']);
                $cliente->apellido = limpiarEntrada($_POST['apellido']);
                $cliente->correo = limpiarEntrada($_POST['correo']);
                $cliente->telefono = limpiarEntrada($_POST['telefono']);
                $cliente->direccion = limpiarEntrada($_POST['direccion']);
                
                if ($cliente->actualizar()) {
                    $mensaje = 'Cliente actualizado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Actualizar cliente', "Cliente: {$cliente->nombre} {$cliente->apellido}");
                } else {
                    $mensaje = 'Error al actualizar el cliente';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar cliente
                $cliente->id_cliente = $_POST['id_cliente'];
                if ($cliente->eliminar()) {
                    $mensaje = 'Cliente eliminado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Eliminar cliente', "ID: {$cliente->id_cliente}");
                } else {
                    $mensaje = 'Error al eliminar el cliente';
                    $tipo_mensaje = 'error';
                }
                break;
        }
    }
}

// Obtener parámetros de búsqueda y paginación
$buscar = $_GET['buscar'] ?? '';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$registros_por_pagina = 10;

// Obtener clientes
if (!empty($buscar)) {
    $resultado = $cliente->buscar($buscar);
    $total_registros = $resultado->rowCount();
} else {
    $resultado = $cliente->leerTodos($registros_por_pagina, ($pagina - 1) * $registros_por_pagina);
    $total_registros = $cliente->contar();
}

$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener cliente para editar
$cliente_editar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $cliente->id_cliente = $_GET['editar'];
    $cliente_editar = $cliente->leerUno();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Sistema de Gestión de Ventas</title>
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
                        <a class="nav-link active" href="clientes.php">
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
                        <?php if (tienePermiso('admin')): ?>
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-user-cog"></i> Usuarios
                            </a>
                        <?php endif; ?>
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
                        <span class="navbar-brand mb-0 h1">Gestión de Clientes</span>
                        
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
                            <h2><i class="fas fa-users text-primary"></i> Clientes</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente">
                                <i class="fas fa-plus"></i> Nuevo Cliente
                            </button>
                        </div>
                    </div>

                    <!-- Búsqueda -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="buscar" class="form-control me-2" 
                                       placeholder="Buscar por nombre, apellido o correo..." 
                                       value="<?php echo htmlspecialchars($buscar); ?>">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="clientes.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Limpiar
                            </a>
                        </div>
                    </div>

                    <!-- Tabla de clientes -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Apellido</th>
                                            <th>Correo</th>
                                            <th>Teléfono</th>
                                            <th>Dirección</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($resultado && $resultado->rowCount() > 0): ?>
                                            <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                                                <tr>
                                                    <td><?php echo $row['id_cliente']; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                                                    <td><?php echo formatearFecha($row['fecha_registro']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                                onclick="editarCliente(<?php echo $row['id_cliente']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                                onclick="eliminarCliente(<?php echo $row['id_cliente']; ?>, '<?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-users text-muted fa-2x mb-2"></i>
                                                    <p class="text-muted mb-0">
                                                        <?php echo empty($buscar) ? 'No hay clientes registrados' : 'No se encontraron resultados'; ?>
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

    <!-- Modal Cliente -->
    <div class="modal fade" id="modalCliente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClienteLabel">
                        <i class="fas fa-user-plus text-primary"></i> 
                        <?php echo $cliente_editar ? 'Editar Cliente' : 'Nuevo Cliente'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formCliente">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="<?php echo $cliente_editar ? 'actualizar' : 'crear'; ?>">
                        <?php if ($cliente_editar): ?>
                            <input type="hidden" name="id_cliente" value="<?php echo $cliente_editar['id_cliente']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['nombre']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                       value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['apellido']) : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['correo']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo $cliente_editar ? htmlspecialchars($cliente_editar['telefono']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo $cliente_editar ? htmlspecialchars($cliente_editar['direccion']) : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $cliente_editar ? 'Actualizar' : 'Guardar'; ?>
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
                    <p>¿Está seguro de que desea eliminar al cliente <strong id="nombreClienteEliminar"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_cliente" id="idClienteEliminar">
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
        // Función para editar cliente
        function editarCliente(id) {
            window.location.href = 'clientes.php?editar=' + id;
        }

        // Función para eliminar cliente
        function eliminarCliente(id, nombre) {
            document.getElementById('idClienteEliminar').value = id;
            document.getElementById('nombreClienteEliminar').textContent = nombre;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        // Limpiar modal al cerrar
        document.getElementById('modalCliente').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formCliente').reset();
            window.location.href = 'clientes.php';
        });

        // Validación del formulario
        document.getElementById('formCliente').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const correo = document.getElementById('correo').value.trim();
            
            if (!nombre || !apellido || !correo) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios.');
                return false;
            }
            
            if (!correo.includes('@')) {
                e.preventDefault();
                alert('Por favor ingrese un correo electrónico válido.');
                return false;
            }
        });
    </script>
</body>
</html>
