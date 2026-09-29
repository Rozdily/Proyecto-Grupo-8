<?php
/**
 * ARCHIVO: controllers/PeriodoController.php
 * Controlador para procesar las acciones CRUD de Periodos de Tutoría con Auditoría.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/PeriodoModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php'; // Inyección de Auditoría

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$periodoModel = new PeriodoModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Identificar al Actor
$usuario_str = $_SESSION['usuario'] ?? 'Admin';
$correo_str = $_SESSION['correo'] ?? 'Sin correo';
$actor_completo = $usuario_str . ' (' . $correo_str . ')';

$url_base = "../views/admin/index.php?seccion=cajon2&tab=periodos";

switch ($accion) {
    
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activo = $_POST['activo'] ?? 1;
            $creado_por = $_SESSION['id_usuario'] ?? 1;

            if (empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Todos los campos son obligatorios."));
                exit();
            }

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            if ($periodoModel->existeCodigo($codigo)) {
                header("Location: $url_base&error=" . urlencode("El código de periodo ($codigo) ya se encuentra registrado."));
                exit();
            }

            try {
                $periodoModel->crear($codigo, $nombre, $fecha_inicio, $fecha_fin, $activo, $creado_por);
                $nuevo_id = $pdo->lastInsertId();

                // AUDITORÍA: Leer fila completa recién creada
                $stmt = $pdo->prepare("SELECT * FROM periodos_tutoria WHERE id_periodo = ?");
                $stmt->execute([$nuevo_id]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'CREAR', 'periodos_tutoria', $nuevo_id, [], $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Periodo creado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_periodo = $_POST['id_registro'] ?? null;
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $fecha_inicio = $_POST['fecha_inicio'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $activo = $_POST['activo'] ?? 1;

            if (!$id_periodo || empty($codigo) || empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar el periodo."));
                exit();
            }

            if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                header("Location: $url_base&error=" . urlencode("La fecha de inicio debe ser menor a la fecha de finalización."));
                exit();
            }

            if ($periodoModel->existeCodigo($codigo, $id_periodo)) {
                header("Location: $url_base&error=" . urlencode("El código ($codigo) ya está en uso por otro periodo."));
                exit();
            }

            try {
                // AUDITORÍA PASO 1: Leer antes
                $stmt = $pdo->prepare("SELECT * FROM periodos_tutoria WHERE id_periodo = ?");
                $stmt->execute([$id_periodo]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $periodoModel->actualizar($id_periodo, $codigo, $nombre, $fecha_inicio, $fecha_fin, $activo);

                // AUDITORÍA PASO 2: Leer después
                $stmt = $pdo->prepare("SELECT * FROM periodos_tutoria WHERE id_periodo = ?");
                $stmt->execute([$id_periodo]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'ACTUALIZAR', 'periodos_tutoria', $id_periodo, $datos_antes, $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Periodo actualizado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'eliminar':
        $id_periodo = $_GET['id'] ?? null;
        
        if ($id_periodo) {
            try {
                // AUDITORÍA PASO 1: Leer antes de borrar
                $stmt = $pdo->prepare("SELECT * FROM periodos_tutoria WHERE id_periodo = ?");
                $stmt->execute([$id_periodo]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $periodoModel->eliminar($id_periodo);

                // AUDITORÍA PASO 2: Registrar eliminación
                $auditoriaModel->registrarAccion($actor_completo, 'ELIMINAR', 'periodos_tutoria', $id_periodo, $datos_antes, []);

                header("Location: $url_base&exito=" . urlencode("El periodo fue borrado del sistema."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("No se pudo borrar el periodo porque ya tiene tutorías o datos enlazados a él."));
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