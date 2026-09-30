<?php
/**
 * ARCHIVO: views/admin/cajon2/partials/tab_periodos.php
 * Interfaz SPA para Periodos de Tutoría y Cohortes de Grado.
 * Controlado 100% por JavaScript nativo para conmutación de vistas, CRUD in-line y validaciones.
 */

// NOTA: Estas variables ($periodos,$cohortes) vendrán del index.php principal del Cajón 2.
$periodos = $periodos ?? []; 
$cohortes = $cohortes ?? [];
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
                                        <a href="javascript:void(0);" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="confirmarEliminacion('../../controllers/PeriodoController.php?accion=eliminar&id=<?php echo $p['id_periodo']; ?>', '¿Estás seguro de eliminar el periodo <b><?php echo addslashes($p['codigo']); ?></b>? Esta acción no se puede deshacer.');" 
                                           title="Borrar">
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
                
                <!-- Tarjeta Visual Emergente para Colisiones -->
                <div id="alerta-colision-periodos" class="alert alert-danger d-none border-0 border-start border-4 border-danger shadow-sm mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> <span id="texto-colision-periodos"></span>
                </div>

                <form id="form-guardar-periodo" action="../../controllers/PeriodoController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-periodos" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-periodos" value="">
                    
                    <!-- Campo oculto para el Código Autogenerado -->
                    <input type="hidden" name="codigo" id="codigo-periodo" value="">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
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
                                        <a href="javascript:void(0);" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="confirmarEliminacion('../../controllers/GrupoTesisController.php?accion=eliminar&id=<?php echo $c['id_cohorte']; ?>', '¿Estás seguro de eliminar la cohorte <b><?php echo addslashes($c['codigo']); ?></b>? Esta acción no se puede deshacer.');" 
                                           title="Borrar">
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

                <!-- Tarjeta Visual Emergente para Colisiones de Cohortes -->
                <div id="alerta-colision-cohortes" class="alert alert-danger d-none border-0 border-start border-4 border-danger shadow-sm mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> <span id="texto-colision-cohortes"></span>
                </div>

                <form id="form-guardar-cohorte" action="../../controllers/GrupoTesisController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-cohortes" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-cohortes" value="">
                    
                    <!-- Campo oculto para el Código Autogenerado -->
                    <input type="hidden" name="codigo" id="codigo-cohorte" value="">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
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
<!-- MODAL GENÉRICO DE CONFIRMACIÓN DE ELIMINACIÓN -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-plano border-top border-4 border-danger shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-exclamation-triangle text-danger me-2"></i> Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-0 fs-6 text-dark" id="texto-confirmacion-eliminacion"></p>
            </div>
            <div class="modal-footer bg-light border-plano justify-content-center">
                <button type="button" class="btn btn-secondary border-plano" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btn-confirmar-eliminar" class="btn btn-danger border-plano fw-bold">Sí, Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- LÓGICA JAVASCRIPT NATIVA PARA MANIPULACIÓN DEL DOM Y VALIDACIÓN -->
