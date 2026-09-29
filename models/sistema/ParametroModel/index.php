<?php
/**
 * ARCHIVO: models/sistema/ParametroModel/index.php
 * Modelo para gestionar la tabla 'parametros_mg' (Configuraciones y Reglas del Sistema).
 */
class ParametroModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS PARÁMETROS COMO UN DICCIONARIO
    // Devuelve un formato ['CLAVE' => 'VALOR'] para inyectar fácilmente en la vista
    public function listarTodosAsociativos() {
        $sql = "SELECT clave, valor FROM parametros_mg";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $parametros = [];
        foreach ($resultados as $fila) {
            $parametros[$fila['clave']] = $fila['valor'];
        }
        
        return $parametros;
    }

    // 2. ACTUALIZAR MÚLTIPLES PARÁMETROS EN BLOQUE (CON TRANSACCIÓN Y UPSERT)
    public function actualizarMultiples($parametrosArray, $id_usuario) {
        // Usamos INSERT ... ON DUPLICATE KEY UPDATE para asegurar que si la regla 
        // se borró accidentalmente o es nueva, el sistema la regenere sola.
        $sql = "INSERT INTO parametros_mg 
                (clave, valor, descripcion, fuente, estado_evidencia, actualizado_por, fecha_actualizacion) 
                VALUES (?, ?, 'Regla operativa del sistema', 'interfaz_admin', 'confirmado', ?, NOW())
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor), 
                actualizado_por = VALUES(actualizado_por), 
                fecha_actualizacion = NOW()";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Iniciamos la transacción para asegurar que o se guardan todos o no se guarda ninguno
        $this->pdo->beginTransaction();
        
        try {
            foreach ($parametrosArray as $clave => $valor) {
                // Solo procesamos claves válidas y no vacías
                if (!empty($clave) && $valor !== '') {
                    $stmt->execute([$clave, $valor, $id_usuario]);
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>