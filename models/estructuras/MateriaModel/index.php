<?php
/**
 * ARCHIVO: models/estructuras/MateriaModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'materias' (Catálogo Académico).
 * Incluye cruces relacionales (JOIN) con la tabla 'carreras'.
 */
class MateriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODAS LAS MATERIAS (CON INNER JOIN A CARRERAS)
    public function listarTodas() {
        // Obtenemos también el nombre de la carrera para pintarlo en la tabla visualmente
        $sql = "SELECT m.id_materia, m.nombre_materia, m.id_carrera, c.nombre_carrera 
                FROM materias m
                INNER JOIN carreras c ON m.id_carrera = c.id_carrera
                ORDER BY c.nombre_carrera ASC, m.nombre_materia ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UNA MATERIA POR ID
    public function obtenerPorId($id_materia) {
        $sql = "SELECT * FROM materias WHERE id_materia = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_materia]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. VERIFICAR DUPLICADOS (Regla UNIQUE: nombre_materia + id_carrera)
    public function existeMateriaEnCarrera($nombre_materia, $id_carrera, $id_excluir = null) {
        $sql = "SELECT id_materia FROM materias WHERE nombre_materia = ? AND id_carrera = ?";
        $params = [$nombre_materia, $id_carrera];
        
        if ($id_excluir) {
            $sql .= " AND id_materia != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // 4. CREAR NUEVA MATERIA
    public function crear($nombre_materia, $id_carrera) {
        $sql = "INSERT INTO materias (nombre_materia, id_carrera) VALUES (?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre_materia, $id_carrera]);
    }

    // 5. ACTUALIZAR MATERIA EXISTENTE
    public function actualizar($id_materia, $nombre_materia, $id_carrera) {
        $sql = "UPDATE materias SET nombre_materia = ?, id_carrera = ? WHERE id_materia = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre_materia, $id_carrera, $id_materia]);
    }

    // 6. ELIMINAR MATERIA FÍSICAMENTE
    public function eliminar($id_materia) {
        $sql = "DELETE FROM materias WHERE id_materia = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_materia]);
    }
}
?>