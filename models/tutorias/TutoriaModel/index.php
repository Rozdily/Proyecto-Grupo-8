<?php
/**
 * ARCHIVO: models/tutorias/TutoriaModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'tutorias'.
 * Adaptado a la nueva estructura de grupos (1 a N) con la tabla 'tutoria_estudiantes'.
 */
class TutoriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODAS LAS TUTORÍAS REGULARES (Agrupadas para soportar 1 a N)
    public function listarTodas() {
        $sql = "SELECT t.id_tutoria, t.id_tutor, t.id_materia, t.id_bloque,
                       t.fecha, t.periodo, t.hora_inicio, t.hora_fin, t.modalidad, 
                       t.lugar_o_enlace, t.estado,
                       CONCAT(ut.nombre, ' ', ut.apellido) AS nombre_tutor,
                       m.nombre_materia,
                       COUNT(te.id_estudiante) AS total_alumnos,
                       GROUP_CONCAT(CONCAT(ue.nombre, ' ', ue.apellido) SEPARATOR ', ') AS nombre_estudiante,
                       MAX(te.id_estudiante) AS id_estudiante, /* Fallback para llenar el select del form actual */
                       MAX(te.observaciones_estudiante) AS observaciones /* Fallback para el form actual */
                FROM tutorias t
                INNER JOIN tutores tu ON t.id_tutor = tu.id_tutor
                INNER JOIN usuarios ut ON tu.id_usuario = ut.id_usuario
                INNER JOIN materias m ON t.id_materia = m.id_materia
                LEFT JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
                LEFT JOIN estudiantes e ON te.id_estudiante = e.id_estudiante
                LEFT JOIN usuarios ue ON e.id_usuario = ue.id_usuario
                GROUP BY t.id_tutoria
                ORDER BY t.fecha DESC, t.hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER ESTUDIANTES PARA FORMULARIO
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

    // 3. OBTENER TUTORES PARA FORMULARIO
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

    // 4. OBTENER MATERIAS PARA FORMULARIO
    public function obtenerMaterias() {
        $sql = "SELECT id_materia, nombre_materia FROM materias ORDER BY nombre_materia ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. OBTENER BLOQUES HORARIOS PARA FORMULARIO
    public function obtenerBloques() {
        $sql = "SELECT id_bloque, nombre_bloque, hora_inicio, hora_fin FROM bloques_horarios ORDER BY hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. CREAR NUEVA TUTORÍA REGULAR (Transacción para las 2 tablas)
    public function crear($id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado, $observaciones) {
        try {
            $this->pdo->beginTransaction();

            // Insertar la sesión (sin el estudiante)
            $sqlTutoria = "INSERT INTO tutorias 
                    (id_tutor, id_materia, id_bloque, fecha, periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, motivo_cancelacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')";
            
            $stmtTutoria = $this->pdo->prepare($sqlTutoria);
            $stmtTutoria->execute([
                $id_tutor, $id_materia, $id_bloque, 
                $fecha, $periodo, $hora_inicio, $hora_fin, 
                $modalidad, $lugar_o_enlace, $estado
            ]);

            // Recuperar el ID de la sesión recién creada
            $id_tutoria = $this->pdo->lastInsertId();

            // Vincular al estudiante en la tabla puente
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

    // 7. ACTUALIZAR TUTORÍA EXISTENTE (Transacción para las 2 tablas)
    public function actualizar($id_tutoria, $id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado, $observaciones) {
        try {
            $this->pdo->beginTransaction();

            // Actualizar datos generales de la sesión
            $sqlTutoria = "UPDATE tutorias 
                    SET id_tutor = ?, id_materia = ?, id_bloque = ?, 
                        fecha = ?, periodo = ?, hora_inicio = ?, hora_fin = ?, 
                        modalidad = ?, lugar_o_enlace = ?, estado = ?
                    WHERE id_tutoria = ?";
            
            $stmtTutoria = $this->pdo->prepare($sqlTutoria);
            $stmtTutoria->execute([
                $id_tutor, $id_materia, $id_bloque, 
                $fecha, $periodo, $hora_inicio, $hora_fin, 
                $modalidad, $lugar_o_enlace, $estado, 
                $id_tutoria
            ]);

            // Actualizar o insertar la vinculación del estudiante en la tabla puente
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

    // 8. ELIMINAR TUTORÍA
    public function eliminar($id_tutoria) {
        // La restricción 'ON DELETE CASCADE' de la BD eliminará a los estudiantes vinculados automáticamente
        $sql = "DELETE FROM tutorias WHERE id_tutoria = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_tutoria]);
    }
}
?>