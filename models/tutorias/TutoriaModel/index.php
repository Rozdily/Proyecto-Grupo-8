<?php
/**
 * ARCHIVO: models/tutorias/TutoriaModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'tutorias'.
 * Adaptado a Módulos Flexibles, Límite de Estudiantes y Gestión de Inscritos.
 */
class TutoriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    public function listarTodas() {
        $sql = "SELECT t.id_tutoria, t.id_tutor, t.id_materia, t.id_bloque,
                       t.fecha, p.codigo as periodo, t.hora_inicio, t.hora_fin, t.modalidad, 
                       t.lugar_o_enlace, t.estado, t.tope_clases, t.clases_impartidas, t.limite_estudiantes,
                       CONCAT(ut.nombre, ' ', ut.apellido) AS nombre_tutor,
                       m.nombre_materia,
                       COUNT(te.id_estudiante) AS total_alumnos,
                       GROUP_CONCAT(CONCAT(ue.nombre, ' ', ue.apellido) SEPARATOR ', ') AS nombre_estudiante,
                       MAX(te.id_estudiante) AS id_estudiante, 
                       MAX(te.observaciones_estudiante) AS observaciones 
                FROM tutorias t
                INNER JOIN tutores tu ON t.id_tutor = tu.id_tutor
                INNER JOIN usuarios ut ON tu.id_usuario = ut.id_usuario
                INNER JOIN materias m ON t.id_materia = m.id_materia
                LEFT JOIN periodos_tutoria p ON t.id_periodo = p.id_periodo
                LEFT JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
                LEFT JOIN estudiantes e ON te.id_estudiante = e.id_estudiante
                LEFT JOIN usuarios ue ON e.id_usuario = ue.id_usuario
                GROUP BY t.id_tutoria
                ORDER BY t.fecha DESC, t.hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstudiantes() {
        $sql = "SELECT e.id_estudiante, e.registro_universitario,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo
                FROM estudiantes e
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                WHERE u.estado = 'activo'
                ORDER BY u.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Obtener la lista de estudiantes inscritos en una tutoría específica
    public function obtenerInscritos($id_tutoria) {
        $sql = "SELECT e.id_estudiante, e.registro_universitario, 
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo 
                FROM tutoria_estudiantes te
                INNER JOIN estudiantes e ON te.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                WHERE te.id_tutoria = ?
                ORDER BY u.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_tutoria]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Remover un estudiante específico de una tutoría
    public function eliminarInscrito($id_tutoria, $id_estudiante) {
        $sql = "DELETE FROM tutoria_estudiantes WHERE id_tutoria = ? AND id_estudiante = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id_tutoria, $id_estudiante]);
    }

    public function obtenerTutores() {
        $sql = "SELECT t.id_tutor,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo
                FROM tutores t
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE u.estado = 'activo'
                ORDER BY u.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMaterias() {
        $sql = "SELECT id_materia, nombre_materia FROM materias ORDER BY nombre_materia ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerBloques() {
        $sql = "SELECT id_bloque, nombre_bloque, hora_inicio, hora_fin FROM bloques_horarios ORDER BY hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $id_periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado, $observaciones, $tope_clases = 1, $limite_estudiantes = 10) {
        try {
            $this->pdo->beginTransaction();

            $sqlTutoria = "INSERT INTO tutorias 
                    (id_tutor, id_materia, id_bloque, fecha, id_periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, motivo_cancelacion, limite_estudiantes, tope_clases, clases_impartidas) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?, 0)";
            
            $stmtTutoria = $this->pdo->prepare($sqlTutoria);
            $stmtTutoria->execute([
                $id_tutor, $id_materia, $id_bloque, 
                $fecha, $id_periodo, $hora_inicio, $hora_fin, 
                $modalidad, $lugar_o_enlace, $estado,
                $limite_estudiantes, $tope_clases
            ]);

            $id_tutoria = $this->pdo->lastInsertId();

            if (!empty($id_estudiante)) {
                $sqlEstudiante = "INSERT INTO tutoria_estudiantes (id_tutoria, id_estudiante, observaciones_estudiante) VALUES (?, ?, ?)";
                $stmtEstudiante = $this->pdo->prepare($sqlEstudiante);
                $stmtEstudiante->execute([$id_tutoria, $id_estudiante, $observaciones]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function actualizar($id_tutoria, $id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $id_periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado, $observaciones, $tope_clases = 1, $clases_impartidas = 0, $limite_estudiantes = 10) {
        try {
            $this->pdo->beginTransaction();

            $sqlTutoria = "UPDATE tutorias 
                    SET id_tutor = ?, id_materia = ?, id_bloque = ?, 
                        fecha = ?, id_periodo = ?, hora_inicio = ?, hora_fin = ?, 
                        modalidad = ?, lugar_o_enlace = ?, estado = ?,
                        limite_estudiantes = ?, tope_clases = ?, clases_impartidas = ?
                    WHERE id_tutoria = ?";
            
            $stmtTutoria = $this->pdo->prepare($sqlTutoria);
            $stmtTutoria->execute([
                $id_tutor, $id_materia, $id_bloque, 
                $fecha, $id_periodo, $hora_inicio, $hora_fin, 
                $modalidad, $lugar_o_enlace, $estado, 
                $limite_estudiantes, $tope_clases, $clases_impartidas,
                $id_tutoria
            ]);

            if (!empty($id_estudiante)) {
                $sqlEstudiante = "INSERT INTO tutoria_estudiantes (id_tutoria, id_estudiante, observaciones_estudiante) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE observaciones_estudiante = VALUES(observaciones_estudiante)";
                $stmtEstudiante = $this->pdo->prepare($sqlEstudiante);
                $stmtEstudiante->execute([$id_tutoria, $id_estudiante, $observaciones]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function eliminar($id_tutoria) {
        $sql = "DELETE FROM tutorias WHERE id_tutoria = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_tutoria]);
    }
}
?>