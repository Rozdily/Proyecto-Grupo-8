<?php
/**
 * ARCHIVO: controllers/EstudianteController.php
 * Controlador para procesar peticiones AJAX del portal del Estudiante.
 */

// Forzar salida limpia en JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión con ruta absoluta
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario que hace la petición sea realmente un Estudiante (id_rol = 3)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 3) {
    echo json_encode(['error' => 'Acceso denegado o sesión expirada.']);
    exit();
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

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
// ACCIÓN 2: GUARDAR LA NUEVA SOLICITUD DE TUTORÍA (LÓGICA DE GRUPOS)
// ============================================================================
if ($accion === 'solicitar_tutoria') {
    $id_estudiante = $_POST['id_estudiante'] ?? 0;
    $id_materia    = $_POST['id_materia'] ?? 0;
    $id_tutor      = $_POST['id_tutor'] ?? 0;
    $fecha         = $_POST['fecha'] ?? '';
    $id_bloque     = $_POST['id_bloque'] ?? 0;
    $modalidad     = $_POST['modalidad'] ?? 'presencial';
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (empty($id_estudiante) || empty($id_materia) || empty($id_tutor) || empty($fecha) || empty($id_bloque)) {
        echo json_encode(['error' => 'Todos los campos obligatorios deben estar completos.']);
        exit();
    }

    try {
        // --- 1. VALIDAR REGLAS DE FECHAS (Escudo Backend) ---
        $min_dias = 2; 
        $max_dias = 60;
        
        $stmtP = $pdo->query("SELECT clave, valor FROM parametros_mg WHERE clave IN ('MIN_DIAS_ANTICIPACION_TUTORIA', 'MAX_DIAS_ANTICIPACION_TUTORIA')");
        while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
            if ($row['clave'] === 'MIN_DIAS_ANTICIPACION_TUTORIA') $min_dias = (int)$row['valor'];
            if ($row['clave'] === 'MAX_DIAS_ANTICIPACION_TUTORIA') $max_dias = (int)$row['valor'];
        }
        
        $fecha_min_permitida = date('Y-m-d', strtotime("+$min_dias days"));
        $fecha_max_permitida = date('Y-m-d', strtotime("+$max_dias days"));

        if ($fecha < $fecha_min_permitida || $fecha > $fecha_max_permitida) {
            echo json_encode(['error' => "Por reglamento, la fecha de solicitud debe estar comprendida entre el " . date('d/m/Y', strtotime($fecha_min_permitida)) . " y el " . date('d/m/Y', strtotime($fecha_max_permitida)) . "."]);
            exit();
        }

        // 2. Obtener las horas exactas del bloque horario seleccionado
        $stmtB = $pdo->prepare("SELECT hora_inicio, hora_fin FROM bloques_horarios WHERE id_bloque = ?");
        $stmtB->execute([$id_bloque]);
        $bloque = $stmtB->fetch(PDO::FETCH_ASSOC);

        if (!$bloque) {
            echo json_encode(['error' => 'El bloque horario seleccionado no es válido.']);
            exit();
        }

        // 3. Validar que el estudiante no tenga ya una clase cruzada (Usando la tabla puente)
        $stmtCheck = $pdo->prepare("
            SELECT t.id_tutoria FROM tutorias t 
            JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
            WHERE te.id_estudiante = ? AND t.fecha = ? AND t.id_bloque = ? AND t.estado NOT IN ('cancelada', 'detenido')
        ");
        $stmtCheck->execute([$id_estudiante, $fecha, $id_bloque]);
        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(['error' => 'Ya tienes una solicitud o estás inscrito en una tutoría para esa misma fecha y horario.']);
            exit();
        }

        // 4. Iniciar Transacción para inserción grupal
        $pdo->beginTransaction();

        // 5. Verificar si el tutor YA TIENE una sesión en ese bloque
        $stmtTutoria = $pdo->prepare("SELECT id_tutoria, id_materia FROM tutorias WHERE id_tutor = ? AND fecha = ? AND id_bloque = ? AND estado IN ('pendiente', 'confirmada')");
        $stmtTutoria->execute([$id_tutor, $fecha, $id_bloque]);
        $tutoria_existente = $stmtTutoria->fetch(PDO::FETCH_ASSOC);

        if ($tutoria_existente) {
            // El tutor ya tiene una clase. ¿Es de la misma materia?
            if ($tutoria_existente['id_materia'] != $id_materia) {
                $pdo->rollBack();
                echo json_encode(['error' => 'El tutor ya tiene agendada una clase para otra materia en ese horario. Por favor selecciona otro horario o tutor.']);
                exit();
            }
            // Mismo tutor, misma materia, misma hora -> Inscribir al estudiante al grupo existente
            $id_tutoria_final = $tutoria_existente['id_tutoria'];
        } else {
            // No hay clase programada, creamos el contenedor de la sesión en la tabla principal
            $stmtPer = $pdo->query("SELECT codigo FROM periodos_tutoria WHERE activo = 1 LIMIT 1");
            $periodo_activo = $stmtPer->fetchColumn() ?: 'II-2026';

            $sqlInsert = "INSERT INTO tutorias 
                          (id_tutor, id_materia, id_bloque, fecha, periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, motivo_cancelacion) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Por asignar', 'pendiente', '')";
            
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $id_tutor, $id_materia, $id_bloque, $fecha, $periodo_activo, 
                $bloque['hora_inicio'], $bloque['hora_fin'], $modalidad
            ]);
            
            $id_tutoria_final = $pdo->lastInsertId();
        }

        // 6. Inscribir al estudiante en la sesión (Tabla puente)
        $sqlInscribir = "INSERT INTO tutoria_estudiantes (id_tutoria, id_estudiante, observaciones_estudiante) VALUES (?, ?, ?)";
        $stmtInscribir = $pdo->prepare($sqlInscribir);
        $stmtInscribir->execute([$id_tutoria_final, $id_estudiante, $observaciones]);

        $pdo->commit();

        echo json_encode(['exito' => true, 'mensaje' => 'Solicitud procesada correctamente. Te has inscrito en la tutoría.']);

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['error' => 'Fallo interno en la base de datos al guardar la solicitud.']);
    }
    exit();
}

// ============================================================================
// ACCIÓN 3: ACTUALIZAR PERFIL DEL ESTUDIANTE
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

// Acción por defecto si no reconoce ninguna
echo json_encode(['error' => 'Acción no reconocida por el servidor.']);
exit();