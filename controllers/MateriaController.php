<?php
/**
 * ARCHIVO: controllers/MateriaController.php
 * Controlador para procesar las acciones CRUD de Materias con Auditoría.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/estructuras/MateriaModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php'; // Inyección de Auditoría

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que el usuario sea administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$materiaModel = new MateriaModel($pdo);
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
            $nombre_materia = trim($_POST['nombre_materia'] ?? '');
            $id_carrera = $_POST['id_carrera'] ?? null;

            if (empty($nombre_materia) || empty($id_carrera)) {
                header("Location: $url_base&error=" . urlencode("El nombre de la materia y la carrera son obligatorios."));
                exit();
            }

            if ($materiaModel->existeMateriaEnCarrera($nombre_materia, $id_carrera)) {
                header("Location: $url_base&error=" . urlencode("La materia '$nombre_materia' ya se encuentra registrada en la carrera seleccionada."));
                exit();
            }

            try {
                $materiaModel->crear($nombre_materia, $id_carrera);
                $nuevo_id = $pdo->lastInsertId();

                // AUDITORÍA: Leer fila completa recién creada
                $stmt = $pdo->prepare("SELECT * FROM materias WHERE id_materia = ?");
                $stmt->execute([$nuevo_id]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'CREAR', 'materias', $nuevo_id, [], $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Materia registrada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno de BD: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_materia = $_POST['id_registro'] ?? null;
            $nombre_materia = trim($_POST['nombre_materia'] ?? '');
            $id_carrera = $_POST['id_carrera'] ?? null;

            if (!$id_materia || empty($nombre_materia) || empty($id_carrera)) {
                header("Location: $url_base&error=" . urlencode("Datos incompletos para actualizar la materia."));
                exit();
            }

            if ($materiaModel->existeMateriaEnCarrera($nombre_materia, $id_carrera, $id_materia)) {
                header("Location: $url_base&error=" . urlencode("El nombre '$nombre_materia' ya está en uso en esta carrera."));
                exit();
            }

            try {
                // AUDITORÍA PASO 1: Leer antes
                $stmt = $pdo->prepare("SELECT * FROM materias WHERE id_materia = ?");
                $stmt->execute([$id_materia]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $materiaModel->actualizar($id_materia, $nombre_materia, $id_carrera);

                // AUDITORÍA PASO 2: Leer después
                $stmt = $pdo->prepare("SELECT * FROM materias WHERE id_materia = ?");
                $stmt->execute([$id_materia]);
                $datos_despues = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $auditoriaModel->registrarAccion($actor_completo, 'ACTUALIZAR', 'materias', $id_materia, $datos_antes, $datos_despues);

                header("Location: $url_base&exito=" . urlencode("Materia actualizada correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("Error interno al actualizar: " . $e->getMessage()));
                exit();
            }
        }
        break;

    case 'eliminar':
        $id_materia = $_GET['id'] ?? null;
        
        if ($id_materia) {
            try {
                // AUDITORÍA PASO 1: Leer antes de borrar
                $stmt = $pdo->prepare("SELECT * FROM materias WHERE id_materia = ?");
                $stmt->execute([$id_materia]);
                $datos_antes = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                $materiaModel->eliminar($id_materia);

                // AUDITORÍA PASO 2: Registrar eliminación
                $auditoriaModel->registrarAccion($actor_completo, 'ELIMINAR', 'materias', $id_materia, $datos_antes, []);

                header("Location: $url_base&exito=" . urlencode("La materia fue eliminada exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: $url_base&error=" . urlencode("No se puede borrar la materia porque tiene tutorías u otros registros vinculados."));
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