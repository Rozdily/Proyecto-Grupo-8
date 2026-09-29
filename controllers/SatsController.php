<?php
/**
 * ARCHIVO: controllers/SatsController.php
 * Motor de procesamiento definitivo para la Importación SATS (Estudiantes).
 */

// Forzar salida limpia en JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión con ruta absoluta segura
$ruta_conexion = __DIR__ . '/../config/conexion.php';
if (!file_exists($ruta_conexion)) {
    echo json_encode(['error_critico' => 'No se encontró el archivo de conexión.']);
    exit();
}
require_once $ruta_conexion;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    echo json_encode(['error' => 'Acceso denegado. Se requiere nivel de administrador.']);
    exit();
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// ============================================================================
// ACCIÓN 1: DESCARGAR PLANTILLA DE REFERENCIA
// ============================================================================
if ($accion === 'descargar_plantilla') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Plantilla_Importacion_SATS.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para tildes en Excel
    
    fputcsv($output, ['RU (Obligatorio)', 'Nombres (Obligatorio)', 'Apellidos (Obligatorio)', 'Correo (Obligatorio)', 'Telefono (Opcional)', 'Carrera (Obligatorio)', 'Semestre (Obligatorio)'], ';', '"', '\\');
    fputcsv($output, ['RU-2026-9999', 'Juan Carlos', 'Perez Silva', 'juan.perez@upds.edu.bo', '70012345', 'Ingeniería de Sistemas', '5'], ';', '"', '\\');
    
    fclose($output);
    exit();
}

// Cargar catálogos en memoria
try {
    $stmt = $pdo->query("SELECT id_carrera, LOWER(TRIM(nombre_carrera)) as nombre_limpio FROM carreras");
    $carreras_db = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $carreras_db[$row['nombre_limpio']] = $row['id_carrera'];
    }

    $stmt = $pdo->query("SELECT LOWER(TRIM(registro_universitario)) FROM estudiantes");
    $rus_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->query("SELECT LOWER(TRIM(correo)) FROM usuarios");
    $correos_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    echo json_encode(['error_critico' => 'Error al consultar catálogos de la BD: ' . $e->getMessage()]);
    exit();
}

function procesarFilaEstudiante($fila, $carreras_db, $rus_existentes, $correos_existentes) {
    if (empty(array_filter($fila))) return null;

    // ESCUDO ANTI-EXCEL: Forzar todas las celdas a UTF-8 por si el archivo viene en formato antiguo (ANSI/Windows-1252)
    $fila = array_map(function($celda) {
        if (!mb_check_encoding($celda, 'UTF-8')) {
            return mb_convert_encoding($celda, 'UTF-8', 'Windows-1252');
        }
        return $celda;
    }, $fila);

    $ru = isset($fila[0]) ? trim($fila[0]) : '';
    $nombres = isset($fila[1]) ? trim($fila[1]) : '';
    $apellidos = isset($fila[2]) ? trim($fila[2]) : '';
    $correo = isset($fila[3]) ? trim($fila[3]) : '';
    $telefono = isset($fila[4]) && trim($fila[4]) !== '' ? trim($fila[4]) : '00000000'; 
    $carrera = isset($fila[5]) ? trim($fila[5]) : '';
    $semestre = isset($fila[6]) && is_numeric(trim($fila[6])) ? (int)trim($fila[6]) : 0;

    $datos_limpios = [$ru, $nombres, $apellidos, $correo, $telefono, $carrera, $semestre];
    
    if (empty($ru) || empty($nombres) || empty($apellidos) || empty($correo) || empty($carrera) || $semestre === 0) {
        return ['estado' => 'error', 'datos' => $datos_limpios, 'mensaje' => 'Faltan datos obligatorios o el semestre es inválido.'];
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return ['estado' => 'error', 'datos' => $datos_limpios, 'mensaje' => 'Formato de correo electrónico inválido.'];
    }
    
    $carrera_lower = strtolower($carrera);
    if (!isset($carreras_db[$carrera_lower])) {
        return ['estado' => 'error', 'datos' => $datos_limpios, 'mensaje' => 'La carrera no existe en la base de datos.'];
    }
    if (in_array(strtolower($ru), $rus_existentes)) {
        return ['estado' => 'advertencia', 'datos' => $datos_limpios, 'mensaje' => 'El RU ya está registrado. Se omitirá.'];
    }
    if (in_array(strtolower($correo), $correos_existentes)) {
        return ['estado' => 'error', 'datos' => $datos_limpios, 'mensaje' => 'El correo ya pertenece a otro usuario.'];
    }

    return [
        'estado' => 'valido', 
        'datos' => $datos_limpios, 
        'mensaje' => 'Listo para importar',
        'id_carrera' => $carreras_db[$carrera_lower]
    ];
}

