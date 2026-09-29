<?php
/**
 * ARCHIVO: views/admin/cajon6/partials/mg_importar.php
 * Interfaz interactiva y moderna (UI Premium) para la importación masiva desde SATS.
 */
?>
<style>
    /* UI Premium SATS */
    .dropzone-premium {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background-color: #f8fafc;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dropzone-premium:hover, .dropzone-premium.dragover {
        border-color: #0ea5e9;
        background-color: #f0f9ff;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.15);
    }
    .stat-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .bg-soft-success { background-color: #d1e7dd; color: #0f5132; }
    .bg-soft-warning { background-color: #fff3cd; color: #664d03; }
    .bg-soft-danger { background-color: #f8d7da; color: #842029; }
    
    .table-modern { border-collapse: separate; border-spacing: 0 6px; }
    .table-modern thead th {
        border-bottom: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #64748b;
        background: transparent;
        padding-bottom: 10px;
    }
    .table-modern tbody tr {
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .table-modern tbody td {
        border-top: 1px solid #f8fafc;
        border-bottom: 1px solid #f8fafc;
        vertical-align: middle;
        font-size: 0.9rem;
        color: #334155;
    }
    .table-modern tbody td:first-child {
        border-left: 1px solid #f8fafc;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .table-modern tbody td:last-child {
        border-right: 1px solid #f8fafc;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
</style>

<div id="seccion-sats-importar" class="d-block animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="text-dark fw-bold mb-1"><i class="fas fa-cloud-upload-alt text-institucional me-2"></i>Importación de Padrones SATS</h5>
            <p class="text-muted mb-0">Arrastra tu archivo para verificar y sincronizar expedientes universitarios.</p>
        </div>
    </div>

    <!-- ZONA 1: DROPZONE -->
    <div class="card border-0 mb-4" id="tarjeta-dropzone">
        <div class="card-body p-5 text-center dropzone-premium" id="dropzone-area" style="cursor: pointer;">
            <div class="mb-4">
                <i class="fas fa-file-csv fa-4x text-secondary" style="opacity: 0.7;"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Arrastra el archivo CSV aquí</h4>
            <p class="text-muted mb-4">O haz clic para explorar en tu computadora. Solo archivos válidos del SATS.</p>
            
            <input type="file" id="archivoCsv" accept=".csv" class="d-none">
            
            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-institucional border-plano px-4 shadow-sm" onclick="document.getElementById('archivoCsv').click();">
                    <i class="fas fa-folder-open me-2"></i>Seleccionar Archivo
                </button>
                <a href="../../controllers/SatsController.php?accion=descargar_plantilla" class="btn btn-light border-plano shadow-sm text-dark border" id="btnDescargarPlantilla">
                    <i class="fas fa-download me-2 text-success"></i>Descargar Plantilla
                </a>
            </div>
        </div>
    </div>

    <!-- ZONA 2: PREVISUALIZACIÓN Y RESULTADOS -->
    <div id="zona-previsualizacion" class="d-none animate__animated animate__fadeInUp">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-soft-success shadow-sm">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="bg-white p-3 rounded-circle me-3 shadow-sm">
                            <i class="fas fa-check-circle fa-lg text-success"></i>
                        </div>
                        <div>
                            <small class="text-uppercase fw-bold text-success opacity-75" style="font-size: 0.7rem;">Listos para importar</small>
                            <h3 class="mb-0 fw-bold" id="resumen-validos">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-soft-warning shadow-sm">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="bg-white p-3 rounded-circle me-3 shadow-sm">
                            <i class="fas fa-exclamation-triangle fa-lg text-warning"></i>
                        </div>
                        <div>
                            <small class="text-uppercase fw-bold text-warning opacity-75" style="font-size: 0.7rem;">Omitidos (Ya existen)</small>
                            <h3 class="mb-0 fw-bold" id="resumen-advertencias">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-soft-danger shadow-sm">
                    <div class="card-body d-flex align-items-center p-3">
                        <div class="bg-white p-3 rounded-circle me-3 shadow-sm">
                            <i class="fas fa-times-circle fa-lg text-danger"></i>
                        </div>
                        <div>
                            <small class="text-uppercase fw-bold text-danger opacity-75" style="font-size: 0.7rem;">Errores Estructurales</small>
                            <h3 class="mb-0 fw-bold" id="resumen-errores">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-flex flex-column justify-content-center gap-2">
                <button class="btn btn-institucional border-plano w-100 fw-bold shadow" id="btnProcesarImportacion" disabled>
                    <i class="fas fa-cloud-upload-alt me-2"></i>Confirmar Importación
                </button>
                <button class="btn btn-light border-plano border w-100 btn-sm text-muted" id="btnCancelarImportacion">
                    <i class="fas fa-undo me-1"></i>Cancelar y subir otro
                </button>
            </div>
        </div>

        <div class="bg-light p-4 rounded-4 shadow-sm border">
            <h6 class="mb-3 fw-bold text-secondary"><i class="fas fa-list me-2"></i>Vista previa de registros (Top 100)</h6>
            <div class="table-responsive" style="max-height: 50vh; overflow-y: auto;">
                <table class="table table-modern w-100" id="tablaPreviewSats">
                    <thead class="sticky-top bg-light">
                        <tr id="cabecera-preview">
                            <!-- Se llena dinámicamente -->
                        </tr>
                    </thead>
                    <tbody id="cuerpo-preview">
                        <!-- Se llena dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone-area');
    const inputCsv = document.getElementById('archivoCsv');
    const zonaPrevisualizacion = document.getElementById('zona-previsualizacion');
    const tarjetaDropzone = document.getElementById('tarjeta-dropzone');
    const btnProcesar = document.getElementById('btnProcesarImportacion');
    const btnCancelar = document.getElementById('btnCancelarImportacion');
    
    let archivoParaProcesar = null;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, prevenirDefaults, false);
    });

    function prevenirDefaults(e) {
        e.preventDefault(); e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => manejarArchivo(e.dataTransfer.files[0]), false);
    inputCsv.addEventListener('change', (e) => manejarArchivo(e.target.files[0]));

    function manejarArchivo(file) {
        if (!file || (!file.name.endsWith('.csv') && file.type !== 'text/csv' && file.type !== 'application/vnd.ms-excel')) {
            alert('Por favor, selecciona un archivo CSV válido.');
            return;
        }
        archivoParaProcesar = file;
        enviarPrevisualizacion(file);
    }

    function enviarPrevisualizacion(file) {
        dropzone.innerHTML = `
            <div class="py-4">
                <div class="spinner-border text-institucional mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="fw-bold text-dark">Analizando estructura...</h5>
                <p class="text-muted">Procesando y validando celdas del archivo.</p>
            </div>`;
        
        let formData = new FormData();
        formData.append('archivo_csv', file);
        formData.append('accion', 'previsualizar');

        fetch('../../controllers/SatsController.php', { method: 'POST', body: formData })
        .then(response => {
            if (!response.ok) throw new Error("Error HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            if(data.error_critico) {
                dropzone.style.borderColor = '#dc3545';
                dropzone.style.backgroundColor = '#fff5f5';
                dropzone.innerHTML = `
                    <div class="py-4">
                        <i class="fas fa-shield-alt fa-4x text-danger mb-3"></i>
                        <h4 class="fw-bold text-danger">Importación Bloqueada</h4>
                        <p class="text-dark mb-4">${data.error_critico}</p>
                        <button class="btn btn-outline-danger border-plano rounded-pill px-4" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>Reintentar
                        </button>
                    </div>`;
                return;
            }
            if(data.error) { alert(data.error); reiniciarDropzone(); return; }
            renderizarPreview(data);
        })
        .catch(error => {
            console.error(error);
            alert("Error de conexión al analizar el archivo.");
            reiniciarDropzone();
        });
    }

    function renderizarPreview(data) {
        tarjetaDropzone.classList.add('d-none');
        zonaPrevisualizacion.classList.remove('d-none');

        document.getElementById('resumen-validos').innerText = data.resumen.validos;
        document.getElementById('resumen-advertencias').innerText = data.resumen.advertencias;
        document.getElementById('resumen-errores').innerText = data.resumen.errores;

        if(data.resumen.validos > 0) btnProcesar.disabled = false;

        let htmlCabecera = '<th class="text-center" style="width: 120px;">Estado</th>';
        data.cabeceras.forEach(col => { htmlCabecera += `<th>${col}</th>`; });
        htmlCabecera += '<th>Diagnóstico del Sistema</th>';
        document.getElementById('cabecera-preview').innerHTML = htmlCabecera;

        let htmlFilas = '';
        data.filas.forEach(fila => {
            let badge = '';
            let borderStyle = '';
            
            // Reemplazando color completo por linea lateral y badge moderno
            if (fila.estado === 'valido') {
                badge = '<span class="badge bg-success rounded-pill px-3 py-2 w-100 shadow-sm"><i class="fas fa-check me-1"></i>Válido</span>';
                borderStyle = 'border-left: 4px solid #198754 !important;';
            } else if (fila.estado === 'advertencia') {
                badge = '<span class="badge bg-warning text-dark rounded-pill px-3 py-2 w-100 shadow-sm"><i class="fas fa-exclamation-triangle me-1"></i>Omitido</span>';
                borderStyle = 'border-left: 4px solid #ffc107 !important;';
            } else {
                badge = '<span class="badge bg-danger rounded-pill px-3 py-2 w-100 shadow-sm"><i class="fas fa-times me-1"></i>Error</span>';
                borderStyle = 'border-left: 4px solid #dc3545 !important;';
            }

            htmlFilas += `<tr>
                <td class="text-center p-3" style="${borderStyle}">${badge}</td>`;
            
            fila.datos.forEach(celda => {
                htmlFilas += `<td class="p-3 text-truncate" style="max-width: 150px;" title="${celda}">${celda}</td>`;
            });
            
            htmlFilas += `<td class="p-3 text-muted fst-italic">${fila.mensaje}</td></tr>`;
        });

        document.getElementById('cuerpo-preview').innerHTML = htmlFilas;
    }

    btnCancelar.addEventListener('click', reiniciarDropzone);

    function reiniciarDropzone() {
        archivoParaProcesar = null;
        inputCsv.value = '';
        zonaPrevisualizacion.classList.add('d-none');
        tarjetaDropzone.classList.remove('d-none');
        btnProcesar.disabled = true;
        
        dropzone.className = 'card-body p-5 text-center dropzone-premium';
        dropzone.style = 'cursor: pointer;';
        dropzone.innerHTML = `
            <div class="mb-4"><i class="fas fa-file-csv fa-4x text-secondary" style="opacity: 0.7;"></i></div>
            <h4 class="fw-bold text-dark mb-2">Arrastra el archivo CSV aquí</h4>
            <p class="text-muted mb-4">O haz clic para explorar en tu computadora. Solo archivos válidos del SATS.</p>
            <div class="d-flex justify-content-center gap-3">
                <button class="btn btn-institucional border-plano px-4 shadow-sm" onclick="document.getElementById('archivoCsv').click();">
                    <i class="fas fa-folder-open me-2"></i>Seleccionar Archivo
                </button>
                <a href="../../controllers/SatsController.php?accion=descargar_plantilla" class="btn btn-light border-plano shadow-sm text-dark border">
                    <i class="fas fa-download me-2 text-success"></i>Descargar Plantilla
                </a>
            </div>`;
    }

    btnProcesar.addEventListener('click', function() {
        if(!archivoParaProcesar) return;
        if(!confirm("¿Estás seguro de sincronizar los padrones? Se insertarán los alumnos válidos.")) return;

        btnProcesar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Inyectando...';
        btnProcesar.disabled = true;
        btnCancelar.disabled = true;

        let formData = new FormData();
        formData.append('archivo_csv', archivoParaProcesar);
        formData.append('accion', 'importar');

        fetch('../../controllers/SatsController.php', { method: 'POST', body: formData })
        .then(response => {
            if (!response.ok) throw new Error("Error HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            if(data.exito) {
                alert(`¡Éxito! Se sincronizaron ${data.insertados} expedientes universitarios.`);
                window.location.reload(); 
            } else {
                alert("Fallo en la importación: " + data.error);
                btnProcesar.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Confirmar Importación';
                btnProcesar.disabled = false;
                btnCancelar.disabled = false;
            }
        })
        .catch(error => {
            console.error(error);
            alert("Error de conexión al procesar.");
            btnProcesar.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Confirmar Importación';
            btnProcesar.disabled = false;
            btnCancelar.disabled = false;
        });
    });
});
</script>