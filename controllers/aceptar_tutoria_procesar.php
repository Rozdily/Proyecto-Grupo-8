<?php
// 1. Evitamos errores invisibles de cabeceras en el servidor
ob_start();

// 2. Cargamos el control de sesiones y la conexión
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../config/conexion.php';

// 3. CONTROL DE SEGURIDAD: Verificación estricta del rol 'tutor'
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'tutor' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login/login.php');
    exit;
}

// 4. Capturamos los datos que vienen del formulario de tarjetas
$id_solicitud   = $_POST['id_solicitud'] ?? null;
$id_tutor       = $_POST['id_tutor'] ?? null; // Cambiado de id_docente a id_tutor
$enlace_reunion = trim($_POST['enlace_reunion'] ?? '');

// Validar que ningún campo vital viaje vacío
if (empty($id_solicitud) || empty($id_tutor) || empty($enlace_reunion)) {
    header('Location: ../views/tutor/solicitudes_tutor.php?error=vacio');
    exit;
}

try {
    // 5. ACTUALIZACIÓN EN LA BASE DE DATOS: Adaptada a la columna id_tutor
    // IMPORTANTE: Asegúrate de que tu columna en la tabla se llame exactamente 'id_tutor'
    $sql = "UPDATE solicitudes_tutorias 
            SET id_tutor = :id_tutor, 
                enlace_reunion = :enlace_reunion, 
                estado_solicitud = 'aceptada' 
            WHERE id_solicitud = :id_solicitud";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_tutor'        => $id_tutor,
        ':enlace_reunion'  => $enlace_reunion,
        ':id_solicitud'    => $id_solicitud
    ]);

    // 6. Guardamos el mensaje de éxito para que lo dibuje la interfaz de tarjetas
    $_SESSION['tutoria_creada'] = "¡Tutoría aceptada con éxito! Se ha agendado y asignado el espacio correspondiente.";
    
    // GUARDADO MANUAL DE LA SESIÓN: Clave para que no se pierda el usuario al redireccionar
    session_write_close();

    // 7. Redirección milimétrica a la carpeta de tutores
    header('Location: ../views/tutor/solicitudes_tutor.php');
    exit;

} catch (PDOException $e) {
    // Si sale un error de SQL, te regresa a la vista de tarjetas con el aviso de error
    header('Location: ../views/tutor/solicitudes_tutor.php?error=sql');
    exit;
}
