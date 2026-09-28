<?php
/**
 * ARCHIVO: views/admin/index.php
 * Front Controller (Enrutador Central) del panel de Administración.
 * Gobierna el Dashboard y los 7 cajones operativos (0 al 6) con la nueva estructura.
 */
session_start();

// 1. SEGURIDAD DE ROL: Validar que el usuario sea Administrador (Rol 1)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../login/index.php');
    exit;
}

// 2. INCLUSIÓN CENTRALIZADA DE CONEXIÓN A BASE DE DATOS
require_once '../../config/conexion.php';

// 3. CAPTURA DE SECCIÓN DINÁMICA (Por defecto ahora es 'dashboard')
$seccion = $_GET['seccion'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Panel de Control</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <?php include '../../includes/header.php'; ?>
</head>
<body>

    <?php include '../../includes/navbar.php'; ?>

    <!-- CONTENEDOR CENTRAL (Restricción estricta de 1000px y centrado) -->
    <div class="container mx-auto" style="max-width: 1000px; min-height: 75vh; margin-top: 2rem; margin-bottom: 3rem;">
        
        <?php
        // ENRUTADOR DINÁMICO MVC BASADO EN EL NUEVO MAPA DE FUNCIONALIDADES
        switch ($seccion) {
            
            // Pantalla de Inicio / Métricas
            case 'dashboard':
                include 'dashboard/index.php';
                break;

            // 0. Notificación: Alerta crítica
            case 'cajon0':
                include 'cajon0/index.php';
                break;
                
            // 1. Gestión de Usuarios
            case 'cajon1':
                include 'cajon1/index.php';
                break;
                
            // 2. Periodos y Cohortes
            case 'cajon2':
                include 'cajon2/index.php';
                break;
                
            // 3. Cronograma General
            case 'cajon3':
                include 'cajon3/index.php';
                break;
                
            // 4. Parámetros del Sistema
            case 'cajon4':
                include 'cajon4/index.php';
                break;
                
            // 5. Control y Reportes
            case 'cajon5':
                include 'cajon5/index.php';
                break;
                
            // 6. Importación SATS
            case 'cajon6':
                include 'cajon6/index.php';
                break;

            // Bloqueo de rutas inexistentes
            default:
                echo '<div class="alert alert-danger text-center mt-5 shadow-sm border-0 border-start border-4 border-danger border-plano">';
                echo '<i class="fas fa-route fa-3x mb-3 text-danger"></i>';
                echo '<h4>Ruta no encontrada</h4>';
                echo '<p class="text-muted">El cajón operativo que intentas buscar no existe en el nuevo mapa de directorios.</p>';
                echo '<a href="index.php?seccion=dashboard" class="btn btn-institucional btn-sm mt-2 border-plano"><i class="fas fa-home"></i> Volver al Inicio</a>';
                echo '</div>';
                break;
        }
        ?>

    </div>

    <?php include '../../includes/footer.php'; ?>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>