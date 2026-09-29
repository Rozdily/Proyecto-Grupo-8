<?php
/**
 * ARCHIVO: views/admin/cajon3/partials/tab_defensas.php
 * Interfaz SPA para Programación de Defensas de Tesis (MG1 y MG2).
 */

// Estas variables vendrán del index.php principal del Cajón 3
$defensas = $defensas ?? [];
$lista_expedientes = $lista_expedientes ?? [];
?>

<div id="seccion-defensas" class="d-block">
    
    <!-- CONTENEDOR DE LA TABLA -->
    <div id="contenedor-tabla-defensas">
        
        <!-- TOOLBAR: Título, Buscador y Botón Nuevo -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-5">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-user-graduate me-2"></i>Programación de Defensas</h6>
            </div>
            <div class="col-md-7 d-flex justify-content-end align-items-center gap-2">
                <div class="input-group input-group-sm w-50 shadow-sm">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorDefensas" class="form-control border-plano" placeholder="Buscar estudiante o ambiente...">
                </div>
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioDefensas('nuevo')">
                    <i class="fas fa-plus me-1"></i> Agendar Defensa
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaDefensas">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Estudiante / Proyecto</th>
                            <th>Etapa</th>
                            <th>Ambiente</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($defensas)): ?>
                            <?php foreach ($defensas as $def): ?>
                                <tr class="fila-defensa">
                                    <td class="ps-4">
                                        <div class="fw-bold text-institucional">
                                            <i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y', strtotime($def['fecha'])); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i> <?php echo date('H:i', strtotime($def['hora_inicio'])) . ' - ' . date('H:i', strtotime($def['hora_fin'])); ?>
                                        </div>
                                    </td>
                                    
                                    <td class="texto-busqueda-def">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($def['nombre_estudiante'] ?? 'Estudiante Desconocido'); ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($def['titulo_trabajo'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($def['titulo_trabajo'] ?? ''); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        $badge_etapa = $def['etapa'] == 'mg1' ? 'bg-primary' : 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $badge_etapa; ?> border-plano"><?php echo strtoupper($def['etapa']); ?></span>
                                    </td>
                                    
                                    <td class="texto-busqueda-def small fw-semibold">
                                        <?php echo htmlspecialchars($def['ambiente']); ?>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        $estado_color = 'text-secondary';
                                        if ($def['estado'] == 'programada') $estado_color = 'text-primary';
                                        if ($def['estado'] == 'realizada') $estado_color = 'text-success';
                                        if ($def['estado'] == 'reprogramada') $estado_color = 'text-warning';
                                        if ($def['estado'] == 'cancelada') $estado_color = 'text-danger';
                                        ?>
                                        <span class="small fw-bold <?php echo $estado_color; ?>"><i class="fas fa-circle ms-1" style="font-size: 8px;"></i> <?php echo ucfirst($def['estado']); ?></span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioDefensas('editar', {
                                                    id_defensa: '<?php echo $def['id_defensa']; ?>',
                                                    id_expediente: '<?php echo $def['id_expediente']; ?>',
                                                    etapa: '<?php echo $def['etapa']; ?>',
                                                    fecha: '<?php echo $def['fecha']; ?>',
                                                    hora_inicio: '<?php echo $def['hora_inicio']; ?>',
                                                    hora_fin: '<?php echo $def['hora_fin']; ?>',
                                                    ambiente: '<?php echo addslashes($def['ambiente']); ?>',
                                                    estado: '<?php echo $def['estado']; ?>',
                                                    obs_fondo: '<?php echo addslashes(str_replace(["\r", "\n"], ' ', $def['obs_fondo'])); ?>',
                                                    obs_forma: '<?php echo addslashes(str_replace(["\r", "\n"], ' ', $def['obs_forma'])); ?>'
                                                })" title="Editar Defensa">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/DefensaController.php?accion=eliminar&id=<?php echo $def['id_defensa']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar esta defensa programada?');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-defensas"><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-user-tie mb-3 d-block fa-2x"></i> No hay defensas programadas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-end">
            <div class="btn-group shadow-sm">
                <button class="btn btn-sm btn-light border-plano text-muted" disabled>Anterior</button>
                <button class="btn btn-sm btn-institucional border-plano">1</button>
                <button class="btn btn-sm btn-light border-plano text-muted" disabled>Siguiente</button>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE: DEFENSAS -->
    <div id="formulario-defensas" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-defensas"><i class="fas fa-plus-circle me-2"></i>Agendar Nueva Defensa</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/DefensaController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-defensas" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-defensas" value="">
                    
                    <div class="row">
                        <!-- Bloque 1: Asignación -->
                        <div class="col-12 mb-2"><h6 class="text-muted small fw-bold border-bottom pb-1">Datos de Asignación</h6></div>
                        <div class="col-md-9 mb-3">
                            <label class="form-label small fw-bold">Expediente (Estudiante y Proyecto)</label>
                            <select name="id_expediente" id="def-expediente" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione expediente...</option>
                                <?php foreach($lista_expedientes as $exp): ?>
                                    <option value="<?php echo $exp['id_expediente']; ?>">
                                        <?php echo htmlspecialchars($exp['nombre_estudiante'] . ' - ' . $exp['titulo_trabajo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Etapa</label>
                            <select name="etapa" id="def-etapa" class="form-select form-select-sm border-plano" required>
                                <option value="mg1">Perfil (MG1)</option>
                                <option value="mg2">Borrador (MG2)</option>
                            </select>
                        </div>

                        <!-- Bloque 2: Logística -->
                        <div class="col-12 mb-2 mt-2"><h6 class="text-muted small fw-bold border-bottom pb-1">Logística de la Defensa</h6></div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fecha Programada</label>
                            <input type="date" name="fecha" id="def-fecha" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="def-inicio" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Hora Fin</label>
                            <input type="time" name="hora_fin" id="def-fin" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Ambiente / Enlace</label>
                            <input type="text" name="ambiente" id="def-ambiente" class="form-control form-control-sm border-plano" placeholder="Ej. Auditorio Central" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado" id="def-estado" class="form-select form-select-sm border-plano" required>
                                <option value="programada">Programada</option>
                                <option value="realizada">Realizada</option>
                                <option value="reprogramada">Reprogramada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>

                        <!-- Bloque 3: Observaciones -->
                        <div class="col-12 mb-2 mt-2"><h6 class="text-muted small fw-bold border-bottom pb-1">Observaciones del Tribunal</h6></div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Observaciones de Fondo (Metodología, aportes)</label>
                            <textarea name="obs_fondo" id="def-obs-fondo" class="form-control form-control-sm border-plano" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Observaciones de Forma (Redacción, formato)</label>
                            <textarea name="obs_forma" id="def-obs-forma" class="form-control form-control-sm border-plano" rows="3"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioDefensas()">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Defensa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- LÓGICA JAVASCRIPT: CRUD IN-LINE Y FILTRO DE BÚSQUEDA -->
<!-- ======================================================================= -->
<script>
    function abrirFormularioDefensas(accion, datos = null) {
        document.getElementById('contenedor-tabla-defensas').classList.add('d-none');
        document.getElementById('formulario-defensas').classList.replace('d-none', 'd-block');
        
        const form = document.querySelector('#formulario-defensas form');
        const titulo = document.getElementById('titulo-form-defensas');
        const inputAccion = document.getElementById('accion-form-defensas');
        const inputId = document.getElementById('id-form-defensas');

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Agendar Nueva Defensa';
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id_defensa;
            titulo.innerHTML = '<i class="fas fa-edit me-2"></i>Editar Defensa Programada';
            
            document.getElementById('def-expediente').value = datos.id_expediente;
            document.getElementById('def-etapa').value = datos.etapa;
            document.getElementById('def-fecha').value = datos.fecha;
            document.getElementById('def-inicio').value = datos.hora_inicio;
            document.getElementById('def-fin').value = datos.hora_fin;
            document.getElementById('def-ambiente').value = datos.ambiente;
            document.getElementById('def-estado').value = datos.estado;
            document.getElementById('def-obs-fondo').value = datos.obs_fondo;
            document.getElementById('def-obs-forma').value = datos.obs_forma;
        }
    }

    function cerrarFormularioDefensas() {
        document.getElementById('formulario-defensas').classList.replace('d-block', 'd-none');
        document.getElementById('contenedor-tabla-defensas').classList.remove('d-none');
        document.querySelector('#formulario-defensas form').reset();
    }

    // Buscador Predictivo en Vivo
    document.addEventListener('DOMContentLoaded', function() {
        const buscadorDef = document.getElementById('buscadorDefensas');
        if(buscadorDef) {
            buscadorDef.addEventListener('keyup', function() {
                const texto = this.value.toLowerCase();
                const filas = document.querySelectorAll('.fila-defensa');
                
                filas.forEach(fila => {
                    let contenidoFila = '';
                    fila.querySelectorAll('.texto-busqueda-def').forEach(celda => {
                        contenidoFila += celda.textContent.toLowerCase() + ' ';
                    });

                    if (contenidoFila.includes(texto)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            });
        }
    });
</script>