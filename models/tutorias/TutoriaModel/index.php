<?php
/**
 * MÓDULO 3: models/tutorias/TutoriaModel/TutoriaModel.php
 * Administra el CRUD de las citas de reforzamiento por materia, 
 * el filtrado SPA del tablero, la evidencia de asistencia y las encuestas de calidad.
 */
class TutoriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: AGENDAR CITA SPA (CRUD - Insert)
    public function agendarCitaSPA($id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $observaciones) {
        // El estado ingresa por defecto como 'pendiente' para respetar la regla del Cajón 3
        $sql = "INSERT INTO tutorias (id_estudiante, id_tutor, id_materia, id_bloque, fecha, periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_estudiante, 
            $id_tutor, 
            $id_materia, 
            $id_bloque, 
            $fecha, 
            $periodo, 
            $hora_inicio, 
            $hora_fin, 
            $modalidad, 
            $lugar_o_enlace, 
            $observaciones
        ]);
    }

    // 2. MÉTODO DE NEGOCIO: FILTRAR POR ESTADOS (Para el tablero interactivo del Cajón 3)
    public function filtrarPorEstados($estado) {
        // Múltiples INNER JOINs para construir la fila completa de la tabla maestra sin recargar
        $sql = "SELECT t.id_tutoria, t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, t.estado,
                       m.nombre_materia,
                       ue.nombre AS estudiante_nombre, ue.apellido AS estudiante_apellido,
                       ut.nombre AS tutor_nombre, ut.apellido AS tutor_apellido
                FROM tutorias t
                INNER JOIN estudiantes e ON t.id_estudiante = e.id_estudiante
                INNER JOIN usuarios ue ON e.id_usuario = ue.id_usuario
                INNER JOIN tutores tu ON t.id_tutor = tu.id_tutor
                INNER JOIN usuarios ut ON tu.id_usuario = ut.id_usuario
                INNER JOIN materias m ON t.id_materia = m.id_materia
                WHERE t.estado = ?
                ORDER BY t.fecha ASC, t.hora_inicio ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. OBTENER UNA TUTORÍA ESPECÍFICA POR ID
    public function obtenerPorId($id_tutoria) {
        $sql = "SELECT id_tutoria, id_estudiante, id_tutor, id_materia, id_bloque, fecha, periodo, 
                       hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, observaciones, motivo_cancelacion 
                FROM tutorias 
                WHERE id_tutoria = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_tutoria]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. CAMBIAR ESTADO (Ej. De 'pendiente' a 'cancelada' o 'confirmada')
    public function cambiarEstado($id_tutoria, $nuevo_estado, $motivo_cancelacion = '') {
        $sql = "UPDATE tutorias 
                SET estado = ?, motivo_cancelacion = ? 
                WHERE id_tutoria = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $nuevo_estado, 
            $motivo_cancelacion, 
            $id_tutoria
        ]);
    }

    // 5. MÉTODO DE NEGOCIO: REGISTRAR ASISTENCIA Y SEGUIMIENTO DE SESIÓN
    public function registrarAsistencia($id_tutoria, $asistio, $temas_tratados, $avance, $recommendations) {
        // A. Insertar la evidencia en la tabla seguimiento_sesion
        $sqlInsert = "INSERT INTO seguimiento_sesion (id_tutoria, asistio, temas_tratados, avance, recommendations) 
                      VALUES (?, ?, ?, ?, ?)";
        
        $stmtInsert = $this->pdo->prepare($sqlInsert);
        $exitoTracking = $stmtInsert->execute([
            $id_tutoria, 
            $asistio, 
            $temas_tratados, 
            $avance, 
            $recommendations
        ]);

        // B. Si la evidencia se guarda, actualizar el estado de la tutoría a 'realizada'
        if ($exitoTracking) {
            $sqlUpdate = "UPDATE tutorias SET estado = 'realizada' WHERE id_tutoria = ?";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            return $stmtUpdate->execute([$id_tutoria]);
        }
        
        return false;
    }

    // 6. MÉTODO DE NEGOCIO: CALIFICAR ENCUESTA (Por el Estudiante)
    public function calificarEncuesta($id_tutoria, $calificacion, $comentario) {
        $sql = "INSERT INTO evaluaciones_tutoria (id_tutoria, calificacion, comentario) 
                VALUES (?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_tutoria, 
            $calificacion, 
            $comentario
        ]);
    }
}
?>