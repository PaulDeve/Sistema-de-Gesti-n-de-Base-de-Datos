<?php
/**
 * Modelo de Producto
 * Sistema de Gestión de Ventas
 */

require_once 'config/database.php';

class Producto {
    private $conn;
    private $table_name = "productos";
    
    public $id_producto;
    public $nombre;
    public $descripcion;
    public $precio;
    public $stock;
    public $fecha_creacion;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Crear nuevo producto
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, precio, stock) 
                  VALUES (:nombre, :descripcion, :precio, :stock)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = floatval($this->precio);
        $this->stock = intval($this->stock);
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":stock", $this->stock);
        
        if($stmt->execute()) {
            $this->id_producto = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Leer todos los productos
    public function leerTodos($limit = 100) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Leer un producto por ID
    public function leerUno() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_producto = :id_producto LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_producto", $this->id_producto);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->precio = $row['precio'];
            $this->stock = $row['stock'];
            $this->fecha_creacion = $row['fecha_creacion'];
            return true;
        }
        return false;
    }
    
    // Actualizar producto
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, descripcion = :descripcion, 
                      precio = :precio, stock = :stock 
                  WHERE id_producto = :id_producto";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = floatval($this->precio);
        $this->stock = intval($this->stock);
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":id_producto", $this->id_producto);
        
        return $stmt->execute();
    }
    
    // Eliminar producto
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_producto = :id_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_producto", $this->id_producto);
        return $stmt->execute();
    }
    
    // Buscar productos por nombre
    public function buscar($termino) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE nombre LIKE :termino OR descripcion LIKE :termino 
                  ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bindParam(":termino", $termino);
        $stmt->execute();
        return $stmt;
    }
    
    // Actualizar stock (para ventas)
    public function actualizarStock($cantidad) {
        $query = "UPDATE " . $this->table_name . " 
                  SET stock = stock - :cantidad 
                  WHERE id_producto = :id_producto AND stock >= :cantidad";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $this->id_producto);
        
        return $stmt->execute();
    }
    
    // Verificar disponibilidad de stock
    public function verificarStock($cantidad) {
        $query = "SELECT stock FROM " . $this->table_name . " 
                  WHERE id_producto = :id_producto";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_producto", $this->id_producto);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            return $row['stock'] >= $cantidad;
        }
        return false;
    }
    
    // Obtener productos con stock bajo
    public function stockBajo($limite = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE stock <= :limite ORDER BY stock ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Contar total de productos
    public function contar() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Obtener valor total del inventario
    public function valorInventario() {
        $query = "SELECT SUM(precio * stock) as valor_total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['valor_total'] ?? 0;
    }
}
?>
