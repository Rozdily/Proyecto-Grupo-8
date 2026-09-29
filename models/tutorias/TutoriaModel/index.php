<?php
/**
 * ARCHIVO: models/tutorias/TutoriaModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'tutorias' (Apoyo académico regular).
 * Incluye cruces con estudiantes, tutores, materias y bloques horarios.
 */
class TutoriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODAS LAS TUTORÍAS REGULARES
    public function listarTodas() {
        $sql = "SELECT t.id_tutoria, t.id_estudiante, t.id_tutor, t.id_materia, t.id_bloque,
                       t.fecha, t.periodo, t.hora_inicio, t.hora_fin, t.modalidad, 
                       t.lugar_o_enlace, t.estado, t.observaciones,
                       CONCAT(ue.nombre, ' ', ue.apellido) AS nombre_estudiante,
                       CONCAT(ut.nombre, ' ', ut.apellido) AS nombre_tutor,
                       m.nombre_materia
                FROM tutorias t
                INNER JOIN estudiantes e ON t.id_estudiante = e.id_estudiante
                INNER JOIN usuarios ue ON e.id_usuario = ue.id_usuario
                INNER JOIN tutores tu ON t.id_tutor = tu.id_tutor
                INNER JOIN usuarios ut ON tu.id_usuario = ut.id_usuario
                INNER JOIN materias m ON t.id_materia = m.id_materia
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

    // 6. CREAR NUEVA TUTORÍA REGULAR
    public function crear($id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado, $observaciones) {
        // motivo_cancelacion es NOT NULL en la base de datos, se envía vacío por defecto[cite: 1]
        $sql = "INSERT INTO tutorias 
                (id_estudiante, id_tutor, id_materia, id_bloque, fecha, periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, observaciones, motivo_cancelacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '')";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_estudiante, $id_tutor, $id_materia, $id_bloque, 
            $fecha, $periodo, $hora_inicio, $hora_fin, 
            $modalidad, $lugar_o_enlace, $estado, $observaciones
        ]);
    }

    // 7. ACTUALIZAR TUTORÍA EXISTENTE
    public function actualizar($id_tutoria, $id_estudiante, $id_tutor, $id_materia, $id_bloque, $fecha, $periodo, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado, $observaciones) {
        $sql = "UPDATE tutorias 
                SET id_estudiante = ?, id_tutor = ?, id_materia = ?, id_bloque = ?, 
                    fecha = ?, periodo = ?, hora_inicio = ?, hora_fin = ?, 
                    modalidad = ?, lugar_o_enlace = ?, estado = ?, observaciones = ?
                WHERE id_tutoria = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_estudiante, $id_tutor, $id_materia, $id_bloque, 
            $fecha, $periodo, $hora_inicio, $hora_fin, 
            $modalidad, $lugar_o_enlace, $estado, $observaciones, 
            $id_tutoria
        ]);
    }

    // 8. ELIMINAR TUTORÍA
    public function eliminar($id_tutoria) {
        $sql = "DELETE FROM tutorias WHERE id_tutoria = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_tutoria]);
    }
}
?>