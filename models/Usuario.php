<?php
/**
 * Modelo de Usuario
 * Sistema de Gestión de Ventas
 */

require_once 'config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";
    
    public $id_usuario;
    public $nombre;
    public $correo;
    public $contraseña;
    public $rol;
    public $fecha_creacion;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Crear nuevo usuario
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, correo, contraseña, rol) 
                  VALUES (:nombre, :correo, :contraseña, :rol)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        
        // Hash de la contraseña
        $this->contraseña = password_hash($this->contraseña, PASSWORD_DEFAULT);
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":contraseña", $this->contraseña);
        $stmt->bindParam(":rol", $this->rol);
        
        if($stmt->execute()) {
            $this->id_usuario = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    // Leer todos los usuarios
    public function leerTodos($limit = 100) {
        $query = "SELECT id_usuario, nombre, correo, rol, fecha_creacion 
                  FROM " . $this->table_name . " ORDER BY nombre ASC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Leer un usuario por ID
    public function leerUno() {
        $query = "SELECT id_usuario, nombre, correo, rol, fecha_creacion 
                  FROM " . $this->table_name . " WHERE id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->nombre = $row['nombre'];
            $this->correo = $row['correo'];
            $this->rol = $row['rol'];
            $this->fecha_creacion = $row['fecha_creacion'];
            return true;
        }
        return false;
    }
    
    // Actualizar usuario
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, correo = :correo, rol = :rol 
                  WHERE id_usuario = :id_usuario";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->rol = htmlspecialchars(strip_tags($this->rol));
        
        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        
        return $stmt->execute();
    }
    
    // Actualizar contraseña
    public function actualizarContraseña($nueva_contraseña) {
        $query = "UPDATE " . $this->table_name . " 
                  SET contraseña = :contraseña 
                  WHERE id_usuario = :id_usuario";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash de la nueva contraseña
        $hash_contraseña = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":contraseña", $hash_contraseña);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        
        return $stmt->execute();
    }
    
    // Eliminar usuario
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        return $stmt->execute();
    }
    
    // Autenticar usuario
    public function autenticar($correo, $contraseña) {
        $query = "SELECT id_usuario, nombre, correo, contraseña, rol 
                  FROM " . $this->table_name . " WHERE correo = :correo LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row && password_verify($contraseña, $row['contraseña'])) {
            $this->id_usuario = $row['id_usuario'];
            $this->nombre = $row['nombre'];
            $this->correo = $row['correo'];
            $this->rol = $row['rol'];
            return true;
        }
        return false;
    }
    
    // Verificar si el correo existe
    public function correoExiste($correo) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE correo = :correo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0;
    }
    
    // Obtener usuarios por rol
    public function obtenerPorRol($rol) {
        $query = "SELECT id_usuario, nombre, correo, rol, fecha_creacion 
                  FROM " . $this->table_name . " WHERE rol = :rol ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":rol", $rol);
        $stmt->execute();
        return $stmt;
    }
    
    // Contar total de usuarios
    public function contar() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    // Verificar permisos de administrador
    public function esAdmin() {
        return $this->rol === 'admin';
    }
    
    // Verificar permisos de vendedor
    public function esVendedor() {
        return $this->rol === 'vendedor' || $this->rol === 'admin';
    }
}
?>
