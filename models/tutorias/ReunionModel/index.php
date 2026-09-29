<?php
/**
 * ARCHIVO: models/tutorias/ReunionModel/index.php
 * Modelo adaptado a la estructura real de la BD 'db_tutorias_upds'.
 * Puente SQL corregido: asignaciones_tutor -> expedientes_mg -> estudiantes -> usuarios.
 */
class ReunionModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODAS LAS REUNIONES
    public function listarTodas() {
        $sql = "SELECT r.id_reunion, r.id_asignacion, r.temas, r.fecha, 
                       r.hora_inicio, r.hora_fin, r.modalidad, r.lugar_o_enlace, r.estado_validacion,
                       CONCAT(ut.nombre, ' ', ut.apellido) AS nombre_tutor,
                       CONCAT(ue.nombre, ' ', ue.apellido) AS nombre_estudiante
                FROM reuniones_mg r
                INNER JOIN asignaciones_tutor a ON r.id_asignacion = a.id_asignacion
                INNER JOIN usuarios ut ON a.id_tutor = ut.id_usuario
                INNER JOIN expedientes_mg ex ON a.id_expediente = ex.id_expediente
                INNER JOIN estudiantes e ON ex.id_estudiante = e.id_estudiante
                INNER JOIN usuarios ue ON e.id_usuario = ue.id_usuario
                ORDER BY r.fecha DESC, r.hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER LISTA DE ASIGNACIONES
    public function obtenerAsignacionesParaFormulario() {
        $sql = "SELECT a.id_asignacion,
                       CONCAT(ut.nombre, ' ', ut.apellido) AS nombre_tutor,
                       CONCAT(ue.nombre, ' ', ue.apellido) AS nombre_estudiante
                FROM asignaciones_tutor a
                INNER JOIN usuarios ut ON a.id_tutor = ut.id_usuario
                INNER JOIN expedientes_mg ex ON a.id_expediente = ex.id_expediente
                INNER JOIN estudiantes e ON ex.id_estudiante = e.id_estudiante
                INNER JOIN usuarios ue ON e.id_usuario = ue.id_usuario
                WHERE a.estado = 'vigente'
                ORDER BY ut.apellido ASC, ue.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. CREAR NUEVA REUNIÓN
    public function crear($id_asignacion, $temas, $fecha, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado_validacion) {
        // Se inyectan campos vacíos/default para cumplir con las reglas NOT NULL de tu BD
        $sql = "INSERT INTO reuniones_mg 
                (id_asignacion, temas, fecha, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado_validacion, avance_sesion, observaciones, fecha_validacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, '', '', NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_asignacion, $temas, $fecha, $hora_inicio, 
            $hora_fin, $modalidad, $lugar_o_enlace, $estado_validacion
        ]);
    }

    // 4. ACTUALIZAR REUNIÓN EXISTENTE
    public function actualizar($id_reunion, $id_asignacion, $temas, $fecha, $hora_inicio, $hora_fin, $modalidad, $lugar_o_enlace, $estado_validacion) {
        $sql = "UPDATE reuniones_mg 
                SET id_asignacion = ?, temas = ?, fecha = ?, hora_inicio = ?, 
                    hora_fin = ?, modalidad = ?, lugar_o_enlace = ?, estado_validacion = ?
                WHERE id_reunion = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_asignacion, $temas, $fecha, $hora_inicio, 
            $hora_fin, $modalidad, $lugar_o_enlace, $estado_validacion, 
            $id_reunion
        ]);
    }

    // 5. ELIMINAR REUNIÓN FÍSICAMENTE
    public function eliminar($id_reunion) {
        $sql = "DELETE FROM reuniones_mg WHERE id_reunion = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_reunion]);
    }
}
?>