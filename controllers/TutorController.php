<?php
/**
 * ARCHIVO: controllers/TutorController.php
 * Controlador para procesar peticiones AJAX del portal del Tutor.
 * Adaptado a la lógica de "Módulos Flexibles" (Múltiples clases por tutoría).
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea Tutor (id_rol = 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    echo json_encode(['error' => 'Acceso denegado o sesión expirada.']);
    exit();
}

$accion = $_POST['accion'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? 0;

try {
    // 1. Obtener el id_tutor correspondiente al usuario logueado
    $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
    $stmtTut->execute([$id_usuario]);
    $id_tutor = $stmtTut->fetchColumn();

    if (!$id_tutor) {
        echo json_encode(['error' => 'No se encontró el registro de tutor asociado a este usuario.']);
        exit();
    }

    // ============================================================================
    // ACCIÓN 1: ACEPTAR Y CONFIRMAR TUTORÍA (De pendiente a confirmada)
    // ============================================================================
    if ($accion === 'aceptar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'] ?? 0;
        $lugar_o_enlace = trim($_POST['lugar_o_enlace'] ?? '');

        if (empty($id_tutoria) || empty($lugar_o_enlace)) {
            echo json_encode(['error' => 'Debe proporcionar el lugar o el enlace de la sesión.']);
            exit();
        }

        $stmtCheck = $pdo->prepare("SELECT id_tutoria FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? AND estado = 'pendiente'");
        $stmtCheck->execute([$id_tutoria, $id_tutor]);
        
        if ($stmtCheck->rowCount() === 0) {
            echo json_encode(['error' => 'La solicitud no existe o ya fue procesada.']);
            exit();
        }

        $sqlUpdate = "UPDATE tutorias SET estado = 'confirmada', lugar_o_enlace = ? WHERE id_tutoria = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$lugar_o_enlace, $id_tutoria]);

        echo json_encode(['exito' => true]);
        exit();
    }

    // ============================================================================
    // ACCIÓN 2: RECHAZAR TUTORÍA (De pendiente a cancelada)
    // ============================================================================
    if ($accion === 'rechazar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'] ?? 0;
        $motivo = trim($_POST['motivo_cancelacion'] ?? '');

        if (empty($id_tutoria) || empty($motivo)) {
            echo json_encode(['error' => 'Debe especificar el motivo del rechazo.']);
            exit();
        }

        $stmtCheck = $pdo->prepare("SELECT id_tutoria FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? AND estado = 'pendiente'");
        $stmtCheck->execute([$id_tutoria, $id_tutor]);
        
        if ($stmtCheck->rowCount() === 0) {
            echo json_encode(['error' => 'La solicitud no existe o ya fue procesada.']);
            exit();
        }

        $sqlUpdate = "UPDATE tutorias SET estado = 'cancelada', motivo_cancelacion = ? WHERE id_tutoria = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$motivo, $id_tutoria]);

        echo json_encode(['exito' => true]);
        exit();
    }

    // ============================================================================
    // ACCIÓN 3: SALIR / CANCELAR TUTORÍA ACTIVA (De confirmada/en_proceso a cancelada)
    // ============================================================================
    if ($accion === 'cancelar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'] ?? 0;
        $motivo = trim($_POST['motivo_cancelacion'] ?? '');

        if (empty($id_tutoria) || empty($motivo)) {
            echo json_encode(['error' => 'Debe especificar el motivo por el cual abandona la tutoría.']);
            exit();
        }

        $stmtCheck = $pdo->prepare("SELECT id_tutoria FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? AND estado IN ('confirmada', 'en_proceso')");
        $stmtCheck->execute([$id_tutoria, $id_tutor]);
        
        if ($stmtCheck->rowCount() === 0) {
            echo json_encode(['error' => 'La tutoría no existe, no te pertenece o ya finalizó.']);
            exit();
        }

        $sqlUpdate = "UPDATE tutorias SET estado = 'cancelada', motivo_cancelacion = ? WHERE id_tutoria = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$motivo, $id_tutoria]);

        echo json_encode(['exito' => true]);
        exit();
    }

    // ============================================================================
    // ACCIÓN 4: GUARDAR O ACTUALIZAR DISPONIBILIDAD HORARIA
    // ============================================================================
    if ($accion === 'guardar_disponibilidad') {
        $id_disponibilidad = $_POST['id_disponibilidad'] ?? 0;
        $dia_semana        = trim($_POST['dia_semana'] ?? '');
        $hora_inicio       = trim($_POST['hora_inicio'] ?? '');
        $hora_fin          = trim($_POST['hora_fin'] ?? '');

        if (empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
            echo json_encode(['error' => 'Debe completar el día y las horas de inicio y fin.']);
            exit();
        }

        $ts_inicio = strtotime($hora_inicio);
        $ts_fin    = strtotime($hora_fin);

        // Validación: Horas idénticas
        if ($ts_inicio === $ts_fin) {
            echo json_encode(['error' => 'La hora de inicio y la hora de fin no pueden ser iguales.']);
            exit();
        }

        // Validación: Orden cronológico
        if ($ts_inicio > $ts_fin) {
            echo json_encode(['error' => 'La hora de inicio no puede ser mayor a la hora de fin.']);
            exit();
        }

        // Validación: Duración mínima (45 min = 2700 segundos)
        if (($ts_fin - $ts_inicio) < 2700) {
            echo json_encode(['error' => 'El bloque horario debe tener una duración mínima de 45 minutos.']);
            exit();
        }

        // Consultar horarios existentes para este tutor en este día
        $sqlCheck = "SELECT id_disponibilidad, hora_inicio, hora_fin FROM disponibilidad_tutor WHERE id_tutor = ? AND dia_semana = ?";
        $paramsCheck = [$id_tutor, $dia_semana];

        // Excluir el registro actual si estamos editando
        if (!empty($id_disponibilidad) && $id_disponibilidad > 0) {
            $sqlCheck .= " AND id_disponibilidad != ?";
            $paramsCheck[] = $id_disponibilidad;
        }

        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        $existentes = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

        // Validación de cruces y margen de 1 hora (3600 segundos)
        foreach ($existentes as $ext) {
            $ext_ts_inicio = strtotime($ext['hora_inicio']);
            $ext_ts_fin    = strtotime($ext['hora_fin']);

            $valido_antes   = $ts_fin <= ($ext_ts_inicio - 3600);
            $valido_despues = $ts_inicio >= ($ext_ts_fin + 3600);

            if (!$valido_antes && !$valido_despues) {
                echo json_encode(['error' => 'Conflicto de horario. Debe existir al menos 1 hora de diferencia con tu bloque de ' . substr($ext['hora_inicio'], 0, 5) . ' a ' . substr($ext['hora_fin'], 0, 5) . '.']);
                exit();
            }
        }

        if (!empty($id_disponibilidad) && $id_disponibilidad > 0) {
            $sqlUpd = "UPDATE disponibilidad_tutor SET dia_semana = ?, hora_inicio = ?, hora_fin = ? WHERE id_disponibilidad = ? AND id_tutor = ?";
            $stmtUpd = $pdo->prepare($sqlUpd);
            $stmtUpd->execute([$dia_semana, $hora_inicio, $hora_fin, $id_disponibilidad, $id_tutor]);
        } else {
            $sqlIns = "INSERT INTO disponibilidad_tutor (id_tutor, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)";
            $stmtIns = $pdo->prepare($sqlIns);
            $stmtIns->execute([$id_tutor, $dia_semana, $hora_inicio, $hora_fin]);
        }

        echo json_encode(['exito' => true]);
        exit();
    }

    // ============================================================================
    // ACCIÓN 5: ELIMINAR DISPONIBILIDAD HORARIA
    // ============================================================================
    if ($accion === 'eliminar_disponibilidad') {
        $id_disponibilidad = $_POST['id_disponibilidad'] ?? 0;

        if (empty($id_disponibilidad)) {
            echo json_encode(['error' => 'ID no válido.']);
            exit();
        }

        $stmtDel = $pdo->prepare("DELETE FROM disponibilidad_tutor WHERE id_disponibilidad = ? AND id_tutor = ?");
        $stmtDel->execute([$id_disponibilidad, $id_tutor]);

        echo json_encode(['exito' => true]);
        exit();
    }

    // ============================================================================
    // ACCIÓN 6: REGISTRAR SEGUIMIENTO Y AVANCE (MÓDULOS DE CLASES FLEXIBLES)
    // ============================================================================
    if ($accion === 'guardar_seguimiento') {
        $id_tutoria      = $_POST['id_tutoria'] ?? 0;
        $asistio         = $_POST['asistio'] ?? 'si';
        $temas_tratados  = trim($_POST['temas_tratados'] ?? '');
        $avance          = trim($_POST['avance'] ?? 'parcial');
        $recommendations = trim($_POST['recommendations'] ?? '');

        if (empty($id_tutoria) || empty($temas_tratados)) {
            echo json_encode(['error' => 'Debe completar los temas tratados para registrar el avance de la sesión.']);
            exit();
        }

        try {
            $pdo->beginTransaction();

            // 1. Verificar la tutoría y obtener los topes actuales
            $stmtCheck = $pdo->prepare("SELECT tope_clases, clases_impartidas, estado FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? AND estado IN ('confirmada', 'en_proceso')");
            $stmtCheck->execute([$id_tutoria, $id_tutor]);
            $tutoria = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$tutoria) {
                $pdo->rollBack();
                echo json_encode(['error' => 'La tutoría no existe, no te pertenece o ya ha sido cerrada.']);
                exit();
            }

            // 2. Insertar el seguimiento/bitácora individual de esta clase
            $sqlIns = "INSERT INTO seguimiento_sesion (id_tutoria, asistio, temas_tratados, avance, recommendations) VALUES (?, ?, ?, ?, ?)";
            $stmtIns = $pdo->prepare($sqlIns);
            $stmtIns->execute([$id_tutoria, $asistio, $temas_tratados, $avance, $recommendations]);

            // 3. Evaluar la lógica del módulo de clases
            $nuevo_impartidas = (int)$tutoria['clases_impartidas'] + 1;
            $tope = (int)$tutoria['tope_clases'];
            
            if ($nuevo_impartidas >= $tope) {
                $nuevo_estado = 'realizada'; // Si alcanza el límite, cerramos el módulo
            } else {
                $nuevo_estado = 'en_proceso'; // Si quedan clases, se mantiene en proceso
            }

            // 4. Actualizar contadores y estado final en la tabla tutorías
            $sqlUpd = "UPDATE tutorias SET clases_impartidas = ?, estado = ? WHERE id_tutoria = ?";
            $stmtUpd = $pdo->prepare($sqlUpd);
            $stmtUpd->execute([$nuevo_impartidas, $nuevo_estado, $id_tutoria]);

            $pdo->commit();
            
            // Retornar mensaje condicional dependiendo de si se cerró o sigue en curso
            echo json_encode([
                'exito' => true, 
                'clases_impartidas' => $nuevo_impartidas,
                'tope_clases' => $tope,
                'nuevo_estado' => $nuevo_estado,
                'mensaje' => $nuevo_estado === 'realizada' ? '¡Módulo completado con éxito!' : "Avance registrado. Clase $nuevo_impartidas de $tope guardada."
            ]);
            exit();
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['error' => 'Fallo interno en la base de datos al guardar el seguimiento.']);
            exit();
        }
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error interno en la BD.']);
    exit();
}

echo json_encode(['error' => 'Acción no reconocida.']);
exit();