<?php
/**
 * ARCHIVO: controllers/GrupoTesisController.php
 * Controlador para procesar las acciones CRUD de Grupos de Tesis / Cohortes con Auditoría.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/GrupoTesisModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php'; // Inyección de Auditoría

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/index.php?error=acceso_denegado");
    exit();
}

$grupoTesisModel = new GrupoTesisModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Identificar al Actor
$usuario_str = $_SESSION['usuario'] ?? 'Admin';
$correo_str = $_SESSION['correo'] ?? 'Sin correo';
$actor_completo = $usuario_str . ' (' . $correo_str . ')';

$url_base = "../views/admin/index.php?seccion=cajon2&tab=cohortes";

switch ($accion) {
    
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activa = $_POST['activa'] ?? 1;

            if (empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Todos los campos son obligatorios."));
                exit();
            }

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            if ($grupoTesisModel->existeCodigo($codigo)) {
                header("Location: $url_base&error=" . urlencode("El código de cohorte ($codigo) ya se encuentra registrado."));
                exit();
            }

            try {
                $grupoTesisModel->crear($codigo, $nombre, $fecha_inicio, $fecha_fin, $activa);
                $nuevo_id = $pdo->lastInsertId();

                // AUDITORÍA: Leer fila completa recién creada
                $stmt = $pdo->prepare("SELECT * FROM cohortes_mg WHERE id_cohorte = ?");
                $stmt->execute([$nuevo_id]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'CREAR', 'cohortes_mg', $nuevo_id, [], $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Cohorte / Grupo de Tesis creado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_cohorte = $_POST['id_registro'] ?? null;
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activa = $_POST['activa'] ?? 1;

            if (!$id_cohorte || empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la cohorte."));
                exit();
            }

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            if ($grupoTesisModel->existeCodigo($codigo, $id_cohorte)) {
                header("Location: $url_base&error=" . urlencode("El código ($codigo) ya está en uso por otra cohorte."));
                exit();
            }

            try {
                // AUDITORÍA PASO 1: Leer antes
                $stmt = $pdo->prepare("SELECT * FROM cohortes_mg WHERE id_cohorte = ?");
                $stmt->execute([$id_cohorte]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $grupoTesisModel->actualizar($id_cohorte, $codigo, $nombre, $fecha_inicio, $fecha_fin, $activa);

                // AUDITORÍA PASO 2: Leer después
                $stmt = $pdo->prepare("SELECT * FROM cohortes_mg WHERE id_cohorte = ?");
                $stmt->execute([$id_cohorte]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'ACTUALIZAR', 'cohortes_mg', $id_cohorte, $datos_antes, $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Cohorte actualizada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'eliminar':
        $id_cohorte = $_GET['id'] ?? null;
        
        if ($id_cohorte) {
            try {
                // AUDITORÍA PASO 1: Leer antes de borrar
                $stmt = $pdo->prepare("SELECT * FROM cohortes_mg WHERE id_cohorte = ?");
                $stmt->execute([$id_cohorte]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $grupoTesisModel->eliminar($id_cohorte);

                // AUDITORÍA PASO 2: Registrar eliminación
                $auditoriaModel->registrarAccion($actor_completo, 'ELIMINAR', 'cohortes_mg', $id_cohorte, $datos_antes, []);

                header("Location: $url_base&exito=" . urlencode("La cohorte fue borrada del sistema."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("No se pudo borrar la cohorte porque existen expedientes o hitos asignados a ella."));
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