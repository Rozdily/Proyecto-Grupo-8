<?php
/**
 * ARCHIVO: controllers/GrupoTesisController.php
 * Controlador para procesar las acciones CRUD de Grupos de Tesis / Cohortes (Cajón 2).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/GrupoTesisModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$grupoTesisModel = new GrupoTesisModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta directo a la pestaña cohortes)
$url_base = "../views/admin/index.php?seccion=cajon2&tab=cohortes";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVO GRUPO DE TESIS (COHORTE)
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activa = $_POST['activa'] ?? 1;

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
            if ($grupoTesisModel->existeCodigo($codigo)) {
                header("Location: $url_base&error=" . urlencode("El código de cohorte ($codigo) ya se encuentra registrado."));
                exit();
            }

            try {
                $grupoTesisModel->crear($codigo, $nombre, $fecha_inicio, $fecha_fin, $activa);
                header("Location: $url_base&exito=" . urlencode("Cohorte / Grupo de Tesis creado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR GRUPO EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nota: En la vista enviamos el ID en el campo 'id_registro'
            $id_cohorte = $_POST['id_registro'] ?? null;
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activa = $_POST['activa'] ?? 1;

            if (!$id_cohorte || empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la cohorte."));
                exit();
            }

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            // Validar que el nuevo código no choque con OTRA cohorte distinta
            if ($grupoTesisModel->existeCodigo($codigo, $id_cohorte)) {
                header("Location: $url_base&error=" . urlencode("El código ($codigo) ya está en uso por otra cohorte."));
                exit();
            }

            try {
                $grupoTesisModel->actualizar($id_cohorte, $codigo, $nombre, $fecha_inicio, $fecha_fin, $activa);
                header("Location: $url_base&exito=" . urlencode("Cohorte actualizada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR GRUPO
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_cohorte = $_GET['id'] ?? null;
        
        if ($id_cohorte) {
            try {
                $grupoTesisModel->eliminar($id_cohorte);
                header("Location: $url_base&exito=" . urlencode("La cohorte fue borrada del sistema."));
                exit();
            } catch (Throwable $e) {
                // Falla común si hay expedientes_mg asignados a esta cohorte (Restricción de llave foránea)
                header("Location: $url_base&error=" . urlencode("No se pudo borrar la cohorte porque existen expedientes o hitos asignados a ella."));
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