<?php
/**
 * MÓDULO 5: models/sistema/AlertaModel/AlertaModel.php
 * Administra la mensajería interna (Cajón 0) y el panel de contingencias.
 * Controla qué alertas están activas y registra la mitigación (resolución) de las mismas.
 */
class AlertaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. MÉTODO DE NEGOCIO: RENDERIZAR CAMPANA CAJÓN 0 (Desplegar Dropdown Flotante)
    // Extrae las notificaciones "no leídas" de un usuario específico para alimentar el icono de la campana en el Navbar.
    public function obtenerAlertasActivasUsuario($id_usuario) {
        $sql = "SELECT id_notificacion, tipo, text_mensaje, url_enlace, leida, fecha_creacion 
                FROM notificaciones 
                WHERE id_usuario = ? AND leida = 0 
                ORDER BY fecha_creacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_usuario]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. CREAR NOTIFICACIÓN (Ej. Sistema detecta un desborde o falta de jurado)
    public function crearNotificacion($id_usuario, $tipo, $text_mensaje, $url_enlace) {
        // La alerta nace por defecto como "no leída" (leida = 0)
        $sql = "INSERT INTO notificaciones (id_usuario, tipo, text_mensaje, url_enlace, leida) 
                VALUES (?, ?, ?, ?, 0)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $id_usuario, 
            $tipo, 
            $text_mensaje, 
            $url_enlace
        ]);
    }

    // 3. MARCAR NOTIFICACIÓN COMO LEÍDA (Desaparece del Dropdown Flotante)
    public function marcarNotificacionLeida($id_notificacion) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id_notificacion]);
    }

    // 4. MÉTODO DE NEGOCIO: MITIGAR ALERTA CRÍTICA (Resolución de Contingencia)
    // Cuando el Coordinador resuelve un conflicto (Ej. Cambia el tope de alumnos), el sistema registra su nota de solución.
    public function mitigarAlerta($tipo_alerta, $id_referencia, $atendida_por, $nota) {
        $sql = "INSERT INTO alertas_atendidas (tipo_alerta, id_referencia, atendida_por, nota, fecha) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $tipo_alerta,
            $id_referencia,
            $atendida_por,
            $nota
        ]);
    }

    // 5. LISTAR ALERTAS ATENDIDAS (Para el panel de reportes de coordinación - Mega Cruce)
    public function listarAlertasAtendidas() {
        $sql = "SELECT a.id, a.tipo_alerta, a.id_referencia, a.nota, a.fecha,
                       u.nombre AS resolutor_nombre, u.apellido AS resolutor_apellido, u.correo
                FROM alertas_atendidas a
                INNER JOIN usuarios u ON a.atendida_por = u.id_usuario
                ORDER BY a.fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>