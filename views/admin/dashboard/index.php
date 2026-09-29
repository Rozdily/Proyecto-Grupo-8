<?php
/**
 * ARCHIVO: views/admin/dashboard/index.php
 * Vista inyectable de Inicio: Panel de Control Central.
 * Cuadrícula principal de accesos a los módulos administrativos.
 */

// INCLUSIÓN CENTRALIZADA DE CONEXIÓN 
// (La mantenemos requerida por si en el futuro necesitas extraer algún dato de sesión o configuración)
require_once __DIR__ . '/../../../config/conexion.php';

$nombre_admin = $_SESSION['nombre'] ?? 'Administrador';
?>

<!-- ENCABEZADO Y BIENVENIDA -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h2 class="fw-light mb-0 text-dark">Panel de Control <span class="fs-6 text-muted ms-2 fw-normal">/ Inicio</span></h2>
    <div class="text-muted small"><i class="fas fa-home text-institucional"></i> <span class="fw-bold">Inicio</span></div>
</div>

<div class="alert bg-white border-0 border-start border-4 border-institucional shadow-sm mb-5 border-plano d-flex align-items-center">
    <div class="me-3 text-institucional">
        <i class="fas fa-user-shield fa-2x"></i>
    </div>
    <div>
        <h5 class="mb-1 fw-bold text-dark">¡Bienvenido al sistema, <?php echo htmlspecialchars($nombre_admin); ?>!</h5>
        <p class="mb-0 text-muted small">Desde aquí puedes gestionar y acceder a todos los módulos administrativos de Tutorías y Grado.</p>
    </div>
</div>

