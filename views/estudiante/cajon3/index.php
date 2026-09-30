<?php
/**
 * ARCHIVO: views/estudiante/cajon3/index.php
 * Vista de Seguimiento: Solicitudes Pendientes e Historial de Tutorías (Actualizado con Módulos de Clases).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_estudiante = 0;
$pendientes = [];
$historial = [];

if (isset($pdo) && $id_usuario > 0) {
    try {
        $stmtEst = $pdo->prepare("SELECT id_estudiante FROM estudiantes WHERE id_usuario = ?");
        $stmtEst->execute([$id_usuario]);
        $id_estudiante = $stmtEst->fetchColumn();

        if ($id_estudiante) {
            // Consulta actualizada incluyendo tope_clases y clases_impartidas
            $sql = "SELECT t.id_tutoria, t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, 
                           t.lugar_o_enlace, t.estado, t.motivo_cancelacion, 
                           t.tope_clases, t.clases_impartidas,
                           te.observaciones_estudiante as observaciones, 
                           m.nombre_materia, u.nombre as tutor_nom, u.apellido as tutor_ape, 
                           b.nombre_bloque
                    FROM tutorias t
                    JOIN materias m ON t.id_materia = m.id_materia
                    JOIN tutores tu ON t.id_tutor = tu.id_tutor
                    JOIN usuarios u ON tu.id_usuario = u.id_usuario
                    JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                    JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
                    WHERE te.id_estudiante = ? 
                    ORDER BY t.fecha DESC, t.hora_inicio DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_estudiante]);
            $todas_tutorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($todas_tutorias as $tut) {
                if ($tut['estado'] === 'pendiente') {
                    $pendientes[] = $tut;
                } else {
                    $historial[] = $tut;
                }
            }
        }
    } catch (PDOException $e) {
        $error_bd = "Error al cargar el historial de tutorías.";
    }
}
?>

<style>
    .section-title {
        font-size: 1.1rem; font-weight: 700; color: #1e293b;
        margin-bottom: 1.2rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;
    }
    .pending-card {
        background: #fff; border: 1px solid #fde68a; border-left: 4px solid #f59e0b;
        border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .pending-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .pending-icon {
        background: #fef3c7; color: #d97706; width: 45px; height: 45px;
        border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 1.2rem;
    }
    .table-container { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; }
    .table-header th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; padding: 1rem; background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; }
    .empty-state { padding: 3rem 2rem; text-align: center; color: #64748b; background: #fff; border: 1px dashed #cbd5e1; border-radius: 8px; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Seguimiento de Tutorías</h3>
            <p class="text-muted mb-0">Revisa el estado de tus solicitudes y tu historial completo de clases.</p>
        </div>
        <a href="index.php?seccion=cajon2" class="btn btn-institucional border-plano shadow-sm">
            <i class="fas fa-plus me-2"></i> Nueva Solicitud
        </a>
    </div>

    <!-- SECCIÓN 1: SOLICITUDES PENDIENTES -->
    <h5 class="section-title"><i class="fas fa-hourglass-half text-warning me-2"></i> Solicitudes en Espera de Confirmación</h5>
    
    <?php if (empty($pendientes)): ?>
        <div class="empty-state mb-5 shadow-sm">
            <i class="fas fa-check-circle fa-3x text-success opacity-50 mb-3"></i>
            <h6 class="fw-bold text-dark">¡Todo al día!</h6>
            <p class="mb-0 text-muted small">No tienes ninguna solicitud pendiente de aprobación por parte de los docentes.</p>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-5">
            <?php foreach ($pendientes as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="pending-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="pending-icon shadow-sm"><i class="fas fa-clock"></i></div>
                            <span class="badge bg-warning text-dark border-plano">EN ESPERA</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($p['nombre_materia']); ?></h6>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-chalkboard-teacher me-1"></i> Prof. <?php echo htmlspecialchars($p['tutor_nom'] . ' ' . $p['tutor_ape']); ?>
                        </p>
                        
                        <div class="bg-light p-2 rounded border border-light mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="far fa-calendar-alt text-secondary me-2" style="width:16px;"></i>
                                <span class="fw-semibold text-dark" style="font-size:0.9rem;"><?php echo date('d/m/Y', strtotime($p['fecha'])); ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-1">
                                <i class="far fa-clock text-secondary me-2" style="width:16px;"></i>
                                <span class="text-dark" style="font-size:0.9rem;"><?php echo substr($p['hora_inicio'],0,5).' - '.substr($p['hora_fin'],0,5); ?> (<?php echo htmlspecialchars($p['nombre_bloque']); ?>)</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <?php if($p['modalidad'] == 'virtual'): ?>
                                    <i class="fas fa-video text-primary me-2" style="width:16px;"></i>
                                    <span class="text-primary fw-semibold" style="font-size:0.9rem;">Virtual</span>
                                <?php else: ?>
                                    <i class="fas fa-building text-success me-2" style="width:16px;"></i>
                                    <span class="text-success fw-semibold" style="font-size:0.9rem;">Presencial</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <span class="badge bg-light text-dark border"><i class="fas fa-layer-group text-primary me-1"></i> Módulo: <?php echo $p['tope_clases']; ?> Clases</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN 2: HISTORIAL COMPLETO -->
    <h5 class="section-title mt-2"><i class="fas fa-history text-secondary me-2"></i> Historial Completo y Módulos</h5>
    
    <div class="table-container shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="table-header">
                        <th class="ps-4">Fecha y Horario</th>
                        <th>Materia y Docente</th>
                        <th>Modalidad / Lugar</th>
                        <th>Estado y Progreso</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state border-0">
                                    <i class="fas fa-folder-open fa-3x text-secondary opacity-50 mb-3"></i>
                                    <p class="mb-0">Aún no tienes un historial de tutorías registradas.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $h): 
                            $badge_class = 'bg-secondary';
                            if($h['estado'] == 'confirmada') $badge_class = 'bg-info text-white';
                            if($h['estado'] == 'realizada') $badge_class = 'bg-success';
                            if($h['estado'] == 'cancelada') $badge_class = 'bg-danger';
                            if($h['estado'] == 'en_proceso') $badge_class = 'bg-primary';
                        ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark"><?php echo date('d/m/Y', strtotime($h['fecha'])); ?></div>
                                <small class="text-muted"><?php echo substr($h['hora_inicio'],0,5).' - '.substr($h['hora_fin'],0,5); ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($h['nombre_materia']); ?></div>
                                <small class="text-muted"><i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($h['tutor_nom'].' '.$h['tutor_ape']); ?></small>
                            </td>
                            <td>
                                <?php if($h['modalidad'] == 'virtual'): ?>
                                    <span class="badge bg-light text-primary border"><i class="fas fa-video me-1"></i> Virtual</span>
                                    <?php if(!empty($h['lugar_o_enlace']) && $h['lugar_o_enlace'] != 'Por asignar' && $h['estado'] != 'cancelada'): ?>
                                        <br><a href="<?php echo htmlspecialchars($h['lugar_o_enlace']); ?>" target="_blank" class="small mt-1 d-inline-block text-truncate" style="max-width:180px;"><i class="fas fa-external-link-alt me-1"></i> Unirse a reunión</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-light text-success border"><i class="fas fa-building me-1"></i> Presencial</span>
                                    <?php if(!empty($h['lugar_o_enlace']) && $h['lugar_o_enlace'] != 'Por asignar' && $h['estado'] != 'cancelada'): ?>
                                        <br><small class="text-muted d-inline-block mt-1"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo htmlspecialchars($h['lugar_o_enlace']); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?> px-2 py-1 border-plano mb-1 d-inline-block">
                                    <?php echo strtoupper($h['estado']); ?>
                                </span>
                                <!-- Indicador visual de clases impartidas vs tope -->
                                <div>
                                    <span class="badge bg-light text-dark border shadow-sm">
                                        <i class="fas fa-layer-group text-primary me-1"></i> <?php echo ($h['clases_impartidas'] ?? 0) . ' / ' . ($h['tope_clases'] ?? 1); ?> Clases
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if($h['estado'] == 'cancelada' && !empty($h['motivo_cancelacion'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-plano" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($h['motivo_cancelacion']); ?>">
                                        <i class="fas fa-info-circle me-1"></i> Motivo
                                    </button>
                                <?php elseif(!empty($h['observaciones'])): ?>
                                    <button type="button" class="btn btn-sm btn-light border border-plano text-muted" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($h['observaciones']); ?>">
                                        <i class="far fa-comment-alt"></i> Mi nota
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>