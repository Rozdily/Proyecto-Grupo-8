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

// Validar que el usuario que hace la petición sea realmente un Estudiante (id_rol = 3)[cite: 6]
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
            // Unir la tabla tutor_materia con tutores y usuarios para sacar los nombres[cite: 6]
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
// ACCIÓN 2: GUARDAR LA NUEVA SOLICITUD DE TUTORÍA (CON REGLAS DE FECHA)
// ============================================================================
if ($accion === 'solicitar_tutoria') {
    // Recoger los datos enviados por POST
    $id_estudiante = $_POST['id_estudiante'] ?? 0;
    $id_materia    = $_POST['id_materia'] ?? 0;
    $id_tutor      = $_POST['id_tutor'] ?? 0;
    $fecha         = $_POST['fecha'] ?? '';
    $id_bloque     = $_POST['id_bloque'] ?? 0;
    $modalidad     = $_POST['modalidad'] ?? 'presencial';
    $observaciones = trim($_POST['observaciones'] ?? '');

    // Validaciones básicas de seguridad
    if (empty($id_estudiante) || empty($id_materia) || empty($id_tutor) || empty($fecha) || empty($id_bloque)) {
        echo json_encode(['error' => 'Todos los campos obligatorios deben estar completos.']);
        exit();
    }

    try {
        // --- 1. VALIDAR REGLAS DE FECHAS (Escudo Backend) ---
        $min_dias = 2; // Valores por defecto
        $max_dias = 60;
        
        // Consultar los límites reales configurados en el Cajón 4[cite: 12]
        $stmtP = $pdo->query("SELECT clave, valor FROM parametros_mg WHERE clave IN ('MIN_DIAS_ANTICIPACION_TUTORIA', 'MAX_DIAS_ANTICIPACION_TUTORIA')");
        while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
            if ($row['clave'] === 'MIN_DIAS_ANTICIPACION_TUTORIA') $min_dias = (int)$row['valor'];
            if ($row['clave'] === 'MAX_DIAS_ANTICIPACION_TUTORIA') $max_dias = (int)$row['valor'];
        }
        
        $fecha_min_permitida = date('Y-m-d', strtotime("+$min_dias days"));
        $fecha_max_permitida = date('Y-m-d', strtotime("+$max_dias days"));

        // Bloquear si la fecha está fuera del rango permitido
        if ($fecha < $fecha_min_permitida || $fecha > $fecha_max_permitida) {
            echo json_encode(['error' => "Por reglamento, la fecha de solicitud debe estar comprendida entre el " . date('d/m/Y', strtotime($fecha_min_permitida)) . " y el " . date('d/m/Y', strtotime($fecha_max_permitida)) . "."]);
            exit();
        }
        // ----------------------------------------------------

        // 2. Obtener las horas exactas del bloque horario seleccionado[cite: 6]
        $stmtB = $pdo->prepare("SELECT hora_inicio, hora_fin FROM bloques_horarios WHERE id_bloque = ?");
        $stmtB->execute([$id_bloque]);
        $bloque = $stmtB->fetch(PDO::FETCH_ASSOC);

        if (!$bloque) {
            echo json_encode(['error' => 'El bloque horario seleccionado no es válido.']);
            exit();
        }

        // 3. Obtener el Periodo Académico Activo (Ej. I-2026)[cite: 6]
        $stmtPer = $pdo->query("SELECT codigo FROM periodos_tutoria WHERE activo = 1 LIMIT 1");
        $periodo_activo = $stmtPer->fetchColumn();
        if (!$periodo_activo) {
            $periodo_activo = 'II-2026'; // Fallback de emergencia
        }

        // 4. Validar que el estudiante no tenga ya una tutoría en esa misma fecha y bloque[cite: 6]
        $stmtCheck = $pdo->prepare("SELECT id_tutoria FROM tutorias WHERE id_estudiante = ? AND fecha = ? AND id_bloque = ? AND estado NOT IN ('cancelada', 'detenido')");
        $stmtCheck->execute([$id_estudiante, $fecha, $id_bloque]);
        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(['error' => 'Ya tienes una solicitud o tutoría programada para esa misma fecha y horario.']);
            exit();
        }

        // 5. Inserción a la base de datos[cite: 6]
        // Se inserta con estado 'pendiente', lugar 'Por asignar' (el tutor lo definirá al confirmar)[cite: 6]
        $sqlInsert = "INSERT INTO tutorias 
                      (id_estudiante, id_tutor, id_materia, id_bloque, fecha, periodo, hora_inicio, hora_fin, modalidad, lugar_o_enlace, estado, observaciones, motivo_cancelacion) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Por asignar', 'pendiente', ?, '')";
        
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            $id_estudiante, 
            $id_tutor, 
            $id_materia, 
            $id_bloque, 
            $fecha, 
            $periodo_activo, 
            $bloque['hora_inicio'], 
            $bloque['hora_fin'], 
            $modalidad, 
            $observaciones
        ]);

        echo json_encode(['exito' => true, 'mensaje' => 'Solicitud guardada correctamente.']);

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Fallo interno en la base de datos al guardar la solicitud.']);
    }
    exit();
}
// ============================================================================
// ACCIÓN 3: ACTUALIZAR PERFIL DEL ESTUDIANTE (CON VALIDACIÓN DE DUPLICADOS)
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
        // 1. Verificar que el correo o usuario no estén siendo usados por OTRA persona
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

        // 2. Preparar actualización
        if (!empty($contrasena)) {
            // Actualizar TODO (incluida contraseña)
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE usuarios SET correo = ?, usuario = ?, telefono = ?, contrasena_hash = ? WHERE id_usuario = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$correo, $usuario, $telefono, $hash, $id_usuario]);
        } else {
            // Actualizar solo datos (sin tocar contraseña)
            $sqlUpdate = "UPDATE usuarios SET correo = ?, usuario = ?, telefono = ? WHERE id_usuario = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$correo, $usuario, $telefono, $id_usuario]);
        }

        // 3. Actualizar la variable de sesión por si cambió el nombre de usuario
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