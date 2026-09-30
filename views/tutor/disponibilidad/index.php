<?php
/**
 * ARCHIVO: views/tutor/disponibilidad/index.php
 * Vista para la Gestión de Disponibilidad Horaria del Tutor (Con Modal, Edición y Alertas Inline).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_tutor = 0;
$disponibilidades = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
        $stmtTut->execute([$id_usuario]);
        $id_tutor = $stmtTut->fetchColumn();

        if ($id_tutor) {
            $sqlDisp = "SELECT id_disponibilidad, dia_semana, hora_inicio, hora_fin 
                        FROM disponibilidad_tutor 
                        WHERE id_tutor = ?
                        ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'), hora_inicio ASC";
            $stmtDisp = $pdo->prepare($sqlDisp);
            $stmtDisp->execute([$id_tutor]);
            $disponibilidades = $stmtDisp->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "Error de BD: " . $e->getMessage();
    }
}
?>

<style>
    .table-container { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; }
    .table-header th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; padding: 1rem; background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Mi Disponibilidad Horaria</h3>
            <p class="text-muted mb-0">Define y administra los días y rangos de hora en los que atiendes tutorías.</p>
        </div>
        <div>
            <button type="button" class="btn btn-institucional border-plano shadow-sm" onclick="abrirModalCrear()">
                <i class="fas fa-plus-circle me-2"></i> Registrar Horario
            </button>
        </div>
    </div>

    <?php if (isset($error_bd)): ?>
        <div class="alert alert-danger border-plano"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_bd; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="table-container shadow-sm">
                <div class="px-4 py-3 border-bottom bg-light">
                    <h6 class="mb-0 fw-bold text-dark"><i class="far fa-clock me-2"></i> Mis Horarios Registrados</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr class="table-header">
                                <th class="ps-4">Día</th>
                                <th>Rango Horario (24h)</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($disponibilidades)): ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="p-5 text-center text-muted">
                                            <i class="far fa-calendar-times fa-3x mb-2 opacity-50"></i>
                                            <h6 class="fw-bold text-dark">Sin horarios registrados</h6>
                                            <p class="mb-0 small">Haz clic en "Registrar Horario" para añadir tu disponibilidad.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($disponibilidades as $d): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($d['dia_semana']); ?></td>
                                    <td><span class="text-muted"><i class="far fa-clock me-1"></i> <?php echo substr($d['hora_inicio'], 0, 5) . ' - ' . substr($d['hora_fin'], 0, 5); ?></span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary border-plano me-1" 
                                                onclick="abrirModalEditar(<?php echo $d['id_disponibilidad']; ?>, '<?php echo $d['dia_semana']; ?>', '<?php echo $d['hora_inicio']; ?>', '<?php echo $d['hora_fin']; ?>')" 
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger border-plano" 
                                                onclick="eliminarDisponibilidad(<?php echo $d['id_disponibilidad']; ?>)" 
                                                title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ÚNICO PARA CREAR / EDITAR HORARIO -->
<div class="modal fade" id="modalDisponibilidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-top border-4 border-institucional shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalTitulo">Registrar Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- CONTENEDOR DE ERROR INLINE (Letras rojas) -->
                <div id="mensajeErrorModal" class="alert alert-danger border-plano d-none small py-2"></div>

                <form id="formDisponibilidad">
                    <input type="hidden" name="accion" value="guardar_disponibilidad">
                    <input type="hidden" name="id_disponibilidad" id="id_disponibilidad" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Día de la Semana <span class="text-danger">*</span></label>
                        <select class="form-select border-plano" name="dia_semana" id="dia_semana" required>
                            <option value="" selected disabled>Selecciona el día...</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miercoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sabado">Sábado</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-secondary">Hora Inicio (Formato 24h) <span class="text-danger">*</span></label>
                            <input type="time" class="form-control border-plano" name="hora_inicio" id="hora_inicio" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold small text-secondary">Hora Fin (Formato 24h) <span class="text-danger">*</span></label>
                            <input type="time" class="form-control border-plano" name="hora_fin" id="hora_fin" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light border-plano" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-institucional border-plano" id="btnGuardarDisp">
                            <i class="fas fa-save me-2"></i> Guardar Horario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let modalDispInst;

document.addEventListener('DOMContentLoaded', function() {
    modalDispInst = new bootstrap.Modal(document.getElementById('modalDisponibilidad'));
    const formDisp = document.getElementById('formDisponibilidad');
    const btnGuardar = document.getElementById('btnGuardarDisp');
    const divError = document.getElementById('mensajeErrorModal');

    formDisp.addEventListener('submit', function(e) {
        e.preventDefault();
        
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
        btnGuardar.disabled = true;
        divError.classList.add('d-none'); // Ocultar error previo al reintentar

        let formData = new FormData(this);

        fetch('../../controllers/TutorController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.exito) {
                window.location.reload();
            } else {
                // Mostrar error inline en letras rojas
                divError.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + data.error;
                divError.classList.remove('d-none');
                btnGuardar.innerHTML = '<i class="fas fa-save me-2"></i> Guardar Horario';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            divError.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Error de conexión al servidor.';
            divError.classList.remove('d-none');
            btnGuardar.innerHTML = '<i class="fas fa-save me-2"></i> Guardar Horario';
            btnGuardar.disabled = false;
        });
    });
});

function limpiarModal() {
    document.getElementById('mensajeErrorModal').classList.add('d-none');
    document.getElementById('mensajeErrorModal').innerHTML = '';
}

function abrirModalCrear() {
    limpiarModal();
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-plus-circle text-institucional me-2"></i> Registrar Horario';
    document.getElementById('formDisponibilidad').reset();
    document.getElementById('id_disponibilidad').value = '';
    modalDispInst.show();
}

function abrirModalEditar(id, dia, inicio, fin) {
    limpiarModal();
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-edit text-institucional me-2"></i> Editar Horario';
    document.getElementById('id_disponibilidad').value = id;
    document.getElementById('dia_semana').value = dia;
    document.getElementById('hora_inicio').value = inicio.substring(0, 5);
    document.getElementById('hora_fin').value = fin.substring(0, 5);
    modalDispInst.show();
}

function eliminarDisponibilidad(idDisponibilidad) {
    if(!confirm("¿Estás seguro de eliminar este horario?")) return;

    let formData = new FormData();
    formData.append('accion', 'eliminar_disponibilidad');
    formData.append('id_disponibilidad', idDisponibilidad);

    fetch('../../controllers/TutorController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.exito) {
            window.location.reload();
        } else {
            alert("Error: " + (data.error || "No se pudo eliminar."));
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error de conexión al servidor.");
    });
}
</script>