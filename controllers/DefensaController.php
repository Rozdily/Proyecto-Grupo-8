<?php
/**
 * ARCHIVO: controllers/DefensaController.php
 * Controlador para procesar las acciones CRUD de Programación de Defensas (Cajón 3).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/titulacion/DefensaModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$defensaModel = new DefensaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta a la pestaña "Programación de Defensas")
$url_base = "../views/admin/index.php?seccion=cajon3&tab=defensas";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVA PROGRAMACIÓN DE DEFENSA
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_expediente = $_POST['id_expediente'] ?? null;
            $etapa         = $_POST['etapa'] ?? 'mg1';
            $fecha         = $_POST['fecha'] ?? '';
            $hora_inicio   = $_POST['hora_inicio'] ?? '';
            $hora_fin      = $_POST['hora_fin'] ?? '';
            $ambiente      = trim($_POST['ambiente'] ?? '');
            $estado        = $_POST['estado'] ?? 'programada';
            $obs_fondo     = trim($_POST['obs_fondo'] ?? '');
            $obs_forma     = trim($_POST['obs_forma'] ?? '');

            // 1. Validar campos obligatorios
            if (!$id_expediente || empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($ambiente)) {
                header("Location: $url_base&error=" . urlencode("El expediente, fecha, horas y ambiente son obligatorios."));
                exit();
            }

            // 2. Validar coherencia de horas
            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            try {
                $defensaModel->crear(
                    $id_expediente, $etapa, $fecha, $hora_inicio, 
                    $hora_fin, $ambiente, $estado, $obs_fondo, $obs_forma
                );
                header("Location: $url_base&exito=" . urlencode("Defensa de tesis agendada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR DEFENSA EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_defensa    = $_POST['id_registro'] ?? null;
            $id_expediente = $_POST['id_expediente'] ?? null;
            $etapa         = $_POST['etapa'] ?? 'mg1';
            $fecha         = $_POST['fecha'] ?? '';
            $hora_inicio   = $_POST['hora_inicio'] ?? '';
            $hora_fin      = $_POST['hora_fin'] ?? '';
            $ambiente      = trim($_POST['ambiente'] ?? '');
            $estado        = $_POST['estado'] ?? 'programada';
            $obs_fondo     = trim($_POST['obs_fondo'] ?? '');
            $obs_forma     = trim($_POST['obs_forma'] ?? '');

            if (!$id_defensa || !$id_expediente || empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($ambiente)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la defensa."));
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            try {
                $defensaModel->actualizar(
                    $id_defensa, $id_expediente, $etapa, $fecha, $hora_inicio, 
                    $hora_fin, $ambiente, $estado, $obs_fondo, $obs_forma
                );
                header("Location: $url_base&exito=" . urlencode("Datos de la defensa actualizados exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR DEFENSA
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_defensa = $_GET['id'] ?? null;
        
        if ($id_defensa) {
            try {
                $defensaModel->eliminar($id_defensa);
                header("Location: $url_base&exito=" . urlencode("La programación de la defensa fue eliminada correctamente."));
                exit();
            } catch (Throwable $e) {
                // Falla típica si hay actas de tribunal ligadas a esta defensa
                header("Location: $url_base&error=" . urlencode("No se puede borrar la defensa porque existen actas o calificaciones vinculadas a ella."));
                exit();
            }
        } else {
            header("Location: $url_base");
            exit();
        }
        break;

    default:
        header("Location: $url_base");
        exit();
}