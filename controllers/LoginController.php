<?php
session_start();

// --- 1. INCLUSIÓN CENTRALIZADA DE CONEXIÓN ---
// Utilizamos require_once para invocar tu bloque PDO nativo
require_once '../config/conexion.php';
// ---------------------------------------------

// 2. IMPORTACIÓN DEL MODELO BAJO LA NUEVA NOMENCLATURA INDEXADA
require_once '../models/usuarios/UsuarioModel/index.php';

// 3. PROTECCIÓN DE RUTA (Solo POST admitido)[cite: 2]
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login/index.php');
    exit;
}

// 4. LIMPIEZA DE DATOS DE ENTRADA[cite: 2]
$usuarioInput = trim($_POST['usuario'] ?? '');
$contrasenaInput = $_POST['contrasena'] ?? '';

// 5. CONSULTA AL MODELO (Asumimos que conexion.php expone la variable $pdo)[cite: 2]
$modelo = new UsuarioModel($pdo);
$usuario = $modelo->obtenerPorUsuario($usuarioInput);

// 6. VALIDACIÓN CRÍTICA MVC[cite: 2]
if ($usuario && $usuario['estado'] === 'activo' && password_verify($contrasenaInput, $usuario['contrasena_hash'])) {
    
    // Inyección de variables en el array nativo $_SESSION (Orden estricta)
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre']     = $usuario['nombre'];
    $_SESSION['id_rol']     = (int) $usuario['id_rol'];
    $_SESSION['correo']     = $usuario['correo'];

    // Registro de auditoría (Acceso exitoso)[cite: 2]
    $stmt = $pdo->prepare("INSERT INTO registro_accesos (id_usuario, ip_origen, resultado) VALUES (?, ?, 'exitoso')");
    $stmt->execute([$usuario['id_usuario'], $_SERVER['REMOTE_ADDR']]);

    // REDIRECCIÓN MULTI-ROL ESTRICTA SEGÚN ÁRBOL DE CARPETAS
    if ($_SESSION['id_rol'] === 1) {
        header('Location: ../views/admin/index.php');
    } elseif ($_SESSION['id_rol'] === 2) {
        header('Location: ../views/tutor/index.php');
    } elseif ($_SESSION['id_rol'] === 3) {
        header('Location: ../views/estudiante/index.php');
    } else {
        // Fallback de seguridad si el rol es desconocido
        header('Location: ../views/login/index.php');
    }
    exit;

} else {
    // 7. FLUJO DE DENEGACIÓN Y RETORNO[cite: 2]
    
    // Registrar el intento fallido si el usuario existía pero falló la clave[cite: 2]
    if ($usuario) {
        $stmt = $pdo->prepare("INSERT INTO registro_accesos (id_usuario, ip_origen, resultado) VALUES (?, ?, 'fallido')");
        $stmt->execute([$usuario['id_usuario'], $_SERVER['REMOTE_ADDR']]);
    }
    
    $_SESSION['login_error'] = 'Credenciales incorrectas o cuenta inactiva. Verifique su información.';
    header('Location: ../views/login/index.php');
    exit;
}