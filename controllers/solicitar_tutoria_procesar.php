<?php
// 1. Evitamos errores invisibles de cabeceras en el servidor
ob_start();

// 2. Cargamos el control de sesiones y la conexión
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../config/conexion.php';

// 3. CONTROL DE EXPULSIÓN: Si se rompe la ruta al redirigir, te desloguea. 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'estudiante') {
    header('Location: ../views/login/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/estudiante/solicitar_tutoria.php');
    exit;
}

// 4. Capturamos los datos del formulario de tutorías
$id_estudiante    = $_POST['id_estudiante'] ?? null;
$id_materia       = $_POST['id_materia'] ?? null;
$fecha_solicitada = $_POST['fecha_solicitada'] ?? null;
$hora_solicitada  = $_POST['hora_solicitada'] ?? null;
$motivo           = trim($_POST['motivo'] ?? '');
$estado_solicitud = 'pendiente'; 

if (empty($id_estudiante) || empty($id_materia) || empty($fecha_solicitada) || empty($hora_solicitada) || empty($motivo)) {
    header('Location: ../views/estudiante/solicitar_tutoria.php?error=vacio');
    exit;
}

try {
    // AQUI ESTABA EL ERROR: Definimos explícitamente la consulta SQL que faltaba
    $sql = "INSERT INTO solicitudes_tutorias (id_estudiante, id_materia, fecha_solicitada, hora_solicitada, motivo, estado_solicitud) 
            VALUES (:id_estudiante, :id_materia, :fecha_solicitada, :hora_solicitada, :motivo, :estado_solicitud)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_estudiante'    => $id_estudiante,
        ':id_materia'       => $id_materia,
        ':fecha_solicitada' => $fecha_solicitada,
        ':hora_solicitada'  => $hora_solicitada,
        ':motivo'           => $motivo,
        ':estado_solicitud' => $estado_solicitud
    ]);

    $_SESSION['solicitud_exito'] = "Su solicitud de tutoría ha sido enviada con éxito al docente.";
    
    // Redirección limpia a la lista de pendientes
    header('Location: ../views/estudiante/solicitudes_pendientes.php');
    exit;

} catch (Throwable $e) { // Cambiado a Throwable para capturar errores de código y de Base de Datos
    // Limpiamos el búfer para asegurarnos de que el texto del error se imprima en pantalla
    ob_end_clean(); 
    
    echo "<h3>⚠️ Error detectado en el proceso:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . " en la línea " . $e->getLine() . "</p>";
    exit;
}
