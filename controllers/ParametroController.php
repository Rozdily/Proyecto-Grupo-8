<?php
/**
 * ARCHIVO: controllers/ParametroController.php
 * Controlador para procesar la actualización masiva de reglas y parámetros del sistema.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/sistema/ParametroModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$parametroModel = new ParametroModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// URL base para la redirección SPA del Cajón 4
$url_base = "../views/admin/index.php?seccion=cajon4&tab=parametros";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // ACTUALIZAR MÚLTIPLES PARÁMETROS DESDE EL FORMULARIO
    // ------------------------------------------------------------------------
    case 'actualizar_multiples':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $parametros = $_POST['parametros'] ?? [];
            $id_usuario = $_SESSION['id_usuario'] ?? 1;

            if (empty($parametros) || !is_array($parametros)) {
                header("Location: $url_base&error=" . urlencode("No se recibieron datos de configuración válidos."));
                exit();
            }

            try {
                $parametroModel->actualizarMultiples($parametros, $id_usuario);
                header("Location: $url_base&exito=" . urlencode("Los parámetros y reglas del sistema han sido actualizados exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar la configuración: " . $e->getMessage()));
                exit();
            }
        }
        break;

    default:
        header("Location: $url_base");
        exit();
}