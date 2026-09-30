<?php
/**
 * ARCHIVO: controllers/TutoriaController.php
 * Controlador para procesar CRUD de Sesiones de Tutorías (Grupos 1 a N) con integración de Bitácora.
 * Adaptado a la normalización de periodos (id_periodo), módulos flexibles y límite de estudiantes.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/tutorias/TutoriaModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php';
require_once __DIR__ . '/../models/estructuras/PeriodoModel/index.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/index.php?error=acceso_denegado");
    exit();
}

$tutoriaModel = new TutoriaModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$periodoModel = new PeriodoModel($pdo); 
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

$usuario_actual = $_SESSION['usuario'] ?? 'Sistema';
$url_base = "../views/admin/index.php?seccion=cajon3&tab=tutorias";

$tope_clases_defecto = 1;
try {
    $stmtParam = $pdo->query("SELECT valor FROM parametros_mg WHERE clave = 'SESIONES_POR_MODULO'");
    if ($rowParam = $stmtParam->fetch(PDO::FETCH_ASSOC)) {
        $tope_clases_defecto = (int)$rowParam['valor'];
    }
} catch (Exception $e) {}

switch ($accion) {
    
    // --- NUEVO: OBTENER LISTA DE INSCRITOS VÍA AJAX ---
    case 'obtener_inscritos':
        header('Content-Type: application/json; charset=utf-8');
        $id_tutoria = $_GET['id_tutoria'] ?? 0;
        $inscritos = $tutoriaModel->obtenerInscritos($id_tutoria);
        echo json_encode($inscritos);
        exit();

    // --- NUEVO: ELIMINAR INSCRITO VÍA AJAX ---
    case 'eliminar_inscrito':
        header('Content-Type: application/json; charset=utf-8');
        $id_tutoria = $_POST['id_tutoria'] ?? 0;
        $id_estudiante = $_POST['id_estudiante'] ?? 0;
        
        if ($tutoriaModel->eliminarInscrito($id_tutoria, $id_estudiante)) {
            echo json_encode(['exito' => true]);
        } else {
            echo json_encode(['error' => 'No se pudo eliminar al estudiante de la tutoría.']);
        }
        exit();

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_estudiante  = $_POST['id_estudiante'] ?? null; 
            $id_tutor       = $_POST['id_tutor'] ?? null;
            $id_materia     = $_POST['id_materia'] ?? null;
            $id_bloque      = $_POST['id_bloque'] ?? null;
            $fecha          = $_POST['fecha'] ?? '';
            $hora_inicio    = $_POST['hora_inicio'] ?? '';
            $hora_fin       = $_POST['hora_fin'] ?? '';
            $modalidad      = $_POST['modalidad'] ?? 'presencial';
            $lugar_o_enlace = trim($_POST['lugar_o_enlace'] ?? '');
            $estado         = $_POST['estado'] ?? 'pendiente';
            $observaciones  = trim($_POST['observaciones'] ?? '');
            
            $tope_clases        = !empty($_POST['tope_clases']) ? (int)$_POST['tope_clases'] : $tope_clases_defecto;
            $limite_estudiantes = !empty($_POST['limite_estudiantes']) ? (int)$_POST['limite_estudiantes'] : 10;

            if (!$id_tutor || !$id_materia || !$id_bloque || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("Faltan datos obligatorios para crear la sesión."));
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            $id_periodo_activo = $periodoModel->obtenerPeriodoPorFecha($fecha);
            if (!$id_periodo_activo) {
                header("Location: $url_base&error=" . urlencode("La fecha seleccionada no corresponde a ningún periodo académico activo. Verifica el calendario institucional."));
                exit();
            }

            try {
                $creado = $tutoriaModel->crear(
                    $id_estudiante, $id_tutor, $id_materia, $id_bloque, 
                    $fecha, $id_periodo_activo, $hora_inicio, $hora_fin, 
                    $modalidad, $lugar_o_enlace, $estado, $observaciones,
                    $tope_clases, $limite_estudiantes
                );
                
                if ($creado) {
                    $nuevo_id = $pdo->lastInsertId();
                    $datos_nuevos = [
                        'id_tutor' => $id_tutor, 'id_materia' => $id_materia, 
                        'fecha' => $fecha, 'hora_inicio' => $hora_inicio, 
                        'hora_fin' => $hora_fin, 'estado' => $estado, 'id_bloque' => $id_bloque,
                        'tope_clases' => $tope_clases, 'limite_estudiantes' => $limite_estudiantes
                    ];
                    $auditoriaModel->registrarAccion($usuario_actual, 'CREAR', 'tutorias', $nuevo_id, [], $datos_nuevos);

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

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_tutoria     = $_POST['id_registro'] ?? null;
            $id_estudiante  = $_POST['id_estudiante'] ?? null;
            $id_tutor       = $_POST['id_tutor'] ?? null;
            $id_materia     = $_POST['id_materia'] ?? null;
            $id_bloque      = $_POST['id_bloque'] ?? null;
            $fecha          = $_POST['fecha'] ?? '';
            $hora_inicio    = $_POST['hora_inicio'] ?? '';
            $hora_fin       = $_POST['hora_fin'] ?? '';
            $modalidad      = $_POST['modalidad'] ?? 'presencial';
            $lugar_o_enlace = trim($_POST['lugar_o_enlace'] ?? '');
            $estado         = $_POST['estado'] ?? 'pendiente';
            $observaciones  = trim($_POST['observaciones'] ?? '');
            
            $tope_clases        = isset($_POST['tope_clases']) ? (int)$_POST['tope_clases'] : null;
            $clases_impartidas  = isset($_POST['clases_impartidas']) ? (int)$_POST['clases_impartidas'] : null;
            $limite_estudiantes = isset($_POST['limite_estudiantes']) ? (int)$_POST['limite_estudiantes'] : null;

            if (!$id_tutoria || !$id_tutor || !$id_materia || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la sesión."));
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: $url_base&error=" . urlencode("La hora de inicio debe ser anterior a la hora de finalización."));
                exit();
            }

            $id_periodo_activo = $periodoModel->obtenerPeriodoPorFecha($fecha);
            if (!$id_periodo_activo) {
                header("Location: $url_base&error=" . urlencode("La fecha seleccionada no corresponde a ningún periodo académico activo. Verifica el calendario institucional."));
                exit();
            }

            try {
                $stmt = $pdo->prepare("SELECT * FROM tutorias WHERE id_tutoria = ?");
                $stmt->execute([$id_tutoria]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                
                if ($tope_clases === null) $tope_clases = (int)($datos_antes['tope_clases'] ?? $tope_clases_defecto);
                if ($clases_impartidas === null) $clases_impartidas = (int)($datos_antes['clases_impartidas'] ?? 0);
                if ($limite_estudiantes === null) $limite_estudiantes = (int)($datos_antes['limite_estudiantes'] ?? 10);

                $actualizado = $tutoriaModel->actualizar(
                    $id_tutoria, $id_estudiante, $id_tutor, $id_materia, $id_bloque, 
                    $fecha, $id_periodo_activo, $hora_inicio, $hora_fin, 
                    $modalidad, $lugar_o_enlace, $estado, $observaciones,
                    $tope_clases, $clases_impartidas, $limite_estudiantes
                );

                if ($actualizado) {
                    $datos_nuevos = [
                        'id_tutor' => $id_tutor, 'id_materia' => $id_materia, 
                        'fecha' => $fecha, 'hora_inicio' => $hora_inicio, 
                        'hora_fin' => $hora_fin, 'estado' => $estado, 'id_bloque' => $id_bloque,
                        'tope_clases' => $tope_clases, 'clases_impartidas' => $clases_impartidas,
                        'limite_estudiantes' => $limite_estudiantes
                    ];
                    $auditoriaModel->registrarAccion($usuario_actual, 'ACTUALIZAR', 'tutorias', $id_tutoria, $datos_antes, $datos_nuevos);

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

    case 'eliminar':
        $id_tutoria = $_GET['id'] ?? null;
        
        if ($id_tutoria) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM tutorias WHERE id_tutoria = ?");
                $stmt->execute([$id_tutoria]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $borrado = $tutoriaModel->eliminar($id_tutoria);

                if ($borrado) {
                    $auditoriaModel->registrarAccion($usuario_actual, 'ELIMINAR', 'tutorias', $id_tutoria, $datos_antes, []);
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