<?php
/**
 * Modelo de Venta
 * Sistema de Gestión de Ventas
 */

require_once 'config/database.php';
require_once 'models/Producto.php';

class Venta {
    private $conn;
    private $table_name = "ventas";
    private $table_detalle = "detalle_ventas";
    
    public $id_venta;
    public $id_cliente;
    public $fecha_venta;
    public $total;
    public $id_usuario;
    public $detalles = array();
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Crear nueva venta con transacción
    public function crear() {
        try {
            $this->conn->beginTransaction();
            
            // Insertar venta principal
            $query = "INSERT INTO " . $this->table_name . " 
                      (id_cliente, total, id_usuario) 
                      VALUES (:id_cliente, :total, :id_usuario)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_cliente", $this->id_cliente);
            $stmt->bindParam(":total", $this->total);
            $stmt->bindParam(":id_usuario", $this->id_usuario);
            
            if(!$stmt->execute()) {
                throw new Exception("Error al crear la venta");
            }
            
            $this->id_venta = $this->conn->lastInsertId();
            
            // Insertar detalles de la venta
            if(!$this->insertarDetalles()) {
                throw new Exception("Error al insertar detalles de la venta");
            }
            
            // Actualizar stock de productos
            if(!$this->actualizarStockProductos()) {
                throw new Exception("Error al actualizar stock de productos");
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en transacción de venta: " . $e->getMessage());
            return false;
        }
    }
    
    // Insertar detalles de la venta
    private function insertarDetalles() {
        foreach($this->detalles as $detalle) {
            $query = "INSERT INTO " . $this->table_detalle . " 
                      (id_venta, id_producto, cantidad, precio_unitario, subtotal) 
                      VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_venta", $this->id_venta);
            $stmt->bindParam(":id_producto", $detalle['id_producto']);
            $stmt->bindParam(":cantidad", $detalle['cantidad']);
            $stmt->bindParam(":precio_unitario", $detalle['precio_unitario']);
            $stmt->bindParam(":subtotal", $detalle['subtotal']);
            
            if(!$stmt->execute()) {
                return false;
            }
        }
        return true;
    }
    
    // Actualizar stock de productos
    private function actualizarStockProductos() {
        $producto = new Producto();
        
        foreach($this->detalles as $detalle) {
            $producto->id_producto = $detalle['id_producto'];
            
            // Verificar stock disponible
            if(!$producto->verificarStock($detalle['cantidad'])) {
                return false;
            }
            
            // Actualizar stock
            if(!$producto->actualizarStock($detalle['cantidad'])) {
                return false;
            }
        }
        return true;
    }
    
    // Leer todas las ventas
    public function leerTodas($limit = 100) {
        $query = "SELECT v.*, c.nombre as nombre_cliente, c.apellido as apellido_cliente, 
                         u.nombre as nombre_usuario
                  FROM " . $this->table_name . " v
                  JOIN clientes c ON v.id_cliente = c.id_cliente
                  JOIN usuarios u ON v.id_usuario = u.id_usuario
                  ORDER BY v.fecha_venta DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Leer una venta por ID con detalles
    public function leerUna() {
        // Obtener datos de la venta
        $query = "SELECT v.*, c.nombre as nombre_cliente, c.apellido as apellido_cliente,
                         u.nombre as nombre_usuario
                  FROM " . $this->table_name . " v
                  JOIN clientes c ON v.id_cliente = c.id_cliente
                  JOIN usuarios u ON v.id_usuario = u.id_usuario
                  WHERE v.id_venta = :id_venta";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_venta", $this->id_venta);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->id_cliente = $row['id_cliente'];
            $this->fecha_venta = $row['fecha_venta'];
            $this->total = $row['total'];
            $this->id_usuario = $row['id_usuario'];
            
            // Obtener detalles
            $this->detalles = $this->obtenerDetalles();
            return true;
        }
        return false;
    }
    
    // Obtener detalles de una venta
    private function obtenerDetalles() {
        $query = "SELECT dv.*, p.nombre as nombre_producto
                  FROM " . $this->table_detalle . " dv
                  JOIN productos p ON dv.id_producto = p.id_producto
                  WHERE dv.id_venta = :id_venta";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_venta", $this->id_venta);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar ventas por cliente
    public function buscarPorCliente($id_cliente) {
        $query = "SELECT v.*, c.nombre as nombre_cliente, c.apellido as apellido_cliente
                  FROM " . $this->table_name . " v
                  JOIN clientes c ON v.id_cliente = c.id_cliente
                  WHERE v.id_cliente = :id_cliente
                  ORDER BY v.fecha_venta DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cliente", $id_cliente);
        $stmt->execute();
        return $stmt;
    }
    
    // Buscar ventas por fecha
    public function buscarPorFecha($fecha_inicio, $fecha_fin) {
        $query = "SELECT v.*, c.nombre as nombre_cliente, c.apellido as apellido_cliente
                  FROM " . $this->table_name . " v
                  JOIN clientes c ON v.id_cliente = c.id_cliente
                  WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY v.fecha_venta DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener estadísticas de ventas
    public function obtenerEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        $where_clause = "";
        $params = array();
        
        if($fecha_inicio && $fecha_fin) {
            $where_clause = "WHERE DATE(fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin";
            $params[':fecha_inicio'] = $fecha_inicio;
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $query = "SELECT 
                      COUNT(*) as total_ventas,
                      SUM(total) as total_ingresos,
                      AVG(total) as promedio_venta,
                      MIN(total) as venta_minima,
                      MAX(total) as venta_maxima
                  FROM " . $this->table_name . " " . $where_clause;
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener productos más vendidos
    public function productosMasVendidos($limit = 10) {
        $query = "SELECT p.nombre, SUM(dv.cantidad) as total_vendido, 
                         SUM(dv.subtotal) as total_ingresos
                  FROM " . $this->table_detalle . " dv
                  JOIN productos p ON dv.id_producto = p.id_producto
                  JOIN " . $this->table_name . " v ON dv.id_venta = v.id_venta
                  GROUP BY dv.id_producto, p.nombre
                  ORDER BY total_vendido DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Contar total de ventas
    public function contar() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Calcular total de la venta
    public function calcularTotal() {
        $this->total = 0;
        foreach($this->detalles as $detalle) {
            $this->total += $detalle['subtotal'];
        }
        return $this->total;
    }
}
?>
