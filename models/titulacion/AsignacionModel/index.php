<?php
/**
 * MÓDULO 4: models/titulacion/AsignacionModel/AsignacionModel.php
 * Administra el amarre crítico entre el estudiante (expediente) y su Tutor de Grado,
 * conservando el historial de asignaciones y gestionando los informes de avance.
 */
class AsignacionModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: EMPAREJAR ALUMNO Y TUTOR (Asignación inicial o nueva)
    public function emparejarAlumnoTutor($id_expediente, $id_tutor, $referencia_decanatura, $numero_carta, $observaciones, $registrado_por) {
        // Toda nueva asignación nace obligatoriamente en estado 'vigente'
        $sql = "INSERT INTO asignaciones_tutor (id_expediente, id_tutor, fecha_asignacion, estado, referencia_decanatura, numero_carta, observaciones, registrado_por) 
                VALUES (?, ?, CURDATE(), 'vigente', ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_expediente,
            $id_tutor,
            $referencia_decanatura,
            $numero_carta,
            $observaciones,
            $registrado_por
        ]);
    }

    // 2. OBTENER LA ASIGNACIÓN VIGENTE DE UN EXPEDIENTE (Mega Cruce)
    public function obtenerAsignacionVigente($id_expediente) {
        // Aplicamos el alias 'nombre_grupo_tesis' para aislar la palabra prohibida del front-end
        $sql = "SELECT a.id_asignacion, a.fecha_asignacion, a.referencia_decanatura, a.numero_carta,
                       t.id_tutor, ut.nombre AS tutor_nombre, ut.apellido AS tutor_apellido, ut.correo AS tutor_correo,
                       ex.titulo_trabajo,
                       c.nombre AS nombre_grupo_tesis
                FROM asignaciones_tutor a
                INNER JOIN tutores t ON a.id_tutor = t.id_tutor
                INNER JOIN usuarios ut ON t.id_usuario = ut.id_usuario
                INNER JOIN expedientes_mg ex ON a.id_expediente = ex.id_expediente
                INNER JOIN cohortes_mg c ON ex.id_cohorte = c.id_cohorte
                WHERE a.id_expediente = ? AND a.estado = 'vigente'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_expediente]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. MÉTODO DE NEGOCIO: CERRAR O REEMPLAZAR ASIGNACIÓN (Historial inmutable)
    public function cerrarAsignacion($id_asignacion, $nuevo_estado, $motivo_fin) {
        // Los estados válidos de cierre son 'finalizada' (terminó con éxito) o 'reemplazada' (cambio de tutor)
        $sql = "UPDATE asignaciones_tutor 
                SET estado = ?, fecha_fin = CURDATE(), motivo_fin = ? 
                WHERE id_asignacion = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $nuevo_estado,
            $motivo_fin,
            $id_asignacion
        ]);
    }

    // 4. LISTAR HISTORIAL COMPLETO DE TUTORES DE UN EXPEDIENTE
    public function listarHistorialTutores($id_expediente) {
        $sql = "SELECT a.id_asignacion, a.fecha_asignacion, a.fecha_fin, a.estado, a.motivo_fin, a.referencia_decanatura,
                       ut.nombre AS tutor_nombre, ut.apellido AS tutor_apellido
                FROM asignaciones_tutor a
                INNER JOIN tutores t ON a.id_tutor = t.id_tutor
                INNER JOIN usuarios ut ON t.id_usuario = ut.id_usuario
                WHERE a.id_expediente = ?
                ORDER BY a.fecha_asignacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_expediente]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. MÉTODO DE NEGOCIO: SUBIR INFORME MENSUAL DE AVANCE
    public function subirInformeMensual($id_expediente, $id_hito, $porcentaje_avance, $fecha_presentacion, $formato, $respaldo_fisico, $observaciones, $registrado_por) {
        $sql = "INSERT INTO informes_avance (id_expediente, id_hito, porcentaje_avance, fecha_presentacion, formato, respaldo_fisico, observaciones, registrado_por) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_expediente,
            $id_hito,
            $porcentaje_avance,
            $fecha_presentacion,
            $formato,
            $respaldo_fisico,
            $observaciones,
            $registrado_por
        ]);
    }
}
?>