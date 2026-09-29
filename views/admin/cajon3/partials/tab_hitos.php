<?php
/**
 * ARCHIVO: views/admin/cajon3/partials/tab_hitos.php
 * Interfaz SPA para Programación de Hitos y Fechas Límite (Cronograma).
 */

// Estas variables vendrán del index.php principal del Cajón 3
$hitos_entregas = $hitos_entregas ?? [];
$lista_cohortes = $lista_cohortes ?? [];
?>

<div id="seccion-hitos" class="d-block">
    
    <!-- CONTENEDOR DE LA TABLA -->
    <div id="contenedor-tabla-hitos">
        
        <!-- TOOLBAR: Título, Buscador y Botón Nuevo -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-5">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-flag-checkered me-2"></i>Hitos y Fechas Límite</h6>
            </div>
            <div class="col-md-7 d-flex justify-content-end align-items-center gap-2">
                <!-- Buscador Predictivo -->
                <div class="input-group input-group-sm w-50 shadow-sm">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorHitos" class="form-control border-plano" placeholder="Buscar hito o cohorte...">
                </div>
                <!-- Botón Nuevo -->
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioHitos('nuevo')">
                    <i class="fas fa-plus me-1"></i> Programar Hito
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaHitos">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Fecha Límite</th>
                            <th>Cohorte</th>
                            <th>Nombre del Hito</th>
                            <th>Etapa / Tipo</th>
                            <th style="width: 15%;">Avance Esp.</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($hitos_entregas)): ?>
                            <?php foreach ($hitos_entregas as $h): ?>
                                <tr class="fila-hito">
                                    <td class="ps-4 fw-bold text-institucional">
                                        <i class="far fa-calendar-check me-1"></i> <?php echo date('d/m/Y', strtotime($h['fecha_limite'])); ?>
                                    </td>
                                    
                                    <td class="texto-busqueda-hito fw-semibold">
                                        <?php echo htmlspecialchars($h['nombre_cohorte'] ?? 'N/A'); ?>
                                        <div class="small text-muted"><?php echo htmlspecialchars($h['codigo_cohorte'] ?? ''); ?></div>
                                    </td>
                                    
                                    <td class="texto-busqueda-hito">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($h['nombre']); ?></div>
                                        <div class="small text-muted">Orden cronológico: <?php echo $h['orden']; ?></div>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        $badge_etapa = $h['etapa'] == 'mg1' ? 'bg-primary' : ($h['etapa'] == 'mg2' ? 'bg-warning text-dark' : 'bg-secondary');
                                        ?>
                                        <span class="badge <?php echo $badge_etapa; ?> border-plano mb-1"><?php echo strtoupper($h['etapa']); ?></span>
                                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem;">
                                            <?php echo str_replace('_', ' ', $h['tipo']); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="small fw-bold me-2"><?php echo $h['avance_esperado_pct']; ?>%</span>
                                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 0;">
                                                <div class="progress-bar bg-institucional" role="progressbar" style="width: <?php echo $h['avance_esperado_pct']; ?>%;" aria-valuenow="<?php echo $h['avance_esperado_pct']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioHitos('editar', {
                                                    id_hito: '<?php echo $h['id_hito']; ?>',
                                                    id_cohorte: '<?php echo $h['id_cohorte']; ?>',
                                                    etapa: '<?php echo $h['etapa']; ?>',
                                                    tipo: '<?php echo $h['tipo']; ?>',
                                                    nombre: '<?php echo addslashes($h['nombre']); ?>',
                                                    orden: '<?php echo $h['orden']; ?>',
                                                    fecha_limite: '<?php echo $h['fecha_limite']; ?>',
                                                    avance_esperado_pct: '<?php echo $h['avance_esperado_pct']; ?>'
                                                })" title="Editar Hito">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/HitoController.php?accion=eliminar&id=<?php echo $h['id_hito']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar esta fecha límite del cronograma?');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-hitos"><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-flag mb-3 d-block fa-2x"></i> No hay hitos ni fechas límite programadas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginador aislado visualmente -->
        <div class="d-flex justify-content-end">
            <div class="btn-group shadow-sm">
                <button class="btn btn-sm btn-light border-plano text-muted" disabled>Anterior</button>
                <button class="btn btn-sm btn-institucional border-plano">1</button>
                <button class="btn btn-sm btn-light border-plano text-muted" disabled>Siguiente</button>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE: HITOS -->
    <div id="formulario-hitos" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-hitos"><i class="fas fa-plus-circle me-2"></i>Programar Nuevo Hito</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/HitoController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-hitos" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-hitos" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Cohorte Asociada</label>
                            <select name="id_cohorte" id="hito-cohorte" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione cohorte...</option>
                                <?php foreach($lista_cohortes as $cohorte): ?>
                                    <option value="<?php echo $cohorte['id_cohorte']; ?>"><?php echo htmlspecialchars($cohorte['nombre'] . ' (' . $cohorte['codigo'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre de la Entrega / Hito (Ej. 1er Borrador)</label>
                            <input type="text" name="nombre" id="hito-nombre" class="form-control form-control-sm border-plano" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fecha Límite</label>
                            <input type="date" name="fecha_limite" id="hito-fecha" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Etapa Académica</label>
                            <select name="etapa" id="hito-etapa" class="form-select form-select-sm border-plano">
                                <option value="previa">Previa / Preparación</option>
                                <option value="mg1">Perfil (MG1)</option>
                                <option value="mg2">Borrador (MG2)</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Tipo</label>
                            <select name="tipo" id="hito-tipo" class="form-select form-select-sm border-plano">
                                <option value="informe">Informe / Entrega</option>
                                <option value="taller">Taller</option>
                                <option value="asignacion_tutor">Asignación</option>
                                <option value="defensa">Defensa</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Orden N°</label>
                            <input type="number" name="orden" id="hito-orden" class="form-control form-control-sm border-plano" min="1" value="1" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label small fw-bold">Avance (%)</label>
                            <input type="number" name="avance_esperado_pct" id="hito-avance" class="form-control form-control-sm border-plano" min="0" max="100" value="0" required>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioHitos()">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Hito</button>
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
    // Control del Formulario In-line
    function abrirFormularioHitos(accion, datos = null) {
        document.getElementById('contenedor-tabla-hitos').classList.add('d-none');
        document.getElementById('formulario-hitos').classList.replace('d-none', 'd-block');
        
        const form = document.querySelector('#formulario-hitos form');
        const titulo = document.getElementById('titulo-form-hitos');
        const inputAccion = document.getElementById('accion-form-hitos');
        const inputId = document.getElementById('id-form-hitos');

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Programar Nuevo Hito';
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id_hito;
            titulo.innerHTML = '<i class="fas fa-edit me-2"></i>Editar Hito del Cronograma';
            
            document.getElementById('hito-cohorte').value = datos.id_cohorte;
            document.getElementById('hito-nombre').value = datos.nombre;
            document.getElementById('hito-etapa').value = datos.etapa;
            document.getElementById('hito-tipo').value = datos.tipo;
            document.getElementById('hito-orden').value = datos.orden;
            document.getElementById('hito-avance').value = datos.avance_esperado_pct;
            document.getElementById('hito-fecha').value = datos.fecha_limite;
        }
    }

    function cerrarFormularioHitos() {
        document.getElementById('formulario-hitos').classList.replace('d-block', 'd-none');
        document.getElementById('contenedor-tabla-hitos').classList.remove('d-none');
        document.querySelector('#formulario-hitos form').reset();
    }

    // Buscador Predictivo en Vivo
    document.addEventListener('DOMContentLoaded', function() {
        const buscadorHitos = document.getElementById('buscadorHitos');
        if(buscadorHitos) {
            buscadorHitos.addEventListener('keyup', function() {
                const texto = this.value.toLowerCase();
                const filas = document.querySelectorAll('.fila-hito');
                
                filas.forEach(fila => {
                    let contenidoFila = '';
                    fila.querySelectorAll('.texto-busqueda-hito').forEach(celda => {
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