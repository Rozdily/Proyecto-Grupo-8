<?php
/**
 * ARCHIVO: controllers/TutoriaController.php
 * Controlador para procesar CRUD de Sesiones de Tutorías (Grupos 1 a N) con integración de Bitácora.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/tutorias/TutoriaModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario tenga acceso (Admin)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/index.php?error=acceso_denegado");
    exit();
}

$tutoriaModel = new TutoriaModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Identificar al usuario que está haciendo la acción
$usuario_actual = $_SESSION['usuario'] ?? 'Sistema';

// URL base para la redirección SPA
$url_base = "../views/admin/index.php?seccion=cajon3&tab=tutorias";

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVA SESIÓN DE TUTORÍA
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // El estudiante ahora puede ser nulo al crear la sesión pura
            $id_estudiante  = $_POST['id_estudiante'] ?? null; 
            $id_tutor       = $_POST['id_tutor'] ?? null;
            $id_materia     = $_POST['id_materia'] ?? null;
            $id_bloque      = $_POST['id_bloque'] ?? null;
            $fecha          = $_POST['fecha'] ?? '';
            $periodo        = trim($_POST['periodo'] ?? 'II-2026');
            $hora_inicio    = $_POST['hora_inicio'] ?? '';
            $hora_fin       = $_POST['hora_fin'] ?? '';
            $modalidad      = $_POST['modalidad'] ?? 'presencial';
            $lugar_o_enlace = trim($_POST['lugar_o_enlace'] ?? '');
            $estado         = $_POST['estado'] ?? 'pendiente';
            $observaciones  = trim($_POST['observaciones'] ?? '');

            if (!$id_tutor || !$id_materia || !$id_bloque || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("Faltan datos obligatorios para crear la sesión."));
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            try {
                $creado = $tutoriaModel->crear(
                    $id_estudiante, $id_tutor, $id_materia, $id_bloque, 
                    $fecha, $periodo, $hora_inicio, $hora_fin, 
                    $modalidad, $lugar_o_enlace, $estado, $observaciones
                );
                
                if ($creado) {
                    // --- INICIO DE AUDITORÍA ---
                    $nuevo_id = $pdo->lastInsertId(); // Capturamos el ID recién creado
                    $datos_nuevos = [
                        'id_tutor' => $id_tutor, 'id_materia' => $id_materia, 
                        'fecha' => $fecha, 'hora_inicio' => $hora_inicio, 
                        'hora_fin' => $hora_fin, 'estado' => $estado, 'id_bloque' => $id_bloque
                    ];
                    $auditoriaModel->registrarAccion($usuario_actual, 'CREAR', 'tutorias', $nuevo_id, [], $datos_nuevos);
                    // --- FIN DE AUDITORÍA ---

                    header("Location: $url_base&exito=" . urlencode("Sesión de tutoría agendada correctamente."));
                } else {
                    header("Location: $url_base&error=" . urlencode("Error al guardar la sesión en la base de datos."));
                }
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR SESIÓN DE TUTORÍA EXISTENTE
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_tutoria     = $_POST['id_registro'] ?? null;
            $id_estudiante  = $_POST['id_estudiante'] ?? null;
            $id_tutor       = $_POST['id_tutor'] ?? null;
            $id_materia     = $_POST['id_materia'] ?? null;
            $id_bloque      = $_POST['id_bloque'] ?? null;
            $fecha          = $_POST['fecha'] ?? '';
            $periodo        = trim($_POST['periodo'] ?? 'II-2026');
            $hora_inicio    = $_POST['hora_inicio'] ?? '';
            $hora_fin       = $_POST['hora_fin'] ?? '';
            $modalidad      = $_POST['modalidad'] ?? 'presencial';
            $lugar_o_enlace = trim($_POST['lugar_o_enlace'] ?? '');
            $estado         = $_POST['estado'] ?? 'pendiente';
            $observaciones  = trim($_POST['observaciones'] ?? '');

            if (!$id_tutoria || !$id_tutor || !$id_materia || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la sesión."));
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            try {
                // --- INICIO DE AUDITORÍA (PASO 1: LEER ANTES) ---
                $stmt = $pdo->prepare("SELECT * FROM tutorias WHERE id_tutoria = ?");
                $stmt->execute([$id_tutoria]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                // --- FIN PASO 1 ---

                // Realizamos el Update a través del modelo
                $actualizado = $tutoriaModel->actualizar(
                    $id_tutoria, $id_estudiante, $id_tutor, $id_materia, $id_bloque, 
                    $fecha, $periodo, $hora_inicio, $hora_fin, 
                    $modalidad, $lugar_o_enlace, $estado, $observaciones
                );

                if ($actualizado) {
                    // --- INICIO DE AUDITORÍA (PASO 2: GUARDAR) ---
                    $datos_nuevos = [
                        'id_tutor' => $id_tutor, 'id_materia' => $id_materia, 
                        'fecha' => $fecha, 'hora_inicio' => $hora_inicio, 
                        'hora_fin' => $hora_fin, 'estado' => $estado, 'id_bloque' => $id_bloque
                    ];
                    $auditoriaModel->registrarAccion($usuario_actual, 'ACTUALIZAR', 'tutorias', $id_tutoria, $datos_antes, $datos_nuevos);
                    // --- FIN DE AUDITORÍA ---

                    header("Location: $url_base&exito=" . urlencode("Configuración de la sesión actualizada exitosamente."));
                } else {
                    header("Location: $url_base&error=" . urlencode("Error al intentar actualizar la sesión."));
                }
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR SESIÓN COMPLETA (Y SUS INSCRITOS POR CASCADE)
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_tutoria = $_GET['id'] ?? null;
        
        if ($id_tutoria) {
            try {
                // --- INICIO DE AUDITORÍA (PASO 1: LEER LO QUE SE VA A BORRAR) ---
                $stmt = $pdo->prepare("SELECT * FROM tutorias WHERE id_tutoria = ?");
                $stmt->execute([$id_tutoria]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                // --- FIN PASO 1 ---

                // Realizamos el borrado a través del modelo
                $borrado = $tutoriaModel->eliminar($id_tutoria);

                if ($borrado) {
                    // --- INICIO DE AUDITORÍA (PASO 2: GUARDAR) ---
                    $auditoriaModel->registrarAccion($usuario_actual, 'ELIMINAR', 'tutorias', $id_tutoria, $datos_antes, []);
                    // --- FIN DE AUDITORÍA ---
                    
                    header("Location: $url_base&exito=" . urlencode("La sesión y todas sus inscripciones fueron borradas."));
                } else {
                    header("Location: $url_base&error=" . urlencode("No se pudo eliminar la sesión."));
                }
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error: existen registros o evaluaciones que dependen de esta sesión."));
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