<!-- ======================================================================= -->
<script>
    // 0. Datos PHP inyectados para validación de colisiones y cálculo de códigos
    const periodosExistentes = <?php echo json_encode($periodos); ?>;
    const cohortesExistentes = <?php echo json_encode($cohortes); ?>;

    // Utilidad: Mostrar Modal de Eliminación
    function confirmarEliminacion(url, mensaje) {
        document.getElementById('texto-confirmacion-eliminacion').innerHTML = mensaje;
        document.getElementById('btn-confirmar-eliminar').href = url;
        const modalElement = document.getElementById('modalConfirmarEliminacion');
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            // Fallback en caso de que bootstrap no esté cargado al instante
            if(confirm(mensaje.replace(/<[^>]*>?/gm, ''))) window.location.href = url;
        }
    }

    // Utilidad: Conversión a Números Romanos (Para Periodos)
    function convertirARomano(num) {
        const valoresRomanos = { M: 1000, CM: 900, D: 500, CD: 400, C: 100, XC: 90, L: 50, XL: 40, X: 10, IX: 9, V: 5, IV: 4, I: 1 };
        let romano = '';
        for (let i of Object.keys(valoresRomanos)) {
            let q = Math.floor(num / valoresRomanos[i]);
            num -= q * valoresRomanos[i];
            romano += i.repeat(q);
        }
        return romano;
    }

    // Utilidad: Parsear un Romano a Número (Para Periodos)
    function deRomanoANumero(str) {
        const valoresRomanos = { M: 1000, CM: 900, D: 500, CD: 400, C: 100, XC: 90, L: 50, XL: 40, X: 10, IX: 9, V: 5, IV: 4, I: 1 };
        let num = 0;
        for (let i of Object.keys(valoresRomanos)) {
            while (str.indexOf(i) === 0) {
                num += valoresRomanos[i];
                str = str.replace(i, '');
            }
        }
        return num;
    }

    // 1. Lógica para generar el Código Autonumérico de Periodos (Ej: I-2026)
    function generarCodigoPeriodo() {
        const anioActual = new Date().getFullYear().toString();
        let maxOrden = 0;

        periodosExistentes.forEach(p => {
            const partes = p.codigo.split('-');
            if (partes.length === 2 && partes[1] === anioActual) {
                const num = deRomanoANumero(partes[0]);
                if (num > maxOrden) maxOrden = num;
            }
        });

        const siguienteOrden = maxOrden + 1;
        return `${convertirARomano(siguienteOrden)}-${anioActual}`;
    }

    // 2. Lógica para generar el Código Autonumérico de Cohortes (Ej: MG-20261)
    function generarCodigoCohorte() {
        const anioActual = new Date().getFullYear().toString();
        let maxOrden = 0;

        cohortesExistentes.forEach(c => {
            const partes = c.codigo.split('-');
            if (partes.length === 2 && partes[0] === 'MG') {
                const anioOrden = partes[1]; // ej. '20261'
                if (anioOrden.startsWith(anioActual)) {
                    const numStr = anioOrden.substring(anioActual.length);
                    const num = parseInt(numStr, 10);
                    if (!isNaN(num) && num > maxOrden) {
                        maxOrden = num;
                    }
                }
            }
        });

        const siguienteOrden = maxOrden + 1;
        return `MG-${anioActual}${siguienteOrden}`;
    }

    // 3. Conmutador de Vistas Principales (Periodos vs Cohortes)
    function conmutarVista(vista) {
        const secPeriodos = document.getElementById('seccion-periodos');
        const secCohortes = document.getElementById('seccion-cohortes');
        const btnPeriodos = document.getElementById('btn-ver-periodos');
        const btnCohortes = document.getElementById('btn-ver-cohortes');

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

    // 4. Abrir Formulario IN-LINE (Nuevo o Editar)
    function abrirFormulario(seccion, accion, datos = null) {
        document.getElementById(`contenedor-tabla-${seccion}`).classList.replace('d-block', 'd-none');
        if(!document.getElementById(`contenedor-tabla-${seccion}`).classList.contains('d-none')){
             document.getElementById(`contenedor-tabla-${seccion}`).classList.add('d-none');
        }
        
        document.getElementById(`formulario-${seccion}`).classList.replace('d-none', 'd-block');
        
        const form = document.querySelector(`#formulario-${seccion} form`);
        const titulo = document.getElementById(`titulo-form-${seccion}`);
        const inputAccion = document.getElementById(`accion-form-${seccion}`);
        const inputId = document.getElementById(`id-form-${seccion}`);

        // Esconder alertas residuales dinámicamente
        const alertaColision = document.getElementById(`alerta-colision-${seccion}`);
        if(alertaColision) alertaColision.classList.add('d-none');

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = seccion === 'periodos' ? '<i class="fas fa-plus-circle me-2"></i>Crear Nuevo Periodo' : '<i class="fas fa-plus-circle me-2"></i>Crear Nueva Cohorte';
            
            if (seccion === 'periodos') {
                document.getElementById('codigo-periodo').value = generarCodigoPeriodo();
            } else if (seccion === 'cohortes') {
                document.getElementById('codigo-cohorte').value = generarCodigoCohorte();
            }
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id;
            titulo.innerHTML = seccion === 'periodos' ? '<i class="fas fa-edit me-2"></i>Editar Periodo' : '<i class="fas fa-edit me-2"></i>Editar Cohorte';
            
            document.getElementById(`codigo-${seccion.slice(0, -1)}`).value = datos.codigo;
            document.getElementById(`nombre-${seccion.slice(0, -1)}`).value = datos.nombre;
            document.getElementById(`inicio-${seccion.slice(0, -1)}`).value = datos.inicio;
            document.getElementById(`fin-${seccion.slice(0, -1)}`).value = datos.fin;
            document.getElementById(`estado-${seccion.slice(0, -1)}`).value = (seccion === 'periodos') ? datos.activo : datos.activa;
        }
    }

    // 5. Cerrar Formulario IN-LINE y Mostrar Tabla
    function cerrarFormulario(seccion) {
        document.getElementById(`formulario-${seccion}`).classList.replace('d-block', 'd-none');
        const alertaColision = document.getElementById(`alerta-colision-${seccion}`);
        if(alertaColision) alertaColision.classList.add('d-none');
        
        const tabla = document.getElementById(`contenedor-tabla-${seccion}`);
        tabla.classList.remove('d-none');
        tabla.classList.add('d-block');
        
        document.querySelector(`#formulario-${seccion} form`).reset();
    }
    
    // 6. Validar colisión de fechas de Periodos
    const formPeriodo = document.getElementById('form-guardar-periodo');
    if(formPeriodo) {
        formPeriodo.addEventListener('submit', function(e) {
            const idPeriodoActual = document.getElementById('id-form-periodos').value;
            const accionActual = document.getElementById('accion-form-periodos').value;
            const fechaInicio = new Date(document.getElementById('inicio-periodo').value).getTime();
            const fechaFin = new Date(document.getElementById('fin-periodo').value).getTime();
            
            const alerta = document.getElementById('alerta-colision-periodos');
            const textoAlerta = document.getElementById('texto-colision-periodos');
            alerta.classList.add('d-none');

            if (fechaInicio > fechaFin) {
                e.preventDefault();
                textoAlerta.innerHTML = "<strong>Error temporal:</strong> La fecha de inicio no puede ser posterior a la fecha de finalización.";
                alerta.classList.remove('d-none');
                return;
            }

            for (let p of periodosExistentes) {
                if (accionActual === 'actualizar' && p.id_periodo == idPeriodoActual) continue;

                const pInicio = new Date(p.fecha_inicio).getTime();
                const pFin = new Date(p.fecha_fin).getTime();

                if (fechaInicio <= pFin && fechaFin >= pInicio) {
                    e.preventDefault();
                    textoAlerta.innerHTML = `<strong>¡Colisión de fechas!</strong> Las fechas elegidas chocan con el periodo <b>${p.codigo} (${p.nombre})</b>, el cual abarca desde el ${p.fecha_inicio} hasta el ${p.fecha_fin}.`;
                    alerta.classList.remove('d-none');
                    return;
                }
            }
        });
    }

    // 7. Validar colisión de fechas de Cohortes
    const formCohorte = document.getElementById('form-guardar-cohorte');
    if(formCohorte) {
        formCohorte.addEventListener('submit', function(e) {
            const idCohorteActual = document.getElementById('id-form-cohortes').value;
            const accionActual = document.getElementById('accion-form-cohortes').value;
            const fechaInicio = new Date(document.getElementById('inicio-cohorte').value).getTime();
            const fechaFin = new Date(document.getElementById('fin-cohorte').value).getTime();
            
            const alerta = document.getElementById('alerta-colision-cohortes');
            const textoAlerta = document.getElementById('texto-colision-cohortes');
            alerta.classList.add('d-none');

            if (fechaInicio > fechaFin) {
                e.preventDefault();
                textoAlerta.innerHTML = "<strong>Error temporal:</strong> La fecha de inicio no puede ser posterior a la fecha de finalización.";
                alerta.classList.remove('d-none');
                return;
            }

            for (let c of cohortesExistentes) {
                if (accionActual === 'actualizar' && c.id_cohorte == idCohorteActual) continue;

                const cInicio = new Date(c.fecha_inicio).getTime();
                const cFin = new Date(c.fecha_fin).getTime();

                if (fechaInicio <= cFin && fechaFin >= cInicio) {
                    e.preventDefault();
                    textoAlerta.innerHTML = `<strong>¡Colisión de fechas!</strong> Las fechas elegidas chocan con la cohorte <b>${c.codigo} (${c.nombre})</b>, la cual abarca desde el ${c.fecha_inicio} hasta el ${c.fecha_fin}.`;
                    alerta.classList.remove('d-none');
                    return;
                }
            }
        });
    }

    // 8. Auto-navegación si hay hash
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('tab') === 'cohortes' || window.location.hash === '#cohortes') {
             setTimeout(() => conmutarVista('cohortes'), 50);
        }
    });
</script>