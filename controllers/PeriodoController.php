<?php
/**
 * ARCHIVO: controllers/PeriodoController.php
 * Controlador para procesar las acciones CRUD de Periodos de Tutoría (Cajón 2).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/PeriodoModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$periodoModel = new PeriodoModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA
$url_base = "../views/admin/index.php?seccion=cajon2&tab=periodos";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVO PERIODO
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activo = $_POST['activo'] ?? 1;
            $creado_por = $_SESSION['id_usuario'] ?? 1; // ID del administrador actual

            // 1. Validar campos vacíos
            if (empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Todos los campos son obligatorios."));
                exit();
            }

            // 2. Validar coherencia de fechas
            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            // 3. Validar duplicados en la base de datos
            if ($periodoModel->existeCodigo($codigo)) {
                header("Location: $url_base&error=" . urlencode("El código de periodo ($codigo) ya se encuentra registrado."));
                exit();
            }

            try {
                $periodoModel->crear($codigo, $nombre, $fecha_inicio, $fecha_fin, $activo, $creado_por);
                header("Location: $url_base&exito=" . urlencode("Periodo creado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR PERIODO EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nota: En la vista enviamos el ID en el campo 'id_registro'
            $id_periodo = $_POST['id_registro'] ?? null;
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activo = $_POST['activo'] ?? 1;

            if (!$id_periodo || empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar el periodo."));
                exit();
            }

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            // Validar que el nuevo código no choque con OTRO periodo distinto
            if ($periodoModel->existeCodigo($codigo, $id_periodo)) {
                header("Location: $url_base&error=" . urlencode("El código ($codigo) ya está en uso por otro periodo."));
                exit();
            }

            try {
                $periodoModel->actualizar($id_periodo, $codigo, $nombre, $fecha_inicio, $fecha_fin, $activo);
                header("Location: $url_base&exito=" . urlencode("Periodo actualizado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR PERIODO
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_periodo = $_GET['id'] ?? null;
        
        if ($id_periodo) {
            try {
                $periodoModel->eliminar($id_periodo);
                header("Location: $url_base&exito=" . urlencode("El periodo fue borrado del sistema."));
                exit();
            } catch (Throwable $e) {
                // Si la eliminación falla, suele ser porque este periodo ya tiene tutorías asociadas (Foreign Key Constraint)
                header("Location: $url_base&error=" . urlencode("No se pudo borrar el periodo porque ya tiene tutorías o datos enlazados a él."));
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