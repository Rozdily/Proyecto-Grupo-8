<?php
/**
 * ARCHIVO: controllers/UsuarioController.php
 * Controlador central para procesar las acciones CRUD del Cajón 1,
 * con persistencia total de datos (memoria old_edit_*) ante errores en edición.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuarios/UsuarioModel/index.php';
require_once __DIR__ . '/../models/usuarios/EstudianteModel/index.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$usuarioModel = new UsuarioModel($pdo);
$estudianteModel = new EstudianteModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    
    // ------------------------------------------------------------------------
    // CREAR NUEVO USUARIO
    // ------------------------------------------------------------------------
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rol     = $_POST['id_rol'] ?? 3;
            $nombre     = trim($_POST['nombre'] ?? '');
            $apellido   = trim($_POST['apellido'] ?? '');
            $correo     = trim($_POST['correo'] ?? '');
            $usuario    = trim($_POST['usuario'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $telefono   = trim($_POST['telefono'] ?? '');

            $sub_modulo = 'usuarios';
            if ($id_rol == 2) $sub_modulo = 'tutores';
            if ($id_rol == 3) $sub_modulo = 'estudiantes';

            $extra_data = [];
            if ($id_rol == 3) {
                $extra_data['semestre']   = $_POST['semestre'] ?? 1;
                $extra_data['id_carrera'] = $_POST['id_carrera'] ?? 1;
            } elseif ($id_rol == 2) {
                $extra_data['especialidad']    = !empty(trim($_POST['especialidad'] ?? '')) ? trim($_POST['especialidad']) : 'Sin especialidad';
                $extra_data['biografia']       = !empty(trim($_POST['biografia'] ?? '')) ? trim($_POST['biografia']) : 'Sin biografía';
                $extra_data['perfil_linkedin'] = !empty(trim($_POST['perfil_linkedin'] ?? '')) ? trim($_POST['perfil_linkedin']) : 'Sin perfil';
                $extra_data['certificaciones'] = !empty(trim($_POST['certificaciones'] ?? '')) ? trim($_POST['certificaciones']) : 'Sin certificaciones';
                $extra_data['areas_expertise'] = !empty(trim($_POST['areas_expertise'] ?? '')) ? trim($_POST['areas_expertise']) : 'Sin áreas registradas';
            }

            $retenerDatos = "&modal=crear&old_nombre=" . urlencode($nombre) . 
                            "&old_apellido=" . urlencode($apellido) . 
                            "&old_correo=" . urlencode($correo) . 
                            "&old_usuario=" . urlencode($usuario) . 
                            "&old_telefono=" . urlencode($telefono);
                            
            if ($id_rol == 3) {
                $retenerDatos .= "&old_semestre=" . urlencode($_POST['semestre'] ?? '') .
                                 "&old_carrera=" . urlencode($_POST['id_carrera'] ?? '');
            } elseif ($id_rol == 2) {
                $retenerDatos .= "&old_especialidad=" . urlencode($_POST['especialidad'] ?? '') .
                                 "&old_biografia=" . urlencode($_POST['biografia'] ?? '') .
                                 "&old_linkedin=" . urlencode($_POST['perfil_linkedin'] ?? '') .
                                 "&old_certificaciones=" . urlencode($_POST['certificaciones'] ?? '') .
                                 "&old_expertise=" . urlencode($_POST['areas_expertise'] ?? '');
            }

            if (empty($nombre) || empty($apellido) || empty($correo) || empty($usuario) || empty($contrasena) || empty($telefono)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Todos los campos obligatorios deben llenarse.") . $retenerDatos);
                exit();
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El formato del correo institucional no es válido.") . $retenerDatos);
                exit();
            }
            if (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/", $nombre) || !preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/", $apellido)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Nombres y apellidos solo deben contener letras.") . $retenerDatos);
                exit();
            }
            if (!is_numeric($telefono) || strlen($telefono) < 8) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El teléfono debe ser numérico y tener al menos 8 dígitos.") . $retenerDatos);
                exit();
            }
            if ($usuarioModel->existeDuplicado($correo, $usuario)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El nombre de usuario o el correo electrónico ya se encuentran registrados.") . $retenerDatos);
                exit();
            }

            try {
                if ($id_rol == 3) {
                    $extra_data['registro_universitario'] = $estudianteModel->generarSiguienteRU();
                }

                $usuarioModel->crearCompleto($id_rol, $nombre, $apellido, $correo, $usuario, $contrasena, $telefono, $extra_data);
                
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("Usuario creado exitosamente."));
                exit();
                
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Fallo en Base de Datos: " . $e->getMessage()) . $retenerDatos);
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR DATOS CON MEMORIA DE FORMULARIO (OLD_EDIT_*)
    // ------------------------------------------------------------------------
    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = $_POST['id_usuario'] ?? null;
            $id_rol     = $_POST['id_rol'] ?? 3;
            $nombre     = trim($_POST['nombre'] ?? '');
            $apellido   = trim($_POST['apellido'] ?? '');
            $correo     = trim($_POST['correo'] ?? '');
            $usuario    = trim($_POST['usuario'] ?? '');
            $telefono   = trim($_POST['telefono'] ?? '');
            $estado     = $_POST['estado'] ?? 'activo';

            $sub_modulo = 'usuarios';
            if ($id_rol == 2) $sub_modulo = 'tutores';
            if ($id_rol == 3) $sub_modulo = 'estudiantes';

            $extra_data = [];
            if ($id_rol == 3) {
                $extra_data['semestre']   = $_POST['semestre'] ?? 1;
                $extra_data['id_carrera'] = $_POST['id_carrera'] ?? 1;
            } elseif ($id_rol == 2) {
                $extra_data['especialidad']    = !empty(trim($_POST['especialidad'] ?? '')) ? trim($_POST['especialidad']) : 'Sin especialidad';
                $extra_data['biografia']       = !empty(trim($_POST['biografia'] ?? '')) ? trim($_POST['biografia']) : 'Sin biografía';
                $extra_data['perfil_linkedin'] = !empty(trim($_POST['perfil_linkedin'] ?? '')) ? trim($_POST['perfil_linkedin']) : 'Sin perfil';
                $extra_data['certificaciones'] = !empty(trim($_POST['certificaciones'] ?? '')) ? trim($_POST['certificaciones']) : 'Sin certificaciones';
                $extra_data['areas_expertise'] = !empty(trim($_POST['areas_expertise'] ?? '')) ? trim($_POST['areas_expertise']) : 'Sin áreas registradas';
            }

            // Construir cadena de retención de datos para edición (old_edit_*)
            $retenerDatosEdit = "&modal=editar" .
                                "&old_edit_id=" . urlencode($id_usuario) .
                                "&old_edit_rol=" . urlencode($id_rol) .
                                "&old_edit_nombre=" . urlencode($nombre) .
                                "&old_edit_apellido=" . urlencode($apellido) .
                                "&old_edit_correo=" . urlencode($correo) .
                                "&old_edit_usuario=" . urlencode($usuario) .
                                "&old_edit_telefono=" . urlencode($telefono) .
                                "&old_edit_estado=" . urlencode($estado);

            if ($id_rol == 3) {
                $retenerDatosEdit .= "&old_edit_semestre=" . urlencode($_POST['semestre'] ?? '') .
                                     "&old_edit_carrera=" . urlencode($_POST['id_carrera'] ?? '');
            } elseif ($id_rol == 2) {
                $retenerDatosEdit .= "&old_edit_especialidad=" . urlencode($_POST['especialidad'] ?? '') .
                                     "&old_edit_biografia=" . urlencode($_POST['biografia'] ?? '') .
                                     "&old_edit_linkedin=" . urlencode($_POST['perfil_linkedin'] ?? '') .
                                     "&old_edit_certificaciones=" . urlencode($_POST['certificaciones'] ?? '') .
                                     "&old_edit_expertise=" . urlencode($_POST['areas_expertise'] ?? '');
            }

            if (!$id_usuario) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("ID de usuario no válido.") . $retenerDatosEdit);
                exit();
            }
            
            if (empty($nombre) || empty($apellido) || empty($correo) || empty($usuario) || empty($telefono)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Todos los campos obligatorios deben llenarse.") . $retenerDatosEdit);
                exit();
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El formato del correo institucional no es válido.") . $retenerDatosEdit);
                exit();
            }

            if (!preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/", $nombre) || !preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/", $apellido)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Nombres y apellidos solo deben contener letras.") . $retenerDatosEdit);
                exit();
            }

            if (!is_numeric($telefono) || strlen($telefono) < 8) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El teléfono debe ser numérico y tener al menos 8 dígitos.") . $retenerDatosEdit);
                exit();
            }

            if ($usuarioModel->existeDuplicado($correo, $usuario, $id_usuario)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El nombre de usuario o el correo electrónico ya se encuentran registrados.") . $retenerDatosEdit);
                exit();
            }

            try {
                $usuarioModel->actualizarCompleto($id_usuario, $id_rol, $nombre, $apellido, $correo, $usuario, $telefono, $estado, $extra_data);
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("Perfil actualizado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Error al actualizar: " . $e->getMessage()) . $retenerDatosEdit);
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // CAMBIAR CLAVE
    // ------------------------------------------------------------------------
    case 'cambiar_clave':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = $_POST['id_usuario'] ?? null;
            $nueva_clave  = $_POST['nueva_contrasena'] ?? '';

            $info_usuario = $usuarioModel->obtenerPorId($id_usuario);
            $sub_modulo = 'usuarios';
            if ($info_usuario) {
                if ($info_usuario['id_rol'] == 2) $sub_modulo = 'tutores';
                if ($info_usuario['id_rol'] == 3) $sub_modulo = 'estudiantes';
            }

            if (empty($nueva_clave) || strlen($nueva_clave) < 6) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("La contraseña debe tener al menos 6 caracteres."));
                exit();
            }

            try {
                $usuarioModel->cambiarContrasena($id_usuario, $nueva_clave);
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("Contraseña cambiada exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("No se pudo cambiar la contraseña."));
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ELIMINAR
    // ------------------------------------------------------------------------
    case 'eliminar':
        $id_usuario = $_GET['id'] ?? $_POST['id_usuario'] ?? null;
        
        if ($id_usuario) {
            $info_usuario = $usuarioModel->obtenerPorId($id_usuario);
            $sub_modulo = 'usuarios';
            if ($info_usuario) {
                if ($info_usuario['id_rol'] == 2) $sub_modulo = 'tutores';
                if ($info_usuario['id_rol'] == 3) $sub_modulo = 'estudiantes';
            }

            try {
                $usuarioModel->eliminar($id_usuario);
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("El usuario fue eliminado del sistema."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("No se pudo eliminar el usuario porque tiene registros dependientes (ej. Grupos de grado)."));
                exit();
            }
        }
        break;

    default:
        header("Location: ../views/admin/index.php?seccion=cajon1");
        exit();
}