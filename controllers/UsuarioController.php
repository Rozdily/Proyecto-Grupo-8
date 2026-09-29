<?php
/**
 * ARCHIVO: controllers/UsuarioController.php
 * Controlador central para procesar las acciones CRUD del Cajón 1,
 * integrado con la Bitácora de Auditoría (Caja Negra) y retención de formulario.
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/usuarios/UsuarioModel/index.php';
require_once __DIR__ . '/../models/usuarios/EstudianteModel/index.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php'; // Inyección de Auditoría

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../views/login/login.php?error=acceso_denegado");
    exit();
}

$usuarioModel = new UsuarioModel($pdo);
$estudianteModel = new EstudianteModel($pdo);
$auditoriaModel = new AuditoriaModel($pdo);
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Identificar al Actor
$usuario_str = $_SESSION['usuario'] ?? 'Admin';
$correo_str = $_SESSION['correo'] ?? 'Sin correo';
if (empty($correo_str) || strpos($correo_str, 'sistema@') !== false) {
    $correo_str = 'Sin correo';
}
$actor_completo = $usuario_str . ' (' . $correo_str . ')';

// Función para no registrar hashes en la caja negra
function limpiarDatosLog($datos) {
    if (isset($datos['contrasena_hash'])) unset($datos['contrasena_hash']);
    if (isset($datos['contrasena'])) unset($datos['contrasena']);
    return $datos;
}

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
            if ($usuarioModel->existeDuplicado($correo, $usuario)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("El nombre de usuario o el correo electrónico ya se encuentran registrados.") . $retenerDatos);
                exit();
            }

            try {
                if ($id_rol == 3) {
                    $extra_data['registro_universitario'] = $estudianteModel->generarSiguienteRU();
                }

                $usuarioModel->crearCompleto($id_rol, $nombre, $apellido, $correo, $usuario, $contrasena, $telefono, $extra_data);
                
                // AUDITORÍA: Buscar el usuario recién creado
                $nuevo_user = $usuarioModel->obtenerPorUsuario($usuario);
                if ($nuevo_user) {
                    $datos_despues = array_merge($nuevo_user, $extra_data); // Unimos datos base y extras
                    $datos_despues = limpiarDatosLog($datos_despues);
                    $auditoriaModel->registrarAccion($actor_completo, 'CREAR', 'usuarios', $nuevo_user['id_usuario'], [], $datos_despues);
                }

                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("Usuario creado exitosamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Fallo en Base de Datos.") . $retenerDatos);
                exit();
            }
        }
        break;

    // ------------------------------------------------------------------------
    // ACTUALIZAR DATOS CON MEMORIA DE FORMULARIO
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

            if (!$id_usuario || empty($nombre) || empty($usuario)) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Datos incompletos."));
                exit();
            }

            try {
                // AUDITORÍA PASO 1: Leer antes
                $datos_antes_bd = $usuarioModel->obtenerPorId($id_usuario) ?: [];
                $datos_antes_bd = limpiarDatosLog($datos_antes_bd);

                $usuarioModel->actualizarCompleto($id_usuario, $id_rol, $nombre, $apellido, $correo, $usuario, $telefono, $estado, $extra_data);
                
                // AUDITORÍA PASO 2: Leer después y guardar
                $datos_despues_bd = $usuarioModel->obtenerPorId($id_usuario) ?: [];
                // Fusionamos lo de BD con lo extra que actualizamos
                $datos_despues_bd = array_merge($datos_despues_bd, $extra_data); 
                $datos_despues_bd = limpiarDatosLog($datos_despues_bd);

                $auditoriaModel->registrarAccion($actor_completo, 'ACTUALIZAR', 'usuarios', $id_usuario, $datos_antes_bd, $datos_despues_bd);

                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("Perfil actualizado correctamente."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("Error al actualizar."));
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

                // AUDITORÍA
                $datos_antes = ['contrasena' => '*** (Oculta por seguridad) ***'];
                $datos_despues = ['contrasena' => '*** (Nueva contraseña generada) ***'];
                $auditoriaModel->registrarAccion($actor_completo, 'ACTUALIZAR', 'usuarios', $id_usuario, $datos_antes, $datos_despues);

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
                // AUDITORÍA PASO 1
                $datos_antes = limpiarDatosLog($info_usuario ?: []);

                $usuarioModel->eliminar($id_usuario);

                // AUDITORÍA PASO 2
                $auditoriaModel->registrarAccion($actor_completo, 'ELIMINAR', 'usuarios', $id_usuario, $datos_antes, []);

                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&exito=" . urlencode("El usuario fue eliminado del sistema."));
                exit();
            } catch (Throwable $e) {
                header("Location: ../views/admin/index.php?seccion=cajon1&sub=$sub_modulo&error=" . urlencode("No se pudo eliminar el usuario porque tiene registros dependientes."));
                exit();
            }
        }
        break;

    default:
        header("Location: ../views/admin/index.php?seccion=cajon1");
        exit();
}