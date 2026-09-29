<?php
/**
 * ARCHIVO: controllers/LoginController.php
 * Controlador para procesar la autenticación, crear la sesión y registrar el LOGIN en la Bitácora.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibir credenciales del formulario
    $usuario_input = trim($_POST['usuario'] ?? '');
    $password_input = $_POST['contrasena'] ?? '';

    if (empty($usuario_input) || empty($password_input)) {
        header("Location: ../views/login/login.php?error=vacio");
        exit();
    }

    try {
        // 1. Buscar al usuario en la base de datos
        $stmt = $pdo->prepare("SELECT id_usuario, id_rol, nombre, apellido, correo, usuario, contrasena_hash, estado FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Validar contraseña (usando password_verify para el hash bcrypt de tu BD)
        if ($user && password_verify($password_input, $user['contrasena_hash'])) {
            
            // Comprobar que no esté inactivo
            if ($user['estado'] !== 'activo') {
                header("Location: ../views/login/login.php?error=inactivo");
                exit();
            }

            // 3. Crear variables de sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['id_rol']     = $user['id_rol'];
            $_SESSION['usuario']    = $user['usuario'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['apellido']   = $user['apellido'];
            $_SESSION['correo']     = $user['correo'];

            // 4. Capturar la IP y guardarla en la sesión (Para usarla luego en el Logout)
            $ip_conexion = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_conexion = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_conexion = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            $_SESSION['ip_origen'] = $ip_conexion; // ¡Dato guardado para el Logout!

            // 5. Capturar Agente de Usuario (Navegador y Sistema Operativo)
            $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'Agente web desconocido';

            // 6. Registrar el evento en la Caja Negra (Auditoría)
            $auditoriaModel = new AuditoriaModel($pdo);
            $correo_registro = empty($user['correo']) ? 'Sin correo' : $user['correo'];
            $actor_completo = $user['usuario'] . ' (' . $correo_registro . ')';

            // Formato estándar de página web para la sesión
            $datos_sesion_web = [
                'evento' => 'Inicio de sesión exitoso',
                'ip_de_conexion' => $ip_conexion,
                'navegador_y_sistema' => $navegador,
                'rol_asignado' => ($user['id_rol'] == 1) ? 'Administrador' : (($user['id_rol'] == 2) ? 'Tutor' : 'Estudiante'),
                'tiempo_servidor' => date('Y-m-d H:i:s')
            ];

            $auditoriaModel->registrarAccion(
                $actor_completo,
                'LOGIN',
                'registro_accesos',
                $user['id_usuario'],
                [], // No hay datos previos en un login
                $datos_sesion_web
            );

            // 7. Redirigir según el Rol
            if ($user['id_rol'] == 1) {
                // Administrador
                header("Location: ../views/admin/index.php");
            } else {
                // Tutor o Estudiante (Ajusta la ruta según tu estructura para ellos)
                header("Location: ../views/portal/index.php"); 
            }
            exit();

        } else {
            // Contraseña incorrecta o usuario no existe
            header("Location: ../views/login/login.php?error=credenciales");
            exit();
        }
    } catch (Throwable $e) {
        header("Location: ../views/login/login.php?error=sistema");
        exit();
    }
} else {
    // Si intentan entrar por GET en lugar de POST
    header("Location: ../views/login/login.php");
    exit();
}