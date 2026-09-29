<?php
/**
 * ARCHIVO: models/titulacion/DefensaModel/index.php
 * Modelo para gestionar el CRUD de la tabla 'defensas_mg' (Programación de Defensas).
 * Incluye cruces relacionales con expedientes y estudiantes.
 */
class DefensaModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LISTAR TODAS LAS DEFENSAS (CON INNER JOINS A EXPEDIENTES Y ESTUDIANTES)
    public function listarTodas() {
        $sql = "SELECT d.id_defensa, d.id_expediente, d.etapa, d.fecha, 
                       d.hora_inicio, d.hora_fin, d.ambiente, d.estado, 
                       d.obs_fondo, d.obs_forma,
                       e.titulo_trabajo,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_estudiante
                FROM defensas_mg d
                INNER JOIN expedientes_mg e ON d.id_expediente = e.id_expediente
                INNER JOIN estudiantes est ON e.id_estudiante = est.id_estudiante
                INNER JOIN usuarios u ON est.id_usuario = u.id_usuario
                ORDER BY d.fecha DESC, d.hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UNA DEFENSA POR ID
    public function obtenerPorId($id_defensa) {
        $sql = "SELECT * FROM defensas_mg WHERE id_defensa = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_defensa]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. OBTENER LISTA DE EXPEDIENTES PARA EL FORMULARIO
    public function obtenerExpedientesParaFormulario() {
        $sql = "SELECT e.id_expediente, e.titulo_trabajo,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_estudiante
                FROM expedientes_mg e
                INNER JOIN estudiantes est ON e.id_estudiante = est.id_estudiante
                INNER JOIN usuarios u ON est.id_usuario = u.id_usuario
                WHERE e.estado = 'activo'
                ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. CREAR NUEVA PROGRAMACIÓN DE DEFENSA
    public function crear($id_expediente, $etapa, $fecha, $hora_inicio, $hora_fin, $ambiente, $estado, $obs_fondo, $obs_forma) {
        $sql = "INSERT INTO defensas_mg 
                (id_expediente, etapa, fecha, hora_inicio, hora_fin, ambiente, estado, obs_fondo, obs_forma) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_expediente, $etapa, $fecha, $hora_inicio, 
            $hora_fin, $ambiente, $estado, $obs_fondo, $obs_forma
        ]);
    }

    // 5. ACTUALIZAR DEFENSA EXISTENTE
    public function actualizar($id_defensa, $id_expediente, $etapa, $fecha, $hora_inicio, $hora_fin, $ambiente, $estado, $obs_fondo, $obs_forma) {
        $sql = "UPDATE defensas_mg 
                SET id_expediente = ?, etapa = ?, fecha = ?, hora_inicio = ?, 
                    hora_fin = ?, ambiente = ?, estado = ?, obs_fondo = ?, obs_forma = ?
                WHERE id_defensa = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $id_expediente, $etapa, $fecha, $hora_inicio, 
            $hora_fin, $ambiente, $estado, $obs_fondo, $obs_forma, 
            $id_defensa
        ]);
    }

    // 6. ELIMINAR DEFENSA FÍSICAMENTE
    public function eliminar($id_defensa) {
        $sql = "DELETE FROM defensas_mg WHERE id_defensa = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$id_defensa]);
    }
}
?>