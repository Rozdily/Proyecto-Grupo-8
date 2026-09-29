<?php
/**
 * ARCHIVO: controllers/ReunionController.php
 * Controlador para procesar las acciones CRUD de Reuniones de Proyecto (Modalidad de Grado).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/tutorias/ReunionModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$reunionModel = new ReunionModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta a la pestaña "Reuniones de Proyecto")
$url_base = "../views/admin/index.php?seccion=cajon3&tab=reuniones";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVA REUNIÓN DE PROYECTO
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_asignacion     = $_POST['id_asignacion'] ?? null;
            $temas             = trim($_POST['temas'] ?? '');
            $fecha             = $_POST['fecha'] ?? '';
            $hora_inicio       = $_POST['hora_inicio'] ?? '';
            $hora_fin          = $_POST['hora_fin'] ?? '';
            $modalidad         = $_POST['modalidad'] ?? 'presencial';
            $lugar_o_enlace    = trim($_POST['lugar_o_enlace'] ?? '');
            $estado_validacion = $_POST['estado_validacion'] ?? 'registrada';

            // 1. Validar campos obligatorios mínimos
            if (!$id_asignacion || empty($temas) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos. Asegúrese de llenar la asignación, temas, fecha y horarios."));
                exit();
            }

            // 2. Validar coherencia de horas
            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            try {
                $reunionModel->crear(
                    $id_asignacion, $temas, $fecha, $hora_inicio, 
                    $hora_fin, $modalidad, $lugar_o_enlace, $estado_validacion
                );
                header("Location: $url_base&exito=" . urlencode("Reunión de proyecto agendada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR REUNIÓN EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_reunion        = $_POST['id_registro'] ?? null;
            $id_asignacion     = $_POST['id_asignacion'] ?? null;
            $temas             = trim($_POST['temas'] ?? '');
            $fecha             = $_POST['fecha'] ?? '';
            $hora_inicio       = $_POST['hora_inicio'] ?? '';
            $hora_fin          = $_POST['hora_fin'] ?? '';
            $modalidad         = $_POST['modalidad'] ?? 'presencial';
            $lugar_o_enlace    = trim($_POST['lugar_o_enlace'] ?? '');
            $estado_validacion = $_POST['estado_validacion'] ?? 'registrada';

            if (!$id_reunion || !$id_asignacion || empty($temas) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la reunión."));
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            try {
                $reunionModel->actualizar(
                    $id_reunion, $id_asignacion, $temas, $fecha, $hora_inicio, 
                    $hora_fin, $modalidad, $lugar_o_enlace, $estado_validacion
                );
                header("Location: $url_base&exito=" . urlencode("Datos de la reunión actualizados exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR REUNIÓN
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_reunion = $_GET['id'] ?? null;
        
        if ($id_reunion) {
            try {
                $reunionModel->eliminar($id_reunion);
                header("Location: $url_base&exito=" . urlencode("La reunión de proyecto fue borrada del cronograma correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("No se puede borrar la reunión porque ya existen actas o seguimientos vinculados a ella."));
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