<?php
/**
 * ARCHIVO: views/estudiante/cajon2/index.php
 * Cajón 2 - Estudiante: Mi Expediente de Grado (Vista de seguimiento).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que sea rol estudiante (3)
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 3) {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    exit();
}

// Aquí, en un entorno real, instanciaríamos el modelo para obtener los datos del estudiante:
// $id_usuario = $_SESSION['id_usuario'];
// $expediente = MgExpedienteModel::obtenerPorEstudiante($id_usuario);
// Para esta vista, simularemos los estados de la interfaz para maquetar el diseño.
$tiene_expediente = true; // Cambiar a false para ver el "Empty State"
$etapa_actual = 'mg1'; // previa, mg1, mg2, finalizado
$estado = 'activo';
?>

<style>
    .premium-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        background: #ffffff;
    }
    .badge-etapa {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 50rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .bg-soft-primary { background-color: #e0f2fe; color: #0369a1; }
    .bg-soft-success { background-color: #dcfce7; color: #15803d; }
    .bg-soft-warning { background-color: #fef3c7; color: #b45309; }
    
    .nav-pills-custom .nav-link {
        color: #64748b;
        border-radius: 12px;
        font-weight: 600;
        padding: 0.75rem 1.25rem;
        transition: all 0.2s;
    }
    .nav-pills-custom .nav-link:hover {
        background-color: #f8fafc;
        color: #0f172a;
    }
    .nav-pills-custom .nav-link.active {
        background-color: #1a3b5c; /* Color institucional UPDS */
        color: #ffffff;
        box-shadow: 0 4px 6px rgba(26, 59, 92, 0.2);
    }
    .timeline-item {
        border-left: 3px solid #e2e8f0;
        padding-left: 1.5rem;
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 0;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background-color: #1a3b5c;
        border: 3px solid #ffffff;
    }
</style>