<!-- CUADRÍCULA PRINCIPAL DE CAJONES OPERATIVOS[cite: 3]-->
<h5 class="fw-light mb-4 text-dark"><i class="fas fa-layer-group text-institucional me-2"></i> Módulos Administrativos</h5>
<div class="row row-cols-1 row-cols-lg-2 g-4 mb-4">
    
    <!-- Cajón 1: Gestión de Usuarios[cite: 3]-->
    <div class="col">
        <div class="card h-100 border-plano shadow-sm border-0 border-top border-3 hover-elevate" style="border-top-color: #0B427B !important;">
            <div class="card-body p-4 d-flex">
                <div class="d-flex justify-content-center align-items-center text-white me-3 rounded-2 shadow-sm" style="width: 55px; height: 55px; background-color: #0B427B; flex-shrink: 0;">
                    <i class="fas fa-users-cog fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-2 text-dark fs-6">1. Gestión de Usuarios</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="fas fa-user-edit text-secondary me-2"></i>Control integral de cuentas y tutores.</li>
                        <li><i class="fas fa-user-graduate text-secondary me-2"></i>Gestión de alumnos regulares y egresados.</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-end text-institucional small fw-semibold">
                <a href="index.php?seccion=cajon1&sub=usuarios" class="text-decoration-none text-reset">Usuarios Generales</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon1&sub=estudiantes" class="text-decoration-none text-reset">Estudiantes</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon1&sub=tutores" class="text-decoration-none text-reset">Tutores</a>
            </div>
                    </div>
    </div>

    <!-- Cajón 2: Periodos y Cohortes[cite: 3]-->
    <div class="col">
        <div class="card h-100 border-plano shadow-sm border-0 border-top border-3 hover-elevate" style="border-top-color: #6c757d !important;">
            <div class="card-body p-4 d-flex">
                <div class="d-flex justify-content-center align-items-center text-white me-3 rounded-2 shadow-sm" style="width: 55px; height: 55px; background-color: #6c757d; flex-shrink: 0;">
                    <i class="fas fa-layer-group fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-2 text-dark fs-6">2. Periodos y Cohortes</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="fas fa-calendar-alt text-secondary me-2"></i>Definición de tiempos académicos.</li>
                        <li><i class="fas fa-folder-open text-secondary me-2"></i>Catálogo y expedientes de grado.</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-end text-muted small fw-semibold">
                <a href="index.php?seccion=cajon2&tab=periodos" class="text-decoration-none text-muted">Periodos y Cohortes</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon2&tab=oferta" class="text-decoration-none text-muted">Oferta Universitaria</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon2&tab=expedientes" class="text-decoration-none text-muted">Expedientes de Tesis</a>
            </div>
        </div>
    </div>

    <!-- Cajón 3: Cronograma General[cite: 3]-->
    <div class="col">
        <div class="card h-100 border-plano shadow-sm border-0 border-top border-3 hover-elevate" style="border-top-color: #17a2b8 !important;">
            <div class="card-body p-4 d-flex">
                <div class="d-flex justify-content-center align-items-center text-white me-3 rounded-2 shadow-sm" style="width: 55px; height: 55px; background-color: #17a2b8; flex-shrink: 0;">
                    <i class="far fa-calendar-check fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-2 text-dark fs-6">3. Cronograma General</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="fas fa-clock text-secondary me-2"></i>Agenda y bloques de horarios institucionales.</li>
                        <li><i class="fas fa-gavel text-secondary me-2"></i>Programación de tribunales y defensas.</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-end" style="color: #17a2b8;">
                <a href="index.php?seccion=cajon2&tab=periodos" class="text-decoration-none small fw-semibold" style="color: #17a2b8;">Hitos</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon2&tab=oferta" class="text-decoration-none small fw-semibold" style="color: #17a2b8;">Prog. Defensas</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon2&tab=expedientes" class="text-decoration-none small fw-semibold" style="color: #17a2b8;">Prog. Reuniones</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon2&tab=expedientes" class="text-decoration-none small fw-semibold" style="color: #17a2b8;">Prog. Tutorias</a>
            </div>
        </div>
    </div>

    <!-- Cajón 4: Parámetros del Sistema[cite: 3]-->
    <div class="col">
        <div class="card h-100 border-plano shadow-sm border-0 border-top border-3 hover-elevate" style="border-top-color: #fd7e14 !important;">
            <div class="card-body p-4 d-flex">
                <div class="d-flex justify-content-center align-items-center text-white me-3 rounded-2 shadow-sm" style="width: 55px; height: 55px; background-color: #fd7e14; flex-shrink: 0;">
                    <i class="fas fa-sliders-h fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-2 text-dark fs-6">4. Parámetros del Sistema</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="fas fa-cogs text-secondary me-2"></i>Topes y reglas de tutorías.</li>
                        <li><i class="fas fa-balance-scale text-secondary me-2"></i>Plazos y cargas para defensas de tesis.</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-end">
                <a href="index.php?seccion=cajon4" class="text-decoration-none small fw-semibold" style="color: #fd7e14;">Ajustar Reglas <i class="fas fa-chevron-right ms-1" style="font-size: 0.7rem;"></i></a>
            </div>
        </div>
    </div>

    <!-- Cajón 5: Control y Reportes[cite: 3]-->
    <div class="col">
        <div class="card h-100 border-plano shadow-sm border-0 border-top border-3 hover-elevate" style="border-top-color: #28a745 !important;">
            <div class="card-body p-4 d-flex">
                <div class="d-flex justify-content-center align-items-center text-white me-3 rounded-2 shadow-sm" style="width: 55px; height: 55px; background-color: #28a745; flex-shrink: 0;">
                    <i class="fas fa-chart-pie fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-2 text-dark fs-6">5. Control y Reportes</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="fas fa-chart-line text-secondary me-2"></i>Análisis de rendimiento académico.</li>
                        <li><i class="fas fa-shield-alt text-secondary me-2"></i>Auditoría y bitácora de seguridad.</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-end" style="color: #28a745;">
                <a href="index.php?seccion=cajon2&tab=periodos" class="text-decoration-none small fw-semibold" style="color: #28a745;">Reportes de Rendimiento</a>
                <span class="mx-2">|</span>
                <a href="index.php?seccion=cajon2&tab=oferta" class="text-decoration-none small fw-semibold" style="color: #28a745;">Bitacora de Auditoria</a>    
            </div>
        </div>
    </div>

    <!-- Cajón 6: Importación SATS[cite: 3]-->
    <div class="col">
        <div class="card h-100 border-plano shadow-sm border-0 border-top border-3 hover-elevate" style="border-top-color: #dc3545 !important;">
            <div class="card-body p-4 d-flex">
                <div class="d-flex justify-content-center align-items-center text-white me-3 rounded-2 shadow-sm" style="width: 55px; height: 55px; background-color: #dc3545; flex-shrink: 0;">
                    <i class="fas fa-file-import fa-lg"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-2 text-dark fs-6">6. Importación SATS</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="fas fa-cloud-upload-alt text-secondary me-2"></i>Carga masiva interactiva (Dropzone).</li>
                        <li><i class="fas fa-tasks text-secondary me-2"></i>Clasificación automática de patrones.</li>
                    </ul>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top p-3 text-end">
                <a href="index.php?seccion=cajon6" class="text-decoration-none small fw-semibold" style="color: #dc3545;">Iniciar Carga SATS <i class="fas fa-chevron-right ms-1" style="font-size: 0.7rem;"></i></a>
            </div>
        </div>
    </div>

</div>