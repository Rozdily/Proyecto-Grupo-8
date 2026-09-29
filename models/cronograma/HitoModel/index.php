<?php
/**
 * ARCHIVO: models/cronograma/HitoModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'calendario_mg' (Hitos y Entregas).
 * Actúa como la agenda de fechas límite por cohorte.
 */
class HitoModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS HITOS (CON INNER JOIN A COHORTES)
    public function listarTodos() {
        // Ordenamos por fecha límite ascendente para que lo más urgente salga primero
        $sql = "SELECT h.id_hito, h.id_cohorte, h.etapa, h.tipo, 
                       h.nombre, h.orden, h.fecha_limite, h.avance_esperado_pct,
                       c.nombre AS nombre_cohorte, c.codigo AS codigo_cohorte
                FROM calendario_mg h
                INNER JOIN cohortes_mg c ON h.id_cohorte = c.id_cohorte
                ORDER BY h.fecha_limite ASC, h.orden ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN HITO POR ID
    public function obtenerPorId($id_hito) {
        $sql = "SELECT * FROM calendario_mg WHERE id_hito = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_hito]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR NUEVO HITO / FECHA LÍMITE
    public function crear($id_cohorte, $etapa, $tipo, $nombre, $orden, $fecha_limite, $avance_esperado_pct) {
        $sql = "INSERT INTO calendario_mg 
                (id_cohorte, etapa, tipo, nombre, orden, fecha_limite, avance_esperado_pct) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_cohorte, 
            $etapa, 
            $tipo, 
            $nombre, 
            $orden, 
            $fecha_limite, 
            $avance_esperado_pct
        ]);
    }

    // 4. ACTUALIZAR HITO EXISTENTE
    public function actualizar($id_hito, $id_cohorte, $etapa, $tipo, $nombre, $orden, $fecha_limite, $avance_esperado_pct) {
        $sql = "UPDATE calendario_mg 
                SET id_cohorte = ?, etapa = ?, tipo = ?, nombre = ?, 
                    orden = ?, fecha_limite = ?, avance_esperado_pct = ? 
                WHERE id_hito = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_cohorte, 
            $etapa, 
            $tipo, 
            $nombre, 
            $orden, 
            $fecha_limite, 
            $avance_esperado_pct, 
            $id_hito
        ]);
    }

    // 5. ELIMINAR HITO FÍSICAMENTE
    public function eliminar($id_hito) {
        $sql = "DELETE FROM calendario_mg WHERE id_hito = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_hito]);
    }
}
?>