<?php
/**
 * ARCHIVO: controllers/EstudianteController.php
 * Controlador para procesar peticiones AJAX del portal del Estudiante.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/PeriodoModel/index.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 3) {
    echo json_encode(['error' => 'Acceso denegado o sesión expirada.']);
    exit();
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$periodoModel = new PeriodoModel($pdo);

// ============================================================================
// ACCIÓN 1: OBTENER TUTORES ASIGNADOS A UNA MATERIA ESPECÍFICA
// ============================================================================
if ($accion === 'obtener_tutores_materia') {
    $id_materia = $_GET['id_materia'] ?? 0;

    if ($id_materia > 0) {
        try {
            $sql = "SELECT t.id_tutor, CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo 
                    FROM tutor_materia tm 
                    INNER JOIN tutores t ON tm.id_tutor = t.id_tutor 
                    INNER JOIN usuarios u ON t.id_usuario = u.id_usuario 
                    WHERE tm.id_materia = ? AND u.estado = 'activo'
                    ORDER BY u.nombre ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_materia]);
            $tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['tutores' => $tutores]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Fallo al consultar los tutores de la base de datos.']);
        }
    } else {
        echo json_encode(['tutores' => []]);
    }
    exit();
}

// ============================================================================
// ACCIÓN 2: GUARDAR LA NUEVA SOLICITUD DE TUTORÍA
// ============================================================================
if ($accion === 'solicitar_tutoria') {
    $id_estudiante = $_POST['id_estudiante'] ?? 0;
    $id_materia    = $_POST['id_materia'] ?? 0;
    $id_tutor      = $_POST['id_tutor'] ?? 0;
    $fecha         = $_POST['fecha'] ?? '';
    $id_bloque     = $_POST['id_bloque'] ?? 0;
    $modalidad     = $_POST['modalidad'] ?? 'presencial';
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (empty($id_estudiante) || empty($id_materia) || empty($fecha) || empty($id_bloque)) {
        echo json_encode(['error' => 'Todos los campos obligatorios deben estar completos.']);
        exit();
    }

    try {
        $min_dias = 2; 
        $max_dias = 60;
        $max_tutorias_activas = 1; 
        $sesiones_por_modulo = 7; 
        $limite_estudiantes_defecto = 10; 
        
        $stmtP = $pdo->query("SELECT clave, valor FROM parametros_mg WHERE clave IN ('MIN_DIAS_ANTICIPACION_TUTORIA', 'MAX_DIAS_ANTICIPACION_TUTORIA', 'MAX_TUTORIAS_ACTIVAS', 'SESIONES_POR_MODULO')");
        while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
            if ($row['clave'] === 'MIN_DIAS_ANTICIPACION_TUTORIA') $min_dias = (int)$row['valor'];
            if ($row['clave'] === 'MAX_DIAS_ANTICIPACION_TUTORIA') $max_dias = (int)$row['valor'];
            if ($row['clave'] === 'MAX_TUTORIAS_ACTIVAS') $max_tutorias_activas = (int)$row['valor'];
            if ($row['clave'] === 'SESIONES_POR_MODULO') $sesiones_por_modulo = (int)$row['valor']; 
        }

        $stmtActivas = $pdo->prepare("
            SELECT COUNT(*) 
            FROM tutorias t 
            JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
            WHERE te.id_estudiante = ? AND t.estado IN ('pendiente', 'confirmada', 'en_proceso')
        ");
        $stmtActivas->execute([$id_estudiante]);
        $total_activas = (int)$stmtActivas->fetchColumn();

        if ($total_activas >= $max_tutorias_activas) {
            echo json_encode(['error' => "Has alcanzado el límite del sistema de $max_tutorias_activas solicitud(es) simultánea(s) permitida(s)."]);
            exit();
        }

        $fecha_min_permitida = date('Y-m-d', strtotime("+$min_dias days"));
        $fecha_max_permitida = date('Y-m-d', strtotime("+$max_dias days"));

        if ($fecha < $fecha_min_permitida || $fecha > $fecha_max_permitida) {
            echo json_encode(['error' => "La fecha de solicitud debe estar entre " . date('d/m/Y', strtotime($fecha_min_permitida)) . " y " . date('d/m/Y', strtotime($fecha_max_permitida)) . "."]);
            exit();
        }

        $id_periodo_activo = $periodoModel->obtenerPeriodoPorFecha($fecha);
        if (!$id_periodo_activo) {
            echo json_encode(['error' => "La fecha seleccionada no corresponde a ningún periodo activo."]);
            exit();
        }

        $stmtB = $pdo->prepare("SELECT hora_inicio, hora_fin FROM bloques_horarios WHERE id_bloque = ?");
        $stmtB->execute([$id_bloque]);
        $bloque = $stmtB->fetch(PDO::FETCH_ASSOC);

        if (!$bloque) {
            echo json_encode(['error' => 'El bloque horario seleccionado no es válido.']);
            exit();
        }

        if (empty($id_tutor)) {
            $stmtAuto = $pdo->prepare("
                SELECT t.id_tutor 
                FROM tutor_materia tm
                JOIN tutores t ON tm.id_tutor = t.id_tutor
                JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE tm.id_materia = ? AND u.estado = 'activo'
                AND t.id_tutor NOT IN (
                    SELECT id_tutor FROM tutorias 
                    WHERE fecha = ? AND id_bloque = ? 
                    AND id_materia != ? 
                    AND estado NOT IN ('cancelada', 'detenido')
                )
                ORDER BY (
                    SELECT COUNT(*) FROM tutorias tut 
                    WHERE tut.id_tutor = t.id_tutor AND tut.fecha = ? AND tut.id_bloque = ? AND tut.id_materia = ?
                ) DESC
                LIMIT 1
            ");
            $stmtAuto->execute([$id_materia, $fecha, $id_bloque, $id_materia, $fecha, $id_bloque, $id_materia]);
            $tutor_disponible = $stmtAuto->fetchColumn();
            
            if ($tutor_disponible) {
                $id_tutor = $tutor_disponible;
            } else {
                echo json_encode(['error' => 'No hay docentes disponibles en el horario seleccionado.']);
                exit();
            }
        }

        $pdo->beginTransaction();

        $stmtTutoria = $pdo->prepare("SELECT id_tutoria, id_materia FROM tutorias WHERE id_tutor = ? AND fecha = ? AND id_bloque = ? AND estado IN ('pendiente', 'confirmada')");
        $stmtTutoria->execute([$id_tutor, $fecha, $id_bloque]);
        $tutoria_existente = $stmtTutoria->fetch(PDO::FETCH_ASSOC);

        if ($tutoria_existente) {
            if ($tutoria_existente['id_materia'] != $id_materia) {
                $pdo->rollBack();
                echo json_encode(['error' => 'El tutor ya tiene agendada una clase para otra materia en ese horario.']);
                exit();
            }
            $id_tutoria_final = $tutoria_existente['id_tutoria'];
        } else {
            $sqlInsert = "INSERT INTO tutorias 
                          (id_tutor, id_materia, id_bloque, fecha, id_periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, motivo_cancelacion, limite_estudiantes, tope_clases, clases_impartidas) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Por asignar', 'pendiente', '', ?, ?, 0)";
            
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $id_tutor, $id_materia, $id_bloque, $fecha, $id_periodo_activo, 
                $bloque['hora_inicio'], $bloque['hora_fin'], $modalidad,
                $limite_estudiantes_defecto, $sesiones_por_modulo 
            ]);
            
            $id_tutoria_final = $pdo->lastInsertId();
        }

        $sqlInscribir = "INSERT INTO tutoria_estudiantes (id_tutoria, id_estudiante, observaciones_estudiante) VALUES (?, ?, ?)";
        $stmtInscribir = $pdo->prepare($sqlInscribir);
        $stmtInscribir->execute([$id_tutoria_final, $id_estudiante, $observaciones]);

        // VERIFICAR LÍMITE DE ESTUDIANTES Y NOTIFICAR SOBRECUPO
        $stmtCheckL = $pdo->prepare("SELECT limite_estudiantes FROM tutorias WHERE id_tutoria = ?");
        $stmtCheckL->execute([$id_tutoria_final]);
        $limite_actual = (int)$stmtCheckL->fetchColumn();

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tutoria_estudiantes WHERE id_tutoria = ?");
        $stmtCount->execute([$id_tutoria_final]);
        $total_inscritos = (int)$stmtCount->fetchColumn();

        if ($limite_actual > 0 && $total_inscritos > $limite_actual) {
            $mensaje_alerta = "Sobrecupo: La tutoría #{$id_tutoria_final} ha excedido su límite de {$limite_actual} estudiantes (Total actual: {$total_inscritos}).";
            $sqlNotif = "INSERT INTO notificaciones (id_usuario, tipo, text_mensaje, url_enlace, leida) VALUES (1, 'critico', ?, 'index.php?seccion=cajon3&tab=tutorias', 0)";
            $stmtNotif = $pdo->prepare($sqlNotif);
            $stmtNotif->execute([$mensaje_alerta]);
        }

        $pdo->commit();

        echo json_encode(['exito' => true]);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['error' => 'Fallo interno en la base de datos al guardar la solicitud.']);
    }
    exit();
}

// ============================================================================
// ACCIÓN 3: UNIRSE A UNA TUTORÍA DISPONIBLE EXISTENTE
// ============================================================================
if ($accion === 'unirse_tutoria') {
    $id_estudiante = $_POST['id_estudiante'] ?? 0;
    $id_tutoria = $_POST['id_tutoria'] ?? 0;

    if (empty($id_estudiante) || empty($id_tutoria)) {
        echo json_encode(['error' => 'Datos de inscripción incompletos.']);
        exit();
    }

    try {
        // 1. Obtener parámetros del sistema (Máximo de tutorías activas)
        $max_tutorias_activas = 1;
        $stmtP = $pdo->query("SELECT valor FROM parametros_mg WHERE clave = 'MAX_TUTORIAS_ACTIVAS'");
        if ($rowParam = $stmtP->fetch(PDO::FETCH_ASSOC)) {
            $max_tutorias_activas = (int)$rowParam['valor'];
        }

        // 2. Verificar el límite de tutorías simultáneas del estudiante
        $stmtActivas = $pdo->prepare("
            SELECT COUNT(*) FROM tutorias t 
            JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
            WHERE te.id_estudiante = ? AND t.estado IN ('pendiente', 'confirmada', 'en_proceso')
        ");
        $stmtActivas->execute([$id_estudiante]);
        $total_activas = (int)$stmtActivas->fetchColumn();

        if ($total_activas >= $max_tutorias_activas) {
            echo json_encode(['error' => "Has alcanzado tu límite máximo de $max_tutorias_activas tutoría(s) activa(s)."]);
            exit();
        }

        // 3. Verificar si el grupo aún tiene cupo disponible
        $stmtCupo = $pdo->prepare("
            SELECT t.limite_estudiantes, (SELECT COUNT(*) FROM tutoria_estudiantes te WHERE te.id_tutoria = t.id_tutoria) as inscritos
            FROM tutorias t WHERE t.id_tutoria = ?
        ");
        $stmtCupo->execute([$id_tutoria]);
        $cupo = $stmtCupo->fetch(PDO::FETCH_ASSOC);

        if (!$cupo || $cupo['inscritos'] >= $cupo['limite_estudiantes']) {
            echo json_encode(['error' => "Este grupo ya alcanzó su límite máximo de estudiantes."]);
            exit();
        }

        // 4. Inscribir al estudiante
        $sqlInscribir = "INSERT INTO tutoria_estudiantes (id_tutoria, id_estudiante, observaciones_estudiante) VALUES (?, ?, 'Inscripción desde grupos disponibles')";
        $stmtInscribir = $pdo->prepare($sqlInscribir);
        $stmtInscribir->execute([$id_tutoria, $id_estudiante]);

        echo json_encode(['exito' => true]);

    } catch (PDOException $e) {
        // Error común: El estudiante ya está inscrito (falla la llave primaria compuesta)
        if ($e->getCode() == 23000) {
             echo json_encode(['error' => 'Ya te encuentras inscrito en esta tutoría.']);
        } else {
             echo json_encode(['error' => 'Fallo en la base de datos al procesar la inscripción.']);
        }
    }
    exit();
}

// ============================================================================
// ACCIÓN 4: ACTUALIZAR PERFIL DEL ESTUDIANTE
// ============================================================================
if ($accion === 'actualizar_perfil') {
    $id_usuario = $_SESSION['id_usuario'] ?? 0;
    
    $correo     = trim($_POST['correo'] ?? '');
    $usuario    = trim($_POST['usuario'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (empty($correo) || empty($usuario) || empty($telefono)) {
        echo json_encode(['error' => 'El correo, el usuario y el teléfono son campos obligatorios.']);
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'El formato del correo electrónico no es válido.']);
        exit();
    }

    try {
        $sqlCheck = "SELECT correo, usuario FROM usuarios WHERE (correo = ? OR usuario = ?) AND id_usuario != ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$correo, $usuario, $id_usuario]);
        
        if ($row = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            if (strtolower($row['correo']) === strtolower($correo)) {
                echo json_encode(['error' => 'Este correo electrónico ya pertenece a otra cuenta.']);
                exit();
            }
            if (strtolower($row['usuario']) === strtolower($usuario)) {
                echo json_encode(['error' => 'El nombre de usuario elegido ya está en uso.']);
                exit();
            }
        }

        if (!empty($contrasena)) {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE usuarios SET correo = ?, usuario = ?, telefono = ?, contrasena_hash = ? WHERE id_usuario = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$correo, $usuario, $telefono, $hash, $id_usuario]);
        } else {
            $sqlUpdate = "UPDATE usuarios SET correo = ?, usuario = ?, telefono = ? WHERE id_usuario = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$correo, $usuario, $telefono, $id_usuario]);
        }

        $_SESSION['usuario'] = $usuario;
        echo json_encode(['exito' => true]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Fallo interno al intentar actualizar los datos en la base de datos.']);
    }
    exit();
}

echo json_encode(['error' => 'Acción no reconocida por el servidor.']);
exit();