<?php
/**
 * ARCHIVO: controllers/LogoutController.php
 * Cierre de sesión y registro avanzado en Bitácora (Navegador, IPs, etc.).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/sistema/AuditoriaModel/index.php';

if (isset($_SESSION['id_usuario'])) {
    try {
        $auditoriaModel = new AuditoriaModel($pdo);
        
        $id_usuario = $_SESSION['id_usuario'];
        $usuario_str = $_SESSION['usuario'] ?? 'Desconocido';
        $correo_str = $_SESSION['correo'] ?? ''; 
        $actor_completo = $usuario_str . ' (' . $correo_str . ')';

        // Captura de IP actual (Salida)
        $ip_actual = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_actual = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_actual = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // IP de cuando inició sesión (almacenada por el LoginController)
        $ip_origen = $_SESSION['ip_origen'] ?? $ip_actual; 
        
        // Captura del Agente de Usuario (Navegador, SO)
        $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'Agente desconocido';

        // Estructura de "Sesión Web" detallada
        $datos_sesion_web = [
            'evento' => 'Cierre de sesión manual exitoso',
            'ip_conexion_inicial' => $ip_origen,
            'ip_desconexion' => $ip_actual,
            'navegador_y_sistema' => $navegador,
            'tiempo_servidor' => date('Y-m-d H:i:s')
        ];

        // Registro en la caja negra (LOGOUT)
        $auditoriaModel->registrarAccion(
            $actor_completo, 
            'LOGOUT', 
            'registro_accesos', 
            $id_usuario, 
            [], 
            $datos_sesion_web
        );
    } catch (Throwable $e) {
        // Ignoramos errores para no bloquear el logout del usuario
    }
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sesión
session_destroy();

// Redirigir a tu pantalla de login
header("Location: ../views/login/index.php");
exit();