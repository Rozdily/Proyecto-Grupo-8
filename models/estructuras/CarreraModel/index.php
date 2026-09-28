<?php
/**
 * ARCHIVO: models/estructuras/CarreraModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'carreras' (Catálogo Académico).
 */
class CarreraModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODAS LAS CARRERAS
    public function listarTodas() {
        // Ordenamos alfabéticamente para que se vea mejor en los <select> y tablas
        $sql = "SELECT id_carrera, nombre_carrera 
                FROM carreras 
                ORDER BY nombre_carrera ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UNA CARRERA POR ID
    public function obtenerPorId($id_carrera) {
        $sql = "SELECT * FROM carreras WHERE id_carrera = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_carrera]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. VERIFICAR DUPLICADOS DE NOMBRE (La DB tiene UNIQUE en nombre_carrera)
    public function existeNombre($nombre_carrera, $id_excluir = null) {
        $sql = "SELECT id_carrera FROM carreras WHERE nombre_carrera = ?";
        $params = [$nombre_carrera];
        
        if ($id_excluir) {
            $sql .= " AND id_carrera != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // 4. CREAR NUEVA CARRERA
    public function crear($nombre_carrera) {
        $sql = "INSERT INTO carreras (nombre_carrera) VALUES (?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre_carrera]);
    }

    // 5. ACTUALIZAR CARRERA EXISTENTE
    public function actualizar($id_carrera, $nombre_carrera) {
        $sql = "UPDATE carreras SET nombre_carrera = ? WHERE id_carrera = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre_carrera, $id_carrera]);
    }

    // 6. ELIMINAR CARRERA FÍSICAMENTE
    public function eliminar($id_carrera) {
        $sql = "DELETE FROM carreras WHERE id_carrera = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_carrera]);
    }
}
?>