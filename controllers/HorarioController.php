<?php
/**
 * ARCHIVO: controllers/HorarioController.php
 * Controlador para procesar la disponibilidad horaria de los tutores con Auditoría.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuarios/TutorModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php'; // Inyección Auditoría

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$tutorModel = new TutorModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Identificar al Actor
$usuario_str = $_SESSION['usuario'] ?? 'Admin';
$correo_str = $_SESSION['correo'] ?? 'Sin correo';
$actor_completo = $usuario_str . ' (' . $correo_str . ')';

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // AGREGAR NUEVO BLOQUE DE HORARIO AL TUTOR
    // ------------------------------------------------------------------------
    case 'agregar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario  = $_POST['id_usuario'] ?? null; 
            $dia_semana  = $_POST['dia_semana'] ?? '';
            $hora_inicio = $_POST['hora_inicio'] ?? '';
            $hora_fin    = $_POST['hora_fin'] ?? '';

            if (!$id_usuario) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=tutores&error=" . urlencode("ID de usuario no válido."));
                exit();
            }

            $retenerDatos = "&old_dia=" . urlencode($dia_semana) . 
                            "&old_inicio=" . urlencode($hora_inicio) . 
                            "&old_fin=" . urlencode($hora_fin);

            if (empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("Todos los campos son obligatorios.") . $retenerDatos);
                exit();
            }

            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("La hora de finalización debe ser mayor.") . $retenerDatos);
                exit();
            }

            $id_tutor_real = $tutorModel->obtenerIdTutorPorUsuario($id_usuario);
            
            if (!$id_tutor_real) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("Error: Este usuario no es tutor."));
                exit();
            }

            try {
                $tutorModel->agregarHorario($id_tutor_real, $dia_semana, $hora_inicio, $hora_fin);
                
                // AUDITORÍA
                $datos_despues = [
                    'id_tutor' => $id_tutor_real,
                    'dia_semana' => $dia_semana,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin' => $hora_fin,
                    'estado' => 'Activo'
                ];
                $auditoriaModel->registrarAccion($actor_completo, 'CREAR', 'disponibilidad_tutor', $id_tutor_real, [], $datos_despues);

                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&exito=" . urlencode("Horario agregado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("Error de sistema al agregar.") . $retenerDatos);
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR BLOQUE DE HORARIO EXISTENTE
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_disponibilidad = $_GET['id_disponibilidad'] ?? null;
        $id_usuario        = $_GET['id_usuario'] ?? null; 

        if ($id_disponibilidad && $id_usuario) {
            try {
                // AUDITORÍA: Generamos un registro descriptivo antes de borrar
                $datos_antes = [
                    'id_registro_horario' => $id_disponibilidad,
                    'id_usuario_tutor' => $id_usuario,
                    'accion_realizada' => 'Bloque de horario eliminado manualmente'
                ];

                $tutorModel->eliminarHorario($id_disponibilidad);

                $auditoriaModel->registrarAccion($actor_completo, 'ELIMINAR', 'disponibilidad_tutor', $id_disponibilidad, $datos_antes, []);

                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&exito=" . urlencode("El bloque de horario fue eliminado."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("No se pudo eliminar el horario."));
                exit();
            }
        } else {
            header("Location: ../views/admin/index.php?seccion=cajon1&sub=tutores");
            exit();
        }
        break;

    default:
        header("Location: ../views/admin/index.php?seccion=cajon1&sub=tutores");
        exit();
}