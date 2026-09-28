<?php
/**
 * MÓDULO 5: models/sistema/AuditoriaModel/AuditoriaModel.php
 * Administra la bitácora policial del sistema ('bitacora_mg').
 * Funciona como una caja negra inmutable: registra el rastro de "quién, cuándo, desde dónde y qué cambió".
 */
class AuditoriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: REGISTRAR LOG INMUTABLE JSON (CREATE)
    // Inserta un registro de auditoría. Los arrays PHP deben ser convertidos a JSON (json_encode) antes de pasarse aquí.
    public function registrarLogInmutableJSON($usuario, $accion, $tabla, $id_registro, $datos_antes, $datos_despues, $ip) {
        $sql = "INSERT INTO bitacora_mg (usuario, accion, tabla, id_registro, datos_antes, datos_despues, ip, fecha) 
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $usuario,
            $accion,
            $tabla,
            $id_registro,
            $datos_antes,   // String en formato JSON
            $datos_despues, // String en formato JSON
            $ip
        ]);
    }

    // 2. LISTAR BITÁCORA COMPLETA (READ)
    public function listarBitacoraCompleta() {
        $sql = "SELECT id, usuario, accion, tabla, id_registro, datos_antes, datos_despues, ip, fecha 
                FROM bitacora_mg 
                ORDER BY fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. MÉTODO DE NEGOCIO: BUSCAR LOG POR USUARIO
    public function buscarLogPorUsuario($usuario_buscado) {
        // Envolvemos la búsqueda en comodines para coincidencias parciales del nombre de cuenta
        $busqueda = "%" . $usuario_buscado . "%";
        
        $sql = "SELECT id, usuario, accion, tabla, id_registro, ip, fecha 
                FROM bitacora_mg 
                WHERE usuario LIKE ? 
                ORDER BY fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$busqueda]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. BUSCAR LOGS POR TABLA AFECTADA (Filtro útil para el Administrador)
    public function buscarLogPorTabla($nombre_tabla) {
        $sql = "SELECT id, usuario, accion, id_registro, datos_antes, datos_despues, ip, fecha 
                FROM bitacora_mg 
                WHERE tabla = ? 
                ORDER BY fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombre_tabla]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================================================
    // ATENCIÓN: Por reglas de auditoría y seguridad arquitectónica, 
    // ESTA CLASE NO TIENE MÉTODOS actualizar() NI eliminar(). 
    // LA BITÁCORA ES ESTRICTAMENTE DE SOLO LECTURA E INSERCIÓN.
    // =====================================================================
}
?>