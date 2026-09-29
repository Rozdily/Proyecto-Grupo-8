<?php
/**
 * ARCHIVO: views/tutor/index.php
 * Plantilla maestra y enrutador principal para el Tutor (Docente).
 */
session_start();

// Validar que el usuario sea tutor (id_rol = 2)[cite: 1]
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    header("Location: ../login/index.php");
    exit();
}

$seccion = $_GET['seccion'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Docente Tutor - UPDS</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Include Global del Header (CSS, FontAwesome, etc.) -->
    <?php include '../../includes/header.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar específico para el Tutor -->
    <?php include '../../includes/navbar_tutor.php'; ?>

    <!-- Contenedor Principal Central -->
    <main class="container py-4 flex-grow-1" style="max-width: 1250px;">
        <?php
        // Enrutador de carpetas para el tutor
        $ruta_cajon = __DIR__ . '/' . $seccion . '/index.php';
        
        if (file_exists($ruta_cajon)) {
            require_once $ruta_cajon;
        } else {
            echo '<div class="alert alert-warning border-plano shadow-sm text-center">
                    <i class="fas fa-tools fa-2x mb-3 text-secondary"></i><br>
                    <h5 class="fw-bold">Módulo en Construcción</h5>
                    <p class="mb-0">La sección solicitada aún no está disponible para los tutores.</p>
                  </div>';
        }
        ?>
    </main>

    <!-- Include Global del Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>