<?php
/**
 * MÓDULO 4: models/titulacion/ExpedienteModel/ExpedienteModel.php
 * Administra la tabla 'expedientes_mg', el historial de avances ('expediente_etapas')
 * y aplica la lógica de negocio para gestionar el ciclo de vida completo de una tesis.
 */
class ExpedienteModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. ABRIR EXPEDIENTE (CREATE)
    public function abrirExpediente($id_estudiante, $id_modalidad, $id_grupo_tesis, $titulo_trabajo, $fecha_inicio, $observaciones) {
        // Por defecto, la etapa nace en 'previa' y el estado en 'activo'
        $sql = "INSERT INTO expedientes_mg (id_estudiante, id_modalidad, id_cohorte, etapa_actual, estado, titulo_trabajo, fecha_inicio, observaciones) 
                VALUES (?, ?, ?, 'previa', 'activo', ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_estudiante, 
            $id_modalidad, 
            $id_grupo_tesis, // Físicamente se mapea al campo id_cohorte
            $titulo_trabajo, 
            $fecha_inicio, 
            $observaciones
        ]);
    }

    // 2. LEER TODOS CON MEGA CRUCE (READ - INNER JOINs Múltiples)
    public function obtenerTodos() {
        // Regla de Vocabulario Aplicada: El alias 'nombre_grupo_tesis' bloquea la fuga de la palabra prohibida hacia el JS
        $sql = "SELECT ex.id_expediente, ex.etapa_actual, ex.estado, ex.titulo_trabajo, ex.fecha_inicio,
                       u.nombre, u.apellido, e.registro_universitario,
                       m.nombre AS nombre_modalidad,
                       c.nombre AS nombre_grupo_tesis 
                FROM expedientes_mg ex
                INNER JOIN estudiantes e ON ex.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN modalidades_grado m ON ex.id_modalidad = m.id_modalidad
                INNER JOIN cohortes_mg c ON ex.id_cohorte = c.id_cohorte
                ORDER BY ex.fecha_inicio DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. OBTENER UN EXPEDIENTE POR ID
    public function obtenerPorId($id_expediente) {
        $sql = "SELECT id_expediente, id_estudiante, id_modalidad, id_cohorte AS id_grupo_tesis, 
                       etapa_actual, estado, titulo_trabajo, fecha_inicio, fecha_cierre, observaciones 
                FROM expedientes_mg 
                WHERE id_expediente = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_expediente]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. ACTUALIZAR EXPEDIENTE (Datos generales)
    public function actualizar($id_expediente, $titulo_trabajo, $estado, $observaciones) {
        $sql = "UPDATE expedientes_mg 
                SET titulo_trabajo = ?, estado = ?, observaciones = ? 
                WHERE id_expediente = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $titulo_trabajo, 
            $estado, 
            $observaciones, 
            $id_expediente
        ]);
    }

    // 5. MÉTODO DE NEGOCIO: PROMOCIONAR ETAPA (De MG1 a MG2)
    public function promocionarEtapa($id_expediente, $nueva_etapa, $resultado_etapa_anterior, $id_usuario_registra) {
        // A. Actualizar el registro maestro del expediente
        $sqlUpdate = "UPDATE expedientes_mg SET etapa_actual = ? WHERE id_expediente = ?";
        $stmtUpdate = $this->pdo->prepare($sqlUpdate);
        $exitoUpdate = $stmtUpdate->execute([$nueva_etapa, $id_expediente]);

        // B. Si la matriz cambia, insertar la huella histórica del salto en expediente_etapas
        if ($exitoUpdate) {
            $sqlInsert = "INSERT INTO expediente_etapas (id_expediente, etapa, fecha_inicio, resultado, registrado_por) 
                          VALUES (?, ?, NOW(), ?, ?)";
            
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            return $stmtInsert->execute([
                $id_expediente, 
                $nueva_etapa, 
                $resultado_etapa_anterior, 
                $id_usuario_registra
            ]);
        }
        
        return false;
    }

    // 6. MÉTODO DE NEGOCIO: BUSCADOR PREDICTIVO SPA (Cajón 2)
    public function buscarPorTextoPredictivo($textoBusqueda) {
        // Envolvemos el término en comodines '%' para buscar coincidencias parciales
        $busqueda = "%" . $textoBusqueda . "%";
        
        $sql = "SELECT ex.id_expediente, ex.etapa_actual, ex.estado, 
                       u.nombre, u.apellido, e.registro_universitario,
                       c.nombre AS nombre_grupo_tesis
                FROM expedientes_mg ex
                INNER JOIN estudiantes e ON ex.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN cohortes_mg c ON ex.id_cohorte = c.id_cohorte
                WHERE e.registro_universitario LIKE ? 
                   OR u.nombre LIKE ? 
                   OR u.apellido LIKE ?
                ORDER BY u.apellido ASC";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Ejecutamos pasando la misma variable a los tres interrogantes lógicos
        $stmt->execute([$busqueda, $busqueda, $busqueda]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>