<div class="container-fluid animate__animated animate__fadeIn pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-dark fw-bold mb-1"><i class="fas fa-graduation-cap text-institucional me-2"></i>Mi Expediente de Grado</h4>
            <p class="text-muted mb-0">Consulta el estado de tu modalidad, tutor asignado y defensas.</p>
        </div>
    </div>

    <?php if (!$tiene_expediente): ?>
        <!-- ESTADO VACÍO (Cuando el estudiante aún no ingresa a Modalidad de Grado) -->
        <div class="card premium-card text-center py-5">
            <div class="card-body">
                <i class="fas fa-folder-open fa-5x text-secondary opacity-50 mb-4"></i>
                <h4 class="fw-bold text-dark">Aún no tienes un expediente activo</h4>
                <p class="text-muted mb-4 max-w-50 mx-auto">Actualmente no te encuentras registrado en ninguna Modalidad de Grado. Si crees que esto es un error, por favor contacta a la Coordinación.</p>
                <button class="btn btn-institucional px-4 rounded-pill shadow-sm">
                    <i class="fas fa-envelope me-2"></i>Contactar Coordinación
                </button>
            </div>
        </div>
    <?php else: ?>
        <!-- CABECERA DEL EXPEDIENTE -->
        <div class="card premium-card mb-4 border-0">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-soft-primary badge-etapa me-2"><i class="fas fa-flag me-1"></i>Etapa Actual: MG1 (Perfil)</span>
                            <span class="badge bg-soft-success badge-etapa"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>Activo</span>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Proyecto de Grado</h3>
                        <p class="text-muted mb-0"><i class="fas fa-users me-2"></i>Cohorte: <strong>Grupo 1 - Marzo 2026</strong></p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <p class="text-muted small mb-1">Avance Global Sugerido</p>
                        <div class="progress" style="height: 10px; border-radius: 10px;">
                            <div class="progress-bar bg-institucional" role="progressbar" style="width: 35%;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-dark fw-bold">35% completado</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑAS DE NAVEGACIÓN -->
        <ul class="nav nav-pills nav-pills-custom mb-4 gap-2" id="expediente-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="datos-tab" data-bs-toggle="pill" data-bs-target="#datos" type="button" role="tab"><i class="fas fa-info-circle me-2"></i>Datos del Proyecto</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tutor-tab" data-bs-toggle="pill" data-bs-target="#tutor" type="button" role="tab"><i class="fas fa-chalkboard-teacher me-2"></i>Mi Tutor</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="defensas-tab" data-bs-toggle="pill" data-bs-target="#defensas" type="button" role="tab"><i class="fas fa-gavel me-2"></i>Tribunales y Defensas</button>
            </li>
        </ul>

        <!-- CONTENIDO DE LAS PESTAÑAS -->
        <div class="tab-content" id="expediente-tabsContent">
            
            <!-- PESTAÑA 1: DATOS DEL PROYECTO -->
            <div class="tab-pane fade show active" id="datos" role="tabpanel" tabindex="0">
                <div class="card premium-card mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark">Información General</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="text-muted small fw-bold text-uppercase mb-1">Título del Trabajo</label>
                                <div class="p-3 bg-light rounded-3 border">
                                    <h6 class="mb-0 text-dark fw-bold">Implementación de un Sistema de Gestión Documental para la UPDS Sede Tarija</h6>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase mb-1">Fecha de Inicio (MG1)</label>
                                <p class="mb-0 fw-semibold text-dark"><i class="far fa-calendar-alt text-institucional me-2"></i>15 de Marzo, 2026</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small fw-bold text-uppercase mb-1">Registro de Aprobación</label>
                                <p class="mb-0 fw-semibold text-dark"><i class="fas fa-file-signature text-institucional me-2"></i>Nota Decanatura N° 045/2026</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA 2: MI TUTOR -->
            <div class="tab-pane fade" id="tutor" role="tabpanel" tabindex="0">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card premium-card h-100">
                            <div class="card-body p-4 text-center">
                                <!-- Estado: Asignación Vigente según modelo BD -->
                                <span class="badge bg-success mb-3">Tutor Vigente</span>
                                <div class="mb-3">
                                    <img src="../../../../assets/img/tutores/default_tutor.png" class="rounded-circle shadow-sm" width="100" height="100" alt="Foto Tutor" style="object-fit: cover; border: 3px solid #f8fafc;">
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Ing. Carlos Docente</h5>
                                <p class="text-muted small mb-3">Especialista en Desarrollo Web y Bases de Datos</p>
                                <div class="d-grid gap-2">
                                    <a href="mailto:tutor@tutorias.local" class="btn btn-outline-primary border-plano">
                                        <i class="fas fa-envelope me-2"></i>Enviar Correo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card premium-card h-100">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                                <h6 class="fw-bold text-dark"><i class="fas fa-history me-2 text-institucional"></i>Historial de Asignaciones</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="timeline-item">
                                    <h6 class="fw-bold text-dark mb-1">Ing. Carlos Docente</h6>
                                    <p class="text-muted small mb-2"><i class="far fa-calendar me-1"></i>Asignado el 20 de Marzo, 2026 (Actual)</p>
                                    <p class="mb-0 small bg-light p-2 rounded border">Referencia: Carta N° 012/2026 entregada.</p>
                                </div>
                                <!-- Ejemplo si hubiera renunciado uno anterior según RN-MG-07 -->
                                <div class="timeline-item" style="border-left-color: transparent;">
                                    <h6 class="text-secondary mb-1">Sin asignaciones previas.</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA 3: TRIBUNALES Y DEFENSAS -->
            <div class="tab-pane fade" id="defensas" role="tabpanel" tabindex="0">
                <!-- Defensa MG1 -->
                <div class="card premium-card mb-4 border-start border-4 border-institucional">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-dark mb-0">Defensa de Perfil (MG1)</h5>
                            <span class="badge bg-soft-warning text-dark px-3 py-2 rounded-pill"><i class="far fa-clock me-1"></i>Programada</span>
                        </div>
                        
                        <div class="row g-4 mt-1">
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded-3 h-100 border">
                                    <p class="text-muted small text-uppercase fw-bold mb-1"><i class="far fa-calendar-alt me-2"></i>Fecha y Hora</p>
                                    <h6 class="fw-bold text-dark mb-0">15 de Mayo, 2026</h6>
                                    <p class="text-muted mb-0">10:00 AM - 11:30 AM</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded-3 h-100 border">
                                    <p class="text-muted small text-uppercase fw-bold mb-1"><i class="fas fa-map-marker-alt me-2"></i>Ambiente</p>
                                    <h6 class="fw-bold text-dark mb-0">Auditorio Principal</h6>
                                    <p class="text-muted mb-0">Bloque A, Planta Baja</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded-3 h-100 border">
                                    <p class="text-muted small text-uppercase fw-bold mb-1"><i class="fas fa-users me-2"></i>Tribunales Asignados</p>
                                    <ul class="list-unstyled mb-0 small text-dark fw-semibold">
                                        <li class="mb-1"><i class="fas fa-user-tie text-secondary me-2"></i>1. Ing. Pedro Rodríguez</li>
                                        <li><i class="fas fa-user-tie text-secondary me-2"></i>2. Lic. Ana Martínez</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Defensa MG2 (Bloqueada/Pendiente) -->
                <div class="card premium-card bg-light border-0" style="opacity: 0.7;">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-lock fa-2x text-secondary mb-2"></i>
                        <h6 class="fw-bold text-dark">Defensa Final (MG2)</h6>
                        <p class="text-muted small mb-0">Debes aprobar tu defensa de Perfil (MG1) y completar los informes de avance para habilitar esta etapa.</p>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<script>
    // Pequeño script para asegurar que las pestañas de Bootstrap 5 funcionen sin dependencias externas pesadas
    document.addEventListener('DOMContentLoaded', function () {
        var triggerTabList = [].slice.call(document.querySelectorAll('#expediente-tabs button'))
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    });
</script>