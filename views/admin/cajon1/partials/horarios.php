<?php
/**
 * ARCHIVO: views/admin/cajon1/partials/horarios.php
 * Interfaz para la gestión de disponibilidad horaria de un tutor específico.
 */

// ¡CORREGIDO!: Se agregó un '../' extra porque este archivo está dentro de 'partials/'
require_once __DIR__ . '/../../../../models/usuarios/TutorModel/index.php';

$id_usuario_tutor = $_GET['id_tutor'] ?? null;
$tutorModel = new TutorModel($pdo);

// Variables de memoria de formulario y mensajes
$error_horario = $_GET['error'] ?? '';
$exito_horario = $_GET['exito'] ?? '';
$old_dia = $_GET['old_dia'] ?? '';
$old_inicio = $_GET['old_inicio'] ?? '';
$old_fin = $_GET['old_fin'] ?? '';

// Obtener datos del tutor y sus horarios
$id_tutor_real = $tutorModel->obtenerIdTutorPorUsuario($id_usuario_tutor);
$tutorInfo = null;
$horarios = [];

if ($id_tutor_real) {
    $tutorInfo = $tutorModel->obtenerPorId($id_tutor_real);
    $horarios = $tutorModel->obtenerHorarios($id_tutor_real);
}

if (!$tutorInfo): ?>
    <div class="alert alert-danger border-plano shadow-sm">
        <i class="fas fa-exclamation-triangle me-2"></i> Error: No se pudo cargar el perfil del tutor seleccionado.
        <br><br>
        <a href="index.php?seccion=cajon1&sub=tutores" class="btn btn-sm btn-outline-danger border-plano">Volver a Tutores</a>
    </div>
<?php else: ?>

    <!-- ALERTAS ESPECÍFICAS DE HORARIOS -->
    <?php if (!empty($exito_horario)): ?>
        <div class="alert alert-success border-0 border-start border-4 border-success border-plano shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($exito_horario); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_horario)): ?>
        <div class="alert alert-danger border-0 border-start border-4 border-danger border-plano shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Aviso:</strong> <?php echo htmlspecialchars($error_horario); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- FORMULARIO PARA AGREGAR HORARIO -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-institucional text-white border-0 border-plano py-3">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Nuevo Horario</h6>
                </div>
                <div class="card-body bg-light">
                    <form action="../../controllers/HorarioController.php?accion=agregar" method="POST">
                        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario_tutor); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Día de la Semana</label>
                            <select name="dia_semana" class="form-select border-plano" required>
                                <option value="" disabled <?php echo empty($old_dia) ? 'selected' : ''; ?>>Seleccione un día...</option>
                                <?php
                                $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
                                foreach ($dias as $dia) {
                                    $selected = ($old_dia === $dia) ? 'selected' : '';
                                    echo "<option value=\"$dia\" $selected>$dia</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Hora de Inicio</label>
                            <input type="time" name="hora_inicio" class="form-control border-plano" value="<?php echo htmlspecialchars($old_inicio); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Hora de Finalización</label>
                            <input type="time" name="hora_fin" class="form-control border-plano" value="<?php echo htmlspecialchars($old_fin); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-institucional border-plano fw-bold">
                                <i class="fas fa-plus-circle me-1"></i> Agregar Disponibilidad
                            </button>
                            <a href="index.php?seccion=cajon1&sub=tutores" class="btn btn-outline-secondary border-plano text-uppercase small" style="font-size: 0.8rem;">
                                <i class="fas fa-arrow-left me-1"></i> Volver a Lista
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABLA DE HORARIOS EXISTENTES -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-white border-bottom border-plano py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-dark">
                        <i class="fas fa-calendar-alt text-institucional me-2"></i>
                        Disponibilidad de: <span class="fw-bold text-institucional"><?php echo htmlspecialchars($tutorInfo['nombre'] . ' ' . $tutorInfo['apellido']); ?></span>
                    </h6>
                    <span class="badge bg-light text-dark border"><i class="fas fa-chalkboard-teacher me-1"></i> <?php echo htmlspecialchars($tutorInfo['especialidad']); ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Día</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Duración</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($horarios)): ?>
                                    <?php foreach ($horarios as $h): 
                                        // Cálculo simple de duración para visualización
                                        $inicio = strtotime($h['hora_inicio']);
                                        $fin = strtotime($h['hora_fin']);
                                        $duracion_horas = round(($fin - $inicio) / 3600, 1);
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">
                                                <i class="fas fa-calendar-day text-secondary me-2 small"></i> <?php echo htmlspecialchars($h['dia_semana']); ?>
                                            </td>
                                            <td><span class="badge bg-light text-dark border-plano border"><i class="far fa-clock text-success me-1"></i> <?php echo date('H:i', $inicio); ?></span></td>
                                            <td><span class="badge bg-light text-dark border-plano border"><i class="far fa-clock text-danger me-1"></i> <?php echo date('H:i', $fin); ?></span></td>
                                            <td class="small text-muted"><?php echo $duracion_horas; ?> hrs</td>
                                            <td class="text-end pe-4">
                                                <a href="../../controllers/HorarioController.php?accion=eliminar&id_disponibilidad=<?php echo $h['id_disponibilidad']; ?>&id_usuario=<?php echo htmlspecialchars($id_usuario_tutor); ?>" 
                                                   class="btn btn-sm btn-outline-danger border-plano" 
                                                   onclick="return confirm('¿Confirma que desea eliminar este bloque de horario para <?php echo $h['dia_semana']; ?>?');"
                                                   title="Eliminar Bloque">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-25"></i>
                                            <h6 class="text-muted fw-light">Este tutor aún no tiene horarios registrados.</h6>
                                            <p class="small text-muted">Utilice el formulario de la izquierda para agregar bloques de disponibilidad.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>