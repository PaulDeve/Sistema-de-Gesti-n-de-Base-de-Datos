<?php
/**
 * Configuración de la base de datos
 * Sistema de Gestión de Ventas
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'gestion_ventas';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    
    public function createDatabase() {
        try {
            $pdo = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear base de datos
            $sql = "CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8 COLLATE utf8_unicode_ci";
            $pdo->exec($sql);
            
            echo "Base de datos creada exitosamente.<br>";
            
            // Seleccionar la base de datos
            $pdo->exec("USE " . $this->db_name);
            
            // Crear tablas
            $this->createTables($pdo);
            
        } catch(PDOException $e) {
            echo "Error creando base de datos: " . $e->getMessage();
        }
    }
    
    private function createTables($pdo) {
        // Tabla usuarios
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            correo VARCHAR(100) UNIQUE NOT NULL,
            contraseña VARCHAR(255) NOT NULL,
            rol ENUM('admin', 'vendedor') DEFAULT 'vendedor',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Tabla clientes
        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id_cliente INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            correo VARCHAR(100) UNIQUE NOT NULL,
            telefono VARCHAR(20),
            direccion TEXT,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Tabla productos
        $sql = "CREATE TABLE IF NOT EXISTS productos (
            id_producto INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            precio DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Tabla ventas
        $sql = "CREATE TABLE IF NOT EXISTS ventas (
            id_venta INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            total DECIMAL(10,2) NOT NULL,
            id_usuario INT NOT NULL,
            FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
        )";
        $pdo->exec($sql);
        
        // Tabla detalle_ventas
        $sql = "CREATE TABLE IF NOT EXISTS detalle_ventas (
            id_detalle INT AUTO_INCREMENT PRIMARY KEY,
            id_venta INT NOT NULL,
            id_producto INT NOT NULL,
            cantidad INT NOT NULL,
            precio_unitario DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
            FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT
        )";
        $pdo->exec($sql);
        
        echo "Tablas creadas exitosamente.<br>";
        
        // Insertar usuario administrador por defecto
        $this->insertDefaultAdmin($pdo);
    }
    
    private function insertDefaultAdmin($pdo) {
        try {
            $sql = "INSERT IGNORE INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt->execute(['Administrador', 'admin@sistema.com', $password_hash, 'admin']);
            echo "Usuario administrador creado: admin@sistema.com / admin123<br>";
        } catch(PDOException $e) {
            echo "Usuario administrador ya existe.<br>";
        }
    }
}
?>
