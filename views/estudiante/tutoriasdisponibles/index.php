<?php
/**
 * ARCHIVO: views/estudiante/tutoriasdisponibles/index.php
 * Vista: Lista de Tutorías Disponibles para Inscripción (Filtradas estrictamente por carrera y materias del estudiante).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_estudiante = 0;
$id_carrera = 0;
$tutorias_disponibles = [];

if (isset($pdo) && $id_usuario > 0) {
    try {
        // 1. Obtener ID del estudiante y su carrera
        $stmtEst = $pdo->prepare("SELECT id_estudiante, id_carrera FROM estudiantes WHERE id_usuario = ?");
        $stmtEst->execute([$id_usuario]);
        if ($rowEst = $stmtEst->fetch(PDO::FETCH_ASSOC)) {
            $id_estudiante = $rowEst['id_estudiante'];
            $id_carrera = $rowEst['id_carrera'];
        }

        if ($id_estudiante > 0 && $id_carrera > 0) {
            // 2. Consulta depurada: Filtra explícitamente que la materia pertenezca a la carrera del estudiante 
            // y que el estudiante no esté ya inscrito, respetando los cupos disponibles.
            $sql = "SELECT t.id_tutoria, t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, 
                           t.estado, t.tope_clases, t.limite_estudiantes, t.clases_impartidas,
                           m.nombre_materia, u.nombre as tutor_nom, u.apellido as tutor_ape, 
                           b.nombre_bloque,
                           (SELECT COUNT(*) FROM tutoria_estudiantes te2 WHERE te2.id_tutoria = t.id_tutoria) as total_inscritos
                    FROM tutorias t
                    JOIN materias m ON t.id_materia = m.id_materia
                    JOIN tutores tu ON t.id_tutor = tu.id_tutor
                    JOIN usuarios u ON tu.id_usuario = u.id_usuario
                    JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                    WHERE m.id_carrera = ? 
                      AND t.estado IN ('pendiente', 'confirmada', 'en_proceso')
                      AND t.fecha >= CURRENT_DATE
                      AND t.id_tutoria NOT IN (
                          SELECT id_tutoria FROM tutoria_estudiantes WHERE id_estudiante = ?
                      )
                    HAVING total_inscritos < t.limite_estudiantes
                    ORDER BY t.fecha ASC, t.hora_inicio ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_carrera, $id_estudiante]);
            $tutorias_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "Error al cargar las tutorías disponibles.";
    }
}
?>

<style>
    .tutoria-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; border-left: 6px solid #10b981; }
    .tutoria-card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .tutoria-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: linear-gradient(to right, #f0fdf4, #dcfce7); }
    .info-row { display: flex; align-items: center; margin-bottom: 0.75rem; font-size: 0.9rem; color: #475569; }
    .info-icon { width: 24px; color: #94a3b8; text-align: center; margin-right: 0.5rem; }
    .empty-state { padding: 4rem 2rem; text-align: center; color: #64748b; background: #fff; border: 1px dashed #cbd5e1; border-radius: 12px; }
</style>

<div class="animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Tutorías Disponibles</h3>
            <p class="text-muted mb-0">Sesiones programadas exclusivamente para las materias de tu carrera.</p>
        </div>
        <a href="index.php?seccion=cajon2" class="btn btn-outline-institucional border-plano shadow-sm">
            <i class="fas fa-plus me-2"></i> Solicitar Nueva
        </a>
    </div>

    <?php if (isset($error_bd)): ?>
        <div class="alert alert-danger border-plano"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_bd; ?></div>
    <?php endif; ?>

    <?php if (empty($tutorias_disponibles)): ?>
        <div class="empty-state shadow-sm">
            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 80px; height: 80px;">
                <i class="fas fa-search fa-2x text-muted opacity-50"></i>
            </div>
            <h5 class="fw-bold text-dark">No hay materias disponibles para inscripción</h5>
            <p class="mb-4 text-muted">No se encontraron grupos abiertos con cupos libres para las materias de tu plan de estudios actual.</p>
            <a href="index.php?seccion=cajon2" class="btn btn-institucional border-plano"><i class="fas fa-calendar-plus me-1"></i> Solicitar Tutoría Personalizada</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($tutorias_disponibles as $tut): 
                $cupos_restantes = $tut['limite_estudiantes'] - $tut['total_inscritos'];
                $porcentaje_ocupacion = ($tut['total_inscritos'] / $tut['limite_estudiantes']) * 100;
                $color_cupo = ($cupos_restantes <= 2) ? 'text-danger' : 'text-success';
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="tutoria-card h-100 d-flex flex-column">
                        <div class="tutoria-header">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success border-plano text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    <?php echo str_replace('_', ' ', $tut['estado']); ?>
                                </span>
                                <?php if($tut['modalidad'] == 'virtual'): ?>
                                    <span class="text-primary small fw-bold"><i class="fas fa-video me-1"></i> Virtual</span>
                                <?php else: ?>
                                    <span class="text-secondary small fw-bold"><i class="fas fa-building me-1"></i> Presencial</span>
                                <?php endif; ?>
                            </div>
                            <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?php echo htmlspecialchars($tut['nombre_materia']); ?>">
                                <?php echo htmlspecialchars($tut['nombre_materia']); ?>
                            </h5>
                            <div class="text-muted small">
                                <i class="fas fa-chalkboard-teacher me-1"></i> Prof. <?php echo htmlspecialchars($tut['tutor_nom'] . ' ' . $tut['tutor_ape']); ?>
                            </div>
                        </div>
                        
                        <div class="card-body p-4 flex-grow-1">
                            <div class="info-row">
                                <div class="info-icon"><i class="far fa-calendar-alt"></i></div>
                                <div class="fw-semibold text-dark"><?php echo date('l, d / m / Y', strtotime($tut['fecha'])); ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-icon"><i class="far fa-clock"></i></div>
                                <div><?php echo substr($tut['hora_inicio'], 0, 5) . ' - ' . substr($tut['hora_fin'], 0, 5); ?> <span class="text-muted small">(<?php echo htmlspecialchars($tut['nombre_bloque']); ?>)</span></div>
                            </div>
                            
                            <hr class="my-3 text-muted opacity-25">
                            
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-dark"><i class="fas fa-users me-1"></i> Cupos Disponibles</span>
                                <span class="small fw-bold <?php echo $color_cupo; ?>"><?php echo $cupos_restantes; ?> de <?php echo $tut['limite_estudiantes']; ?> libres</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $porcentaje_ocupacion; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0 p-4 pt-0">
                            <button class="btn btn-institucional w-100 border-plano shadow-sm fw-bold btn-unirse" 
                                    data-id="<?php echo $tut['id_tutoria']; ?>"
                                    data-materia="<?php echo htmlspecialchars($tut['nombre_materia']); ?>">
                                <i class="fas fa-sign-in-alt me-2"></i> Unirse al Grupo
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Confirmación para Unirse -->
<div class="modal fade" id="modalUnirse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-plano border-top border-4 border-institucional shadow">
            <div class="modal-body text-center py-4">
                <i class="fas fa-info-circle fa-2x text-institucional mb-3"></i>
                <p class="mb-0 fs-6 text-dark">¿Deseas inscribirte a la tutoría de <b id="nombre-materia-modal"></b>?</p>
                <div id="error-unirse" class="alert alert-danger d-none small py-2 mb-0 mt-3 text-start border-plano"></div>
            </div>
            <div class="modal-footer bg-light border-plano justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary border-plano" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-institucional border-plano fw-bold" id="btn-confirmar-unirse">Sí, Inscribirme</button>
            </div>
        </div>
    </div>
</div>

<script>
    let tutoriaAUnirse = 0;
    
    document.querySelectorAll('.btn-unirse').forEach(btn => {
        btn.addEventListener('click', function() {
            tutoriaAUnirse = this.getAttribute('data-id');
            document.getElementById('nombre-materia-modal').innerText = this.getAttribute('data-materia');
            document.getElementById('error-unirse').classList.add('d-none');
            const modal = new bootstrap.Modal(document.getElementById('modalUnirse'));
            modal.show();
        });
    });

    document.getElementById('btn-confirmar-unirse').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';

        let formData = new FormData();
        formData.append('accion', 'unirse_tutoria');
        formData.append('id_tutoria', tutoriaAUnirse);
        formData.append('id_estudiante', <?php echo $id_estudiante; ?>);

        fetch('../../controllers/EstudianteController.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.exito) {
                window.location.href = 'index.php?seccion=mistutorias';
            } else {
                const errBox = document.getElementById('error-unirse');
                errBox.innerText = data.error || 'No se pudo procesar la inscripción.';
                errBox.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = 'Sí, Inscribirme';
            }
        })
        .catch(err => {
            const errBox = document.getElementById('error-unirse');
            errBox.innerText = 'Error de conexión con el servidor.';
            errBox.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = 'Sí, Inscribirme';
        });
    });
</script>