<?php
/**
 * MÓDULO 6: models/importaciones/ImportacionModel/ImportacionModel.php
 * Administra la cabecera y el detalle de la sincronización de archivos CSV desde SATS.
 * Da soporte a la previsualización interactiva en caliente (colores) y la inyección en bloque.
 */
class ImportacionModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: REGISTRAR CABECERA DE IMPORTACIÓN
    // Crea el registro principal del lote. Devuelve el ID generado para enlazar los detalles.
    public function registrarCabeceraImportacion($archivo_nombre, $total_filas, $filas_exito, $filas_error, $ejecutado_por) {
        $sql = "INSERT INTO importaciones_mg (archivo_nombre, total_filas, filas_exito, filas_error, ejecutado_por, fecha_importacion) 
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $archivo_nombre, 
            $total_filas, 
            $filas_exito, 
            $filas_error, 
            $ejecutado_por
        ]);
        
        // Retorna el ID autoincremental recién insertado (Vital para el detalle)
        return $this->pdo->lastInsertId();
    }

    // 2. MÉTODO DE NEGOCIO: INYECCIÓN DE DETALLE POR FILA (Carga en Bloque)
    // Registra línea por línea si la importación fue 'exito', 'advertencia' o 'error'.
    public function registrarDetalleFila($id_importacion, $fila_numero, $resultado, $mensaje_error, $datos_fila_json) {
        $sql = "INSERT INTO importaciones_mg_detalle (id_importacion, fila_numero, resultado, mensaje_error, datos_fila) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_importacion, 
            $fila_numero, 
            $resultado, 
            $mensaje_error, 
            $datos_fila_json
        ]);
    }

    // 3. MÉTODO DE NEGOCIO: MOTOR DE CLASIFICACIÓN DINÁMICA (Para Frontend SPA)
    // Lee un Registro Universitario (RU) desde el CSV y verifica en la BD para decidir 
    // si el JavaScript debe pintar la fila de Verde (OK), Amarillo (Falta cuenta) o Rojo (Error crítico).
    public function validarAlumnoSATS($registro_universitario) {
        $sql = "SELECT e.id_estudiante, e.semestre, u.estado 
                FROM estudiantes e
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                WHERE e.registro_universitario = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$registro_universitario]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. LISTAR HISTORIAL DE IMPORTACIONES MASIVAS (Mega Cruce)
    public function listarHistorialImportaciones() {
        $sql = "SELECT i.id_importacion, i.archivo_nombre, i.total_filas, i.filas_exito, i.filas_error, i.fecha_importacion,
                       u.nombre AS admin_nombre, u.apellido AS admin_apellido
                FROM importaciones_mg i
                INNER JOIN usuarios u ON i.ejecutado_por = u.id_usuario
                ORDER BY i.fecha_importacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. OBTENER DETALLE DE UNA IMPORTACIÓN ESPECÍFICA (Auditoría de Errores)
    public function obtenerDetalleImportacion($id_importacion) {
        $sql = "SELECT id, fila_numero, resultado, mensaje_error, datos_fila 
                FROM importaciones_mg_detalle 
                WHERE id_importacion = ?
                ORDER BY fila_numero ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_importacion]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>