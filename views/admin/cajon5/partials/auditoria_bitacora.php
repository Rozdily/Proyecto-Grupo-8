<?php
/**
 * ARCHIVO: views/admin/cajon5/partials/auditoria_bitacora.php
 * Interfaz de la Caja Negra con Paginación, Selección Múltiple (Shift+Click) y Scroll Infinito.
 */

$registros = $registros_bitacora ?? [];
?>

<div id="seccion-auditoria" class="d-block">
    <div class="row mb-3 align-items-center">
        <div class="col-md-5">
            <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-server text-institucional me-2"></i>Bitácora de Eventos y Seguridad</h6>
            <small class="text-muted">Registro inmutable de acciones en el sistema (Caja Negra).</small>
        </div>
        <div class="col-md-7 d-flex justify-content-end align-items-center gap-2">
            <!-- Buscador -->
            <div class="input-group input-group-sm w-50 shadow-sm">
                <span class="input-group-text bg-white border-plano"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="buscadorAuditoria" class="form-control border-plano" placeholder="Buscar usuario, correo, IP o tabla...">
            </div>
            <!-- Botón para activar Modo Selección -->
            <button class="btn btn-sm btn-outline-secondary border-plano shadow-sm" id="btnModoExportar">
                <i class="fas fa-list-check me-1"></i> Seleccionar / Exportar
            </button>
            <!-- Botón de Acción (Oculto por defecto) -->
            <button class="btn btn-sm btn-institucional border-plano shadow-sm d-none" id="btnExportarSeleccionados">
                <i class="fas fa-print me-1"></i> Imprimir Seleccionados
            </button>
        </div>
    </div>

    <!-- TABLA DE AUDITORÍA (Con contenedor para Scroll Infinito) -->
    <div class="card border-0 shadow-sm border-plano mb-3">
        <div class="card-body p-0 table-responsive" id="contenedorTablaAuditoria" style="max-height: 65vh; overflow-y: auto;">
            <table class="table table-hover table-striped mb-0 align-middle" id="tablaAuditoria" style="font-size: 0.85rem;">
                <thead class="bg-dark text-white text-uppercase sticky-top" style="z-index: 10;">
                    <tr>
                        <!-- Columna Checkbox (Oculta en modo normal) -->
                        <th class="col-checkbox d-none text-center" style="width: 50px;">
                            <input class="form-check-input" type="checkbox" id="checkAll" title="Seleccionar todos los filtrados">
                        </th>
                        <th class="ps-4">Fecha / Hora</th>
                        <th>IP Origen</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                        <th class="text-center col-detalles" style="width: 15%;">Detalle de Cambios</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaAuditoria">
                    <?php if (!empty($registros)): ?>
                        <?php foreach ($registros as $index =>$log): ?>
                            <tr class="fila-auditoria" data-index="<?php echo $index; ?>">
                                <!-- Casilla de Selección -->
                                <td class="col-checkbox d-none text-center">
                                    <input class="form-check-input check-row" type="checkbox" value="<?php echo htmlspecialchars($log['id']); ?>">
                                </td>

                                <td class="ps-4 text-nowrap">
                                    <div class="fw-bold text-dark"><i class="far fa-calendar-alt me-1 text-muted"></i> <?php echo date('d/m/Y', strtotime($log['fecha'])); ?></div>
                                    <div class="text-muted"><i class="far fa-clock me-1 text-muted"></i> <?php echo date('H:i:s', strtotime($log['fecha'])); ?></div>
                                </td>
                                
                                <td class="texto-busqueda-log text-nowrap">
                                    <span class="badge bg-light text-dark border"><i class="fas fa-network-wired text-muted me-1"></i> <?php echo htmlspecialchars($log['ip']); ?></span>
                                </td>
                                
                                <td class="texto-busqueda-log">
                                    <?php 
                                    $actor_raw =$log['usuario'];
                                    $nombre_u =$actor_raw;
                                    $correo_u = 'Sin correo';

                                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $actor_raw,$matches)) {
                                        $nombre_u = trim($matches[1]);
                                        $correo_u = trim($matches[2]);
                                    }
                                    
                                    if (empty($correo_u) || strpos($correo_u, 'sistema@') !== false || $correo_u === 'sin_correo') {$correo_u = 'Sin correo';
                                    }
                                    ?>
                                    <div class="fw-bold text-institucional">
                                        <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($nombre_u); ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="far fa-envelope me-1"></i><?php echo htmlspecialchars($correo_u); ?>
                                    </div>
                                </td>
                                
                                <td class="texto-busqueda-log text-nowrap">
                                    <?php 
                                        $badge_color = 'bg-secondary';
                                        $accion_str = strtolower($log['accion']);
                                        if (strpos($accion_str, 'crear') !== false)$badge_color = 'bg-success';
                                        elseif (strpos($accion_str, 'actualizar') !== false)$badge_color = 'bg-warning text-dark';
                                        elseif (strpos($accion_str, 'eliminar') !== false)$badge_color = 'bg-danger';
                                        elseif (strpos($accion_str, 'inicio') !== false || strpos($accion_str, 'login') !== false || strpos($accion_str, 'logout') !== false)$badge_color = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?php echo $badge_color; ?> mb-1 border-plano"><?php echo htmlspecialchars(strtoupper($log['accion'])); ?></span>
                                    <div class="text-muted fw-bold">Tabla: <?php echo htmlspecialchars($log['tabla']); ?></div>
                                    <div class="text-muted small">ID Fila: <?php echo htmlspecialchars($log['id_registro']); ?></div>
                                </td>

                                <td class="text-center col-detalles">
                                    <?php 
                                    $datos_antes = ($log['datos_antes'] !== '[]' && !empty($log['datos_antes'])) ?$log['datos_antes'] : '';
                                    $datos_despues = ($log['datos_despues'] !== '[]' && !empty($log['datos_despues'])) ?$log['datos_despues'] : '';
                                    ?>
                                    
                                    <?php if ($datos_antes ||$datos_despues): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary border-plano btn-ver-detalles" 
                                                data-id="<?php echo htmlspecialchars($log['id']); ?>"
                                                data-accion="<?php echo htmlspecialchars(strtoupper($log['accion'])); ?>"
                                                data-antes='<?php echo htmlspecialchars($datos_antes, ENT_QUOTES, 'UTF-8'); ?>' 
                                                data-despues='<?php echo htmlspecialchars($datos_despues, ENT_QUOTES, 'UTF-8'); ?>'
                                                data-bs-toggle="modal" data-bs-target="#modalDetalleAuditoria">
                                            <i class="fas fa-eye me-1"></i> Detalles
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small">Sin datos</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="fila-vacia-auditoria">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-shield-alt mb-3 d-block fa-2x text-institucional opacity-50"></i> 
                                No hay eventos registrados en la bitácora aún.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- CONTENEDOR DE PAGINACIÓN -->
    <div class="d-flex justify-content-between align-items-center mb-3" id="contenedorPaginacion">
        <small class="text-muted" id="infoPaginacion">Mostrando registros</small>
        <nav aria-label="Paginación">
            <ul class="pagination pagination-sm mb-0" id="paginacionAuditoria">
                <!-- Se llena por JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<!-- MODAL DINÁMICO (Sin cambios estructurales) -->
