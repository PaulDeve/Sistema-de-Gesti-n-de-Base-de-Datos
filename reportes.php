<?php
/**
 * Reportes y Estadísticas
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
require_once 'models/Producto.php';
require_once 'models/Cliente.php';
$venta = new Venta();
$producto = new Producto();
$cliente = new Cliente();

// Obtener parámetros de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Día actual
$tipo_reporte = $_GET['tipo'] ?? 'general';

// Obtener estadísticas según el tipo de reporte
switch ($tipo_reporte) {
    case 'ventas':
        $estadisticas_ventas = $venta->obtenerEstadisticas($fecha_inicio, $fecha_fin);
        $productos_mas_vendidos = $venta->productosMasVendidos(10);
        $ventas_por_fecha = $venta->leerTodas(100); // Para gráfico
        break;
        
    case 'inventario':
        $valor_total_inventario = $producto->valorInventario();
        $productos_stock_bajo = $producto->stockBajo(20);
        $total_productos = $producto->contar();
        break;
        
    case 'clientes':
        $total_clientes = $cliente->contar();
        $clientes_recientes = $cliente->leerTodos(10);
        break;
        
    default: // general
        $estadisticas_ventas = $venta->obtenerEstadisticas($fecha_inicio, $fecha_fin);
        $productos_mas_vendidos = $venta->productosMasVendidos(5);
        $valor_total_inventario = $producto->valorInventario();
        $productos_stock_bajo = $producto->stockBajo(10);
        $total_clientes = $cliente->contar();
        $total_productos = $producto->contar();
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-card.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .stats-card.warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }
        .stats-card.info {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
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
                        <a class="nav-link active" href="reportes.php">
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
                        <span class="navbar-brand mb-0 h1">Reportes y Estadísticas</span>
                        
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
                    <!-- Filtros de Fecha -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?php echo $fecha_inicio; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="<?php echo $fecha_fin; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="tipo" class="form-label">Tipo de Reporte</label>
                                    <select class="form-select" id="tipo" name="tipo">
                                        <option value="general" <?php echo $tipo_reporte === 'general' ? 'selected' : ''; ?>>General</option>
                                        <option value="ventas" <?php echo $tipo_reporte === 'ventas' ? 'selected' : ''; ?>>Ventas</option>
                                        <option value="inventario" <?php echo $tipo_reporte === 'inventario' ? 'selected' : ''; ?>>Inventario</option>
                                        <option value="clientes" <?php echo $tipo_reporte === 'clientes' ? 'selected' : ''; ?>>Clientes</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Navegación de Reportes -->
                    <ul class="nav nav-pills mb-4" id="reporteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-chart-pie"></i> General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ventas-tab" data-bs-toggle="pill" data-bs-target="#ventas" type="button" role="tab">
                                <i class="fas fa-chart-line"></i> Ventas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inventario-tab" data-bs-toggle="pill" data-bs-target="#inventario" type="button" role="tab">
                                <i class="fas fa-boxes"></i> Inventario
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="clientes-tab" data-bs-toggle="pill" data-bs-target="#clientes" type="button" role="tab">
                                <i class="fas fa-users"></i> Clientes
                            </button>
                        </li>
                    </ul>

                    <!-- Contenido de los Tabs -->
                    <div class="tab-content" id="reporteTabsContent">
                        <!-- Tab General -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <div class="card stats-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <h4><?php echo $total_clientes ?? 0; ?></h4>
                                            <p class="mb-0">Total Clientes</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card stats-card success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-box fa-2x mb-2"></i>
                                            <h4><?php echo $total_productos ?? 0; ?></h4>
                                            <p class="mb-0">Total Productos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card stats-card warning">
                                        <div class="card-body text-center">
                                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                            <h4><?php echo $estadisticas_ventas['total_ventas'] ?? 0; ?></h4>
                                            <p class="mb-0">Total Ventas</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card stats-card info">
                                        <div class="card-body text-center">
                                            <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                            <h4><?php echo formatearMoneda($valor_total_inventario ?? 0); ?></h4>
                                            <p class="mb-0">Valor Inventario</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Ventas del Período</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="ventasChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning"></i> Productos con Stock Bajo</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (isset($productos_stock_bajo) && $productos_stock_bajo->rowCount() > 0): ?>
                                                <div class="list-group list-group-flush">
                                                    <?php while ($prod = $productos_stock_bajo->fetch(PDO::FETCH_ASSOC)): ?>
                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-1"><?php echo htmlspecialchars($prod['nombre']); ?></h6>
                                                                <small class="text-muted">Stock: <?php echo $prod['stock']; ?></small>
                                                            </div>
                                                            <span class="badge bg-danger rounded-pill">Bajo</span>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                                    <p class="text-muted mb-0">Todo el stock está en buen nivel</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Ventas -->
                        <div class="tab-pane fade" id="ventas" role="tabpanel">
                            <?php if (isset($estadisticas_ventas)): ?>
                                <div class="row mb-4">
                                    <div class="col-md-3 mb-3">
                                        <div class="card stats-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                                <h4><?php echo $estadisticas_ventas['total_ventas'] ?? 0; ?></h4>
                                                <p class="mb-0">Total Ventas</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card stats-card success">
                                            <div class="card-body text-center">
                                                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                                <h4><?php echo formatearMoneda($estadisticas_ventas['total_ingresos'] ?? 0); ?></h4>
                                                <p class="mb-0">Total Ingresos</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card stats-card warning">
                                            <div class="card-body text-center">
                                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                                <h4><?php echo formatearMoneda($estadisticas_ventas['promedio_venta'] ?? 0); ?></h4>
                                                <p class="mb-0">Promedio por Venta</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card stats-card info">
                                            <div class="card-body text-center">
                                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                                <h4><?php echo $estadisticas_ventas['ventas_hoy'] ?? 0; ?></h4>
                                                <p class="mb-0">Ventas Hoy</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0"><i class="fas fa-chart-bar text-primary"></i> Productos Más Vendidos</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="chart-container">
                                                    <canvas id="productosVendidosChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="mb-0"><i class="fas fa-list text-info"></i> Top Productos</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (isset($productos_mas_vendidos) && $productos_mas_vendidos->rowCount() > 0): ?>
                                                    <div class="list-group list-group-flush">
                                                        <?php $contador = 1; ?>
                                                        <?php while ($prod = $productos_mas_vendidos->fetch(PDO::FETCH_ASSOC)): ?>
                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <span class="badge bg-primary me-2">#<?php echo $contador++; ?></span>
                                                                    <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="fw-bold"><?php echo $prod['total_vendido']; ?> uds</div>
                                                                    <small class="text-muted"><?php echo formatearMoneda($prod['total_ingresos']); ?></small>
                                                                </div>
                                                            </div>
                                                        <?php endwhile; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center py-4">
                                                        <i class="fas fa-chart-bar text-muted fa-2x mb-2"></i>
                                                        <p class="text-muted mb-0">No hay datos de productos vendidos</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Inventario -->
                        <div class="tab-pane fade" id="inventario" role="tabpanel">
                            <?php if (isset($valor_total_inventario)): ?>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="card stats-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                                <h4><?php echo formatearMoneda($valor_total_inventario); ?></h4>
                                                <p class="mb-0">Valor Total del Inventario</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card stats-card warning">
                                            <div class="card-body text-center">
                                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                <h4><?php echo $productos_stock_bajo->rowCount(); ?></h4>
                                                <p class="mb-0">Productos con Stock Bajo</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-boxes text-warning"></i> Productos con Stock Bajo</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($productos_stock_bajo->rowCount() > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Producto</th>
                                                            <th>Stock Actual</th>
                                                            <th>Precio Unitario</th>
                                                            <th>Valor en Stock</th>
                                                            <th>Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($prod = $productos_stock_bajo->fetch(PDO::FETCH_ASSOC)): ?>
                                                            <tr>
                                                                <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                                                                <td>
                                                                    <span class="badge bg-danger"><?php echo $prod['stock']; ?></span>
                                                                </td>
                                                                <td><?php echo formatearMoneda($prod['precio']); ?></td>
                                                                <td><?php echo formatearMoneda($prod['precio'] * $prod['stock']); ?></td>
                                                                <td>
                                                                    <?php if ($prod['stock'] <= 5): ?>
                                                                        <span class="badge bg-danger">Crítico</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-warning">Bajo</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                                <p class="text-muted mb-0">Todo el stock está en buen nivel</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Clientes -->
                        <div class="tab-pane fade" id="clientes" role="tabpanel">
                            <?php if (isset($total_clientes)): ?>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <div class="card stats-card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-users fa-2x mb-2"></i>
                                                <h4><?php echo $total_clientes; ?></h4>
                                                <p class="mb-0">Total de Clientes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card stats-card info">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                                <h4><?php echo date('Y'); ?></h4>
                                                <p class="mb-0">Año Actual</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-users text-info"></i> Clientes Recientes</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($clientes_recientes) && $clientes_recientes->rowCount() > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Cliente</th>
                                                            <th>Correo</th>
                                                            <th>Teléfono</th>
                                                            <th>Fecha Registro</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($cliente_row = $clientes_recientes->fetch(PDO::FETCH_ASSOC)): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($cliente_row['nombre'] . ' ' . $cliente_row['apellido']); ?></strong>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($cliente_row['correo']); ?></td>
                                                                <td><?php echo htmlspecialchars($cliente_row['telefono']); ?></td>
                                                                <td><?php echo formatearFecha($cliente_row['fecha_registro']); ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-users text-muted fa-2x mb-2"></i>
                                                <p class="text-muted mb-0">No hay clientes registrados</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de Ventas
        const ventasCtx = document.getElementById('ventasChart');
        if (ventasCtx) {
            new Chart(ventasCtx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Ventas Mensuales',
                        data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 32000, 35000, 40000, 38000, 45000],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de Productos Más Vendidos
        const productosCtx = document.getElementById('productosVendidosChart');
        if (productosCtx) {
            new Chart(productosCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Producto A', 'Producto B', 'Producto C', 'Producto D', 'Producto E'],
                    datasets: [{
                        data: [30, 25, 20, 15, 10],
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Cambiar tab activo según el tipo de reporte
        const tipoReporte = '<?php echo $tipo_reporte; ?>';
        if (tipoReporte !== 'general') {
            const tab = document.getElementById(tipoReporte + '-tab');
            const content = document.getElementById(tipoReporte);
            if (tab && content) {
                // Remover active de todos los tabs
                document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(t => t.classList.remove('show', 'active'));
                
                // Activar el tab correspondiente
                tab.classList.add('active');
                content.classList.add('show', 'active');
            }
        }
    </script>
</body>
</html>
