<?php
/**
 * ARCHIVO: views/admin/cajon3/partials/tab_tutorias.php
 * Interfaz para Programación de Tutorías de Materias (Gestión de Grupos).
 */

// Estas variables vendrán del index.php principal del Cajón 3
$tutorias_regulares = $tutorias_regulares ?? [];
$lista_estudiantes = $lista_estudiantes ?? [];
$lista_tutores = $lista_tutores ?? [];
$lista_materias = $lista_materias ?? [];
$lista_bloques = $lista_bloques ?? [];
?>

<div id="seccion-tutorias-regulares" class="d-block">
    
    <!-- CONTENEDOR DE LA TABLA -->
    <div id="contenedor-tabla-tutorias-regulares">
        
        <!-- TOOLBAR -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-4">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2"></i>Sesiones de Tutoría (Grupos)</h6>
            </div>
            <div class="col-md-8 d-flex justify-content-end align-items-center gap-2">
                <div class="input-group input-group-sm w-50 shadow-sm">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorTutoriasReg" class="form-control border-plano" placeholder="Buscar sesión, tutor o materia...">
                </div>
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioTutoriasRegulares('nuevo')">
                    <i class="fas fa-plus me-1"></i> Abrir Nueva Sesión
                </button>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaTutoriasRegulares">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Materia</th>
                            <th>Tutor Asignado</th>
                            <th>Asistencia (Grupo)</th>
                            <th>Lugar / Modalidad</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tutorias_regulares)): ?>
                            <?php foreach ($tutorias_regulares as $tut): ?>
                                <tr class="fila-tutoria-reg">
                                    <td class="ps-4">
                                        <div class="fw-bold text-institucional">
                                            <i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($tut['fecha'])); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i> <?php echo date('H:i', strtotime($tut['hora_inicio'])) . ' - ' . date('H:i', strtotime($tut['hora_fin'])); ?>
                                        </div>
                                    </td>
                                    
                                    <td class="texto-busqueda-tut-reg">
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($tut['nombre_materia'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($tut['nombre_materia'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="small text-muted">Periodo: <?php echo htmlspecialchars($tut['periodo']); ?></div>
                                    </td>
                                    
                                    <td class="texto-busqueda-tut-reg">
                                        <div class="fw-bold text-dark"><i class="fas fa-chalkboard-teacher text-muted me-1"></i> <?php echo htmlspecialchars($tut['nombre_tutor'] ?? 'N/A'); ?></div>
                                    </td>

                                    <td>
                                        <span class="badge bg-light border border-secondary text-secondary" title="<?php echo htmlspecialchars($tut['nombre_estudiante'] ?? 'Sin inscritos'); ?>">
                                            <i class="fas fa-users me-1"></i> <?php echo $tut['total_alumnos'] ?? 0; ?> Inscrito(s)
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if(strtolower($tut['modalidad']) == 'virtual'): ?>
                                            <span class="badge bg-info text-dark border-plano mb-1"><i class="fas fa-video me-1"></i> Virtual</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary border-plano mb-1"><i class="fas fa-building me-1"></i> Presencial</span>
                                        <?php endif; ?>
                                        <div class="small text-muted text-truncate" style="max-width: 120px;" title="<?php echo htmlspecialchars($tut['lugar_o_enlace']); ?>">
                                            <?php echo htmlspecialchars($tut['lugar_o_enlace']); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php 
                                        $estado_color = 'text-secondary';
                                        switch($tut['estado']) {
                                            case 'pendiente': $estado_color = 'text-warning text-dark'; break;
                                            case 'confirmada': $estado_color = 'text-primary'; break;
                                            case 'en_proceso': $estado_color = 'text-info text-dark'; break;
                                            case 'realizada': $estado_color = 'text-success'; break;
                                            case 'cancelada': $estado_color = 'text-danger'; break;
                                            case 'detenido': $estado_color = 'text-secondary'; break;
                                        }
                                        ?>
                                        <span class="small fw-bold <?php echo $estado_color; ?>"><i class="fas fa-circle ms-1" style="font-size: 8px;"></i> <?php echo str_replace('_', ' ', ucfirst($tut['estado'])); ?></span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioTutoriasRegulares('editar', {
                                                    id_tutoria: '<?php echo $tut['id_tutoria']; ?>',
                                                    id_estudiante: '<?php echo $tut['id_estudiante']; ?>',
                                                    id_tutor: '<?php echo $tut['id_tutor']; ?>',
                                                    id_materia: '<?php echo $tut['id_materia']; ?>',
                                                    id_bloque: '<?php echo $tut['id_bloque']; ?>',
                                                    fecha: '<?php echo $tut['fecha']; ?>',
                                                    periodo: '<?php echo $tut['periodo']; ?>',
                                                    hora_inicio: '<?php echo $tut['hora_inicio']; ?>',
                                                    hora_fin: '<?php echo $tut['hora_fin']; ?>',
                                                    modalidad: '<?php echo $tut['modalidad']; ?>',
                                                    lugar_o_enlace: '<?php echo addslashes($tut['lugar_o_enlace']); ?>',
                                                    estado: '<?php echo $tut['estado']; ?>',
                                                    observaciones: '<?php echo addslashes($tut['observaciones']); ?>'
                                                })" title="Editar Configuración de la Sesión">
                                            <i class="fas fa-cog text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/TutoriaController.php?accion=eliminar&id=<?php echo $tut['id_tutoria']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar toda la sesión? Esto eliminará también a los estudiantes inscritos en este grupo.');" title="Borrar Sesión">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-tutorias-reg"><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-book-reader mb-3 d-block fa-2x"></i> No hay sesiones de tutoría programadas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE -->
    <div id="formulario-tutorias-regulares" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-tutorias-reg"><i class="fas fa-plus-circle me-2"></i>Abrir Nueva Sesión de Tutoría</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/TutoriaController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-tutorias-reg" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-tutorias-reg" value="">
                    
                    <div class="row">
                        <!-- Bloque 1: Involucrados -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Estudiante Inicial (Opcional)</label>
                            <!-- Se quitó el atributo required para permitir crear sesiones sin estudiantes iniciales -->
                            <select name="id_estudiante" id="treg-estudiante" class="form-select form-select-sm border-plano">
                                <option value="" selected>-- Solo abrir la sesión --</option>
                                <?php foreach($lista_estudiantes as $est): ?>
                                    <option value="<?php echo $est['id_estudiante']; ?>"><?php echo htmlspecialchars($est['nombre_completo'] . ' (' . $est['registro_universitario'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Tutor</label>
                            <select name="id_tutor" id="treg-tutor" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione tutor...</option>
                                <?php foreach($lista_tutores as $tut): ?>
                                    <option value="<?php echo $tut['id_tutor']; ?>"><?php echo htmlspecialchars($tut['nombre_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Materia</label>
                            <select name="id_materia" id="treg-materia" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione materia...</option>
                                <?php foreach($lista_materias as $mat): ?>
                                    <option value="<?php echo $mat['id_materia']; ?>"><?php echo htmlspecialchars($mat['nombre_materia']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Bloque 2: Logística -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" name="fecha" id="treg-fecha" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Bloque Horario</label>
                            <select name="id_bloque" id="treg-bloque" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione bloque...</option>
                                <?php foreach($lista_bloques as $bloq): ?>
                                    <option value="<?php echo $bloq['id_bloque']; ?>"><?php echo htmlspecialchars($bloq['nombre_bloque'] . ' (' . date('H:i', strtotime($bloq['hora_inicio'])) . ' - ' . date('H:i', strtotime($bloq['hora_fin'])) . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="treg-inicio" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Hora Fin</label>
                            <input type="time" name="hora_fin" id="treg-fin" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Periodo</label>
                            <input type="text" name="periodo" id="treg-periodo" class="form-control form-control-sm border-plano" value="II-2026" required>
                        </div>

                        <!-- Bloque 3: Estado y Modalidad -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Modalidad</label>
                            <select name="modalidad" id="treg-modalidad" class="form-select form-select-sm border-plano" required>
                                <option value="presencial">Presencial</option>
                                <option value="virtual">Virtual</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Lugar o Enlace</label>
                            <input type="text" name="lugar_o_enlace" id="treg-lugar" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado" id="treg-estado" class="form-select form-select-sm border-plano" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="realizada">Realizada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="detenido">Detenido</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-2">
                            <label class="form-label small fw-bold">Observaciones del Estudiante (Opcional)</label>
                            <textarea name="observaciones" id="treg-observaciones" class="form-control form-control-sm border-plano" rows="2"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioTutoriasRegulares()">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Configuración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirFormularioTutoriasRegulares(accion, datos = null) {
        document.getElementById('contenedor-tabla-tutorias-regulares').classList.add('d-none');
        document.getElementById('formulario-tutorias-regulares').classList.replace('d-none', 'd-block');
        
        const form = document.querySelector('#formulario-tutorias-regulares form');
        const titulo = document.getElementById('titulo-form-tutorias-reg');
        const inputAccion = document.getElementById('accion-form-tutorias-reg');
        const inputId = document.getElementById('id-form-tutorias-reg');

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Abrir Nueva Sesión de Tutoría';
            document.getElementById('treg-periodo').value = 'II-2026'; // Valor por defecto
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id_tutoria;
            titulo.innerHTML = '<i class="fas fa-cog me-2"></i>Editar Configuración de Sesión';
            
            document.getElementById('treg-estudiante').value = datos.id_estudiante || '';
            document.getElementById('treg-tutor').value = datos.id_tutor;
            document.getElementById('treg-materia').value = datos.id_materia;
            document.getElementById('treg-bloque').value = datos.id_bloque;
            document.getElementById('treg-fecha').value = datos.fecha;
            document.getElementById('treg-periodo').value = datos.periodo;
            document.getElementById('treg-inicio').value = datos.hora_inicio;
            document.getElementById('treg-fin').value = datos.hora_fin;
            document.getElementById('treg-modalidad').value = datos.modalidad;
            document.getElementById('treg-lugar').value = datos.lugar_o_enlace;
            document.getElementById('treg-estado').value = datos.estado;
            document.getElementById('treg-observaciones').value = datos.observaciones;
        }
    }

    function cerrarFormularioTutoriasRegulares() {
        document.getElementById('formulario-tutorias-regulares').classList.replace('d-block', 'd-none');
        document.getElementById('contenedor-tabla-tutorias-regulares').classList.remove('d-none');
        document.querySelector('#formulario-tutorias-regulares form').reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const buscadorReg = document.getElementById('buscadorTutoriasReg');
        if(buscadorReg) {
            buscadorReg.addEventListener('keyup', function() {
                const texto = this.value.toLowerCase();
                document.querySelectorAll('.fila-tutoria-reg').forEach(fila => {
                    let contenidoFila = '';
                    fila.querySelectorAll('.texto-busqueda-tut-reg').forEach(celda => contenidoFila += celda.textContent.toLowerCase() + ' ');
                    fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
                });
            });
        }
    });
</script>