// ============================================================================
// ACCIÓN 2: PREVISUALIZAR (AJAX)
// ============================================================================
if ($accion === 'previsualizar') {
    if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error_critico' => 'No se recibió ningún archivo válido.']);
        exit();
    }

    $archivo = $_FILES['archivo_csv']['tmp_name'];
    $handle = fopen($archivo, "r");
    
    $cabeceras_leidas = fgetcsv($handle, 1000, ";", '"', '\\');
    if ($cabeceras_leidas && count($cabeceras_leidas) == 1 && strpos($cabeceras_leidas[0], ',') !== false) {
        $cabeceras_leidas = explode(',', $cabeceras_leidas[0]);
    }

    if (!$cabeceras_leidas || count($cabeceras_leidas) < 7) {
        fclose($handle);
        echo json_encode(['error_critico' => 'ESTRUCTURA INVÁLIDA: El archivo debe tener exactamente 7 columnas. Utiliza la plantilla oficial.']);
        exit();
    }

    $cabeceras = ['RU', 'Nombres', 'Apellidos', 'Correo', 'Teléfono', 'Carrera', 'Semestre'];
    $filas_procesadas = [];
    $resumen = ['validos' => 0, 'advertencias' => 0, 'errores' => 0];
    $contador = 0;

    while (($data = fgetcsv($handle, 1000, ";", '"', '\\')) !== FALSE) {
        if (count($data) == 1 && strpos($data[0], ',') !== false) {
            $data = explode(',', $data[0]);
        }

        $resultado = procesarFilaEstudiante($data, $carreras_db, $rus_existentes, $correos_existentes);
        
        if ($resultado) {
            if ($resultado['estado'] === 'valido') $resumen['validos']++;
            if ($resultado['estado'] === 'advertencia') $resumen['advertencias']++;
            if ($resultado['estado'] === 'error') $resumen['errores']++;
            
            if ($contador < 100) {
                $filas_procesadas[] = [
                    'estado' => $resultado['estado'],
                    'datos' => $resultado['datos'],
                    'mensaje' => $resultado['mensaje']
                ];
            }
            $contador++;
        }
    }
    fclose($handle);

    if ($contador === 0) {
        echo json_encode(['error_critico' => 'ARCHIVO VACÍO: El documento no contiene registros para procesar.']);
        exit();
    }

    echo json_encode([
        'resumen' => $resumen,
        'cabeceras' => $cabeceras,
        'filas' => $filas_procesadas
    ]);
    exit();
}

