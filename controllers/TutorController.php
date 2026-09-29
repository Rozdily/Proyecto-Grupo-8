<?php
/**
 * ARCHIVO: controllers/TutorController.php
 * Controlador para procesar peticiones AJAX del portal del Tutor.
 */

// Forzar salida limpia en JSON
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión con ruta absoluta
require_once __DIR__ . '/../config/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario que hace la petición sea realmente un Tutor (id_rol = 2)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    echo json_encode(['error' => 'Acceso denegado o sesión expirada.']);
    exit();
}

$accion = $_POST['accion'] ?? '';
$id_usuario = $_SESSION['id_usuario'] ?? 0;

try {
    // Obtener el id_tutor correspondiente al usuario logueado
    $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
    $stmtTut->execute([$id_usuario]);
    $id_tutor = $stmtTut->fetchColumn();

    if (!$id_tutor) {
        echo json_encode(['error' => 'No se encontró el registro de tutor asociado a este usuario.']);
        exit();
    }

    // ============================================================================
    // ACCIÓN 1: ACEPTAR Y CONFIRMAR TUTORÍA
    // ============================================================================
    if ($accion === 'aceptar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'] ?? 0;
        $lugar_o_enlace = trim($_POST['lugar_o_enlace'] ?? '');

        if (empty($id_tutoria) || empty($lugar_o_enlace)) {
            echo json_encode(['error' => 'Debe proporcionar el lugar o el enlace de la sesión.']);
            exit();
        }

        // Verificar que la tutoría pertenezca a este tutor y esté pendiente
        $stmtCheck = $pdo->prepare("SELECT id_tutoria FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? AND estado = 'pendiente'");
        $stmtCheck->execute([$id_tutoria, $id_tutor]);
        
        if ($stmtCheck->rowCount() === 0) {
            echo json_encode(['error' => 'La solicitud no existe, ya fue procesada o no te pertenece.']);
            exit();
        }

        // Actualizar estado a 'confirmada' y guardar el lugar/enlace
        $sqlUpdate = "UPDATE tutorias SET estado = 'confirmada', lugar_o_enlace = ? WHERE id_tutoria = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$lugar_o_enlace, $id_tutoria]);

        echo json_encode(['exito' => true, 'mensaje' => 'Tutoría confirmada exitosamente.']);
        exit();
    }

    // ============================================================================
    // ACCIÓN 2: RECHAZAR TUTORÍA
    // ============================================================================
    if ($accion === 'rechazar_tutoria') {
        $id_tutoria = $_POST['id_tutoria'] ?? 0;
        $motivo = trim($_POST['motivo_cancelacion'] ?? '');

        if (empty($id_tutoria) || empty($motivo)) {
            echo json_encode(['error' => 'Debe especificar el motivo del rechazo.']);
            exit();
        }

        // Verificar que la tutoría pertenezca a este tutor y esté pendiente
        $stmtCheck = $pdo->prepare("SELECT id_tutoria FROM tutorias WHERE id_tutoria = ? AND id_tutor = ? AND estado = 'pendiente'");
        $stmtCheck->execute([$id_tutoria, $id_tutor]);
        
        if ($stmtCheck->rowCount() === 0) {
            echo json_encode(['error' => 'La solicitud no existe, ya fue procesada o no te pertenece.']);
            exit();
        }

        // Actualizar estado a 'cancelada' y guardar el motivo
        $sqlUpdate = "UPDATE tutorias SET estado = 'cancelada', motivo_cancelacion = ? WHERE id_tutoria = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$motivo, $id_tutoria]);

        echo json_encode(['exito' => true, 'mensaje' => 'Tutoría rechazada correctamente.']);
        exit();
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error interno en la base de datos al procesar la solicitud.']);
    exit();
}

echo json_encode(['error' => 'Acción no reconocida por el servidor.']);
exit();