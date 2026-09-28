<?php
/**
 * ARCHIVO: controllers/MateriaController.php
 * Controlador para procesar las acciones CRUD de Materias (Catálogo Académico).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/MateriaModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$materiaModel = new MateriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta a la pestaña "Oferta Universitaria")
$url_base = "../views/admin/index.php?seccion=cajon2&tab=oferta";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVA MATERIA
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_materia = trim($_POST['nombre_materia'] ?? '');
            $id_carrera = $_POST['id_carrera'] ?? null;

            // 1. Validar campos vacíos
            if (empty($nombre_materia) || empty($id_carrera)) {
                header("Location: $url_base&error=" . urlencode("El nombre de la materia y la carrera son obligatorios."));
                exit();
            }

            // 2. Validar duplicados en la MISMA carrera
            if ($materiaModel->existeMateriaEnCarrera($nombre_materia, $id_carrera)) {
                header("Location: $url_base&error=" . urlencode("La materia '$nombre_materia' ya se encuentra registrada en la carrera seleccionada."));
                exit();
            }

            try {
                $materiaModel->crear($nombre_materia, $id_carrera);
                header("Location: $url_base&exito=" . urlencode("Materia registrada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR MATERIA EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Se envía a través del input oculto 'id_registro'
            $id_materia = $_POST['id_registro'] ?? null;
            $nombre_materia = trim($_POST['nombre_materia'] ?? '');
            $id_carrera = $_POST['id_carrera'] ?? null;

            if (!$id_materia || empty($nombre_materia) || empty($id_carrera)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la materia."));
                exit();
            }

            // Validar que el nuevo nombre no choque con OTRA materia en la MISMA carrera
            if ($materiaModel->existeMateriaEnCarrera($nombre_materia, $id_carrera, $id_materia)) {
                header("Location: $url_base&error=" . urlencode("El nombre '$nombre_materia' ya está en uso en esta carrera."));
                exit();
            }

            try {
                $materiaModel->actualizar($id_materia, $nombre_materia, $id_carrera);
                header("Location: $url_base&exito=" . urlencode("Materia actualizada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR MATERIA
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_materia = $_GET['id'] ?? null;
        
        if ($id_materia) {
            try {
                $materiaModel->eliminar($id_materia);
                header("Location: $url_base&exito=" . urlencode("La materia fue eliminada exitosamente."));
                exit();
            } catch (Throwable $e) {
                // Falla clásica por restricción de llave foránea (FK)
                header("Location: $url_base&error=" . urlencode("No se puede borrar la materia porque tiene tutorías u otros registros vinculados."));
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