<?php
/**
 * Modelo de Cliente
 * Sistema de Gestión de Ventas
 */

require_once 'config/database.php';

class Cliente {
    private $conn;
    private $table_name = "clientes";
    
    public $id_cliente;
    public $nombre;
    public $apellido;
    public $correo;
    public $telefono;
    public $direccion;
    public $fecha_registro;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Crear nuevo cliente
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, apellido, correo, telefono, direccion) 
                  VALUES (:nombre, :apellido, :correo, :telefono, :direccion)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":direccion", $this->direccion);
        
        if($stmt->execute()) {
            $this->id_cliente = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Leer todos los clientes
    public function leerTodos($limit = 100) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_registro DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Leer un cliente por ID
    public function leerUno() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cliente = :id_cliente LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->nombre = $row['nombre'];
            $this->apellido = $row['apellido'];
            $this->correo = $row['correo'];
            $this->telefono = $row['telefono'];
            $this->direccion = $row['direccion'];
            $this->fecha_registro = $row['fecha_registro'];
            return true;
        }
        return false;
    }
    
    // Actualizar cliente
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, apellido = :apellido, correo = :correo, 
                      telefono = :telefono, direccion = :direccion 
                  WHERE id_cliente = :id_cliente";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        
        return $stmt->execute();
    }
    
    // Eliminar cliente
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cliente = :id_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        return $stmt->execute();
    }
    
    // Buscar clientes por nombre o apellido
    public function buscar($termino) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE nombre LIKE :termino OR apellido LIKE :termino 
                  ORDER BY nombre, apellido";
        $stmt = $this->conn->prepare($query);
        $termino = "%" . $termino . "%";
        $stmt->bindParam(":termino", $termino);
        $stmt->execute();
        return $stmt;
    }
    
    // Contar total de clientes
    public function contar() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
