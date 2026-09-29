<?php
/**
 * ARCHIVO: views/admin/cajon2/partials/tab_expedientes.php
 * Interfaz SPA para Tablero de Seguimiento de Expedientes de Tesis.
 * Incluye filtros de fase en bloque y buscador cruzado en vivo.
 */

// Estas variables vendrán del index.php principal del Cajón 2
$expedientes = $expedientes ?? [];
$lista_estudiantes = $lista_estudiantes ?? [];
$lista_cohortes = $lista_cohortes ?? [];
$lista_modalidades = $lista_modalidades ?? [];
?>

<div id="seccion-expedientes" class="d-block">
    
    <!-- CONTENEDOR DE LA TABLA -->
    <div id="contenedor-tabla-expedientes">
        
        <!-- TOOLBAR: Título, Filtros de Fase, Buscador y Botón Nuevo -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-folder-open me-2"></i>Expedientes de Tesis</h6>
            </div>
            <div class="col-md-9 d-flex justify-content-end align-items-center gap-2 flex-wrap">
                
                <!-- Botones de Sub-filtros por Fase (JavaScript) -->
                <div class="btn-group shadow-sm" role="group" id="filtro-fases">
                    <button type="button" class="btn btn-sm btn-institucional border-plano btn-fase active" data-fase="todos">Todos</button>
                    <button type="button" class="btn btn-sm btn-outline-institucional border-plano btn-fase" data-fase="mg1">Perfil (MG1)</button>
                    <button type="button" class="btn btn-sm btn-outline-institucional border-plano btn-fase" data-fase="mg2">Borrador (MG2)</button>
                    <button type="button" class="btn btn-sm btn-outline-institucional border-plano btn-fase" data-fase="finalizado">Finalizado</button>
                </div>

                <!-- Buscador Predictivo -->
                <div class="input-group input-group-sm w-auto shadow-sm" style="max-width: 200px;">
                    <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="buscadorExpedientes" class="form-control border-plano" placeholder="Buscar alumno o título...">
                </div>

                <!-- Botón Nuevo -->
                <button class="btn btn-sm btn-success border-plano shadow-sm" onclick="abrirFormularioExpedientes('nuevo')">
                    <i class="fas fa-plus me-1"></i> Nuevo
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano mb-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle" id="tablaExpedientes">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Estudiante / Título</th>
                            <th>Modalidad / Cohorte</th>
                            <th>Fase Actual</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expedientes)): ?>
                            <?php foreach ($expedientes as $e): ?>
                                <!-- NOTA: Inyectamos data-fase para el filtro cruzado JS -->
                                <tr class="fila-expediente" data-fase="<?php echo $e['etapa_actual']; ?>">
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $e['id_expediente']; ?></td>
                                    
                                    <!-- Texto para cruzar en el buscador -->
                                    <td class="texto-busqueda-exp">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($e['nombre_estudiante']); ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($e['titulo_trabajo']); ?>">
                                            <?php echo htmlspecialchars($e['titulo_trabajo']); ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($e['nombre_modalidad']); ?></div>
                                        <div class="small text-institucional"><?php echo htmlspecialchars($e['nombre_cohorte']); ?></div>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        $fase_color = 'bg-secondary';
                                        if ($e['etapa_actual'] == 'mg1') $fase_color = 'bg-primary';
                                        if ($e['etapa_actual'] == 'mg2') $fase_color = 'bg-warning text-dark';
                                        if ($e['etapa_actual'] == 'finalizado') $fase_color = 'bg-success';
                                        ?>
                                        <span class="badge <?php echo $fase_color; ?> border-plano fw-normal">
                                            <?php echo strtoupper($e['etapa_actual']); ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                        $estado_color = 'text-success';
                                        if ($e['estado'] == 'reprobado' || $e['estado'] == 'abandono') $estado_color = 'text-danger';
                                        ?>
                                        <span class="small fw-bold <?php echo $estado_color; ?>"><i class="fas fa-circle ms-1" style="font-size: 8px;"></i> <?php echo ucfirst($e['estado']); ?></span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light border border-plano me-1" 
                                                onclick="abrirFormularioExpedientes('editar', {
                                                    id: '<?php echo $e['id_expediente']; ?>',
                                                    id_estudiante: '<?php echo $e['id_estudiante']; ?>',
                                                    id_modalidad: '<?php echo $e['id_modalidad']; ?>',
                                                    id_cohorte: '<?php echo $e['id_cohorte']; ?>',
                                                    etapa: '<?php echo $e['etapa_actual']; ?>',
                                                    estado: '<?php echo $e['estado']; ?>',
                                                    titulo: '<?php echo addslashes($e['titulo_trabajo']); ?>',
                                                    inicio: '<?php echo $e['fecha_inicio']; ?>',
                                                    cierre: '<?php echo $e['fecha_cierre']; ?>',
                                                    obs: '<?php echo addslashes(str_replace(array("\r", "\n"), ' ', $e['observaciones'])); ?>'
                                                })" title="Editar Expediente">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>
                                        <a href="../../controllers/ExpedienteController.php?accion=eliminar&id=<?php echo $e['id_expediente']; ?>" 
                                           class="btn btn-sm btn-light border border-plano" 
                                           onclick="return confirm('¿Confirma que desea borrar este expediente? Se perderá todo el seguimiento del alumno.');" title="Borrar">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="fila-vacia-expedientes"><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-folder-open mb-3 d-block fa-2x"></i> No hay expedientes registrados.</td></tr>
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

    <!-- FORMULARIO IN-LINE: EXPEDIENTES -->
    <div id="formulario-expedientes" class="d-none">
        <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional">
            <div class="card-header bg-white border-bottom border-plano py-3">
                <h6 class="mb-0 text-institucional fw-bold" id="titulo-form-expedientes"><i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Expediente</h6>
            </div>
            <div class="card-body bg-light p-4">
                <form action="../../controllers/ExpedienteController.php" method="POST">
                    <input type="hidden" name="accion" id="accion-form-expedientes" value="crear">
                    <input type="hidden" name="id_registro" id="id-form-expedientes" value="">
                    
                    <div class="row">
                        <!-- Bloque 1: Vinculaciones -->
                        <div class="col-12 mb-2"><h6 class="text-muted small fw-bold border-bottom pb-1">Vínculos Académicos</h6></div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Estudiante / Egresado</label>
                            <select name="id_estudiante" id="exp-estudiante" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <?php foreach($lista_estudiantes as $est): ?>
                                    <option value="<?php echo $est['id_estudiante']; ?>"><?php echo htmlspecialchars($est['nombre_completo'] . ' (' . $est['registro_universitario'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Cohorte de Grado</label>
                            <select name="id_cohorte" id="exp-cohorte" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <?php foreach($lista_cohortes as $cohorte): ?>
                                    <option value="<?php echo $cohorte['id_cohorte']; ?>"><?php echo htmlspecialchars($cohorte['nombre'] . ' (' . $cohorte['codigo'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold">Modalidad de Grado</label>
                            <select name="id_modalidad" id="exp-modalidad" class="form-select form-select-sm border-plano" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <?php foreach($lista_modalidades as $mod): ?>
                                    <option value="<?php echo $mod['id_modalidad']; ?>"><?php echo htmlspecialchars($mod['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Bloque 2: Proyecto -->
                        <div class="col-12 mb-2 mt-2"><h6 class="text-muted small fw-bold border-bottom pb-1">Detalles del Proyecto</h6></div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">Título del Trabajo / Proyecto</label>
                            <input type="text" name="titulo_trabajo" id="exp-titulo" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fase / Etapa Actual</label>
                            <select name="etapa_actual" id="exp-etapa" class="form-select form-select-sm border-plano">
                                <option value="previa">Fase Previa</option>
                                <option value="mg1">Fase Perfil (MG1)</option>
                                <option value="mg2">Fase Borrador (MG2)</option>
                                <option value="finalizado">Finalizado / Titulado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Estado General</label>
                            <select name="estado" id="exp-estado" class="form-select form-select-sm border-plano">
                                <option value="activo">Activo (En Curso)</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="reprobado">Reprobado</option>
                                <option value="abandono">Abandono</option>
                                <option value="retirado">Retirado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" id="exp-inicio" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small fw-bold">Fecha de Cierre (Proyectada)</label>
                            <input type="date" name="fecha_cierre" id="exp-cierre" class="form-control form-control-sm border-plano" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">Observaciones / Notas</label>
                            <textarea name="observaciones" id="exp-obs" class="form-control form-control-sm border-plano" rows="2"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-secondary border-plano" onclick="cerrarFormularioExpedientes()">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-institucional border-plano"><i class="fas fa-save me-1"></i> Guardar Expediente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- LÓGICA JAVASCRIPT: CRUD IN-LINE Y FILTRO CRUZADO POR FASE EN BLOQUE -->
<!-- ======================================================================= -->
<script>
    // 1. Control de Formulario In-line
    function abrirFormularioExpedientes(accion, datos = null) {
        document.getElementById('contenedor-tabla-expedientes').classList.add('d-none');
        document.getElementById('formulario-expedientes').classList.replace('d-none', 'd-block');
        
        const form = document.querySelector('#formulario-expedientes form');
        const titulo = document.getElementById('titulo-form-expedientes');
        const inputAccion = document.getElementById('accion-form-expedientes');
        const inputId = document.getElementById('id-form-expedientes');

        if (accion === 'nuevo') {
            form.reset();
            inputAccion.value = 'crear';
            inputId.value = '';
            titulo.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Expediente';
        } else if (accion === 'editar' && datos) {
            inputAccion.value = 'actualizar';
            inputId.value = datos.id;
            titulo.innerHTML = '<i class="fas fa-edit me-2"></i>Editar Expediente de Tesis';
            
            // Llenar campos dinámicamente
            document.getElementById('exp-estudiante').value = datos.id_estudiante;
            document.getElementById('exp-cohorte').value = datos.id_cohorte;
            document.getElementById('exp-modalidad').value = datos.id_modalidad;
            document.getElementById('exp-titulo').value = datos.titulo;
            document.getElementById('exp-etapa').value = datos.etapa;
            document.getElementById('exp-estado').value = datos.estado;
            document.getElementById('exp-inicio').value = datos.inicio;
            document.getElementById('exp-cierre').value = datos.cierre;
            document.getElementById('exp-obs').value = datos.obs;
        }
    }

    function cerrarFormularioExpedientes() {
        document.getElementById('formulario-expedientes').classList.replace('d-block', 'd-none');
        document.getElementById('contenedor-tabla-expedientes').classList.remove('d-none');
        document.querySelector('#formulario-expedientes form').reset();
    }

    // 2. Filtros de Fase Cruzado con Buscador de Texto
    document.addEventListener('DOMContentLoaded', function() {
        let faseActiva = 'todos';
        const buscadorExp = document.getElementById('buscadorExpedientes');
        const botonesFase = document.querySelectorAll('.btn-fase');

        function aplicarFiltroCruzado() {
            const texto = buscadorExp.value.toLowerCase();
            const filas = document.querySelectorAll('.fila-expediente');

            filas.forEach(fila => {
                const textoFila = fila.querySelector('.texto-busqueda-exp').textContent.toLowerCase();
                const faseFila = fila.getAttribute('data-fase');

                // Condición 1: El texto coincide
                const coincideTexto = textoFila.includes(texto);
                // Condición 2: La fase coincide (o es "todos")
                const coincideFase = (faseActiva === 'todos' || faseActiva === faseFila);

                if (coincideTexto && coincideFase) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        // Listener para los botones en bloque
        botonesFase.forEach(btn => {
            btn.addEventListener('click', function() {
                // Quitar estilo activo a todos
                botonesFase.forEach(b => {
                    b.classList.remove('btn-institucional', 'active');
                    b.classList.add('btn-outline-institucional');
                });
                
                // Dar estilo activo al botón clickeado
                this.classList.remove('btn-outline-institucional');
                this.classList.add('btn-institucional', 'active');
                
                // Extraer fase y filtrar
                faseActiva = this.getAttribute('data-fase');
                aplicarFiltroCruzado();
            });
        });

        // Listener para la barra de búsqueda
        if(buscadorExp) {
            buscadorExp.addEventListener('keyup', aplicarFiltroCruzado);
        }
    });
</script>