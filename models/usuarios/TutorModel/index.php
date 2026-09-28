<?php
/**
 * ARCHIVO: models/usuarios/TutorModel/index.php
 * Administra el CRUD de la tabla 'tutores', su relación con 'usuarios', 
 * la gestión de disponibilidad horaria y la lógica de negocio.
 */
class TutorModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR DOCENTES (READ con INNER JOIN a usuarios)
    public function listarDocentes() {
        $sql = "SELECT t.id_tutor, t.id_usuario, u.nombre, u.apellido, u.correo, 
                       t.especialidad, t.biografia, t.foto_perfil, t.areas_expertise 
                FROM tutores t
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE u.estado = 'activo'
                ORDER BY u.apellido ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN TUTOR POR ID (Con datos de usuario)
    public function obtenerPorId($id_tutor) {
        $sql = "SELECT t.id_tutor, t.id_usuario, u.nombre, u.apellido, u.correo, u.telefono,
                       t.especialidad, t.biografia, t.foto_perfil, t.perfil_linkedin, 
                       t.certificaciones, t.areas_expertise 
                FROM tutores t
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE t.id_tutor = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_tutor]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR TUTOR
    public function crear($id_usuario, $especialidad, $biografia, $foto_perfil, $perfil_linkedin, $certificaciones, $areas_expertise) {
        $sql = "INSERT INTO tutores (id_usuario, especialidad, biografia, foto_perfil, perfil_linkedin, certificaciones, areas_expertise) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_usuario,
            $especialidad,
            $biografia,
            $foto_perfil,
            $perfil_linkedin,
            $certificaciones,
            $areas_expertise
        ]);
    }

    // 4. ACTUALIZAR TUTOR
    public function actualizar($id_tutor, $especialidad, $biografia, $foto_perfil, $perfil_linkedin, $certificaciones, $areas_expertise) {
        $sql = "UPDATE tutores 
                SET especialidad = ?, biografia = ?, foto_perfil = ?, perfil_linkedin = ?, certificaciones = ?, areas_expertise = ? 
                WHERE id_tutor = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $especialidad,
            $biografia,
            $foto_perfil,
            $perfil_linkedin,
            $certificaciones,
            $areas_expertise,
            $id_tutor
        ]);
    }

    // 5. ELIMINAR TUTOR FÍSICAMENTE
    public function eliminar($id_tutor) {
        $sql = "DELETE FROM tutores WHERE id_tutor = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_tutor]);
    }

    // 6. MÉTODO DE NEGOCIO: EVALUAR CARGA DE ALUMNOS (Cajón 2 - Regla RN-MG-08)
    public function evaluarCargaAlumnos($id_tutor) {
        $sql = "SELECT COUNT(id_asignacion) AS total_asignados 
                FROM asignaciones_tutor 
                WHERE id_tutor = ? AND estado = 'vigente'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_tutor]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total_asignados'] ?? 0;
    }

    // ========================================================================
    // GESTIÓN DE HORARIOS (TABLA: disponibilidad_tutor)
    // ========================================================================

    // 7. TRADUCIR ID USUARIO A ID TUTOR
    public function obtenerIdTutorPorUsuario($id_usuario) {
        $sql = "SELECT id_tutor FROM tutores WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_usuario]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id_tutor'] : null;
    }

    // 8. OBTENER HORARIOS DEL TUTOR
    public function obtenerHorarios($id_tutor) {
        $sql = "SELECT id_disponibilidad, id_tutor, dia_semana, hora_inicio, hora_fin 
                FROM disponibilidad_tutor 
                WHERE id_tutor = ? 
                ORDER BY 
                    FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'), 
                    hora_inicio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_tutor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 9. AGREGAR HORARIO
    public function agregarHorario($id_tutor, $dia_semana, $hora_inicio, $hora_fin) {
        $sql = "INSERT INTO disponibilidad_tutor (id_tutor, dia_semana, hora_inicio, hora_fin) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id_tutor, $dia_semana, $hora_inicio, $hora_fin]);
    }

    // 10. ELIMINAR HORARIO
    public function eliminarHorario($id_disponibilidad) {
        $sql = "DELETE FROM disponibilidad_tutor WHERE id_disponibilidad = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id_disponibilidad]);
    }
}
?>