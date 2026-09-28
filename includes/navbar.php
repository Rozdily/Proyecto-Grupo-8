<?php
/**
 * ARCHIVO: includes/navbar.php
 * Barra de navegación principal superior (Refactorizada con campana dinámica).
 */

$seccion_actual = $_GET['seccion'] ?? 'dashboard';

// ---------------------------------------------------------
// LÓGICA DINÁMICA DE ALERTAS (CAJÓN 0)
// ---------------------------------------------------------
$alertas_tutoria = 0;
$alertas_cohorte = 0;

// Verificamos que la variable $pdo exista (heredada del index.php padre)
if (isset($pdo)) {
    try {
        // Consulta para alertas de tutoría (Cambiará la campana de color)
        $stmtTut = $pdo->query("SELECT COUNT(*) FROM notificaciones WHERE tipo = 'tutoria' AND estado = 'activa'");
        $alertas_tutoria = $stmtTut->fetchColumn();

        // Consulta para alertas de cohortes (Activa el puntito de color)
        $stmtCoh = $pdo->query("SELECT COUNT(*) FROM notificaciones WHERE tipo = 'cohorte' AND estado = 'activa'");
        $alertas_cohorte = $stmtCoh->fetchColumn();
    } catch (PDOException $e) {
        // Fallback silencioso por si la tabla 'notificaciones' aún no se ha creado
    }
}

// Configuración de los estilos dinámicos de la campana
// Si hay alerta de tutoría -> Campana Naranja/Warning, si no -> Blanca
$clase_campana = ($alertas_tutoria > 0) ? 'text-warning' : 'text-white';

// Si hay alerta de cohorte -> Puntito visible y Rojo, si no -> Invisible (d-none)
$clase_punto = ($alertas_cohorte > 0) ? 'bg-danger d-block' : 'd-none';
// ---------------------------------------------------------
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm border-plano" style="background-color: #0B427B;">
    <div class="container-fluid px-4">
        
        <button class="navbar-toggler border-plano" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Navegación">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="menuPrincipal">
            
            <!-- Menú Izquierdo: Cajones Administrativos -->
            <ul class="navbar-nav me-auto align-items-center">
                <li class="nav-item me-2">
                    <a class="nav-link px-2 text-white <?php echo ($seccion_actual === 'dashboard') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=dashboard" title="Dashboard">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'cajon1') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=cajon1">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'cajon2') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=cajon2">Periodos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'cajon3') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=cajon3">Cronograma</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'cajon4') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=cajon4">Parámetros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'cajon5') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=cajon5">Reportes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'cajon6') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=cajon6">SATS</a>
                </li>
            </ul>

            <!-- Menú Derecho: Cajón 0, Perfil y Logout -->
            <div class="d-flex align-items-center text-white">
                
                <!-- Cajón 0: Campana Dinámica -->
                <a href="index.php?seccion=cajon0" class="position-relative me-4 text-decoration-none" title="Notificaciones del Sistema">
                    <!-- Icono de campana (Cambia de color con Tutorías) -->
                    <i class="fas fa-bell fa-lg <?php echo $clase_campana; ?>"></i>
                    
                    <!-- Puntito decorativo (Aparece con Cohortes de grado) -->
                    <span class="position-absolute top-0 start-100 translate-middle p-1 border border-light rounded-circle <?php echo $clase_punto; ?>">
                        <span class="visually-hidden">Alertas de cohortes</span>
                    </span>
                </a>

                <div class="text-end me-2 lh-1">
                    <div class="fw-semibold" style="font-size: 0.95rem;">
                        <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?>
                    </div>
                    <div style="font-size: 0.75rem; color: #a3c2e0; margin-top: 2px;">
                        <?php echo htmlspecialchars($_SESSION['correo'] ?? 'admin@upds.edu.bo'); ?>
                    </div>
                </div>
                
                <div class="me-4">
                    <i class="fas fa-user-circle fa-2x" style="color: #9bb7d4;"></i>
                </div>

                <a href="../../controllers/LogoutController.php" class="text-white text-decoration-none d-flex align-items-center border-start border-light border-opacity-25 ps-3 py-1">
                    <span class="me-2 small">Salir</span>
                    <i class="fas fa-sign-out-alt"></i>
                </a>
                
            </div>
        </div>
    </div>
</nav>