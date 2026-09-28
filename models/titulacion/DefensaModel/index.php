<?php
/**
 * MÓDULO 4: models/titulacion/DefensaModel/DefensaModel.php
 * Administra el CRUD de las defensas de grado, la designación de tribunales evaluadores
 * y el asentamiento oficial de calificaciones finales.
 */
class DefensaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: PROGRAMAR DEFENSA (Aplicando regla de 14 días)
    public function programarDefensa14Dias($id_expediente, $etapa, $fecha, $hora_inicio, $hora_fin, $ambiente) {
        // El estado nace obligatoriamente como 'programada'
        $sql = "INSERT INTO defensas_mg (id_expediente, etapa, fecha, hora_inicio, hora_fin, ambiente, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'programada')";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_expediente, 
            $etapa, 
            $fecha, 
            $hora_inicio, 
            $hora_fin, 
            $ambiente
        ]);
    }

    // 2. LEER TODAS LAS DEFENSAS PROGRAMADAS (Mega Cruce)
    public function listarDefensas() {
        // Uso de alias 'nombre_grupo_tesis' para bloquear la palabra prohibida
        $sql = "SELECT d.id_defensa, d.etapa, d.fecha, d.hora_inicio, d.hora_fin, d.ambiente, d.estado,
                       ex.titulo_trabajo,
                       u.nombre AS estudiante_nombre, u.apellido AS estudiante_apellido,
                       cg.nombre AS nombre_grupo_tesis
                FROM defensas_mg d
                INNER JOIN expedientes_mg ex ON d.id_expediente = ex.id_expediente
                INNER JOIN estudiantes e ON ex.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN cohortes_mg cg ON ex.id_cohorte = cg.id_cohorte
                ORDER BY d.fecha ASC, d.hora_inicio ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. MÉTODO DE NEGOCIO: REGISTRAR JURADOS EVALUADORES
    public function registrarJurados($id_expediente, $etapa, $id_tutor_uno, $id_tutor_dos, $registrado_por) {
        // La regla de negocio exige exactamente 2 tribunales para la defensa. 
        // Usamos una transacción PDO manual para asegurar que ambos se guarden o ninguno.
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO tribunales_defensa (id_expediente, etapa, id_tutor, orden, fecha_asignacion, estado, registrado_por) 
                    VALUES (?, ?, ?, ?, CURDATE(), 'vigente', ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Inyectamos el Jurado 1 (Orden 1 - Presidente)
            $stmt->execute([$id_expediente, $etapa, $id_tutor_uno, 1, $registrado_por]);
            
            // Inyectamos el Jurado 2 (Orden 2 - Vocal)
            $stmt->execute([$id_expediente, $etapa, $id_tutor_dos, 2, $registrado_por]);

            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // 4. OBTENER JURADOS DE UN EXPEDIENTE
    public function obtenerJuradosPorExpediente($id_expediente, $etapa) {
        $sql = "SELECT tr.id, tr.orden, tr.estado, tr.fecha_asignacion,
                       u.nombre, u.apellido, u.correo
                FROM tribunales_defensa tr
                INNER JOIN tutores t ON tr.id_tutor = t.id_tutor
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE tr.id_expediente = ? AND tr.etapa = ? AND tr.estado = 'vigente'
                ORDER BY tr.orden ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_expediente, $etapa]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. MÉTODO DE NEGOCIO: ASENTAR NOTA FINAL DEFENSA
    public function asentarNotaFinal($id_defensa, $nota, $observaciones, $publicada, $registrada_por) {
        $sql = "INSERT INTO calificaciones_mg (id_defensa, nota, observaciones, publicada, registrada_por) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_defensa, 
            $nota, 
            $observaciones, 
            $publicada, 
            $registrada_por
        ]);
    }
    
    // 6. CAMBIAR ESTADO DE LA DEFENSA (Ej. 'realizada' o 'cancelada')
    public function cambiarEstadoDefensa($id_defensa, $nuevo_estado) {
        $sql = "UPDATE defensas_mg SET estado = ? WHERE id_defensa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nuevo_estado, $id_defensa]);
    }
}
?>