<?php
/**
 * ARCHIVO: views/tutor/solicitudes/index.php
 * Vista de Gestión de Solicitudes Pendientes para el Tutor.
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_tutor = 0;
$solicitudes = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        // 1. Obtener ID real del tutor
        $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
        $stmtTut->execute([$id_usuario]);
        $id_tutor = $stmtTut->fetchColumn();

        if ($id_tutor) {
            // 2. Consultar solo las solicitudes pendientes de este tutor
            $sql = "SELECT t.id_tutoria, t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, t.observaciones, 
                           m.nombre_materia, u.nombre as est_nom, u.apellido as est_ape, e.registro_universitario,
                           b.nombre_bloque
                    FROM tutorias t
                    JOIN materias m ON t.id_materia = m.id_materia
                    JOIN estudiantes e ON t.id_estudiante = e.id_estudiante
                    JOIN usuarios u ON e.id_usuario = u.id_usuario
                    JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                    WHERE t.id_tutor = ? AND t.estado = 'pendiente'
                    ORDER BY t.fecha ASC, t.hora_inicio ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_tutor]);
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "No se pudieron cargar las solicitudes pendientes.";
    }
}
?>

<style>
    .request-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        border-left: 4px solid #f59e0b; /* Borde naranja de pendiente */
    }
    .request-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .btn-action {
        width: 40px; height: 40px;
        display: inline-flex; justify-content: center; align-items: center;
        border-radius: 8px; font-size: 1.1rem;
    }
    .empty-state {
        background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;
        padding: 4rem 2rem; text-align: center; color: #64748b;
    }
</style>

<div class="animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Bandeja de Solicitudes</h3>
            <p class="text-muted mb-0">Revisa, aprueba o rechaza las peticiones de tutoría de tus estudiantes.</p>
        </div>
    </div>

    <?php if (isset($error_bd)): ?>
        <div class="alert alert-danger border-plano"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_bd; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($solicitudes)): ?>
            <div class="col-12">
                <div class="empty-state shadow-sm">
                    <i class="fas fa-inbox fa-3x text-secondary opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark">Bandeja Vacía</h5>
                    <p class="mb-0">Excelente, no tienes solicitudes de tutoría pendientes de revisión.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($solicitudes as $sol): ?>
                <div class="col-md-6 col-lg-6">
                    <div class="request-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($sol['nombre_materia']); ?></h6>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-user-graduate me-1"></i> <?php echo htmlspecialchars($sol['est_nom'] . ' ' . $sol['est_ape']); ?> (RU: <?php echo htmlspecialchars($sol['registro_universitario']); ?>)
                                </p>
                            </div>
                            <span class="badge bg-warning text-dark border-plano px-2 py-1"><i class="fas fa-hourglass-half me-1"></i> PENDIENTE</span>
                        </div>
                        
                        <div class="bg-light p-3 rounded border border-light mb-3 flex-grow-1">
                            <div class="row g-2 text-dark small mb-2">
                                <div class="col-6">
                                    <i class="far fa-calendar-alt text-institucional me-1"></i> <strong>Fecha:</strong><br>
                                    <?php echo date('d/m/Y', strtotime($sol['fecha'])); ?>
                                </div>
                                <div class="col-6">
                                    <i class="far fa-clock text-institucional me-1"></i> <strong>Horario:</strong><br>
                                    <?php echo substr($sol['hora_inicio'],0,5).' - '.substr($sol['hora_fin'],0,5); ?>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <strong>Modalidad Solicitada:</strong> 
                                <?php if($sol['modalidad'] == 'virtual'): ?>
                                    <span class="text-primary fw-semibold"><i class="fas fa-video me-1"></i> Virtual</span>
                                <?php else: ?>
                                    <span class="text-success fw-semibold"><i class="fas fa-building me-1"></i> Presencial</span>
                                <?php endif; ?>
                            </div>

                            <?php if(!empty($sol['observaciones'])): ?>
                                <div class="mt-2 pt-2 border-top">
                                    <strong>Nota del estudiante:</strong>
                                    <p class="text-muted fst-italic mb-0 mt-1">"<?php echo htmlspecialchars($sol['observaciones']); ?>"</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Botonera de Acción -->
                        <div class="d-flex justify-content-end gap-2 mt-auto">
                            <!-- Botón Rechazar -->
                            <button type="button" class="btn btn-outline-danger border-plano px-3" 
                                    onclick="abrirModalRechazar(<?php echo $sol['id_tutoria']; ?>)">
                                <i class="fas fa-times me-1"></i> Rechazar
                            </button>
                            <!-- Botón Aceptar -->
                            <button type="button" class="btn btn-success border-plano px-4" 
                                    onclick="abrirModalAceptar(<?php echo $sol['id_tutoria']; ?>, '<?php echo $sol['modalidad']; ?>')">
                                <i class="fas fa-check me-1"></i> Aceptar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PARA ACEPTAR TUTORÍA -->
