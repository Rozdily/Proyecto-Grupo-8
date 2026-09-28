<?php
/**
 * ARCHIVO: controllers/CarreraController.php
 * Controlador para procesar las acciones CRUD del Catálogo de Carreras (Cajón 2).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/CarreraModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$carreraModel = new CarreraModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta a la pestaña "Oferta Universitaria")
$url_base = "../views/admin/index.php?seccion=cajon2&tab=oferta";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVA CARRERA
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_carrera = trim($_POST['nombre_carrera'] ?? '');

            // 1. Validar campo vacío
            if (empty($nombre_carrera)) {
                header("Location: $url_base&error=" . urlencode("El nombre de la carrera es obligatorio."));
                exit();
            }

            // 2. Validar duplicados (UNIQUE constraint)
            if ($carreraModel->existeNombre($nombre_carrera)) {
                header("Location: $url_base&error=" . urlencode("La carrera '$nombre_carrera' ya se encuentra registrada en el sistema."));
                exit();
            }

            try {
                $carreraModel->crear($nombre_carrera);
                header("Location: $url_base&exito=" . urlencode("Carrera registrada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR CARRERA EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Se envía a través del input oculto 'id_registro'
            $id_carrera = $_POST['id_registro'] ?? null;
            $nombre_carrera = trim($_POST['nombre_carrera'] ?? '');

            if (!$id_carrera || empty($nombre_carrera)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la carrera."));
                exit();
            }

            // Validar que el nuevo nombre no choque con OTRA carrera distinta
            if ($carreraModel->existeNombre($nombre_carrera, $id_carrera)) {
                header("Location: $url_base&error=" . urlencode("El nombre '$nombre_carrera' ya está en uso por otra carrera."));
                exit();
            }

            try {
                $carreraModel->actualizar($id_carrera, $nombre_carrera);
                header("Location: $url_base&exito=" . urlencode("Carrera actualizada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR CARRERA
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_carrera = $_GET['id'] ?? null;
        
        if ($id_carrera) {
            try {
                $carreraModel->eliminar($id_carrera);
                header("Location: $url_base&exito=" . urlencode("La carrera fue eliminada exitosamente."));
                exit();
            } catch (Throwable $e) {
                // Si la eliminación falla, normalmente es porque existen materias o estudiantes vinculados a esta carrera
                header("Location: $url_base&error=" . urlencode("No se puede borrar la carrera porque existen materias o estudiantes vinculados a ella."));
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