<?php
/**
 * MÓDULO 5: models/sistema/ParametroModel/ParametroModel.php
 * Administra el CRUD de la tabla 'parametros_mg', gestionando las reglas de negocio,
 * topes de horas, límites de alumnos y registrando quién altera la configuración del sistema.
 */
class ParametroModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODOS LOS PARÁMETROS (Con INNER JOIN para ver quién actualizó)
    public function listarTodos() {
        $sql = "SELECT p.clave, p.valor, p.descripcion, p.fuente, p.estado_evidencia, 
                       p.actualizado_por, p.fecha_actualizacion,
                       u.nombre AS admin_nombre, u.apellido AS admin_apellido
                FROM parametros_mg p
                LEFT JOIN usuarios u ON p.actualizado_por = u.id_usuario
                ORDER BY p.clave ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. MÉTODO DE NEGOCIO: LEER PARÁMETRO INDIVIDUAL (Backend Reader)
    // Extrae el valor exacto de una regla (ej. 'dias_anticipacion_tribunal') para validaciones en los Controladores.
    public function obtenerValorParametro($clave) {
        $sql = "SELECT valor 
                FROM parametros_mg 
                WHERE clave = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clave]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado['valor'] : null;
    }

    // 3. OBTENER DETALLE COMPLETO DE UN PARÁMETRO
    public function obtenerPorClave($clave) {
        $sql = "SELECT clave, valor, descripcion, fuente, estado_evidencia, actualizado_por, fecha_actualizacion 
                FROM parametros_mg 
                WHERE clave = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clave]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. MÉTODO DE NEGOCIO: GUARDAR TOPES NUMÉRICOS (UPDATE)
    // Actualiza el valor de la regla y registra qué usuario administrador hizo el cambio.
    public function guardarTopesNumericos($clave, $nuevo_valor, $id_usuario_admin) {
        $sql = "UPDATE parametros_mg 
                SET valor = ?, actualizado_por = ?, fecha_actualizacion = CURRENT_TIMESTAMP 
                WHERE clave = ?";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $nuevo_valor, 
            $id_usuario_admin, 
            $clave
        ]);
    }

    // 5. CREAR NUEVO PARÁMETRO (Configuración inicial)
    public function crear($clave, $valor, $descripcion, $fuente, $estado_evidencia, $creado_por) {
        $sql = "INSERT INTO parametros_mg (clave, valor, descripcion, fuente, estado_evidencia, actualizado_por) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $clave,
            $valor,
            $descripcion,
            $fuente,
            $estado_evidencia,
            $creado_por
        ]);
    }
}
?>