<div class="modal fade" id="modalDetalleAuditoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-plano shadow">
            <div class="modal-header bg-light border-bottom border-plano">
                <h5 class="modal-title text-dark fw-bold" id="modalTitulo"><i class="fas fa-code-compare text-institucional me-2"></i>Detalle de Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-white">
                <div class="row" id="contenedor-comparacion">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="text-danger fw-bold mb-2"><i class="fas fa-minus-circle me-1"></i>Estado Anterior (Fila Completa)</h6>
                        <pre id="json-antes" class="bg-dark text-light p-3 rounded shadow-sm border border-secondary" style="font-size: 0.85rem; max-height: 420px; overflow-y: auto;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success fw-bold mb-2"><i class="fas fa-plus-circle me-1"></i>Estado Nuevo (Fila Completa - Cambios Resaltados)</h6>
                        <pre id="json-despues" class="bg-dark text-light p-3 rounded shadow-sm border border-secondary" style="font-size: 0.85rem; max-height: 420px; overflow-y: auto;"></pre>
                    </div>
                </div>
                <div class="row d-none" id="contenedor-sesion">
                    <div class="col-12">
                        <h6 class="text-info fw-bold mb-3"><i class="fas fa-globe me-1"></i>Datos de Conexión Web</h6>
                        <div class="bg-light p-4 rounded border border-plano">
                            <ul class="list-group list-group-flush bg-transparent" id="lista-sesion">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top border-plano">
                <button type="button" class="btn btn-secondary border-plano btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-institucional border-plano btn-sm" id="btnDescargarJson">
                    <i class="fas fa-file-download me-1"></i> Descargar Registro (.txt)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Variables de Estado Globales
        const filasTotales = Array.from(document.querySelectorAll('.fila-auditoria'));
        let filasFiltradas = filasTotales;
        
        // Paginación Normal
        const filasPorPagina = 10;
        let paginaActual = 1;

        // Variables de Modo Selección / Scroll Infinito
        let isModoExportacion = false;
        let registrosVisiblesScroll = 0;
        const TAMAÑO_CARGA_SCROLL = 30; // Carga 30 registros nuevos cada vez que tocas fondo
        
        // ---------------------------------------------------------
        // 1. RENDERIZADO INTELIGENTE (Cambia según el modo)
        // ---------------------------------------------------------
        function renderizarTabla() {
            filasTotales.forEach(f => f.style.display = 'none');
            
            if (isModoExportacion) {
                // MODO SCROLL INFINITO
                const filasAMostrar = filasFiltradas.slice(0, registrosVisiblesScroll);
                filasAMostrar.forEach(f => f.style.display = '');
                
                const total = filasFiltradas.length;
                document.getElementById('infoPaginacion').innerText = `Selección Múltiple Activa: Mostrando ${Math.min(registrosVisiblesScroll, total)} de ${total} registros (Desliza para cargar más)`;
                
            } else {
                // MODO PAGINACIÓN CLÁSICA
                const inicio = (paginaActual - 1) * filasPorPagina;
                const fin = inicio + filasPorPagina;
                
                filasFiltradas.slice(inicio, fin).forEach(f => f.style.display = '');
                
                const total = filasFiltradas.length;
                document.getElementById('infoPaginacion').innerText = total > 0 ? `Mostrando ${inicio + 1} a ${Math.min(fin, total)} de ${total} registros` : 'No se encontraron registros';
                renderizarBotonesPaginacion(total);
            }
        }

        function renderizarBotonesPaginacion(totalRegistros) {
            const totalPaginas = Math.ceil(totalRegistros / filasPorPagina);
            const ul = document.getElementById('paginacionAuditoria');
            let html = '';
            
            if (totalPaginas > 1) {
                html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}"><a class="page-link text-dark" href="#" data-page="${paginaActual - 1}">Anterior</a></li>`;
                let startPage = Math.max(1, paginaActual - 2);
                let endPage = Math.min(totalPaginas, paginaActual + 2);
                
                for(let i = startPage; i <= endPage; i++) {
                    html += `<li class="page-item ${paginaActual === i ? 'active' : ''}">
                                <a class="page-link ${paginaActual === i ? 'bg-institucional text-white border-institucional' : 'text-dark'}" href="#" data-page="${i}">${i}</a>
                             </li>`;
                }
                html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}"><a class="page-link text-dark" href="#" data-page="${paginaActual + 1}">Siguiente</a></li>`;
            }
            ul.innerHTML = html;

            ul.querySelectorAll('.page-link').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const btnPage = parseInt(this.getAttribute('data-page'));
                    if(!isNaN(btnPage) && btnPage >= 1 && btnPage <= totalPaginas) {
                        paginaActual = btnPage;
                        renderizarTabla();
                    }
                });
            });
        }

        // Buscador
        const buscadorAuditoria = document.getElementById('buscadorAuditoria');
        if(buscadorAuditoria) {
            buscadorAuditoria.addEventListener('keyup', function() {
                const texto = this.value.toLowerCase();
                filasFiltradas = filasTotales.filter(fila => {
                    let contenidoFila = '';
                    fila.querySelectorAll('.texto-busqueda-log').forEach(celda => contenidoFila += celda.textContent.toLowerCase() + ' ');
                    return contenidoFila.includes(texto);
                });
                
                paginaActual = 1;
                // Si está en exportación, resetear el scroll a los primeros N elementos
                if(isModoExportacion) registrosVisiblesScroll = TAMAÑO_CARGA_SCROLL; 
                
                renderizarTabla();
                
                // Si busca algo y el "checkAll" está activado, se recomienda deseleccionarlo
                document.getElementById('checkAll').checked = false;
            });
        }
        
        // ---------------------------------------------------------
        // 2. LÓGICA DE SELECCIÓN Y EXPORTACIÓN
        // ---------------------------------------------------------
        
        // Activar/Desactivar Modo Selección
        document.getElementById('btnModoExportar').addEventListener('click', function() {
            isModoExportacion = !isModoExportacion;
            
            const btnExportarSel = document.getElementById('btnExportarSeleccionados');
            const contPaginacion = document.getElementById('paginacionAuditoria');
            const colChecks = document.querySelectorAll('.col-checkbox');
            
            if (isModoExportacion) {
                // Entrando a Modo Exportación
                this.innerHTML = '<i class="fas fa-times me-1"></i> Cancelar Selección';
                this.classList.replace('btn-outline-secondary', 'btn-danger');
                btnExportarSel.classList.remove('d-none');
                contPaginacion.classList.add('d-none');
                colChecks.forEach(c => c.classList.remove('d-none'));
                
                registrosVisiblesScroll = TAMAÑO_CARGA_SCROLL;
                renderizarTabla();
            } else {
                // Saliendo de Modo Exportación
                this.innerHTML = '<i class="fas fa-list-check me-1"></i> Seleccionar / Exportar';
                this.classList.replace('btn-danger', 'btn-outline-secondary');
                btnExportarSel.classList.add('d-none');
                contPaginacion.classList.remove('d-none');
                colChecks.forEach(c => c.classList.add('d-none'));
                
                // Limpiar checks
                document.getElementById('checkAll').checked = false;
                document.querySelectorAll('.check-row').forEach(cb => cb.checked = false);
                
                renderizarTabla();
            }
        });

        // Evento de Scroll Infinito
        const contenedorTabla = document.getElementById('contenedorTablaAuditoria');
        contenedorTabla.addEventListener('scroll', function() {
            if (!isModoExportacion) return;
            
            // Si llega al final del contenedor, cargar más registros
            if (contenedorTabla.scrollTop + contenedorTabla.clientHeight >= contenedorTabla.scrollHeight - 50) {
                if (registrosVisiblesScroll < filasFiltradas.length) {
                    registrosVisiblesScroll += TAMAÑO_CARGA_SCROLL;
                    renderizarTabla();
                }
            }
        });

        // Lógica: Seleccionar Todos los filtrados
        document.getElementById('checkAll').addEventListener('change', function(e) {
            const estado = e.target.checked;
            // Solo afectamos a las filas que superaron el filtro de búsqueda
            filasFiltradas.forEach(fila => {
                const cb = fila.querySelector('.check-row');
                if(cb) cb.checked = estado;
            });
        });

        // Lógica: Shift + Click
        let lastCheckedBox = null;
        document.getElementById('cuerpoTablaAuditoria').addEventListener('click', function(e) {
            if (e.target.classList.contains('check-row')) {
                if (e.shiftKey && lastCheckedBox) {
                    // Extraemos solo los checkboxes de las filas filtradas (estén visibles o no en el scroll)
                    let checkboxesFiltrados = filasFiltradas.map(f => f.querySelector('.check-row'));
                    
                    let start = checkboxesFiltrados.indexOf(lastCheckedBox);
                    let end = checkboxesFiltrados.indexOf(e.target);
                    
                    if (start !== -1 && end !== -1) {
                        let min = Math.min(start, end);
                        let max = Math.max(start, end);
                        for (let i = min; i <= max; i++) {
                            checkboxesFiltrados[i].checked = e.target.checked;
                        }
                    }
                }
                lastCheckedBox = e.target;
            }
        });

        // Acción de Imprimir/Exportar los Seleccionados
        document.getElementById('btnExportarSeleccionados').addEventListener('click', function() {
            // 1. Verificar si hay algo seleccionado
            const seleccionados = Array.from(document.querySelectorAll('.check-row')).filter(cb => cb.checked);
            if (seleccionados.length === 0) {
                alert("Debes seleccionar al menos un registro (checkbox) para exportar.");
                return;
            }

            // 2. Ocultar todas las filas temporalmente
            filasTotales.forEach(f => f.style.display = 'none');
            
            // 3. Mostrar estrictamente solo las filas marcadas
            seleccionados.forEach(cb => {
                cb.closest('tr').style.display = '';
            });

            // 4. Ocultar interfaz no deseada en el reporte (checks, botones)
            document.querySelectorAll('.col-checkbox, .col-detalles').forEach(el => el.style.display = 'none');
            
            // 5. Imprimir
            window.print();
            
            // 6. Restaurar interfaz
            document.querySelectorAll('.col-checkbox, .col-detalles').forEach(el => el.style.display = '');
            renderizarTabla(); // Devuelve la vista a como estaba antes de imprimir
        });

        // Inicializar
        renderizarTabla();

        // ---------------------------------------------------------
        // 3. LÓGICA DEL MODAL DE DETALLES Y DIFFING (JSON)
        // ---------------------------------------------------------
        let logIdActual = '';
        document.querySelectorAll('.btn-ver-detalles').forEach(boton => {
            boton.addEventListener('click', function() {
                logIdActual = this.getAttribute('data-id') || 'evento';
                const accion = this.getAttribute('data-accion') || '';
                const jsonAntesStr = this.getAttribute('data-antes');
                const jsonDespuesStr = this.getAttribute('data-despues');
                
                const contComparacion = document.getElementById('contenedor-comparacion');
                const contSesion = document.getElementById('contenedor-sesion');
                
                if (accion.includes('LOGIN') || accion.includes('LOGOUT')) {
                    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-sign-in-alt text-info me-2"></i>Información de Sesión';
                    contComparacion.classList.add('d-none');
                    contSesion.classList.remove('d-none');
                    
                    let datosSesion = {};
                    try { datosSesion = JSON.parse(jsonDespuesStr); } catch(e) {}
                    
                    let htmlSesion = '';
                    for (const [key, value] of Object.entries(datosSesion)) {
                        let icon = 'fa-info-circle';
                        if (key.includes('ip')) icon = 'fa-network-wired';
                        if (key.includes('navegador')) icon = 'fa-compass';
                        
                        htmlSesion += `
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-dark text-capitalize"><i class="fas ${icon} text-muted me-2"></i> ${key.replace(/_/g, ' ')}</span>
                                <span class="text-secondary font-monospace">${value}</span>
                            </li>
                        `;
                    }
                    document.getElementById('lista-sesion').innerHTML = htmlSesion || '<li class="list-group-item text-muted">Datos no disponibles.</li>';
                
                } else {
                    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-code-compare text-institucional me-2"></i>Comparación de Registro';
                    contComparacion.classList.remove('d-none');
                    contSesion.classList.add('d-none');
                    
                    let objAntes = {};
                    let objDespues = {};
                    try { objAntes = jsonAntesStr ? JSON.parse(jsonAntesStr) : {}; } catch(e) {}
                    try { objDespues = jsonDespuesStr ? JSON.parse(jsonDespuesStr) : {}; } catch(e) {}
                    
                    let htmlAntes = "{\n";
                    let htmlDespues = "{\n";
                    
                    const todasLasLlaves = new Set([...Object.keys(objAntes), ...Object.keys(objDespues)]);
                    
                    todasLasLlaves.forEach(key => {
                        let rawAntes = objAntes[key];
                        let rawDespues = objDespues[key];

                        if (rawDespues === undefined && rawAntes !== undefined) {
                            rawDespues = rawAntes; 
                        }

                        let strAntes = (rawAntes !== undefined && rawAntes !== null) ? String(rawAntes) : 'null';
                        let strDespues = (rawDespues !== undefined && rawDespues !== null) ? String(rawDespues) : 'null';

                        let dispAntes = rawAntes !== undefined ? JSON.stringify(rawAntes) : 'null';
                        let dispDespues = rawDespues !== undefined ? JSON.stringify(rawDespues) : 'null';

                        if (strAntes !== strDespues) {
                            htmlAntes += `    <span class="bg-danger bg-opacity-25 px-1 rounded">"${key}": ${dispAntes}</span>,\n`;
                            htmlDespues += `    <span class="bg-success bg-opacity-25 px-1 rounded">"${key}": ${dispDespues}</span>,\n`;
                        } else {
                            htmlAntes += `    "${key}": ${dispAntes},\n`;
                            htmlDespues += `    "${key}": ${dispDespues},\n`;
                        }
                    });
                    
                    document.getElementById('json-antes').innerHTML = htmlAntes.replace(/,\n$/g, '\n') + "}";
                    document.getElementById('json-despues').innerHTML = htmlDespues.replace(/,\n$/g, '\n') + "}";
                }
            });
        });

        document.getElementById('btnDescargarJson').onclick = function() {
            let contenidoArchivo = "=====================================================\n" +
                                   "UPDS - REPORTE DE EVENTO #" + logIdActual + "\n" +
                                   "=====================================================\n\n";
            
            if (!document.getElementById('contenedor-comparacion').classList.contains('d-none')) {
                contenidoArchivo += "[ESTADO ANTERIOR COMPLETO]\n" + document.getElementById('json-antes').innerText + "\n\n" +
                                    "[ESTADO NUEVO COMPLETO]\n" + document.getElementById('json-despues').innerText + "\n";
            } else {
                contenidoArchivo += "[DETALLES DE SESIÓN WEB]\n" + document.getElementById('lista-sesion').innerText + "\n";
            }
                
            const dataStr = "data:text/plain;charset=utf-8," + encodeURIComponent(contenidoArchivo);
            const enlace = document.createElement('a');
            enlace.href = dataStr;
            enlace.download = "auditoria_log_" + logIdActual + ".txt";
            document.body.appendChild(enlace); 
            enlace.click();
            enlace.remove();
        };
    });
</script>