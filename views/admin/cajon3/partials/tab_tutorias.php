<?php
/**
 * ARCHIVO: views/admin/cajon3/partials/tab_tutorias.php
 * Interfaz para Programación de Tutorías de Materias (Gestión de Grupos).
 * Adaptado con botón interactivo de lista de estudiantes y eliminación modal.
 */

$tutorias_regulares = $tutorias_regulares ?? [];
$lista_estudiantes = $lista_estudiantes ?? [];
$lista_tutores = $lista_tutores ?? [];
$lista_materias = $lista_materias ?? [];
$lista_bloques = $lista_bloques ?? [];
?>

<div id="seccion-tutorias-regulares" class="d-block">
    
    <div id="contenedor-tabla-tutorias-regulares">
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
                            <th>Estado y Avance</th>
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
                                        <div class="small text-muted">Periodo: <?php echo htmlspecialchars($tut['periodo'] ?? 'No Asignado'); ?></div>
                                    </td>
                                    
                                    <td class="texto-busqueda-tut-reg">
                                        <div class="fw-bold text-dark"><i class="fas fa-chalkboard-teacher text-muted me-1"></i> <?php echo htmlspecialchars($tut['nombre_tutor'] ?? 'N/A'); ?></div>
                                    </td>

                                    <!-- Botón Interactivo para ver Inscritos -->
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary border-plano shadow-sm text-dark bg-light" 
                                                onclick="verInscritos(<?php echo $tut['id_tutoria']; ?>)" title="Ver lista de inscritos">
                                            <i class="fas fa-users me-1 text-institucional"></i> <span id="contador-inscritos-<?php echo $tut['id_tutoria']; ?>"><?php echo $tut['total_alumnos'] ?? 0; ?></span> Inscrito(s)
                                        </button>
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
                                        <div class="small fw-bold <?php echo $estado_color; ?> mb-1">
                                            <i class="fas fa-circle ms-1" style="font-size: 8px;"></i> <?php echo str_replace('_', ' ', ucfirst($tut['estado'])); ?>
                                        </div>
                                        <span class="badge bg-light text-dark border border-plano shadow-sm" title="Progreso del Módulo">
                                            <i class="fas fa-layer-group text-primary me-1"></i> <?php echo ($tut['clases_impartidas'] ?? 0) . ' / ' . ($tut['tope_clases'] ?? 1); ?> Clases
                                        </span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioTutoriasRegulares('editar', {
                                                    id_tutoria: '<?php echo $tut['id_tutoria']; ?>',
                                                    id_estudiante: '<?php echo $tut['id_estudiante'] ?? ''; ?>',
                                                    nombre_estudiante: '<?php echo addslashes($tut['nombre_estudiante'] ?? 'Sin estudiante inicial'); ?>',
                                                    id_tutor: '<?php echo $tut['id_tutor']; ?>',
                                                    id_materia: '<?php echo $tut['id_materia']; ?>',
                                                    id_bloque: '<?php echo $tut['id_bloque']; ?>',
                                                    fecha: '<?php echo $tut['fecha']; ?>',
                                                    hora_inicio: '<?php echo $tut['hora_inicio']; ?>',
                                                    hora_fin: '<?php echo $tut['hora_fin']; ?>',
                                                    modalidad: '<?php echo $tut['modalidad']; ?>',
                                                    lugar_o_enlace: '<?php echo addslashes($tut['lugar_o_enlace']); ?>',
                                                    estado: '<?php echo $tut['estado']; ?>',
                                                    tope_clases: '<?php echo $tut['tope_clases'] ?? ''; ?>',
                                                    clases_impartidas: '<?php echo $tut['clases_impartidas'] ?? 0; ?>',
                                                    limite_estudiantes: '<?php echo $tut['limite_estudiantes'] ?? ''; ?>',
                                                    observaciones: '<?php echo addslashes($tut['observaciones']); ?>'
                                                })" title="Editar Configuración de la Sesión">
                                            <i class="fas fa-cog text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/TutoriaController.php?accion=eliminar&id=<?php echo $tut['id_tutoria']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar toda la sesión? Esto eliminará también a los estudiantes inscritos.');" title="Borrar Sesión">
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
                    
                    <input type="hidden" name="id_materia" id="treg-materia" value="">
                    <input type="hidden" name="hora_inicio" id="treg-inicio" value="">
                    <input type="hidden" name="hora_fin" id="treg-fin" value="">
                    <input type="hidden" name="id_estudiante" id="treg-estudiante" value="">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Estudiante Inicial</label>
                            <input type="text" id="treg-estudiante-texto" class="form-control form-control-sm border-plano bg-light text-secondary" readonly placeholder="Sin estudiante asignado">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Tutor Asignado</label>
                            <select name="id_tutor" id="treg-tutor" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione tutor...</option>
                                <?php foreach($lista_tutores as$tut): ?>
                                    <option value="<?php echo $tut['id_tutor']; ?>"><?php echo htmlspecialchars($tut['nombre_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-success"><i class="fas fa-users-cog me-1"></i> Límite de Estudiantes</label>
                            <input type="number" name="limite_estudiantes" id="treg-limite" class="form-control form-control-sm border-success text-center fw-bold" min="1" placeholder="Ej: 10">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" name="fecha" id="treg-fecha" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Bloque Horario</label>
                            <select name="id_bloque" id="treg-bloque" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione bloque...</option>
                                <?php foreach($lista_bloques as$bloq): ?>
                                    <option value="<?php echo $bloq['id_bloque']; ?>"><?php echo htmlspecialchars($bloq['nombre_bloque'] . ' (' . date('H:i', strtotime($bloq['hora_inicio'])) . ' - ' . date('H:i', strtotime($bloq['hora_fin'])) . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado" id="treg-estado" class="form-select form-select-sm border-plano" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="detenido">Detenido</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Modalidad</label>
                            <select name="modalidad" id="treg-modalidad" class="form-select form-select-sm border-plano" required>
                                <option value="presencial">Presencial</option>
                                <option value="virtual">Virtual</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label small fw-bold">Lugar o Enlace</label>
                            <input type="text" name="lugar_o_enlace" id="treg-lugar" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold text-primary"><i class="fas fa-layer-group me-1"></i> Tope Clases</label>
                            <input type="number" name="tope_clases" id="treg-tope" class="form-control form-control-sm border-primary text-center fw-bold" min="1" placeholder="Ej: 7">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold text-primary"><i class="fas fa-check-double me-1"></i> Impartidas</label>
                            <input type="number" name="clases_impartidas" id="treg-impartidas" class="form-control form-control-sm border-primary text-center fw-bold" min="0" placeholder="0">
                        </div>
                        
                        <div class="col-12 mb-2">
                            <label class="form-label small fw-bold">Observaciones del Estudiante (Opcional)</label>
                            <textarea name="observaciones" id="treg-observaciones" class="form-control form-control-sm border-plano" rows="1"></textarea>
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

<!-- ======================================================================= -->
<!-- NUEVOS MODALES: GESTIÓN DE INSCRITOS Y ELIMINACIÓN SEGURA -->
<!-- ======================================================================= -->

<!-- 1. Modal Lista de Inscritos -->
<div class="modal fade" id="modalListaInscritos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-plano shadow">
            <div class="modal-header bg-white border-bottom border-plano py-3">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-users text-institucional me-2"></i> Estudiantes Inscritos</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="list-group list-group-flush" id="lista-inscritos-body">
                    <!-- Inyectado dinámicamente por JS -->
                </ul>
            </div>
            <div class="modal-footer bg-light border-plano">
                <button type="button" class="btn btn-sm btn-secondary border-plano" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal Advertencia (Sin Alert) para Remover Estudiante -->
<div class="modal fade" id="modalConfirmarRemover" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-plano border-top border-4 border-danger shadow">
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <p class="mb-0 fs-6 text-dark">¿Seguro que deseas remover a este estudiante del grupo?</p>
                <div id="error-remover" class="alert alert-danger d-none small py-2 mb-0 mt-3 text-start border-plano border-0 border-start border-3 border-danger"></div>
            </div>
            <div class="modal-footer bg-light border-plano justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary border-plano" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-danger border-plano fw-bold" id="btn-confirmar-remover">Sí, Remover</button>
            </div>
        </div>
    </div>
</div>

<script>
    // LÓGICA DE FORMULARIO DE TUTORÍAS
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
            
            document.getElementById('treg-estudiante-texto').value = 'Asignación automática / No especificado';
            document.getElementById('treg-estudiante').value = '';
            document.getElementById('treg-limite').value = '';
            document.getElementById('treg-tope').value = '';
            document.getElementById('treg-impartidas').value = '0';

        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id_tutoria;
            titulo.innerHTML = '<i class="fas fa-cog me-2"></i>Editar Configuración de Sesión';
            
            document.getElementById('treg-estudiante-texto').value = datos.nombre_estudiante || 'Sin estudiante asignado';
            document.getElementById('treg-estudiante').value = datos.id_estudiante || '';
            document.getElementById('treg-materia').value = datos.id_materia || '';
            document.getElementById('treg-inicio').value = datos.hora_inicio || '';
            document.getElementById('treg-fin').value = datos.hora_fin || '';
            
            document.getElementById('treg-tutor').value = datos.id_tutor;
            document.getElementById('treg-limite').value = datos.limite_estudiantes || '';
            document.getElementById('treg-bloque').value = datos.id_bloque;
            document.getElementById('treg-fecha').value = datos.fecha;
            document.getElementById('treg-modalidad').value = datos.modalidad;
            document.getElementById('treg-lugar').value = datos.lugar_o_enlace;
            document.getElementById('treg-observaciones').value = datos.observaciones;
            document.getElementById('treg-tope').value = datos.tope_clases || '';
            document.getElementById('treg-impartidas').value = datos.clases_impartidas || '0';

            const estadoSelect = document.getElementById('treg-estado');
            if(datos.estado === 'en_proceso' || datos.estado === 'realizada') {
                let existe = Array.from(estadoSelect.options).some(opt => opt.value === datos.estado);
                if(!existe) {
                    let nuevaOpcion = document.createElement('option');
                    nuevaOpcion.value = datos.estado;
                    nuevaOpcion.text = datos.estado === 'en_proceso' ? 'En Proceso (Automático)' : 'Realizada (Automático)';
                    estadoSelect.add(nuevaOpcion);
                }
            }
            estadoSelect.value = datos.estado;
        }
    }

    function cerrarFormularioTutoriasRegulares() {
        document.getElementById('formulario-tutorias-regulares').classList.replace('d-block', 'd-none');
        document.getElementById('contenedor-tabla-tutorias-regulares').classList.remove('d-none');
        
        const estadoSelect = document.getElementById('treg-estado');
        Array.from(estadoSelect.options).forEach(opt => {
            if(opt.value === 'en_proceso' || opt.value === 'realizada') {
                estadoSelect.remove(opt.index);
            }
        });

        document.querySelector('#formulario-tutorias-regulares form').reset();
    }

    // LÓGICA DE INSCRITOS (AJAX)
    let tutoriaActivaInscritos = 0;
    let estudianteAEliminar = 0;

    function verInscritos(id_tutoria) {
        tutoriaActivaInscritos = id_tutoria;
        const lista = document.getElementById('lista-inscritos-body');
        lista.innerHTML = '<li class="list-group-item text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i> Cargando estudiantes...</li>';
        
        const modal = new bootstrap.Modal(document.getElementById('modalListaInscritos'));
        modal.show();

        fetch(`../../controllers/TutoriaController.php?accion=obtener_inscritos&id_tutoria=${id_tutoria}`)
            .then(res => res.json())
            .then(data => {
                lista.innerHTML = '';
                if(data.length === 0) {
                    lista.innerHTML = '<li class="list-group-item text-center text-muted small py-4">No hay estudiantes inscritos en este grupo.</li>';
                    return;
                }
                data.forEach(est => {
                    lista.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3" id="item-inscrito-${est.id_estudiante}">
                            <div>
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">${est.nombre_completo}</div>
                                <div class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-id-card me-1"></i> ${est.registro_universitario}</div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger border-plano px-3 py-1" onclick="confirmarRemoverInscrito(${id_tutoria}, ${est.id_estudiante})" title="Remover Estudiante">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        </li>
                    `;
                });
            })
            .catch(err => {
                lista.innerHTML = '<li class="list-group-item text-center text-danger small py-4"><i class="fas fa-exclamation-triangle me-2"></i> Error al conectar con el servidor.</li>';
            });
    }

    function confirmarRemoverInscrito(id_tutoria, id_estudiante) {
        estudianteAEliminar = id_estudiante;
        document.getElementById('error-remover').classList.add('d-none');
        const modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirmarRemover'));
        modalConfirm.show();
    }

    document.getElementById('btn-confirmar-remover').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Removiendo...';

        let formData = new FormData();
        formData.append('accion', 'eliminar_inscrito');
        formData.append('id_tutoria', tutoriaActivaInscritos);
        formData.append('id_estudiante', estudianteAEliminar);

        fetch('../../controllers/TutoriaController.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.exito) {
                // Remover visualmente la fila del estudiante
                const item = document.getElementById(`item-inscrito-${estudianteAEliminar}`);
                if(item) item.remove();
                
                // Actualizar el botón contador de la tabla general
                const btnContador = document.getElementById(`contador-inscritos-${tutoriaActivaInscritos}`);
                if(btnContador) {
                    let actual = parseInt(btnContador.innerText) || 0;
                    if(actual > 0) btnContador.innerText = actual - 1;
                }
                
                bootstrap.Modal.getInstance(document.getElementById('modalConfirmarRemover')).hide();
                
                // Si la lista quedó vacía, mostrar mensaje
                const listaContainer = document.getElementById('lista-inscritos-body');
                if (listaContainer.children.length === 0) {
                    listaContainer.innerHTML = '<li class="list-group-item text-center text-muted small py-4">No hay estudiantes inscritos en este grupo.</li>';
                }
            } else {
                const errBox = document.getElementById('error-remover');
                errBox.innerText = data.error || 'No se pudo remover al estudiante.';
                errBox.classList.remove('d-none');
            }
        })
        .catch(err => {
            const errBox = document.getElementById('error-remover');
            errBox.innerText = 'Error de conexión con el servidor.';
            errBox.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Sí, Remover';
        });
    });

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