// ============================================================================
// ACCIÓN 3: IMPORTAR DEFINITIVAMENTE (AJAX)
// ============================================================================
if ($accion === 'importar') {
    if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'No se recibió ningún archivo válido.']);
        exit();
    }

    $archivo = $_FILES['archivo_csv']['tmp_name'];
    $nombre_archivo = $_FILES['archivo_csv']['name'];
    $handle = fopen($archivo, "r");
    
    $esPrimeraFila = true;
    $insertados = 0;
    $id_admin = $_SESSION['id_usuario'] ?? 1;

    try {
        $pdo->beginTransaction();

        $stmtImport = $pdo->prepare("INSERT INTO importaciones_mg (archivo_nombre, ejecutado_por) VALUES (?, ?)");
        $stmtImport->execute([$nombre_archivo, $id_admin]);
        $id_importacion = $pdo->lastInsertId();

        $stmtUser = $pdo->prepare("INSERT INTO usuarios (id_rol, nombre, apellido, correo, usuario, contrasena_hash, telefono, estado) VALUES (3, ?, ?, ?, ?, ?, ?, 'activo')");
        $stmtEstudiante = $pdo->prepare("INSERT INTO estudiantes (id_usuario, id_carrera, semestre, registro_universitario) VALUES (?, ?, ?, ?)");
        $stmtLog = $pdo->prepare("INSERT INTO importaciones_mg_detalle (id_importacion, fila_numero, resultado, mensaje_error, datos_fila) VALUES (?, ?, ?, ?, ?)");

        $fila_num = 1;
        $exitos = 0;
        $errores = 0;

        while (($data = fgetcsv($handle, 1000, ";", '"', '\\')) !== FALSE) {
            if (count($data) == 1 && strpos($data[0], ',') !== false) {
                $data = explode(',', $data[0]);
            }

            if ($esPrimeraFila) {
                $esPrimeraFila = false;
                $fila_num++;
                continue;
            }

            $resultado = procesarFilaEstudiante($data, $carreras_db, $rus_existentes, $correos_existentes);
            
            if ($resultado && $resultado['estado'] === 'valido') {
                $ru = $resultado['datos'][0];
                $nombres = $resultado['datos'][1];
                $apellidos = $resultado['datos'][2];
                $correo = $resultado['datos'][3];
                $telefono = $resultado['datos'][4];
                $id_carrera = $resultado['id_carrera'];
                $semestre = $resultado['datos'][6];

                $usuario_login = strtolower($ru);
                $password_hash = password_hash($ru, PASSWORD_DEFAULT);

                try {
                    $stmtUser->execute([$nombres, $apellidos, $correo, $usuario_login, $password_hash, $telefono]);
                    $id_usuario_nuevo = $pdo->lastInsertId();

                    $stmtEstudiante->execute([$id_usuario_nuevo, $id_carrera, $semestre, $ru]);
                    
                    $stmtLog->execute([$id_importacion, $fila_num, 'exito', 'Importado correctamente', json_encode($resultado['datos'])]);
                    $exitos++;
                    $insertados++;

                    $rus_existentes[] = strtolower($ru);
                    $correos_existentes[] = strtolower($correo);

                } catch (Exception $e) {
                    $stmtLog->execute([$id_importacion, $fila_num, 'error', 'Fallo SQL: ' . $e->getMessage(), json_encode($resultado['datos'])]);
                    $errores++;
                }
            } else if ($resultado && $resultado['estado'] !== 'valido') {
                $estado_log = $resultado['estado'] === 'advertencia' ? 'advertencia' : 'error';
                $stmtLog->execute([$id_importacion, $fila_num, $estado_log, $resultado['mensaje'], json_encode($resultado['datos'])]);
                if ($estado_log === 'error') $errores++;
            }
            $fila_num++;
        }

        $stmtUpdate = $pdo->prepare("UPDATE importaciones_mg SET total_filas = ?, filas_exito = ?, filas_error = ? WHERE id_importacion = ?");
        $stmtUpdate->execute([$fila_num - 2, $exitos, $errores, $id_importacion]);

        $pdo->commit();
        fclose($handle);

        echo json_encode(['exito' => true, 'insertados' => $insertados]);

    } catch (Exception $e) {
        $pdo->rollBack();
        if (is_resource($handle)) fclose($handle);
        echo json_encode(['error' => 'Fallo crítico en BD: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['error' => 'Acción no reconocida.']);
?>