<div class="modal fade" id="modalAceptar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-top border-4 border-success shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-check-circle text-success me-2"></i> Confirmar Tutoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAceptar">
                    <input type="hidden" name="accion" value="aceptar_tutoria">
                    <input type="hidden" name="id_tutoria" id="aceptar_id_tutoria">
                    
                    <div class="alert alert-info border-plano small">
                        <i class="fas fa-info-circle me-1"></i> Al aceptar, la tutoría quedará agendada. Define el lugar o enlace para notificar al estudiante.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary" id="lblLugarEnlace">Lugar o Enlace de la Sesión <span class="text-danger">*</span></label>
                        <input type="text" class="form-control border-plano" name="lugar_o_enlace" id="inputLugarEnlace" required 
                               placeholder="Ej: Aula 102, Bloque C / https://zoom.us/j/1234...">
                        <div class="form-text mt-1" id="helpLugarEnlace"></div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light border-plano" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success border-plano" id="btnSubmitAceptar">
                            <i class="fas fa-save me-2"></i> Confirmar y Agendar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA RECHAZAR TUTORÍA -->
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-top border-4 border-danger shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-times-circle text-danger me-2"></i> Rechazar Tutoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRechazar">
                    <input type="hidden" name="accion" value="rechazar_tutoria">
                    <input type="hidden" name="id_tutoria" id="rechazar_id_tutoria">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Motivo del rechazo <span class="text-danger">*</span></label>
                        <textarea class="form-control border-plano" name="motivo_cancelacion" rows="3" required 
                                  placeholder="Ej: Tengo otra reunión en ese horario. Por favor, solicita en otro bloque disponible."></textarea>
                        <div class="form-text mt-1">El estudiante verá este motivo en su historial.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light border-plano" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger border-plano" id="btnSubmitRechazar">
                            <i class="fas fa-paper-plane me-2"></i> Enviar Rechazo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Referencias a Modales
let modalAceptarInst;
let modalRechazarInst;

document.addEventListener('DOMContentLoaded', function() {
    modalAceptarInst = new bootstrap.Modal(document.getElementById('modalAceptar'));
    modalRechazarInst = new bootstrap.Modal(document.getElementById('modalRechazar'));

    // Configurar envío del formulario ACEPTAR
    document.getElementById('formAceptar').addEventListener('submit', function(e) {
        e.preventDefault();
        procesarFormulario(this, 'btnSubmitAceptar', modalAceptarInst, 'Tutoría confirmada y agendada correctamente.');
    });

    // Configurar envío del formulario RECHAZAR
    document.getElementById('formAceptar').addEventListener('submit', function(e) {
        // Ignorar aquí, corregimos selector abajo
    });
    
    document.getElementById('formRechazar').addEventListener('submit', function(e) {
        e.preventDefault();
        procesarFormulario(this, 'btnSubmitRechazar', modalRechazarInst, 'Tutoría rechazada. El estudiante será notificado.');
    });
});

// Función para abrir Modal Aceptar
function abrirModalAceptar(idTutoria, modalidad) {
    document.getElementById('aceptar_id_tutoria').value = idTutoria;
    
    // Adaptar textos según modalidad
    const inputLugar = document.getElementById('inputLugarEnlace');
    const helpLugar = document.getElementById('helpLugarEnlace');
    
    inputLugar.value = '';
    if(modalidad === 'virtual') {
        inputLugar.placeholder = "https://teams.microsoft.com/l/meetup-join/...";
        helpLugar.innerHTML = "Pega aquí el enlace de Microsoft Teams o Zoom.";
        inputLugar.type = "url";
    } else {
        inputLugar.placeholder = "Ej: Aula 102, Bloque C";
        helpLugar.innerHTML = "Indica el bloque y número de aula presencial.";
        inputLugar.type = "text";
    }
    
    modalAceptarInst.show();
}

// Función para abrir Modal Rechazar
function abrirModalRechazar(idTutoria) {
    document.getElementById('rechazar_id_tutoria').value = idTutoria;
    document.getElementById('formRechazar').reset();
    modalRechazarInst.show();
}

// Función centralizada para enviar AJAX
function procesarFormulario(formulario, btnId, modalInstancia, mensajeExito) {
    const btn = document.getElementById(btnId);
    const textoOriginal = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
    btn.disabled = true;

    let formData = new FormData(formulario);

    fetch('../../controllers/TutorController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.exito) {
            alert(mensajeExito);
            modalInstancia.hide();
            window.location.reload();
        } else {
            alert("Error: " + (data.error || "No se pudo procesar la solicitud."));
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error de conexión al servidor.");
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    });
}
</script>