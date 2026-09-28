<?php
/**
 * ARCHIVO: views/admin/cajon2/partials/tab_periodos.php
 * Interfaz SPA para Periodos de Tutoría y Cohortes de Grado.
 * Controlado 100% por JavaScript nativo para conmutación de vistas y CRUD in-line.
 */

// NOTA: Estas variables ($periodos,$cohortes) vendrán del index.php principal del Cajón 2.
$periodos =$periodos ?? []; 
$cohortes =$cohortes ?? [];
?>

<!-- INTERRUPTORES SUPERIORES (Conmutador JS) -->
<div class="d-flex justify-content-center mb-4">
    <div class="btn-group shadow-sm" role="group">
        <button type="button" id="btn-ver-periodos" class="btn btn-institucional border-plano fw-bold" onclick="conmutarVista('periodos')">
            <i class="fas fa-calendar-check me-2"></i>Ver Periodos de Tutoría
        </button>
        <button type="button" id="btn-ver-cohortes" class="btn btn-outline-institucional border-plano" onclick="conmutarVista('cohortes')">
            <i class="fas fa-users me-2"></i>Ver Cohortes de Grado
        </button>
    </div>
</div>

<!-- ======================================================================= -->
<!-- SECCIÓN A: PERIODOS DE TUTORÍA -->
<!-- ======================================================================= -->
<div id="seccion-periodos" class="d-block">
    
    <!-- CONTENEDOR DE LA TABLA -->
    <div id="contenedor-tabla-periodos">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-list me-2"></i>Lista de Periodos de Tutoría</h6>
            <button class="btn btn-sm btn-success border-plano" onclick="abrirFormulario('periodos', 'nuevo')">
                <i class="fas fa-plus me-1"></i> Nuevo Periodo
            </button>
        </div>
        <div class="card border-0 shadow-sm border-plano">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nombre</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($periodos)): ?>
                            <?php foreach ($periodos as$p): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-institucional"><?php echo htmlspecialchars($p['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($p['fecha_inicio']); ?></td>
                                    <td><?php echo htmlspecialchars($p['fecha_fin']); ?></td>
                                    <td>
                                        <?php if ($p['activo'] == 1): ?>
                                            <span class="badge bg-success border-plano">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger border-plano">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormulario('periodos', 'editar', {
                                                    id: '<?php echo $p['id_periodo']; ?>',
                                                    codigo: '<?php echo addslashes($p['codigo']); ?>',
                                                    nombre: '<?php echo addslashes($p['nombre']); ?>',
                                                    inicio: '<?php echo $p['fecha_inicio']; ?>',
                                                    fin: '<?php echo $p['fecha_fin']; ?>',
                                                    activo: '<?php echo $p['activo']; ?>'
                                                })" title="Editar Periodo">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/PeriodoController.php?accion=eliminar&id=<?php echo $p['id_periodo']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar el periodo <?php echo addslashes($p['codigo']); ?>? Esta acción no se puede deshacer.');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No hay periodos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE (Oculto por defecto) -->
    <div id="formulario-periodos" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-periodos"><i class="fas fa-plus-circle me-2"></i>Crear Nuevo Periodo</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/PeriodoController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-periodos" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-periodos" value="">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Código (Ej. I-2026)</label>
                            <input type="text" name="codigo" id="codigo-periodo" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label small fw-bold">Nombre Descriptivo</label>
                            <input type="text" name="nombre" id="nombre-periodo" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="inicio-periodo" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Fecha de Fin</label>
                            <input type="date" name="fecha_fin" id="fin-periodo" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="activo" id="estado-periodo" class="form-select form-select-sm border-plano">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormulario('periodos')">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Periodo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- ======================================================================= -->
<!-- SECCIÓN B: COHORTES DE GRADO -->
<!-- ======================================================================= -->
<div id="seccion-cohortes" class="d-none">
    
    <!-- CONTENEDOR DE LA TABLA -->
    <div id="contenedor-tabla-cohortes">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-users me-2"></i>Lista de Cohortes de Grado</h6>
            <button class="btn btn-sm btn-success border-plano" onclick="abrirFormulario('cohortes', 'nuevo')">
                <i class="fas fa-plus me-1"></i> Nueva Cohorte
            </button>
        </div>
        <div class="card border-0 shadow-sm border-plano">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nombre de Cohorte</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cohortes)): ?>
                            <?php foreach ($cohortes as$c): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-success"><?php echo htmlspecialchars($c['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($c['fecha_inicio']); ?></td>
                                    <td><?php echo htmlspecialchars($c['fecha_fin']); ?></td>
                                    <td>
                                        <?php if ($c['activa'] == 1): ?>
                                            <span class="badge bg-success border-plano">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger border-plano">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormulario('cohortes', 'editar', {
                                                    id: '<?php echo $c['id_cohorte']; ?>',
                                                    codigo: '<?php echo addslashes($c['codigo']); ?>',
                                                    nombre: '<?php echo addslashes($c['nombre']); ?>',
                                                    inicio: '<?php echo $c['fecha_inicio']; ?>',
                                                    fin: '<?php echo $c['fecha_fin']; ?>',
                                                    activa: '<?php echo $c['activa']; ?>'
                                                })" title="Editar Cohorte">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <!-- AQUÍ: Apuntando al controlador correcto -->
                                        <a href="../../controllers/GrupoTesisController.php?accion=eliminar&id=<?php echo $c['id_cohorte']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar la cohorte <?php echo addslashes($c['codigo']); ?>?');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No hay cohortes registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE (Oculto por defecto) -->
    <div id="formulario-cohortes" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-success">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-success fw-bold" id="titulo-form-cohortes"><i class="fas fa-plus-circle me-2"></i>Crear Nueva Cohorte</h6>
            </div>
            <div class="card-body bg-light p-4">
                <!-- AQUÍ: Apuntando al controlador correcto -->
                <form action="../../controllers/GrupoTesisController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-cohortes" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-cohortes" value="">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Código (Ej. MG-2026-1)</label>
                            <input type="text" name="codigo" id="codigo-cohorte" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label small fw-bold">Nombre Descriptivo</label>
                            <input type="text" name="nombre" id="nombre-cohorte" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="inicio-cohorte" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Fecha de Fin</label>
                            <input type="date" name="fecha_fin" id="fin-cohorte" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="activa" id="estado-cohorte" class="form-select form-select-sm border-plano">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormulario('cohortes')">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-success border-plano"><i class="fas fa-save me-1"></i> Guardar Cohorte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- LÓGICA JAVASCRIPT NATIVA PARA MANIPULACIÓN DEL DOM -->
