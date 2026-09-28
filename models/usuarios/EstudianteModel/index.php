<?php
/**
 * MÓDULO 1: models/usuarios/EstudianteModel/EstudianteModel.php
 * Administra el CRUD de la tabla 'estudiantes', su relación con 'usuarios' y 'carreras',
 * las validaciones académicas, y la autogeneración de Registros Universitarios.
 */
class EstudianteModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR ESTUDIANTES (READ con INNER JOIN a usuarios y carreras)
    public function listarEstudiantes() {
        $sql = "SELECT e.id_estudiante, e.id_usuario, e.registro_universitario, e.semestre, 
                       u.nombre, u.apellido, u.correo, u.estado, 
                       c.nombre_carrera, c.id_carrera
                FROM estudiantes e
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN carreras c ON e.id_carrera = c.id_carrera
                ORDER BY u.apellido ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN ESTUDIANTE POR ID
    public function obtenerPorId($id_estudiante) {
        $sql = "SELECT e.id_estudiante, e.id_usuario, e.registro_universitario, e.semestre, 
                       u.nombre, u.apellido, u.correo, u.telefono, u.estado, 
                       c.nombre_carrera, c.id_carrera
                FROM estudiantes e
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN carreras c ON e.id_carrera = c.id_carrera
                WHERE e.id_estudiante = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_estudiante]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR ESTUDIANTE
    public function crear($id_usuario, $id_carrera, $semestre, $registro_universitario) {
        $sql = "INSERT INTO estudiantes (id_usuario, id_carrera, semestre, registro_universitario) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_usuario, 
            $id_carrera, 
            $semestre, 
            $registro_universitario
        ]);
    }

    // 4. ACTUALIZAR ESTUDIANTE
    public function actualizar($id_estudiante, $id_carrera, $semestre, $registro_universitario) {
        $sql = "UPDATE estudiantes 
                SET id_carrera = ?, semestre = ?, registro_universitario = ? 
                WHERE id_estudiante = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_carrera, 
            $semestre, 
            $registro_universitario, 
            $id_estudiante
        ]);
    }

    // 5. ELIMINAR ESTUDIANTE FÍSICAMENTE
    public function eliminar($id_estudiante) {
        $sql = "DELETE FROM estudiantes WHERE id_estudiante = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_estudiante]);
    }

    // =========================================================
    // MÉTODOS DE NEGOCIO (Plan de Implementación y Reglas SPA)
    // =========================================================

    // 6. VALIDAR SEMESTRES PARA ASIGNACIÓN DE GRADO
    public function validarSemestres($id_estudiante) {
        $sql = "SELECT semestre FROM estudiantes WHERE id_estudiante = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_estudiante]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado && $resultado['semestre'] >= 9) {
            return true;
        }
        return false;
    }

    // 7. OBTENER HISTORIAL DE EXPEDIENTES Y GRUPOS DE TESIS
    public function obtenerHistorial($id_estudiante) {
        $sql = "SELECT exp.id_expediente, exp.etapa_actual, exp.estado, exp.titulo_trabajo,
                       modg.nombre AS nombre_modalidad, 
                       cg.codigo AS codigo_grupo_tesis
                FROM expedientes_mg exp
                INNER JOIN modalidades_grado modg ON exp.id_modalidad = modg.id_modalidad
                INNER JOIN cohortes_mg cg ON exp.id_cohorte = cg.id_cohorte
                WHERE exp.id_estudiante = ?
                ORDER BY exp.fecha_inicio DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_estudiante]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // NUEVOS MÉTODOS (Carreras y Autogeneración de RU)
    // =========================================================

    // 8. OBTENER LISTA DE CARRERAS (Para el menú desplegable)
    public function obtenerCarreras() {
        $sql = "SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 9. AUTOGENERAR EL SIGUIENTE REGISTRO UNIVERSITARIO (Formato RU-AÑO-XXXX)
    public function generarSiguienteRU() {
        $anio_actual = date('Y');
        $prefijo = "RU-" . $anio_actual . "-";
        
        // Buscamos el registro más alto de este año
        $sql = "SELECT registro_universitario 
                FROM estudiantes 
                WHERE registro_universitario LIKE ? 
                ORDER BY registro_universitario DESC 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$prefijo . '%']);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            // Si ya hay registros este año (ej. RU-2026-0016), extraemos el 16 y sumamos 1
            $partes = explode('-', $resultado['registro_universitario']);
            $ultimo_numero = (int) end($partes);
            $siguiente_numero = $ultimo_numero + 1;
        } else {
            // Si es el primer estudiante del año
            $siguiente_numero = 1;
        }
        
        // Formateamos para que siempre tenga 4 dígitos (0001, 0012, 0105...)
        return sprintf("%s%04d", $prefijo, $siguiente_numero);
    }
}
?>