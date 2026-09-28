<?php
/**
 * ARCHIVO: views/admin/cajon2/partials/tab_oferta.php
 * Interfaz SPA para Catálogo de Carreras y Materias.
 * Incluye buscadores predictivos en vivo y filtros cruzados por JS nativo.
 */

// Estas variables vendrán del index.php principal del Cajón 2
$carreras =$carreras ?? []; 
$materias =$materias ?? [];
?>

<!-- INTERRUPTORES SUPERIORES (Conmutador JS) -->
<div class="d-flex justify-content-center mb-4">
    <div class="btn-group shadow-sm" role="group">
        <button type="button" id="btn-ver-carreras" class="btn btn-institucional border-plano fw-bold" onclick="conmutarVistaOferta('carreras')">
            <i class="fas fa-graduation-cap me-2"></i>Ver Carreras
        </button>
        <button type="button" id="btn-ver-materias" class="btn btn-outline-institucional border-plano" onclick="conmutarVistaOferta('materias')">
            <i class="fas fa-book me-2"></i>Ver Materias
        </button>
    </div>
</div>

<!-- ======================================================================= -->
<!-- SECCIÓN A: CATÁLOGO DE CARRERAS -->
<!-- ======================================================================= -->
<div id="seccion-carreras" class="d-block">
    <div id="contenedor-tabla-carreras">
        <!-- TOOLBAR: Buscador Predictivo y Botón Nuevo -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-list me-2"></i>Catálogo de Carreras</h6>
            </div>
            <div class="col-md-6 d-flex justify-content-end gap-2">
                <div class="input-group input-group-sm w-50 shadow-sm">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorCarreras" class="form-control border-plano" placeholder="Buscar carrera en vivo...">
                </div>
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioOferta('carreras', 'nuevo')">
                    <i class="fas fa-plus me-1"></i> Nueva Carrera
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaCarreras">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre de la Carrera</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($carreras)): ?>
                            <?php foreach ($carreras as$c): ?>
                                <tr class="fila-carrera">
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $c['id_carrera']; ?></td>
                                    <td class="texto-busqueda fw-semibold text-dark"><?php echo htmlspecialchars($c['nombre_carrera']); ?></td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioOferta('carreras', 'editar', {
                                                    id: '<?php echo $c['id_carrera']; ?>',
                                                    nombre: '<?php echo addslashes($c['nombre_carrera']); ?>'
                                                })" title="Editar Carrera">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/CarreraController.php?accion=eliminar&id=<?php echo $c['id_carrera']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar la carrera <?php echo addslashes($c['nombre_carrera']); ?>? Las materias asociadas podrían verse afectadas.');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-carreras"><td colspan="3" class="text-center py-4 text-muted">No hay carreras registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Paginador simulado visualmente según requerimiento -->
        <div class="d-flex justify-content-end">
            <div class="btn-group shadow-sm">
                <button class="btn btn-sm btn-light border-plano text-muted" disabled>Anterior</button>
                <button class="btn btn-sm btn-institucional border-plano">1</button>
                <button class="btn btn-sm btn-light border-plano text-muted" disabled>Siguiente</button>
            </div>
        </div>
    </div>

    <!-- FORMULARIO IN-LINE: CARRERAS -->
    <div id="formulario-carreras" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-carreras"><i class="fas fa-plus-circle me-2"></i>Crear Nueva Carrera</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/CarreraController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-carreras" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-carreras" value="">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">Nombre Completo de la Carrera</label>
                            <input type="text" name="nombre_carrera" id="nombre-carrera" class="form-control form-control-sm border-plano" placeholder="Ej. Ingeniería de Sistemas" required>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioOferta('carreras')">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Carrera</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- SECCIÓN B: CATÁLOGO DE MATERIAS (CON FILTRO CRUZADO) -->
