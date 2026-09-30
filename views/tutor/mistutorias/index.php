<?php
/**
 * ARCHIVO: views/tutor/mistutorias/index.php
 * Vista de Tutorías activas del docente con opción a salir/cancelar (Soporte Grupal 1:N).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_tutor = 0;
$tutorias_activas = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
        $stmtTut->execute([$id_usuario]);
        $id_tutor = $stmtTut->fetchColumn();

        if ($id_tutor) {
            // Consulta adaptada a tutoria_estudiantes con agregación grupal
            $sql = "SELECT t.id_tutoria, t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, t.lugar_o_enlace, t.estado, 
                           m.nombre_materia, b.nombre_bloque,
                           COUNT(te.id_estudiante) as total_alumnos,
                           GROUP_CONCAT(CONCAT(u.nombre, ' ', u.apellido, ' (RU: ', e.registro_universitario, ')') SEPARATOR '||') as lista_estudiantes
                    FROM tutorias t
                    JOIN materias m ON t.id_materia = m.id_materia
                    JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                    LEFT JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
                    LEFT JOIN estudiantes e ON te.id_estudiante = e.id_estudiante
                    LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
                    WHERE t.id_tutor = ? AND t.estado IN ('confirmada', 'en_proceso')
                    GROUP BY t.id_tutoria
                    ORDER BY t.fecha ASC, t.hora_inicio ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_tutor]);
            $tutorias_activas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "No se pudieron cargar tus tutorías activas.";
    }
}
?>

<style>
    .tutoria-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border-left: 4px solid #0ea5e9;
        transition: transform 0.2s;
    }
    .tutoria-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .estado-en_proceso { border-left-color: #8b5cf6; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Mis Tutorías Activas</h3>
            <p class="text-muted mb-0">Listado de clases confirmadas y en proceso con tus grupos asignados.</p>
        </div>
    </div>

    <?php if (isset($error_bd)): ?>
        <div class="alert alert-danger border-plano"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_bd; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($tutorias_activas)): ?>
            <div class="col-12">
                <div class="p-5 text-center text-muted bg-light border rounded shadow-sm">
                    <i class="fas fa-book-reader fa-3x mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">No hay tutorías activas</h5>
                    <p class="mb-0">Actualmente no tienes tutorías agendadas ni en proceso.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tutorias_activas as $tut): 
                $card_class = ($tut['estado'] === 'en_proceso') ? 'estado-en_proceso' : '';
                $badge_class = ($tut['estado'] === 'en_proceso') ? 'bg-purple' : 'bg-info';
            ?>
                <div class="col-md-6 col-lg-6">
                    <div class="tutoria-card <?php echo $card_class; ?> p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($tut['nombre_materia']); ?></h5>
                                <span class="badge border border-secondary text-secondary bg-light mt-1">
                                    <?php echo ($tut['modalidad'] == 'virtual') ? '<i class="fas fa-video me-1"></i> Virtual' : '<i class="fas fa-building me-1"></i> Presencial'; ?>
                                </span>
                            </div>
                            <span class="badge <?php echo $badge_class; ?> text-white border-plano px-2 py-1">
                                <?php echo strtoupper(str_replace('_', ' ', $tut['estado'])); ?>
                            </span>
                        </div>
                        
                        <div class="bg-light p-3 rounded border border-light mb-3 flex-grow-1">
                            <div class="mb-3 border-bottom pb-2">
                                <div class="text-secondary small fw-bold text-uppercase mb-2">
                                    Estudiantes Participantes (<span class="text-dark"><?php echo $tut['total_alumnos']; ?></span>)
                                </div>
                                <ul class="list-unstyled mb-0 ms-1">
                                    <?php if (!empty($tut['lista_estudiantes'])): ?>
                                        <?php 
                                        $alumnos = explode('||', $tut['lista_estudiantes']);
                                        foreach($alumnos as $alumno): 
                                        ?>
                                            <li class="small text-dark mb-2 border-bottom border-light pb-1">
                                                <i class="fas fa-user-graduate text-institucional me-2"></i><?php echo htmlspecialchars($alumno); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="small text-muted fst-italic">Sin estudiantes inscritos aún.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="row g-2 text-dark small">
                                <div class="col-6">
                                    <i class="far fa-calendar-alt text-institucional me-1"></i> <strong>Fecha:</strong><br>
                                    <?php echo date('d/m/Y', strtotime($tut['fecha'])); ?>
                                </div>
                                <div class="col-6">
                                    <i class="far fa-clock text-institucional me-1"></i> <strong>Horario:</strong><br>
                                    <?php echo substr($tut['hora_inicio'], 0, 5) . ' - ' . substr($tut['hora_fin'], 0, 5); ?>
                                </div>
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <i class="fas fa-map-marker-alt text-institucional me-1"></i> <strong>Lugar / Enlace:</strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($tut['lugar_o_enlace']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-auto">
                            <button type="button" class="btn btn-outline-danger border-plano" 
                                    onclick="abrirModalSalir(<?php echo $tut['id_tutoria']; ?>, '<?php echo htmlspecialchars($tut['nombre_materia']); ?>')">
                                <i class="fas fa-sign-out-alt me-1"></i> Salir de la Tutoría
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PARA SALIR / CANCELAR TUTORÍA -->
<div class="modal fade" id="modalSalirTutoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-top border-4 border-danger shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-sign-out-alt text-danger me-2"></i> Salir de la Tutoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSalirTutoria">
                    <input type="hidden" name="accion" value="cancelar_tutoria">
                    <input type="hidden" name="id_tutoria" id="cancelar_id_tutoria">
                    
                    <div class="alert alert-warning border-plano small">
                        <i class="fas fa-exclamation-triangle me-1"></i> Estás a punto de cancelar tu participación en la tutoría de <strong id="nombre_materia_cancelar"></strong>. Esta acción notificará a los estudiantes del grupo y no se puede deshacer.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Motivo de salida <span class="text-danger">*</span></label>
                        <textarea class="form-control border-plano" name="motivo_cancelacion" rows="3" required 
                                  placeholder="Ej: Inconveniente personal de fuerza mayor, pido disculpas a los estudiantes."></textarea>
                        <div class="form-text mt-1 text-danger">Debes justificar el motivo por el cual abandonas la sesión.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light border-plano" data-bs-dismiss="modal">Mantener Tutoría</button>
                        <button type="submit" class="btn btn-danger border-plano" id="btnSubmitSalir">
                            <i class="fas fa-check me-2"></i> Confirmar Salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let modalSalirInst;

document.addEventListener('DOMContentLoaded', function() {
    modalSalirInst = new bootstrap.Modal(document.getElementById('modalSalirTutoria'));

    document.getElementById('formSalirTutoria').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btnSubmit = document.getElementById('btnSubmitSalir');
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
        btnSubmit.disabled = true;

        let formData = new FormData(this);

        fetch('../../controllers/TutorController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.exito) {
                alert("Has salido de la tutoría. El registro ha sido cancelado.");
                modalSalirInst.hide();
                window.location.reload();
            } else {
                alert("Error: " + (data.error || "No se pudo procesar la solicitud."));
                btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i> Confirmar Salida';
                btnSubmit.disabled = false;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error de conexión al servidor.");
            btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i> Confirmar Salida';
            btnSubmit.disabled = false;
        });
    });
});

function abrirModalSalir(idTutoria, materia) {
    document.getElementById('cancelar_id_tutoria').value = idTutoria;
    document.getElementById('nombre_materia_cancelar').textContent = materia;
    document.getElementById('formSalirTutoria').reset();
    modalSalirInst.show();
}
</script>