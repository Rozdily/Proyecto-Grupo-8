<?php
/**
 * ARCHIVO: models/titulacion/ExpedienteModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'expedientes_mg'.
 * Maneja cruces complejos con estudiantes, cohortes y modalidades.
 */
class ExpedienteModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS EXPEDIENTES (CON INNER JOINS)
    public function listarTodos() {
        $sql = "SELECT e.id_expediente, e.id_estudiante, e.id_modalidad, e.id_cohorte, 
                       e.etapa_actual, e.estado, e.titulo_trabajo, e.fecha_inicio, 
                       e.fecha_cierre, e.observaciones,
                       est.registro_universitario,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_estudiante,
                       m.nombre AS nombre_modalidad,
                       c.nombre AS nombre_cohorte
                FROM expedientes_mg e
                INNER JOIN estudiantes est ON e.id_estudiante = est.id_estudiante
                INNER JOIN usuarios u ON est.id_usuario = u.id_usuario
                INNER JOIN modalidades_grado m ON e.id_modalidad = m.id_modalidad
                INNER JOIN cohortes_mg c ON e.id_cohorte = c.id_cohorte
                ORDER BY e.fecha_inicio DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN EXPEDIENTE POR ID
    public function obtenerPorId($id_expediente) {
        $sql = "SELECT * FROM expedientes_mg WHERE id_expediente = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_expediente]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. OBTENER LISTA DE ESTUDIANTES PARA EL FORMULARIO
    public function obtenerEstudiantesParaFormulario() {
        $sql = "SELECT est.id_estudiante, est.registro_universitario, 
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo
                FROM estudiantes est
                INNER JOIN usuarios u ON est.id_usuario = u.id_usuario
                WHERE u.estado = 'activo'
                ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. OBTENER LISTA DE MODALIDADES DE GRADO ACTIVAS PARA EL FORMULARIO
    public function obtenerModalidadesActivas() {
        $sql = "SELECT id_modalidad, nombre, codigo 
                FROM modalidades_grado 
                WHERE activa = 1 
                ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. VERIFICAR DUPLICADOS (REGLA UNIQUE: estudiante + modalidad + cohorte)
    public function existeExpediente($id_estudiante, $id_modalidad, $id_cohorte, $id_excluir = null) {
        $sql = "SELECT id_expediente FROM expedientes_mg 
                WHERE id_estudiante = ? AND id_modalidad = ? AND id_cohorte = ?";
        $params = [$id_estudiante, $id_modalidad, $id_cohorte];
        
        if ($id_excluir) {
            $sql .= " AND id_expediente != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // 6. CREAR NUEVO EXPEDIENTE
    public function crear($id_estudiante, $id_modalidad, $id_cohorte, $etapa_actual, $estado, $titulo_trabajo, $fecha_inicio, $fecha_cierre, $observaciones) {
        $sql = "INSERT INTO expedientes_mg 
                (id_estudiante, id_modalidad, id_cohorte, etapa_actual, estado, titulo_trabajo, fecha_inicio, fecha_cierre, observaciones) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_estudiante, $id_modalidad, $id_cohorte, 
            $etapa_actual, $estado, $titulo_trabajo, 
            $fecha_inicio, $fecha_cierre, $observaciones
        ]);
    }

    // 7. ACTUALIZAR EXPEDIENTE EXISTENTE
    public function actualizar($id_expediente, $id_estudiante, $id_modalidad, $id_cohorte, $etapa_actual, $estado, $titulo_trabajo, $fecha_inicio, $fecha_cierre, $observaciones) {
        $sql = "UPDATE expedientes_mg 
                SET id_estudiante = ?, id_modalidad = ?, id_cohorte = ?, 
                    etapa_actual = ?, estado = ?, titulo_trabajo = ?, 
                    fecha_inicio = ?, fecha_cierre = ?, observaciones = ?
                WHERE id_expediente = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_estudiante, $id_modalidad, $id_cohorte, 
            $etapa_actual, $estado, $titulo_trabajo, 
            $fecha_inicio, $fecha_cierre, $observaciones, 
            $id_expediente
        ]);
    }

    // 8. ELIMINAR EXPEDIENTE FÍSICAMENTE
    public function eliminar($id_expediente) {
        $sql = "DELETE FROM expedientes_mg WHERE id_expediente = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_expediente]);
    }
}
?>