<!-- ======================================================================= -->
<div id="seccion-materias" class="d-none">
    <div id="contenedor-tabla-materias">
        <!-- TOOLBAR AVANZADO: Buscador + Filtro Select + Botón Nuevo -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-4">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-book-open me-2"></i>Catálogo de Materias</h6>
            </div>
            <div class="col-md-8 d-flex justify-content-end gap-2">
                <select id="filtroCarreras" class="form-select form-select-sm w-auto shadow-sm border-plano">
                    <option value="todas">-- Ver todas las carreras --</option>
                    <?php if (!empty($carreras)): ?>
                        <?php foreach ($carreras as$c): ?>
                            <option value="<?php echo $c['id_carrera']; ?>"><?php echo htmlspecialchars($c['nombre_carrera']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <div class="input-group input-group-sm w-auto shadow-sm">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorMaterias" class="form-control border-plano" placeholder="Buscar materia...">
                </div>
                
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioOferta('materias', 'nuevo')">
                    <i class="fas fa-plus me-1"></i> Nueva Materia
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaMaterias">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Materia</th>
                            <th>Pertenece a Carrera</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($materias)): ?>
                            <?php foreach ($materias as$m): ?>
                                <!-- NOTA: Inyectamos data-carrera para el filtro JS -->
                                <tr class="fila-materia" data-carrera="<?php echo $m['id_carrera']; ?>">
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $m['id_materia']; ?></td>
                                    <td class="texto-busqueda-mat fw-semibold text-dark"><?php echo htmlspecialchars($m['nombre_materia']); ?></td>
                                    <td><span class="badge bg-secondary border-plano fw-normal"><?php echo htmlspecialchars($m['nombre_carrera'] ?? 'Sin asignar'); ?></span></td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioOferta('materias', 'editar', {
                                                    id: '<?php echo $m['id_materia']; ?>',
                                                    nombre: '<?php echo addslashes($m['nombre_materia']); ?>',
                                                    carrera: '<?php echo $m['id_carrera']; ?>'
                                                })" title="Editar Materia">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/MateriaController.php?accion=eliminar&id=<?php echo $m['id_materia']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar la materia <?php echo addslashes($m['nombre_materia']); ?>?');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-materias"><td colspan="4" class="text-center py-4 text-muted">No hay materias registradas.</td></tr>
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

    <!-- FORMULARIO IN-LINE: MATERIAS -->
    <div id="formulario-materias" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-success">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-success fw-bold" id="titulo-form-materias"><i class="fas fa-plus-circle me-2"></i>Crear Nueva Materia</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/MateriaController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-materias" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-materias" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre de la Materia</label>
                            <input type="text" name="nombre_materia" id="nombre-materia" class="form-control form-control-sm border-plano" placeholder="Ej. Base de Datos I" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Asignar a Carrera</label>
                            <select name="id_carrera" id="id-carrera-materia" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione una carrera...</option>
                                <?php if (!empty($carreras)): ?>
                                    <?php foreach ($carreras as$c): ?>
                                        <option value="<?php echo $c['id_carrera']; ?>"><?php echo htmlspecialchars($c['nombre_carrera']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioOferta('materias')">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-success border-plano"><i class="fas fa-save me-1"></i> Guardar Materia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- LÓGICA JAVASCRIPT: CONMUTADOR, CRUD IN-LINE Y FILTROS PREDICTIVOS -->
<!-- ======================================================================= -->
<script>
    // 1. Conmutador Principal (Carreras vs Materias)
    function conmutarVistaOferta(vista) {
        const secCarreras = document.getElementById('seccion-carreras');
        const secMaterias = document.getElementById('seccion-materias');
        const btnCarreras = document.getElementById('btn-ver-carreras');
        const btnMaterias = document.getElementById('btn-ver-materias');

        cerrarFormularioOferta('carreras');
        cerrarFormularioOferta('materias');

        if (vista === 'carreras') {
            secCarreras.classList.replace('d-none', 'd-block');
            secMaterias.classList.replace('d-block', 'd-none');
            
            btnCarreras.classList.replace('btn-outline-institucional', 'btn-institucional');
            btnCarreras.classList.add('fw-bold');
            btnMaterias.classList.replace('btn-institucional', 'btn-outline-institucional');
            btnMaterias.classList.remove('fw-bold');
        } else {
            secCarreras.classList.replace('d-block', 'd-none');
            secMaterias.classList.replace('d-none', 'd-block');
            
            btnMaterias.classList.replace('btn-outline-institucional', 'btn-institucional');
            btnMaterias.classList.add('fw-bold');
            btnCarreras.classList.replace('btn-institucional', 'btn-outline-institucional');
            btnCarreras.classList.remove('fw-bold');
        }
    }

    // 2. Control de Formularios In-line
    function abrirFormularioOferta(seccion, accion, datos = null) {
        document.getElementById(`contenedor-tabla-${seccion}`).classList.add('d-none');
        document.getElementById(`formulario-${seccion}`).classList.replace('d-none', 'd-block');
        
        const form = document.querySelector(`#formulario-${seccion} form`);
        const titulo = document.getElementById(`titulo-form-${seccion}`);
        const inputAccion = document.getElementById(`accion-form-${seccion}`);
        const inputId = document.getElementById(`id-form-${seccion}`);

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = seccion === 'carreras' ? '<i class="fas fa-plus-circle me-2"></i>Crear Nueva Carrera' : '<i class="fas fa-plus-circle me-2"></i>Crear Nueva Materia';
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id;
            titulo.innerHTML = seccion === 'carreras' ? '<i class="fas fa-edit me-2"></i>Editar Carrera' : '<i class="fas fa-edit me-2"></i>Editar Materia';
            
            if (seccion === 'carreras') {
                document.getElementById('nombre-carrera').value = datos.nombre;
            } else {
                document.getElementById('nombre-materia').value = datos.nombre;
                document.getElementById('id-carrera-materia').value = datos.carrera;
            }
        }
    }

    function cerrarFormularioOferta(seccion) {
        document.getElementById(`formulario-${seccion}`).classList.replace('d-block', 'd-none');
        document.getElementById(`contenedor-tabla-${seccion}`).classList.remove('d-none');
        document.querySelector(`#formulario-${seccion} form`).reset();
    }

    // =====================================================================
    // 3. MOTOR DE FILTRADO EN VIVO (Buscador Predictivo y Filtro Cruzado)
    // =====================================================================
    document.addEventListener('DOMContentLoaded', function() {
        
        // A) Buscador Predictivo Simple para Carreras
        const buscadorCarreras = document.getElementById('buscadorCarreras');
        if(buscadorCarreras) {
            buscadorCarreras.addEventListener('keyup', function() {
                const texto = this.value.toLowerCase();
                const filas = document.querySelectorAll('.fila-carrera');
                
                filas.forEach(fila => {
                    const nombre = fila.querySelector('.texto-busqueda').textContent.toLowerCase();
                    if (nombre.includes(texto)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            });
        }

        // B) Filtro Cruzado Avanzado para Materias (Texto + Select Carrera)
        const buscadorMaterias = document.getElementById('buscadorMaterias');
        const filtroCarrerasSelect = document.getElementById('filtroCarreras');

        function filtrarMateriasCruzado() {
            if(!buscadorMaterias || !filtroCarrerasSelect) return;
            
            const textoBusqueda = buscadorMaterias.value.toLowerCase();
            const idCarreraFiltro = filtroCarrerasSelect.value; // "todas" o el ID numérico
            const filasMaterias = document.querySelectorAll('.fila-materia');

            filasMaterias.forEach(fila => {
                const nombreMateria = fila.querySelector('.texto-busqueda-mat').textContent.toLowerCase();
                const idCarreraFila = fila.getAttribute('data-carrera');

                // Condición 1: ¿Coincide el texto?
                const coincideTexto = nombreMateria.includes(textoBusqueda);
                
                // Condición 2: ¿Coincide el select de carrera?
                const coincideCarrera = (idCarreraFiltro === 'todas' || idCarreraFiltro === idCarreraFila);

                // Si cumple AMBAS, la mostramos. Si falla alguna, la ocultamos.
                if (coincideTexto && coincideCarrera) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        // Ejecutar el filtro cruzado cuando se escribe o se cambia el select
        if(buscadorMaterias) buscadorMaterias.addEventListener('keyup', filtrarMateriasCruzado);
        if(filtroCarrerasSelect) filtroCarrerasSelect.addEventListener('change', filtrarMateriasCruzado);
    });
</script>