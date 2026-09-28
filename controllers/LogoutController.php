<?php
/**
 * ARCHIVO: controllers/LogoutController.php
 * Controlador responsable de destruir la sesión del usuario de forma segura
 * y redirigirlo al inicio de sesión bajo la nueva estructura.
 */

// 1. Iniciar o reanudar la sesión actual para interceptarla
session_start();

// 2. Vaciar todas las variables de sesión del array nativo
$_SESSION = array();

// 3. Destruir la cookie de la sesión (Capa de seguridad adicional)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión físicamente en el servidor
session_destroy();

// 5. Redirección estricta a la vista de Login (Nueva nomenclatura indexada)
header('Location: ../views/login/index.php');
exit;