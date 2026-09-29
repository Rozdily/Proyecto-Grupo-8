<?php
/**
 * ARCHIVO: controllers/CarreraController.php
 * Controlador para procesar las acciones CRUD del Catálogo de Carreras con Auditoría.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/CarreraModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php'; // Inyección de Auditoría

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$carreraModel = new CarreraModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Identificar al Actor
$usuario_str = $_SESSION['usuario'] ?? 'Admin';
$correo_str = $_SESSION['correo'] ?? 'Sin correo';
$actor_completo = $usuario_str . ' (' . $correo_str . ')';

// Variables base para la redirección SPA
$url_base = "../views/admin/index.php?seccion=cajon2&tab=oferta";

switch ($accion) {
    
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_carrera = trim($_POST['nombre_carrera'] ?? '');

            if (empty($nombre_carrera)) {
                header("Location: $url_base&error=" . urlencode("El nombre de la carrera es obligatorio."));
                exit();
            }

            if ($carreraModel->existeNombre($nombre_carrera)) {
                header("Location: $url_base&error=" . urlencode("La carrera '$nombre_carrera' ya se encuentra registrada en el sistema."));
                exit();
            }

            try {
                $carreraModel->crear($nombre_carrera);
                $nuevo_id = $pdo->lastInsertId();

                // AUDITORÍA: Leer fila completa recién creada
                $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id_carrera = ?");
                $stmt->execute([$nuevo_id]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'CREAR', 'carreras', $nuevo_id, [], $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Carrera registrada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_carrera = $_POST['id_registro'] ?? null;
            $nombre_carrera = trim($_POST['nombre_carrera'] ?? '');

            if (!$id_carrera || empty($nombre_carrera)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la carrera."));
                exit();
            }

            if ($carreraModel->existeNombre($nombre_carrera, $id_carrera)) {
                header("Location: $url_base&error=" . urlencode("El nombre '$nombre_carrera' ya está en uso por otra carrera."));
                exit();
            }

            try {
                // AUDITORÍA PASO 1: Leer antes
                $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id_carrera = ?");
                $stmt->execute([$id_carrera]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $carreraModel->actualizar($id_carrera, $nombre_carrera);

                // AUDITORÍA PASO 2: Leer después
                $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id_carrera = ?");
                $stmt->execute([$id_carrera]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'ACTUALIZAR', 'carreras', $id_carrera, $datos_antes, $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Carrera actualizada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'eliminar':
        $id_carrera = $_GET['id'] ?? null;
        
        if ($id_carrera) {
            try {
                // AUDITORÍA PASO 1: Leer antes de borrar
                $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id_carrera = ?");
                $stmt->execute([$id_carrera]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $carreraModel->eliminar($id_carrera);

                // AUDITORÍA PASO 2: Registrar eliminación
                $auditoriaModel->registrarAccion($actor_completo, 'ELIMINAR', 'carreras', $id_carrera, $datos_antes, []);

                header("Location: $url_base&exito=" . urlencode("La carrera fue eliminada exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("No se puede borrar la carrera porque existen materias o estudiantes vinculados a ella."));
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