<?php
/**
 * MÓDULO 1: models/usuarios/RolModel/RolModel.php
 * Administra el CRUD manual de la tabla 'roles'
 */
class RolModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LEER TODOS LOS ROLES
    public function obtenerTodos() {
        $sql = "SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. LEER UN ROL POR ID
    public function obtenerPorId($id_rol) {
        $sql = "SELECT id_rol, nombre_rol FROM roles WHERE id_rol = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_rol]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR ROL
    public function crear($nombre_rol) {
        $sql = "INSERT INTO roles (nombre_rol) VALUES (?)";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$nombre_rol]);
    }

    // 4. ACTUALIZAR ROL
    public function actualizar($id_rol, $nombre_rol) {
        $sql = "UPDATE roles SET nombre_rol = ? WHERE id_rol = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$nombre_rol, $id_rol]);
    }

    // 5. ELIMINAR ROL
    public function eliminar($id_rol) {
        $sql = "DELETE FROM roles WHERE id_rol = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_rol]);
    }
}
?>
