<?php
/**
 * ARCHIVO: controllers/HitoController.php
 * Controlador para procesar las acciones CRUD de Programación de Hitos (Cajón 3).
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/cronograma/HitoModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$hitoModel = new HitoModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Variables base para la redirección SPA (Apunta a la pestaña "Programación de Hitos")
$url_base = "../views/admin/index.php?seccion=cajon3&tab=hitos";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVO HITO / FECHA LÍMITE
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_cohorte          = $_POST['id_cohorte'] ?? null;
            $nombre              = trim($_POST['nombre'] ?? '');
            $etapa               = $_POST['etapa'] ?? 'previa';
            $tipo                = $_POST['tipo'] ?? 'informe';
            $orden               = (int)($_POST['orden'] ?? 1);
            $avance_esperado_pct = (int)($_POST['avance_esperado_pct'] ?? 0);
            $fecha_limite        = $_POST['fecha_limite'] ?? '';

            // 1. Validar campos obligatorios
            if (!$id_cohorte || empty($nombre) || empty($fecha_limite)) {
                header("Location: $url_base&error=" . urlencode("La cohorte, el nombre del hito y la fecha límite son obligatorios."));
                exit();
            }

            // 2. Validar rango del porcentaje (0 al 100)
            if ($avance_esperado_pct < 0 || $avance_esperado_pct > 100) {
                header("Location: $url_base&error=" . urlencode("El porcentaje de avance esperado debe estar entre 0 y 100."));
                exit();
            }

            try {
                $hitoModel->crear(
                    $id_cohorte, $etapa, $tipo, $nombre, 
                    $orden, $fecha_limite, $avance_esperado_pct
                );
                header("Location: $url_base&exito=" . urlencode("Hito programado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR HITO EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_hito             = $_POST['id_registro'] ?? null;
            $id_cohorte          = $_POST['id_cohorte'] ?? null;
            $nombre              = trim($_POST['nombre'] ?? '');
            $etapa               = $_POST['etapa'] ?? 'previa';
            $tipo                = $_POST['tipo'] ?? 'informe';
            $orden               = (int)($_POST['orden'] ?? 1);
            $avance_esperado_pct = (int)($_POST['avance_esperado_pct'] ?? 0);
            $fecha_limite        = $_POST['fecha_limite'] ?? '';

            if (!$id_hito || !$id_cohorte || empty($nombre) || empty($fecha_limite)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar el hito."));
                exit();
            }

            if ($avance_esperado_pct < 0 || $avance_esperado_pct > 100) {
                header("Location: $url_base&error=" . urlencode("El porcentaje de avance esperado debe estar entre 0 y 100."));
                exit();
            }

            try {
                $hitoModel->actualizar(
                    $id_hito, $id_cohorte, $etapa, $tipo, 
                    $nombre, $orden, $fecha_limite, $avance_esperado_pct
                );
                header("Location: $url_base&exito=" . urlencode("Hito actualizado exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR HITO
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_hito = $_GET['id'] ?? null;
        
        if ($id_hito) {
            try {
                $hitoModel->eliminar($id_hito);
                header("Location: $url_base&exito=" . urlencode("El hito fue borrado del cronograma correctamente."));
                exit();
            } catch (Throwable $e) {
                // Falla típica si el hito ya está ligado a entregas (informes de avance) de alumnos
                header("Location: $url_base&error=" . urlencode("No se puede borrar el hito porque ya existen informes o entregas estudiantiles vinculadas a él."));
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