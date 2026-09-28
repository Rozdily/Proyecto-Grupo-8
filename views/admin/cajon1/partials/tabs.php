<?php
/**
 * ARCHIVO: views/admin/cajon1/partials/tabs.php
 * Menú de pestañas de navegación para los sub-módulos del Cajón 1.
 */
?>
<ul class="nav nav-tabs border-plano mb-4">
    <li class="nav-item">
        <a class="nav-link text-dark border-plano <?php echo $submodulo === 'usuarios' ? 'active fw-bold border-top border-3 border-institucional' : 'bg-light'; ?>" 
           href="index.php?seccion=cajon1&sub=usuarios">
            <i class="fas fa-users-cog me-2"></i>Usuarios Generales
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark border-plano <?php echo $submodulo === 'estudiantes' ? 'active fw-bold border-top border-3 border-institucional' : 'bg-light'; ?>" 
           href="index.php?seccion=cajon1&sub=estudiantes">
            <i class="fas fa-user-graduate me-2"></i>Estudiantes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark border-plano <?php echo $submodulo === 'tutores' ? 'active fw-bold border-top border-3 border-institucional' : 'bg-light'; ?>" 
           href="index.php?seccion=cajon1&sub=tutores">
            <i class="fas fa-chalkboard-teacher me-2"></i>Tutores
        </a>
    </li>
</ul>