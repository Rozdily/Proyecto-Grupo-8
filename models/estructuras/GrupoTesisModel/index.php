<?php
/**
 * ARCHIVO: models/estructuras/GrupoTesisModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'cohortes_mg' (Grupos de Tesis).
 */
class GrupoTesisModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS GRUPOS (COHORTES)
    public function listarTodos() {
        $sql = "SELECT id_cohorte, codigo, nombre, fecha_inicio, fecha_fin, activa 
                FROM cohortes_mg 
                ORDER BY fecha_inicio DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN GRUPO POR ID
    public function obtenerPorId($id_cohorte) {
        $sql = "SELECT * FROM cohortes_mg WHERE id_cohorte = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_cohorte]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. VERIFICAR DUPLICADOS DE CÓDIGO
    public function existeCodigo($codigo, $id_excluir = null) {
        $sql = "SELECT id_cohorte FROM cohortes_mg WHERE codigo = ?";
        $params = [$codigo];
        
        if ($id_excluir) {
            $sql .= " AND id_cohorte != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // 4. CREAR NUEVO GRUPO DE TESIS
    public function crear($codigo, $nombre, $fecha_inicio, $fecha_fin, $activa) {
        $sql = "INSERT INTO cohortes_mg (codigo, nombre, fecha_inicio, fecha_fin, activa) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $codigo, 
            $nombre, 
            $fecha_inicio, 
            $fecha_fin, 
            $activa
        ]);
    }

    // 5. ACTUALIZAR GRUPO EXISTENTE
    public function actualizar($id_cohorte, $codigo, $nombre, $fecha_inicio, $fecha_fin, $activa) {
        $sql = "UPDATE cohortes_mg 
                SET codigo = ?, nombre = ?, fecha_inicio = ?, fecha_fin = ?, activa = ? 
                WHERE id_cohorte = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $codigo, 
            $nombre, 
            $fecha_inicio, 
            $fecha_fin, 
            $activa, 
            $id_cohorte
        ]);
    }

    // 6. ELIMINAR GRUPO FÍSICAMENTE
    public function eliminar($id_cohorte) {
        $sql = "DELETE FROM cohortes_mg WHERE id_cohorte = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_cohorte]);
    }
}
?>