<!-- ======================================================================= -->
<script>
    // 1. Conmutador de Vistas Principales (Periodos vs Cohortes)
    function conmutarVista(vista) {
        const secPeriodos = document.getElementById('seccion-periodos');
        const secCohortes = document.getElementById('seccion-cohortes');
        const btnPeriodos = document.getElementById('btn-ver-periodos');
        const btnCohortes = document.getElementById('btn-ver-cohortes');

        // Resetear ambos formularios al cambiar de pestaña interna
        cerrarFormulario('periodos');
        cerrarFormulario('cohortes');

        if (vista === 'periodos') {
            secPeriodos.classList.replace('d-none', 'd-block');
            secCohortes.classList.replace('d-block', 'd-none');
            
            btnPeriodos.classList.replace('btn-outline-institucional', 'btn-institucional');
            btnPeriodos.classList.add('fw-bold');
            btnCohortes.classList.replace('btn-institucional', 'btn-outline-institucional');
            btnCohortes.classList.remove('fw-bold');
        } else {
            secPeriodos.classList.replace('d-block', 'd-none');
            secCohortes.classList.replace('d-none', 'd-block');
            
            btnCohortes.classList.replace('btn-outline-institucional', 'btn-institucional');
            btnCohortes.classList.add('fw-bold');
            btnPeriodos.classList.replace('btn-institucional', 'btn-outline-institucional');
            btnPeriodos.classList.remove('fw-bold');
        }
    }

    // 2. Abrir Formulario IN-LINE (Nuevo o Editar)
    function abrirFormulario(seccion, accion, datos = null) {
        // Ocultar la tabla
        document.getElementById(`contenedor-tabla-${seccion}`).classList.replace('d-block', 'd-none');
        if(!document.getElementById(`contenedor-tabla-${seccion}`).classList.contains('d-none')){
             document.getElementById(`contenedor-tabla-${seccion}`).classList.add('d-none');
        }
        
        // Mostrar el formulario
        document.getElementById(`formulario-${seccion}`).classList.replace('d-none', 'd-block');
        
        const form = document.querySelector(`#formulario-${seccion} form`);
        const titulo = document.getElementById(`titulo-form-${seccion}`);
        const inputAccion = document.getElementById(`accion-form-${seccion}`);
        const inputId = document.getElementById(`id-form-${seccion}`);

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = seccion === 'periodos' ? '<i class="fas fa-plus-circle me-2"></i>Crear Nuevo Periodo' : '<i class="fas fa-plus-circle me-2"></i>Crear Nueva Cohorte';
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id;
            titulo.innerHTML = seccion === 'periodos' ? '<i class="fas fa-edit me-2"></i>Editar Periodo' : '<i class="fas fa-edit me-2"></i>Editar Cohorte';
            
            // Llenar campos dinámicamente
            document.getElementById(`codigo-${seccion.slice(0, -1)}`).value = datos.codigo;
            document.getElementById(`nombre-${seccion.slice(0, -1)}`).value = datos.nombre;
            document.getElementById(`inicio-${seccion.slice(0, -1)}`).value = datos.inicio;
            document.getElementById(`fin-${seccion.slice(0, -1)}`).value = datos.fin;
            document.getElementById(`estado-${seccion.slice(0, -1)}`).value = (seccion === 'periodos') ? datos.activo : datos.activa;
        }
    }

    // 3. Cerrar Formulario IN-LINE y Mostrar Tabla
    function cerrarFormulario(seccion) {
        document.getElementById(`formulario-${seccion}`).classList.replace('d-block', 'd-none');
        
        const tabla = document.getElementById(`contenedor-tabla-${seccion}`);
        tabla.classList.remove('d-none');
        tabla.classList.add('d-block');
        
        document.querySelector(`#formulario-${seccion} form`).reset();
    }
    
    // 4. Si la pestaña se recargó y estaba en 'cohortes', conmutar la vista por defecto a cohortes
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('tab') === 'cohortes' || window.location.hash === '#cohortes') {
             // Pequeño timeout para asegurar que el DOM cargó completo
             setTimeout(() => conmutarVista('cohortes'), 50);
        }
    });
</script>