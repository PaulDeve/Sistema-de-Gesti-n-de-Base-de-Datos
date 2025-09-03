<?php
/**
 * Dashboard Principal
 * Sistema de Gestión de Ventas
 */

require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar autenticación
if (!estaAutenticado()) {
    redirigir('login.php');
}

// Obtener estadísticas del dashboard
try {
    $estadisticas = obtenerEstadisticasDashboard();
} catch (Exception $e) {
    // Si hay error, crear estadísticas por defecto
    $estadisticas = array(
        'total_clientes' => 0,
        'total_productos' => 0,
        'total_ventas' => 0,
        'valor_inventario' => 0,
        'productos_stock_bajo' => 0,
        'estadisticas_ventas' => array()
    );
}

// Obtener productos con stock bajo
try {
    require_once 'models/Producto.php';
    $producto = new Producto();
    $productos_stock_bajo = $producto->stockBajo(5);
} catch (Exception $e) {
    $productos_stock_bajo = null;
}

// Obtener ventas recientes
try {
    require_once 'models/Venta.php';
    $venta = new Venta();
    $ventas_recientes = $venta->leerTodas(5);
    $productos_mas_vendidos = $venta->productosMasVendidos(5);
} catch (Exception $e) {
    $ventas_recientes = null;
    $productos_mas_vendidos = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestión de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
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
        .card-stats {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .card-stats .card-body {
            padding: 1.5rem;
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
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
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .chart-container {
            position: relative;
            height: 300px;
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
                        <a class="nav-link active" href="dashboard.php">
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
                        <span class="navbar-brand mb-0 h1">Dashboard</span>
                        
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo $_SESSION['usuario_nombre']; ?></div>
                                <small class="text-muted"><?php echo ucfirst($_SESSION['usuario_rol']); ?></small>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="p-4">
                    <!-- Welcome Message -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                <h5 class="mb-1">
                                    <i class="fas fa-sun"></i> ¡Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?>!
                                </h5>
                                <p class="mb-0">Aquí tienes un resumen de las actividades de tu negocio.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon me-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <p class="stats-number text-success"><?php echo $estadisticas['total_clientes']; ?></p>
                                            <p class="stats-label">Total Clientes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon me-3" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div>
                                            <p class="stats-number text-primary"><?php echo $estadisticas['total_productos']; ?></p>
                                            <p class="stats-label">Total Productos</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon me-3" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div>
                                            <p class="stats-number text-warning"><?php echo $estadisticas['total_ventas']; ?></p>
                                            <p class="stats-label">Total Ventas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon me-3" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div>
                                            <p class="stats-number text-purple"><?php echo formatearMoneda($estadisticas['valor_inventario']); ?></p>
                                            <p class="stats-label">Valor Inventario</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Tables Row -->
                    <div class="row">
                        <!-- Sales Chart -->
                        <div class="col-lg-8 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-line text-primary"></i> Ventas del Mes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Alerts -->
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle text-warning"></i> Alertas de Stock
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if ($productos_stock_bajo && $productos_stock_bajo->rowCount() > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php while ($producto_stock = $productos_stock_bajo->fetch(PDO::FETCH_ASSOC)): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $producto_stock['nombre']; ?></h6>
                                                        <small class="text-muted">Stock actual: <?php echo $producto_stock['stock']; ?></small>
                                                    </div>
                                                    <span class="badge bg-warning rounded-pill">Bajo</span>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php elseif ($productos_stock_bajo === null): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                                            <p class="text-muted mb-0">Error al cargar datos de stock</p>
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

                    <!-- Recent Sales and Top Products -->
                    <div class="row">
                        <!-- Recent Sales -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock text-info"></i> Ventas Recientes
                                    </h5>
                                    <a href="ventas.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                                </div>
                                <div class="card-body p-0">
                                    <?php if ($ventas_recientes && $ventas_recientes->rowCount() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Cliente</th>
                                                        <th>Total</th>
                                                        <th>Fecha</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($venta_reciente = $ventas_recientes->fetch(PDO::FETCH_ASSOC)): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo $venta_reciente['nombre_cliente'] . ' ' . $venta_reciente['apellido_cliente']; ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success"><?php echo formatearMoneda($venta_reciente['total']); ?></span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"><?php echo formatearFecha($venta_reciente['fecha_venta']); ?></small>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php elseif ($ventas_recientes === null): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                                            <p class="text-muted mb-0">Error al cargar datos de ventas</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-shopping-cart text-muted fa-2x mb-2"></i>
                                            <p class="text-muted mb-0">No hay ventas recientes</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Top Products -->
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-star text-warning"></i> Productos Más Vendidos
                                    </h5>
                                    <a href="reportes.php" class="btn btn-sm btn-outline-primary">Ver Reporte</a>
                                </div>
                                <div class="card-body p-0">
                                    <?php if ($productos_mas_vendidos && $productos_mas_vendidos->rowCount() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Cantidad</th>
                                                        <th>Ingresos</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($producto_top = $productos_mas_vendidos->fetch(PDO::FETCH_ASSOC)): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php echo $producto_top['nombre']; ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-info"><?php echo $producto_top['total_vendido']; ?></span>
                                                            </td>
                                                            <td>
                                                                <span class="text-success fw-bold"><?php echo formatearMoneda($producto_top['total_ingresos']); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php elseif ($productos_mas_vendidos === null): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                                            <p class="text-muted mb-0">Error al cargar datos de productos</p>
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

                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt text-warning"></i> Acciones Rápidas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <a href="ventas.php?accion=nueva" class="btn btn-primary w-100 py-3">
                                                <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                                Nueva Venta
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="clientes.php?accion=nuevo" class="btn btn-success w-100 py-3">
                                                <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                                Nuevo Cliente
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="productos.php?accion=nuevo" class="btn btn-info w-100 py-3">
                                                <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                                Nuevo Producto
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="reportes.php" class="btn btn-warning w-100 py-3">
                                                <i class="fas fa-chart-pie fa-2x mb-2"></i><br>
                                                Ver Reportes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
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
    </script>
</body>
</html>
