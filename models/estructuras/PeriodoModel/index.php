<?php
/**
 * ARCHIVO: models/estructuras/PeriodoModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'periodos_tutoria'.
 */
class PeriodoModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS PERIODOS
    public function listarTodos() {
        $sql = "SELECT id_periodo, codigo, nombre, fecha_inicio, fecha_fin, activo, creado_por, fecha_creacion 
                FROM periodos_tutoria 
                ORDER BY fecha_inicio DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN PERIODO POR ID
    public function obtenerPorId($id_periodo) {
        $sql = "SELECT * FROM periodos_tutoria WHERE id_periodo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_periodo]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. VERIFICAR DUPLICADOS DE CÓDIGO
    public function existeCodigo($codigo, $id_excluir = null) {
        $sql = "SELECT id_periodo FROM periodos_tutoria WHERE codigo = ?";
        $params = [$codigo];
        
        if ($id_excluir) {
            $sql .= " AND id_periodo != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // 4. CREAR NUEVO PERIODO
    public function crear($codigo, $nombre, $fecha_inicio, $fecha_fin, $activo, $creado_por) {
        $sql = "INSERT INTO periodos_tutoria (codigo, nombre, fecha_inicio, fecha_fin, activo, creado_por) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $codigo, 
            $nombre, 
            $fecha_inicio, 
            $fecha_fin, 
            $activo, 
            $creado_por
        ]);
    }

    // 5. ACTUALIZAR PERIODO EXISTENTE
    public function actualizar($id_periodo, $codigo, $nombre, $fecha_inicio, $fecha_fin, $activo) {
        $sql = "UPDATE periodos_tutoria 
                SET codigo = ?, nombre = ?, fecha_inicio = ?, fecha_fin = ?, activo = ? 
                WHERE id_periodo = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $codigo, 
            $nombre, 
            $fecha_inicio, 
            $fecha_fin, 
            $activo, 
            $id_periodo
        ]);
    }

    // 6. ELIMINAR PERIODO FÍSICAMENTE
    public function eliminar($id_periodo) {
        $sql = "DELETE FROM periodos_tutoria WHERE id_periodo = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_periodo]);
    }
}
?>