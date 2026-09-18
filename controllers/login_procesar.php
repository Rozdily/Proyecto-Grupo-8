<?php
session_start();
require_once '../config/conexion.php';
require_once '../models/UsuarioModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login/login.php');
    exit;
}

$usuarioInput = trim($_POST['usuario'] ?? '');
$contrasenaInput = $_POST['contrasena'] ?? '';

$modelo = new UsuarioModel($pdo);
$usuario = $modelo->obtenerPorUsuario($usuarioInput);

if ($usuario && $usuario['estado'] === 'activo' && password_verify($contrasenaInput, $usuario['contrasena_hash'])) {
    // Login correcto
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['nombre_rol'];

    // Registrar acceso exitoso
    $pdo->prepare("INSERT INTO registro_accesos (id_usuario, ip_origen, resultado) VALUES (?, ?, 'exitoso')")
        ->execute([$usuario['id_usuario'], $_SERVER['REMOTE_ADDR']]);

    // Redirección según rol
    switch ($usuario['nombre_rol']) {
        case 'administrador':
            header('Location: ../controllers/usuarios_listar.php');
            break;
        case 'tutor':
            header('Location: ../views/tutor/panel.php'); // aún no existe, lo crearemos después
            break;
        case 'estudiante':
            header('Location: ../views/estudiante/panel.php'); // aún no existe
            break;
        default:
            header('Location: ../views/login/login.php');
    }
    exit;

} else {
    // Login fallido — registrar si el usuario existe
    if ($usuario) {
        $pdo->prepare("INSERT INTO registro_accesos (id_usuario, ip_origen, resultado) VALUES (?, ?, 'fallido')")
            ->execute([$usuario['id_usuario'], $_SERVER['REMOTE_ADDR']]);
    }
    $_SESSION['login_error'] = 'El usuario o la constraseña son erroneos.';
    header('Location: ../views/login/login.php');
    exit;
}