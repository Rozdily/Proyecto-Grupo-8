<?php
/**
 * ARCHIVO: controllers/ExpedienteController.php
 * Controlador para procesar las acciones CRUD de Expedientes de Tesis (Cajón 2).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/titulacion/ExpedienteModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$expedienteModel = new ExpedienteModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta a la pestaña "Expedientes de Tesis")
$url_base = "../views/admin/index.php?seccion=cajon2&tab=expedientes";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVO EXPEDIENTE DE TESIS
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_estudiante  = $_POST['id_estudiante'] ?? null;
            $id_cohorte     = $_POST['id_cohorte'] ?? null;
            $id_modalidad   = $_POST['id_modalidad'] ?? null;
            $titulo_trabajo = trim($_POST['titulo_trabajo'] ?? '');
            $etapa_actual   = $_POST['etapa_actual'] ?? 'previa';
            $estado         = $_POST['estado'] ?? 'activo';
            $fecha_inicio   = $_POST['fecha_inicio'] ?? '';
            $fecha_cierre   = $_POST['fecha_cierre'] ?? '';
            $observaciones  = trim($_POST['observaciones'] ?? '');

            // 1. Validar campos obligatorios
            if (!$id_estudiante || !$id_cohorte || !$id_modalidad || empty($titulo_trabajo) || empty($fecha_inicio) || empty($fecha_cierre)) {
                header("Location: $url_base&error=" . urlencode("Todos los campos obligatorios deben ser completados."));
                exit();
            }

            // 2. Validar coherencia de fechas
            if (strtotime($fecha_inicio) > strtotime($fecha_cierre)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio no puede ser mayor a la fecha de cierre."));
                exit();
            }

            // 3. Validar duplicados (Regla UNIQUE: id_estudiante + id_modalidad + id_cohorte)
            if ($expedienteModel->existeExpediente($id_estudiante, $id_modalidad, $id_cohorte)) {
                header("Location: $url_base&error=" . urlencode("El estudiante ya tiene un expediente registrado en esta misma cohorte y modalidad."));
                exit();
            }

            try {
                $expedienteModel->crear(
                    $id_estudiante, $id_modalidad, $id_cohorte, 
                    $etapa_actual, $estado, $titulo_trabajo, 
                    $fecha_inicio, $fecha_cierre, $observaciones
                );
                header("Location: $url_base&exito=" . urlencode("Expediente de tesis registrado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR EXPEDIENTE EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_expediente  = $_POST['id_registro'] ?? null;
            $id_estudiante  = $_POST['id_estudiante'] ?? null;
            $id_cohorte     = $_POST['id_cohorte'] ?? null;
            $id_modalidad   = $_POST['id_modalidad'] ?? null;
            $titulo_trabajo = trim($_POST['titulo_trabajo'] ?? '');
            $etapa_actual   = $_POST['etapa_actual'] ?? 'previa';
            $estado         = $_POST['estado'] ?? 'activo';
            $fecha_inicio   = $_POST['fecha_inicio'] ?? '';
            $fecha_cierre   = $_POST['fecha_cierre'] ?? '';
            $observaciones  = trim($_POST['observaciones'] ?? '');

            if (!$id_expediente || !$id_estudiante || !$id_cohorte || !$id_modalidad || empty($titulo_trabajo) || empty($fecha_inicio) || empty($fecha_cierre)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar el expediente."));
                exit();
            }

            if (strtotime($fecha_inicio) > strtotime($fecha_cierre)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio no puede ser mayor a la fecha de cierre."));
                exit();
            }

            // Validar choque de duplicados excluyendo el expediente actual
            if ($expedienteModel->existeExpediente($id_estudiante, $id_modalidad, $id_cohorte, $id_expediente)) {
                header("Location: $url_base&error=" . urlencode("Este cambio generaría un duplicado. El estudiante ya tiene otro expediente idéntico."));
                exit();
            }

            try {
                $expedienteModel->actualizar(
                    $id_expediente, $id_estudiante, $id_modalidad, $id_cohorte, 
                    $etapa_actual, $estado, $titulo_trabajo, 
                    $fecha_inicio, $fecha_cierre, $observaciones
                );
                header("Location: $url_base&exito=" . urlencode("Expediente actualizado exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR EXPEDIENTE
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_expediente = $_GET['id'] ?? null;
        
        if ($id_expediente) {
            try {
                $expedienteModel->eliminar($id_expediente);
                header("Location: $url_base&exito=" . urlencode("El expediente de tesis fue eliminado correctamente."));
                exit();
            } catch (Throwable $e) {
                // Falla típica de llave foránea (Si el expediente tiene hitos, documentos o asignaciones de tutor)
                header("Location: $url_base&error=" . urlencode("No se puede borrar el expediente porque contiene seguimientos, asignaciones de tutor o calificaciones vinculadas."));
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