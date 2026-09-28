<?php
/**
 * ARCHIVO: controllers/AlertaController.php
 * Controlador responsable de procesar las acciones sobre las contingencias y alertas.
 */
session_start();

// 1. SEGURIDAD DE SESIÓN Y PROTECCIÓN DE RUTA
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../views/login/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si se accede por GET, devolver al Cajón 0 del rol correspondiente
    $ruta_fallback = ($_SESSION['id_rol'] === 1) ? '../views/admin/index.php?seccion=cajon0' : '../views/login/index.php';
    header("Location: $ruta_fallback");
    exit;
}

// 2. INCLUSIÓN CENTRALIZADA DE CONEXIÓN
require_once '../config/conexion.php';

// 3. IMPORTACIÓN DEL MODELO CON LA NUEVA NOMENCLATURA INDEXADA
require_once '../models/sistema/AlertaModel/index.php';

// 4. INSTANCIACIÓN DEL MODELO
// Asumimos que $pdo se expone de forma nativa desde conexion.php
$alertaModel = new AlertaModel($pdo);

// 5. ENRUTADOR DE ACCIONES
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'mitigar_alerta':
        // Limpieza y validación estricta de los datos de entrada
        $id_notificacion = filter_input(INPUT_POST, 'id_notificacion', FILTER_VALIDATE_INT);
        $nota_resolucion = trim($_POST['nota_resolucion'] ?? '');
        $id_usuario_mitigador = $_SESSION['id_usuario'];

        if ($id_notificacion && !empty($nota_resolucion)) {
            // Ejecutamos el método del modelo para asentar la mitigación
            $resultado = $alertaModel->mitigarAlerta($id_notificacion, $nota_resolucion, $id_usuario_mitigador);
            
            if ($resultado) {
                $_SESSION['alerta_exito'] = "Contingencia mitigada exitosamente y registrada en la bitácora.";
            } else {
                $_SESSION['alerta_error'] = "Hubo un problema al procesar la mitigación. Intente de nuevo.";
            }
        } else {
            $_SESSION['alerta_error'] = "La nota de resolución es estrictamente obligatoria.";
        }

        // Redirección al panel de contingencias del Administrador (Cajón 0)
        header('Location: ../views/admin/index.php?seccion=cajon0');
        break;

    default:
        // Acción no reconocida, redirección de seguridad
        header('Location: ../views/admin/index.php?seccion=cajon0');
        break;
}
exit;