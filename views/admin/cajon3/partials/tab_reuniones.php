<?php
/**
 * ARCHIVO: views/admin/cajon3/partials/tab_reuniones.php
 * Interfaz para Programación de Reuniones de Proyecto/Tesis (Tabla: reuniones_mg).
 */
$reuniones_mg = $reuniones_mg ?? [];
$lista_asignaciones = $lista_asignaciones ?? []; 
?>
<div id="seccion-reuniones" class="d-block">
    <div id="contenedor-tabla-reuniones">
        <div class="row mb-3 align-items-center">
            <div class="col-md-5">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-comments me-2"></i>Reuniones de Proyecto (MG)</h6>
            </div>
            <div class="col-md-7 d-flex justify-content-end align-items-center gap-2">
                <div class="input-group input-group-sm w-50 shadow-sm">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorReuniones" class="form-control border-plano" placeholder="Buscar tutor o estudiante...">
                </div>
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioReuniones('nuevo')">
                    <i class="fas fa-plus me-1"></i> Agendar Reunión
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaReuniones">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Guía / Tesista</th>
                            <th>Temas a Tratar</th>
                            <th>Lugar / Enlace</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reuniones_mg)): ?>
                            <?php foreach ($reuniones_mg as $reu): ?>
                                <tr class="fila-reunion">
                                    <td class="ps-4">
                                        <div class="fw-bold text-institucional"><i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($reu['fecha'])); ?></div>
                                        <div class="small text-muted"><i class="far fa-clock me-1"></i> <?php echo date('H:i', strtotime($reu['hora_inicio'])) . ' - ' . date('H:i', strtotime($reu['hora_fin'])); ?></div>
                                    </td>
                                    
                                    <td class="texto-busqueda-reu">
                                        <div class="fw-bold text-dark"><i class="fas fa-chalkboard-teacher text-muted me-1"></i> <?php echo htmlspecialchars($reu['nombre_tutor'] ?? 'N/A'); ?></div>
                                        <div class="small text-muted"><i class="fas fa-user-graduate text-muted me-1"></i> <?php echo htmlspecialchars($reu['nombre_estudiante'] ?? 'N/A'); ?></div>
                                    </td>
                                    
                                    <td class="texto-busqueda-reu text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($reu['temas']); ?>">
                                        <?php echo htmlspecialchars($reu['temas']); ?>
                                    </td>
                                    
                                    <td>
                                        <?php if(strtolower($reu['modalidad']) == 'virtual'): ?>
                                            <span class="badge bg-info text-dark border-plano mb-1"><i class="fas fa-video me-1"></i> Virtual</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary border-plano mb-1"><i class="fas fa-building me-1"></i> Presencial</span>
                                        <?php endif; ?>
                                        <div class="small text-muted text-truncate" style="max-width: 120px;" title="<?php echo htmlspecialchars($reu['lugar_o_enlace']); ?>">
                                            <?php echo htmlspecialchars($reu['lugar_o_enlace']); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php 
                                        $estado_color = 'text-secondary';
                                        if ($reu['estado_validacion'] == 'registrada') $estado_color = 'text-primary';
                                        if ($reu['estado_validacion'] == 'validada') $estado_color = 'text-success';
                                        if ($reu['estado_validacion'] == 'observada') $estado_color = 'text-warning text-dark';
                                        ?>
                                        <span class="small fw-bold <?php echo $estado_color; ?>"><i class="fas fa-circle ms-1" style="font-size: 8px;"></i> <?php echo ucfirst($reu['estado_validacion']); ?></span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioReuniones('editar', {
                                                    id_reunion: '<?php echo $reu['id_reunion']; ?>',
                                                    id_asignacion: '<?php echo $reu['id_asignacion']; ?>',
                                                    temas: '<?php echo addslashes($reu['temas']); ?>',
                                                    fecha: '<?php echo $reu['fecha']; ?>',
                                                    hora_inicio: '<?php echo $reu['hora_inicio']; ?>',
                                                    hora_fin: '<?php echo $reu['hora_fin']; ?>',
                                                    modalidad: '<?php echo $reu['modalidad']; ?>',
                                                    lugar_o_enlace: '<?php echo addslashes($reu['lugar_o_enlace']); ?>',
                                                    estado_validacion: '<?php echo $reu['estado_validacion']; ?>'
                                                })">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/ReunionController.php?accion=eliminar&id=<?php echo $reu['id_reunion']; ?>" class="btn btn-sm btn-light border border-plano" onclick="return confirm('¿Borrar esta reunión de proyecto?');"><i class="fas fa-trash-alt text-danger"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-reuniones"><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-comments mb-3 d-block fa-2x"></i> No hay reuniones de proyecto programadas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE -->
    <div id="formulario-reuniones" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-reuniones"><i class="fas fa-plus-circle me-2"></i>Agendar Nueva Reunión de Proyecto</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/ReunionController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-reuniones" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-reuniones" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Guía asignado - Tesista</label>
                            <select name="id_asignacion" id="reu-asignacion" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione el par asignado...</option>
                                <?php foreach($lista_asignaciones as $asig): ?>
                                    <option value="<?php echo $asig['id_asignacion']; ?>">
                                        <?php echo htmlspecialchars('Guía: ' . $asig['nombre_tutor'] . ' | Tesista: ' . $asig['nombre_estudiante']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Temas u Objetivos de la Reunión</label>
                            <input type="text" name="temas" id="reu-temas" class="form-control form-control-sm border-plano" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fecha</label>
                            <input type="date" name="fecha" id="reu-fecha" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="reu-inicio" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Hora Fin</label>
                            <input type="time" name="hora_fin" id="reu-fin" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Modalidad</label>
                            <select name="modalidad" id="reu-modalidad" class="form-select form-select-sm border-plano" required>
                                <option value="presencial">Presencial</option>
                                <option value="virtual">Virtual</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Lugar o Enlace</label>
                            <input type="text" name="lugar_o_enlace" id="reu-lugar" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Estado de Validación</label>
                            <select name="estado_validacion" id="reu-estado" class="form-select form-select-sm border-plano" required>
                                <option value="registrada">Registrada</option>
                                <option value="validada">Validada</option>
                                <option value="observada">Observada</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioReuniones()">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Reunión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirFormularioReuniones(accion, datos = null) {
        document.getElementById('contenedor-tabla-reuniones').classList.add('d-none');
        document.getElementById('formulario-reuniones').classList.replace('d-none', 'd-block');
        
        const form = document.querySelector('#formulario-reuniones form');
        const titulo = document.getElementById('titulo-form-reuniones');
        const inputAccion = document.getElementById('accion-form-reuniones');
        const inputId = document.getElementById('id-form-reuniones');

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Agendar Nueva Reunión de Proyecto';
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id_reunion;
            titulo.innerHTML = '<i class="fas fa-edit me-2"></i>Editar Reunión de Proyecto';
            
            document.getElementById('reu-asignacion').value = datos.id_asignacion;
            document.getElementById('reu-temas').value = datos.temas;
            document.getElementById('reu-fecha').value = datos.fecha;
            document.getElementById('reu-inicio').value = datos.hora_inicio;
            document.getElementById('reu-fin').value = datos.hora_fin;
            document.getElementById('reu-modalidad').value = datos.modalidad;
            document.getElementById('reu-lugar').value = datos.lugar_o_enlace;
            document.getElementById('reu-estado').value = datos.estado_validacion;
        }
    }

    function cerrarFormularioReuniones() {
        document.getElementById('formulario-reuniones').classList.replace('d-block', 'd-none');
        document.getElementById('contenedor-tabla-reuniones').classList.remove('d-none');
        document.querySelector('#formulario-reuniones form').reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const buscadorReu = document.getElementById('buscadorReuniones');
        if(buscadorReu) {
            buscadorReu.addEventListener('keyup', function() {
                const texto = this.value.toLowerCase();
                document.querySelectorAll('.fila-reunion').forEach(fila => {
                    let contenidoFila = '';
                    fila.querySelectorAll('.texto-busqueda-reu').forEach(celda => contenidoFila += celda.textContent.toLowerCase() + ' ');
                    fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
                });
            });
        }
    });
</script>