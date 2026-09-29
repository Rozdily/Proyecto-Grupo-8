<?php
/**
 * ARCHIVO: controllers/ParametroController.php
 * Controlador para gestionar las reglas de negocio y parámetros del sistema.
 */

require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario que ejecuta esto sea un Administrador (id_rol = 1)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/lo.php");
    exit();
}

$accion = $_POST['accion'] ?? '';
$id_admin = $_SESSION['id_usuario'] ?? 1;

// ============================================================================
// ACCIÓN: ACTUALIZAR MÚLTIPLES PARÁMETROS EN BLOQUE
// ============================================================================
if ($accion === 'actualizar_multiples') {
    
    $parametros = $_POST['parametros'] ?? [];

    if (empty($parametros) || !is_array($parametros)) {
        header("Location: ../views/admin/index.php?seccion=cajon4&tab=parametros&error=" . urlencode("No se enviaron datos para actualizar."));
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Utilizamos UPSERT (Insertar si no existe, actualizar si ya existe).
        // Esto garantiza que si agregas nuevas claves en el HTML, se creen solas en la BD.
        $sql = "INSERT INTO parametros_mg (clave, valor, descripcion, fuente, estado_evidencia, actualizado_por, fecha_actualizacion) 
                VALUES (?, ?, 'Regla configurada desde el Panel de Administración', 'Panel Admin', 'confirmado', ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor),
                actualizado_por = VALUES(actualizado_por),
                fecha_actualizacion = CURRENT_TIMESTAMP";
        
        $stmt = $pdo->prepare($sql);

        foreach ($parametros as $clave => $valor) {
            // Limpieza básica de seguridad
            $clave_limpia = strtoupper(trim($clave));
            $valor_limpio = trim($valor);
            
            // Ejecutar el UPSERT por cada parámetro que llega del formulario
            $stmt->execute([$clave_limpia, $valor_limpio, $id_admin]);
        }

        $pdo->commit();
        
        // Redirigir de vuelta al Cajón 4 con el mensaje de éxito
        header("Location: ../views/admin/index.php?seccion=cajon4&tab=parametros&exito=" . urlencode("Configuración y reglas guardadas correctamente."));
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: ../views/admin/index.php?seccion=cajon4&tab=parametros&error=" . urlencode("Fallo interno en BD al guardar configuración."));
        exit();
    }
}

// Si la acción no es reconocida
header("Location: ../views/admin/index.php?seccion=cajon4&error=" . urlencode("Acción no válida."));
exit();