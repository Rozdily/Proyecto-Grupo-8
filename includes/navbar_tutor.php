<?php
/**
 * ARCHIVO: includes/navbar_tutor.php
 * Barra de navegación principal superior exclusiva para Tutores.
 */
$seccion_actual = $_GET['seccion'] ?? 'dashboard';

// Rescate seguro de variables de sesión
$nombre_usuario = $_SESSION['nombre'] ?? 'Tutor';
$apellido_usuario = $_SESSION['apellido'] ?? '';
$iniciales = strtoupper(substr($nombre_usuario, 0, 1) . substr($apellido_usuario, 0, 1));
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm border-plano sticky-top" style="background-color: #0B427B;">
    <div class="container-fluid px-4">
        
        <!-- Logo UPDS -->
        <a class="navbar-brand d-flex align-items-center me-4" href="index.php?seccion=dashboard" style="border-right: 1px solid rgba(255,255,255,0.2); padding-right: 1.5rem;">
            <div class="lh-1">
                <div class="fw-bold fs-5">UPDS</div>
                <div style="font-size: 0.65rem; letter-spacing: 1px; color: #a3c2e0;">PORTAL TUTOR</div>
            </div>
        </a>
        
        <button class="navbar-toggler border-plano" type="button" data-bs-toggle="collapse" data-bs-target="#menuTutor">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="menuTutor">
            <!-- Menú Principal Tutor -->
            <ul class="navbar-nav me-auto align-items-center">
                
                <!-- 1. MI PANEL (Dashboard) -->
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'dashboard') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=dashboard">
                       <i class="fas fa-chalkboard-teacher me-1"></i> Mi Panel
                    </a>
                </li>
                
                <!-- 2. SOLICITUDES (Gestión de tutorías pendientes) -->
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'solicitudes') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=solicitudes">
                       <i class="fas fa-inbox me-1"></i> Solicitudes
                    </a>
                </li>

                <!-- 3. DISPONIBILIDAD HORARIA -->
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'disponibilidad') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=disponibilidad">
                       <i class="far fa-clock me-1"></i> Mis Horarios
                    </a>
                </li>
                
                <!-- 4. MODALIDAD DE GRADO -->
                <li class="nav-item">
                    <a class="nav-link px-3 text-white <?php echo ($seccion_actual === 'modalidad') ? 'fw-bold border-bottom border-3 border-white' : ''; ?>" 
                       href="index.php?seccion=modalidad">
                       <i class="fas fa-user-graduate me-1"></i> Tesistas (MG)
                    </a>
                </li>

            </ul>

            <!-- Menú Derecho: Notificaciones y Perfil -->
            <div class="d-flex align-items-center text-white">
                
                <!-- Campana de Notificaciones -->
                <a href="#" class="position-relative me-4 text-white text-decoration-none hover-elevate" title="Notificaciones">
                    <i class="far fa-bell fa-lg"></i>
                </a>

                <!-- Avatar Circular -->
                <div class="rounded-circle bg-light text-institucional d-flex justify-content-center align-items-center me-2 fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.9rem;">
                    <?php echo $iniciales; ?>
                </div>

                <!-- Nombre y Rol -->
                <div class="text-end me-4 lh-1">
                    <div class="fw-semibold" style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars(explode(' ', $nombre_usuario)[0] . ' ' . explode(' ', $apellido_usuario)[0]); ?>
                    </div>
                    <div style="font-size: 0.75rem; color: #a3c2e0; margin-top: 2px;">
                        Docente Tutor
                    </div>
                </div>
                
                <!-- Botón de Salida -->
                <a href="../../controllers/LogoutController.php" class="btn btn-outline-light btn-sm border-plano">
                    <i class="fas fa-sign-out-alt me-1"></i> Salir
                </a>
            </div>
        </div>
    </div>
</nav>