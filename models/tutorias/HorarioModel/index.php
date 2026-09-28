<?php
/**
 * MÓDULO 3: models/tutorias/HorarioModel/HorarioModel.php
 * Administra el catálogo de turnos (bloques_horarios), la asignación de turnos por docente
 * y el motor lógico anti-cruces para prevenir choques de horarios en el sistema.
 */
class HorarioModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS BLOQUES INSTITUCIONALES (READ)
    public function listarBloques() {
        $sql = "SELECT id_bloque, nombre_bloque, hora_inicio, hora_fin, descripcion 
                FROM bloques_horarios 
                ORDER BY hora_inicio ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER LOS BLOQUES SELECCIONADOS POR UN TUTOR (INNER JOIN)
    public function obtenerBloquesPorTutor($id_tutor) {
        $sql = "SELECT b.id_bloque, b.nombre_bloque, b.hora_inicio, b.hora_fin 
                FROM bloques_horarios b
                INNER JOIN tutor_bloque_seleccionado tb ON b.id_bloque = tb.id_bloque
                WHERE tb.id_tutor = ?
                ORDER BY b.hora_inicio ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_tutor]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. MÉTODO DE NEGOCIO: GUARDAR DISPONIBILIDAD DE TUTOR
    public function guardarDisponibilidadTutor($id_tutor, $id_bloque) {
        $sql = "INSERT INTO tutor_bloque_seleccionado (id_tutor, id_bloque) 
                VALUES (?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_tutor, $id_bloque]);
    }

    // 4. ELIMINAR DISPONIBILIDAD DE TUTOR (Para limpiar o resetear su agenda)
    public function eliminarDisponibilidadTutor($id_tutor, $id_bloque) {
        $sql = "DELETE FROM tutor_bloque_seleccionado 
                WHERE id_tutor = ? AND id_bloque = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_tutor, $id_bloque]);
    }

    // 5. MÉTODO DE NEGOCIO: VERIFICAR CHOQUE DE AULAS Y HORARIOS (Anti-Cruce)
    // Devuelve TRUE si ya existe un evento programado para ese tutor en ese rango de horas exacto.
    public function verificarChoqueDeAulas($id_tutor, $fecha, $hora_inicio, $hora_fin) {
        // La lógica compara que la hora de inicio nueva sea menor a la hora de fin existente, 
        // y que la hora de fin nueva sea mayor a la hora de inicio existente (Solapamiento matemático).
        $sql = "SELECT id_tutoria 
                FROM tutorias 
                WHERE id_tutor = ? 
                AND fecha = ? 
                AND estado IN ('pendiente', 'confirmada', 'en_proceso')
                AND (hora_inicio < ? AND hora_fin > ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // CUIDADO: El orden de vinculación aquí es vital para la ecuación de solapamiento
        $stmt->execute([
            $id_tutor, 
            $fecha, 
            $hora_fin,   // Se compara contra la hora_inicio de la BD
            $hora_inicio // Se compara contra la hora_fin de la BD
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la consulta arroja al menos 1 registro, significa que HAY CHOQUE CRÍTICO.
        if ($resultado) {
            return true; 
        }
        return false;
    }
}
?>