<?php
/**
 * Gestión de Ventas
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar autenticación
if (!estaAutenticado()) {
    redirigir('login.php');
}

// Incluir modelos
require_once 'models/Venta.php';
require_once 'models/Cliente.php';
require_once 'models/Producto.php';
$venta = new Venta();
$cliente = new Cliente();
$producto = new Producto();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear_venta':
                // Crear nueva venta
                $venta->id_cliente = $_POST['id_cliente'];
                $venta->total = 0;
                $venta->id_usuario = $_SESSION['usuario_id'];
                
                // Procesar detalles de la venta
                $detalles = [];
                $total_venta = 0;
                
                foreach ($_POST['productos'] as $index => $id_producto) {
                    if (!empty($id_producto) && !empty($_POST['cantidades'][$index])) {
                        $cantidad = intval($_POST['cantidades'][$index]);
                        $precio_unitario = floatval($_POST['precios'][$index]);
                        $subtotal = $cantidad * $precio_unitario;
                        
                        $detalles[] = [
                            'id_producto' => $id_producto,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio_unitario,
                            'subtotal' => $subtotal
                        ];
                        
                        $total_venta += $subtotal;
                    }
                }
                
                if (empty($detalles)) {
                    $mensaje = 'Debe agregar al menos un producto a la venta';
                    $tipo_mensaje = 'error';
                } else {
                    $venta->total = $total_venta;
                    $venta->detalles = $detalles;
                    
                    if ($venta->crear()) {
                        $mensaje = 'Venta registrada exitosamente';
                        $tipo_mensaje = 'exito';
                        registrarActividad('Crear venta', "Venta ID: {$venta->id_venta}, Total: " . formatearMoneda($total_venta));
                    } else {
                        $mensaje = 'Error al registrar la venta';
                        $tipo_mensaje = 'error';
                    }
                }
                break;
        }
    }
}

// Obtener parámetros
$accion = $_GET['accion'] ?? 'listar';
$pagina = max(1, intval($_GET['pagina'] ?? 1));
$registros_por_pagina = 10;

// Obtener datos según la acción
if ($accion === 'nueva') {
    // Para nueva venta
    $clientes = $cliente->leerTodos();
    $productos = $producto->leerTodos();
} else {
    // Para listar ventas
    $resultado = $venta->leerTodas($registros_por_pagina, ($pagina - 1) * $registros_por_pagina);
    $total_registros = $venta->contar();
    $total_paginas = ceil($total_registros / $registros_por_pagina);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - Sistema de Gestión de Ventas</title>
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
        .producto-row {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .total-venta {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
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
                        <a class="nav-link active" href="ventas.php">
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
                        <span class="navbar-brand mb-0 h1">Gestión de Ventas</span>
                        
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

                    <!-- Header con navegación -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2><i class="fas fa-shopping-cart text-primary"></i> Ventas</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if ($accion === 'listar'): ?>
                                <a href="ventas.php?accion=nueva" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nueva Venta
                                </a>
                            <?php else: ?>
                                <a href="ventas.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-list"></i> Ver Ventas
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($accion === 'nueva'): ?>
                        <!-- Formulario de Nueva Venta -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-cart-plus text-success"></i> Nueva Venta</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formVenta">
                                    <input type="hidden" name="accion" value="crear_venta">
                                    
                                    <!-- Selección de Cliente -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="id_cliente" class="form-label">Cliente *</label>
                                            <select class="form-select" id="id_cliente" name="id_cliente" required>
                                                <option value="">Seleccionar cliente...</option>
                                                <?php while ($cliente_row = $clientes->fetch(PDO::FETCH_ASSOC)): ?>
                                                    <option value="<?php echo $cliente_row['id_cliente']; ?>">
                                                        <?php echo htmlspecialchars($cliente_row['nombre'] . ' ' . $cliente_row['apellido']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha de Venta</label>
                                            <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i'); ?>" readonly>
                                        </div>
                                    </div>

                                    <!-- Productos -->
                                    <div class="mb-4">
                                        <h6><i class="fas fa-box text-info"></i> Productos de la Venta</h6>
                                        <div id="productos-container">
                                            <div class="producto-row">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Producto</label>
                                                        <select class="form-select producto-select" name="productos[]" onchange="actualizarPrecio(this)">
                                                            <option value="">Seleccionar producto...</option>
                                                            <?php 
                                                            $productos->execute(); // Resetear el cursor
                                                            while ($producto_row = $productos->fetch(PDO::FETCH_ASSOC)): 
                                                            ?>
                                                                <option value="<?php echo $producto_row['id_producto']; ?>" 
                                                                        data-precio="<?php echo $producto_row['precio']; ?>"
                                                                        data-stock="<?php echo $producto_row['stock']; ?>">
                                                                    <?php echo htmlspecialchars($producto_row['nombre']); ?> 
                                                                    (Stock: <?php echo $producto_row['stock']; ?>)
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Precio</label>
                                                        <input type="number" class="form-control precio-input" name="precios[]" 
                                                               step="0.01" min="0" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Cantidad</label>
                                                        <input type="number" class="form-control cantidad-input" name="cantidades[]" 
                                                               min="1" value="1" onchange="calcularSubtotal(this)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Subtotal</label>
                                                        <input type="text" class="form-control subtotal-input" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarProducto(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-outline-primary" onclick="agregarProducto()">
                                                <i class="fas fa-plus"></i> Agregar Producto
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Total -->
                                    <div class="row">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="mb-2">Total de la Venta</h6>
                                                    <div class="total-venta" id="total-venta">$0.00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-save"></i> Registrar Venta
                                        </button>
                                        <a href="ventas.php" class="btn btn-secondary btn-lg ms-2">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Lista de Ventas -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID Venta</th>
                                                <th>Cliente</th>
                                                <th>Total</th>
                                                <th>Usuario</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($resultado && $resultado->rowCount() > 0): ?>
                                                <?php while ($row = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                                                    <tr>
                                                        <td><strong>#<?php echo $row['id_venta']; ?></strong></td>
                                                        <td><?php echo htmlspecialchars($row['nombre_cliente'] . ' ' . $row['apellido_cliente']); ?></td>
                                                        <td><span class="badge bg-success"><?php echo formatearMoneda($row['total']); ?></span></td>
                                                        <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                                                        <td><?php echo formatearFecha($row['fecha_venta']); ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-info btn-action" 
                                                                    onclick="verDetalleVenta(<?php echo $row['id_venta']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <i class="fas fa-shopping-cart text-muted fa-2x mb-2"></i>
                                                        <p class="text-muted mb-0">No hay ventas registradas</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Paginación -->
                                <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                                    <nav aria-label="Paginación" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($pagina > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>">
                                                        Anterior
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                                <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?pagina=<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($pagina < $total_paginas): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>">
                                                        Siguiente
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Venta -->
    <div class="modal fade" id="modalDetalleVenta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye text-info"></i> Detalle de Venta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalle-venta-content">
                    <!-- Contenido del detalle -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let contadorProductos = 1;

        // Función para agregar producto
        function agregarProducto() {
            contadorProductos++;
            const container = document.getElementById('productos-container');
            const nuevoProducto = document.createElement('div');
            nuevoProducto.className = 'producto-row';
            nuevoProducto.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Producto</label>
                        <select class="form-select producto-select" name="productos[]" onchange="actualizarPrecio(this)">
                            <option value="">Seleccionar producto...</option>
                            ${document.querySelector('.producto-select').innerHTML}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio</label>
                        <input type="number" class="form-control precio-input" name="precios[]" step="0.01" min="0" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control cantidad-input" name="cantidades[]" min="1" value="1" onchange="calcularSubtotal(this)">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control subtotal-input" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(nuevoProducto);
        }

        // Función para eliminar producto
        function eliminarProducto(button) {
            if (document.querySelectorAll('.producto-row').length > 1) {
                button.closest('.producto-row').remove();
                calcularTotal();
            }
        }

        // Función para actualizar precio
        function actualizarPrecio(select) {
            const row = select.closest('.producto-row');
            const precioInput = row.querySelector('.precio-input');
            const cantidadInput = row.querySelector('.cantidad-input');
            const subtotalInput = row.querySelector('.subtotal-input');
            
            const option = select.options[select.selectedIndex];
            if (option.value) {
                precioInput.value = option.dataset.precio;
                calcularSubtotal(cantidadInput);
            } else {
                precioInput.value = '';
                subtotalInput.value = '';
            }
        }

        // Función para calcular subtotal
        function calcularSubtotal(input) {
            const row = input.closest('.producto-row');
            const precioInput = row.querySelector('.precio-input');
            const subtotalInput = row.querySelector('.subtotal-input');
            
            if (precioInput.value && input.value) {
                const subtotal = parseFloat(precioInput.value) * parseInt(input.value);
                subtotalInput.value = formatearMoneda(subtotal);
                calcularTotal();
            }
        }

        // Función para calcular total
        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal-input').forEach(input => {
                if (input.value) {
                    const valor = parseFloat(input.value.replace(/[$,]/g, ''));
                    if (!isNaN(valor)) {
                        total += valor;
                    }
                }
            });
            document.getElementById('total-venta').textContent = formatearMoneda(total);
        }

        // Función para formatear moneda
        function formatearMoneda(cantidad) {
            return '$' + parseFloat(cantidad).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Función para ver detalle de venta
        function verDetalleVenta(id) {
            // Aquí podrías hacer una llamada AJAX para obtener los detalles
            // Por ahora solo abrimos el modal
            new bootstrap.Modal(document.getElementById('modalDetalleVenta')).show();
        }

        // Validación del formulario
        document.getElementById('formVenta').addEventListener('submit', function(e) {
            const cliente = document.getElementById('id_cliente').value;
            const productos = document.querySelectorAll('.producto-select');
            let productosValidos = 0;
            
            productos.forEach(select => {
                if (select.value) productosValidos++;
            });
            
            if (!cliente) {
                e.preventDefault();
                alert('Debe seleccionar un cliente.');
                return false;
            }
            
            if (productosValidos === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto a la venta.');
                return false;
            }
        });
    </script>
</body>
</html>
