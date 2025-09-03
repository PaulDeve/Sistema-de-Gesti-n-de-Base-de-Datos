<?php
/**
 * Gestión de Productos
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar autenticación
if (!estaAutenticado()) {
    redirigir('login.php');
}

// Incluir modelo de productos
require_once 'models/Producto.php';
$producto = new Producto();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                // Crear nuevo producto
                $producto->nombre = limpiarEntrada($_POST['nombre']);
                $producto->descripcion = limpiarEntrada($_POST['descripcion']);
                $producto->precio = floatval($_POST['precio']);
                $producto->stock = intval($_POST['stock']);
                
                if ($producto->crear()) {
                    $mensaje = 'Producto creado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Crear producto', "Producto: {$producto->nombre}");
                } else {
                    $mensaje = 'Error al crear el producto';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'actualizar':
                // Actualizar producto existente
                $producto->id_producto = $_POST['id_producto'];
                $producto->nombre = limpiarEntrada($_POST['nombre']);
                $producto->descripcion = limpiarEntrada($_POST['descripcion']);
                $producto->precio = floatval($_POST['precio']);
                $producto->stock = intval($_POST['stock']);
                
                if ($producto->actualizar()) {
                    $mensaje = 'Producto actualizado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Actualizar producto', "Producto: {$producto->nombre}");
                } else {
                    $mensaje = 'Error al actualizar el producto';
                    $tipo_mensaje = 'error';
                }
                break;
                
            case 'eliminar':
                // Eliminar producto
                $producto->id_producto = $_POST['id_producto'];
                if ($producto->eliminar()) {
                    $mensaje = 'Producto eliminado exitosamente';
                    $tipo_mensaje = 'exito';
                    registrarActividad('Eliminar producto', "ID: {$producto->id_producto}");
                } else {
                    $mensaje = 'Error al eliminar el producto';
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

// Obtener productos
if (!empty($buscar)) {
    $resultado = $producto->buscar($buscar);
    $total_registros = $resultado->rowCount();
} else {
    $resultado = $producto->leerTodos($registros_por_pagina, ($pagina - 1) * $registros_por_pagina);
    $total_registros = $producto->contar();
}

$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $producto->id_producto = $_GET['editar'];
    $producto_editar = $producto->leerUno();
}

// Obtener estadísticas de inventario
$valor_total_inventario = $producto->valorInventario();
$productos_stock_bajo = $producto->stockBajo(10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Sistema de Gestión de Ventas</title>
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
        .stock-bajo {
            color: #dc3545;
            font-weight: bold;
        }
        .stock-medio {
            color: #ffc107;
            font-weight: bold;
        }
        .stock-alto {
            color: #28a745;
            font-weight: bold;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
                        <a class="nav-link active" href="productos.php">
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
                        <span class="navbar-brand mb-0 h1">Gestión de Productos</span>
                        
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

                    <!-- Estadísticas de Inventario -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-dollar-sign fa-2x"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo formatearMoneda($valor_total_inventario); ?></h4>
                                            <p class="mb-0">Valor Total del Inventario</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0"><?php echo $productos_stock_bajo->rowCount(); ?></h4>
                                            <p class="mb-0">Productos con Stock Bajo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Header con búsqueda y botón nuevo -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2><i class="fas fa-box text-primary"></i> Productos</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </button>
                        </div>
                    </div>

                    <!-- Búsqueda -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="buscar" class="form-control me-2" 
                                       placeholder="Buscar por nombre o descripción..." 
                                       value="<?php echo htmlspecialchars($buscar); ?>">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="productos.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Limpiar
                            </a>
                        </div>
                    </div>

                    <!-- Tabla de productos -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                            <th>Estado Stock</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($resultado && $resultado->rowCount() > 0): ?>
                                            <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                                                <tr>
                                                    <td><?php echo $row['id_producto']; ?></td>
                                                    <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars(substr($row['descripcion'], 0, 50)) . (strlen($row['descripcion']) > 50 ? '...' : ''); ?></td>
                                                    <td><span class="badge bg-success"><?php echo formatearMoneda($row['precio']); ?></span></td>
                                                    <td>
                                                        <?php 
                                                        $stock = $row['stock'];
                                                        if ($stock <= 5) {
                                                            echo '<span class="stock-bajo">' . $stock . '</span>';
                                                        } elseif ($stock <= 15) {
                                                            echo '<span class="stock-medio">' . $stock . '</span>';
                                                        } else {
                                                            echo '<span class="stock-alto">' . $stock . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if ($stock <= 5) {
                                                            echo '<span class="badge bg-danger">Crítico</span>';
                                                        } elseif ($stock <= 15) {
                                                            echo '<span class="badge bg-warning">Bajo</span>';
                                                        } else {
                                                            echo '<span class="badge bg-success">Normal</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo formatearFecha($row['fecha_creacion']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                                onclick="editarProducto(<?php echo $row['id_producto']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                                                onclick="eliminarProducto(<?php echo $row['id_producto']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-box text-muted fa-2x mb-2"></i>
                                                    <p class="text-muted mb-0">
                                                        <?php echo empty($buscar) ? 'No hay productos registrados' : 'No se encontraron resultados'; ?>
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

    <!-- Modal Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductoLabel">
                        <i class="fas fa-box-open text-primary"></i> 
                        <?php echo $producto_editar ? 'Editar Producto' : 'Nuevo Producto'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formProducto">
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'actualizar' : 'crear'; ?>">
                        <?php if ($producto_editar): ?>
                            <input type="hidden" name="id_producto" value="<?php echo $producto_editar['id_producto']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['nombre']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="precio" name="precio" 
                                           step="0.01" min="0" 
                                           value="<?php echo $producto_editar ? $producto_editar['precio'] : ''; ?>" 
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock Inicial *</label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       min="0" 
                                       value="<?php echo $producto_editar ? $producto_editar['stock'] : '0'; ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $producto_editar ? 'Actualizar' : 'Guardar'; ?>
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
                    <p>¿Está seguro de que desea eliminar el producto <strong id="nombreProductoEliminar"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id_producto" id="idProductoEliminar">
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
        // Función para editar producto
        function editarProducto(id) {
            window.location.href = 'productos.php?editar=' + id;
        }

        // Función para eliminar producto
        function eliminarProducto(id, nombre) {
            document.getElementById('idProductoEliminar').value = id;
            document.getElementById('nombreProductoEliminar').textContent = nombre;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        // Limpiar modal al cerrar
        document.getElementById('modalProducto').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formProducto').reset();
            window.location.href = 'productos.php';
        });

        // Validación del formulario
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const precio = parseFloat(document.getElementById('precio').value);
            const stock = parseInt(document.getElementById('stock').value);
            
            if (!nombre || precio < 0 || stock < 0) {
                e.preventDefault();
                alert('Por favor complete todos los campos correctamente.');
                return false;
            }
        });
    </script>
</body>
</html>
