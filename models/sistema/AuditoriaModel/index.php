<?php
/**
 * ARCHIVO: models/sistema/AuditoriaModel/index.php
 * Modelo de la "Caja Negra" inmutable (Tabla: bitacora_mg).
 * Gestiona la lectura y escritura de toda la auditoría del sistema.
 */
class AuditoriaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // ========================================================================
    // 1. LECTURA (Usado por el Cajón 5 para mostrar la tabla)
    // ========================================================================
    public function listarHistorialCompleto($limite = 500) {
        // Usamos COALESCE para evitar valores nulos y TRIM para asegurar la relación exacta
        $sql = "SELECT b.id, b.usuario, 
                       COALESCE(u.correo, 'sistema@upds.edu.bo') AS correo, 
                       b.accion, b.tabla, b.id_registro, 
                       b.datos_antes, b.datos_despues, b.ip, b.fecha 
                FROM bitacora_mg b
                LEFT JOIN usuarios u ON TRIM(b.usuario) = TRIM(u.usuario)
                ORDER BY b.fecha DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        // Se bindea como entero para la cláusula LIMIT
        $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // 2. ESCRITURA INMUTABLE (Usado por los demás Controladores del sistema)
    // ========================================================================
    public function registrarAccion($usuario, $accion, $tabla_afectada, $id_registro, $datos_antes = [], $datos_despues = []) {
        
        // Convertimos los arrays de PHP a formato JSON puro para la BD
        $json_antes = json_encode($datos_antes, JSON_UNESCAPED_UNICODE);
        $json_despues = json_encode($datos_despues, JSON_UNESCAPED_UNICODE);
        
        // Capturar la IP real del usuario (incluyendo proxies si existen)
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // Se inserta en la BD utilizando NOW() para la fecha y hora exactas del servidor
        $sql = "INSERT INTO bitacora_mg 
                (usuario, accion, tabla, id_registro, datos_antes, datos_despues, ip, fecha) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $usuario, 
            $accion, 
            $tabla_afectada, 
            $id_registro, 
            $json_antes, 
            $json_despues, 
            $ip
        ]);
    }
}
?>