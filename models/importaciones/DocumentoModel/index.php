<?php
/**
 * MÓDULO 6: models/importaciones/DocumentoModel/DocumentoModel.php
 * Administra el motor de plantillas HTML, el generador de actas (PDF/Word),
 * y el control transaccional de numeración correlativa anual para documentos oficiales.
 */
class DocumentoModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: CARGAR PLANTILLA ACTIVA (Ej. cargarPlantillaWord o HTML)
    // Extrae el cuerpo HTML con las variables {{etiquetas}} listas para ser reemplazadas por el Controlador.
    public function obtenerPlantillaActiva($codigo_plantilla) {
        $sql = "SELECT id_plantilla, codigo, nombre, cuerpo_html, version 
                FROM plantillas_documento 
                WHERE codigo = ? AND activa = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$codigo_plantilla]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2. MÉTODO DE NEGOCIO: OBTENER SIGUIENTE CORRELATIVO SEGURO (Transacción)
    // Lee el último número de acta/carta de este año, lo incrementa en +1 y lo actualiza bloqueando la fila.
    public function obtenerSiguienteCorrelativo($tipo, $anio) {
        try {
            $this->pdo->beginTransaction();

            // Verificar si ya existe un contador para este tipo y año
            $sqlCheck = "SELECT ultimo_numero FROM contadores_documento WHERE tipo = ? AND anio = ? FOR UPDATE";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([$tipo, $anio]);
            $resultado = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                $nuevo_numero = $resultado['ultimo_numero'] + 1;
                $sqlUpdate = "UPDATE contadores_documento SET ultimo_numero = ? WHERE tipo = ? AND anio = ?";
                $stmtUpdate = $this->pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([$nuevo_numero, $tipo, $anio]);
            } else {
                // Es el primer documento del año
                $nuevo_numero = 1;
                $sqlInsert = "INSERT INTO contadores_documento (tipo, anio, ultimo_numero) VALUES (?, ?, ?)";
                $stmtInsert = $this->pdo->prepare($sqlInsert);
                $stmtInsert->execute([$tipo, $anio, $nuevo_numero]);
            }

            $this->pdo->commit();
            return $nuevo_numero;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // 3. MÉTODO DE NEGOCIO: GENERAR ACTA O CARTA (Guardar Snapshot)
    // Guarda el HTML final ya procesado para futuras reimpresiones exactas.
    public function guardarDocumentoGenerado($id_plantilla, $tipo, $id_expediente, $destinatario, $numero_correlativo, $contenido_snapshot, $generado_por) {
        $sql = "INSERT INTO documentos_generados (id_plantilla, tipo, id_expediente, destinatario, numero_correlativo, contenido_snapshot, generado_por, fecha_generacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_plantilla,
            $tipo,
            $id_expediente,
            $destinatario,
            $numero_correlativo,
            $contenido_snapshot,
            $generado_por
        ]);
    }

    // 4. LISTAR DOCUMENTOS DE UN EXPEDIENTE (Mega Cruce para el historial del alumno)
    public function listarDocumentosPorExpediente($id_expediente) {
        $sql = "SELECT dg.id, dg.tipo, dg.destinatario, dg.numero_correlativo, dg.fecha_generacion,
                       pd.nombre AS nombre_plantilla,
                       u.nombre AS emisor_nombre, u.apellido AS emisor_apellido
                FROM documentos_generados dg
                INNER JOIN plantillas_documento pd ON dg.id_plantilla = pd.id_plantilla
                INNER JOIN usuarios u ON dg.generado_por = u.id_usuario
                WHERE dg.id_expediente = ?
                ORDER BY dg.fecha_generacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_expediente]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 5. OBTENER UN SNAPSHOT PARA REIMPRESIÓN (Lectura inmutable)
    public function obtenerSnapshotDocumento($id_documento_generado) {
        $sql = "SELECT contenido_snapshot, numero_correlativo, tipo, fecha_generacion 
                FROM documentos_generados 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_documento_generado]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>