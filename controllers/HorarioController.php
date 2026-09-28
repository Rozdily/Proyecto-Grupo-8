<?php
/**
 * ARCHIVO: controllers/HorarioController.php
 * Controlador para procesar la asignación y eliminación de la disponibilidad horaria de los tutores.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuarios/TutorModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$tutorModel = new TutorModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // AGREGAR NUEVO BLOQUE DE HORARIO AL TUTOR
    // ------------------------------------------------------------------------
    case 'agregar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nota: Recibimos el id_usuario desde el formulario de la vista horarios
            $id_usuario  = $_POST['id_usuario'] ?? null; 
            $dia_semana  = $_POST['dia_semana'] ?? '';
            $hora_inicio = $_POST['hora_inicio'] ?? '';
            $hora_fin    = $_POST['hora_fin'] ?? '';

            if (!$id_usuario) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=tutores&error=" . urlencode("ID de usuario no válido."));
                exit();
            }

            // Memoria de formulario para retener datos si falla la validación
            $retenerDatos = "&old_dia=" . urlencode($dia_semana) . 
                            "&old_inicio=" . urlencode($hora_inicio) . 
                            "&old_fin=" . urlencode($hora_fin);

            // 1. Validar que no existan campos vacíos
            if (empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("Todos los campos (Día, Hora de Inicio y Hora de Fin) son obligatorios.") . $retenerDatos);
                exit();
            }

            // 2. Validar coherencia de tiempo (La hora fin debe ser estrictamente posterior a la hora de inicio)
            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("La hora de finalización debe ser mayor a la hora de inicio.") . $retenerDatos);
                exit();
            }

            // 3. Traducir el id_usuario al id_tutor (llave foránea de la tabla disponibilidad_tutor)
            $id_tutor_real = $tutorModel->obtenerIdTutorPorUsuario($id_usuario);
            
            if (!$id_tutor_real) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("Error: Este usuario no cuenta con un perfil de tutor activo en la base de datos."));
                exit();
            }

            try {
                // Procedemos a guardar en la base de datos
                $tutorModel->agregarHorario($id_tutor_real, $dia_semana, $hora_inicio, $hora_fin);
                
                // Redirigimos al panel de horarios con éxito
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&exito=" . urlencode("Horario agregado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=horarios&id_tutor=$id_usuario&error=" . urlencode("Error de sistema al agregar el horario.") . $retenerDatos);
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR BLOQUE DE HORARIO EXISTENTE
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_disponibilidad = $_GET['id_disponibilidad'] ?? null;
        $id_usuario        = $_GET['id_usuario'] ?? null; // Lo arrastramos para saber a qué vista redirigir

        if ($id_disponibilidad && $id_usuario) {
            try {
                $tutorModel->eliminarHorario($id_disponibilidad);
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