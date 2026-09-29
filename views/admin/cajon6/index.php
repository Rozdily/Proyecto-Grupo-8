<?php
/**
 * ARCHIVO: views/admin/cajon6/partials/mg_importar.php
 * Interfaz interactiva (Dropzone) para la previsualización e importación masiva desde SATS.
 * NOTA: Es normal que no haya código PHP aquí, ya que todo el trabajo lo hace JavaScript (Fetch) y el Controlador.
 */
?>

<div id="seccion-sats-importar" class="d-block">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h6 class="text-dark mb-0 fw-bold"><i class="fas fa-cloud-upload-alt text-institucional me-2"></i>Importación de Padrones SATS</h6>
            <small class="text-muted">Carga archivos .csv para sincronizar expedientes y padrones estudiantiles.</small>
        </div>
    </div>

    <!-- ZONA 1: DROPZONE PARA CARGAR ARCHIVO -->
    <div class="card border-0 shadow-sm border-plano mb-4" id="tarjeta-dropzone">
        <div class="card-body p-5 text-center" id="dropzone-area" style="border: 2px dashed #ccc; border-radius: 8px; cursor: pointer; transition: 0.3s; background-color: #f8f9fa;">
            <i class="fas fa-file-csv fa-4x text-secondary mb-3"></i>
            <h5 class="fw-bold text-dark">Arrastra tu archivo CSV del SATS aquí</h5>
            <p class="text-muted mb-3">Descarga el molde, llénalo sin alterar las cabeceras y guárdalo como .CSV (Delimitado por comas).</p>
            
            <input type="file" id="archivoCsv" accept=".csv" class="d-none">
            
            <div class="d-flex justify-content-center gap-3 mt-4">
                <button class="btn btn-institucional border-plano" onclick="document.getElementById('archivoCsv').click();">
                    <i class="fas fa-folder-open me-1"></i> Seleccionar Archivo
                </button>
                
                <!-- BOTÓN: DESCARGAR PLANTILLA -->
                <a href="/tecnologiasweb/controllers/SatsController.php?accion=descargar_plantilla" class="btn btn-outline-success border-plano shadow-sm" id="btnDescargarPlantilla">
                    <i class="fas fa-file-excel me-1"></i> Descargar Plantilla de Referencia
                </a>
            </div>
            <div class="mt-3">
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Asegúrate de usar la plantilla oficial del sistema.</small>
            </div>
        </div>
    </div>

    <!-- ZONA 2: PREVISUALIZACIÓN Y RESULTADOS (Oculta por defecto) -->
    <div id="zona-previsualizacion" class="d-none">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-plano bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0 fw-bold" id="resumen-validos">0</h4>
                        <small>Filas Válidas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-plano bg-warning text-dark">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0 fw-bold" id="resumen-advertencias">0</h4>
                        <small>Advertencias</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-plano bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h4 class="mb-0 fw-bold" id="resumen-errores">0</h4>
                        <small>Errores Críticos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-flex flex-column justify-content-center">
                <button class="btn btn-institucional border-plano w-100 fw-bold mb-2 shadow-sm" id="btnProcesarImportacion" disabled>
                    <i class="fas fa-save me-1"></i> Confirmar Importación
                </button>
                <button class="btn btn-outline-secondary border-plano w-100 btn-sm" id="btnCancelarImportacion">
                    Cancelar
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-plano">
            <div class="card-header bg-dark text-white border-bottom border-plano py-2">
                <h6 class="mb-0 fw-bold"><i class="fas fa-search me-2"></i>Previsualización de Datos (Primeras 100 filas)</h6>
            </div>
            <div class="card-body p-0 table-responsive" style="max-height: 50vh; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;" id="tablaPreviewSats">
                    <thead class="bg-light sticky-top">
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
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.style.backgroundColor = '#e9ecef';
            dropzone.style.borderColor = '#1a3b5c';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.style.backgroundColor = '#f8f9fa';
            dropzone.style.borderColor = '#ccc';
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
        dropzone.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x text-institucional mb-3"></i><h5 class="fw-bold">Analizando archivo SATS...</h5><p>Comprobando estructura de columnas y datos...</p>';
        
        let formData = new FormData();
        formData.append('archivo_csv', file);
        formData.append('accion', 'previsualizar');

        fetch('/tecnologiasweb/controllers/SatsController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error("Error HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            if(data.error_critico) {
                dropzone.style.borderColor = '#dc3545';
                dropzone.style.backgroundColor = '#f8d7da';
                dropzone.innerHTML = `
                    <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                    <h5 class="fw-bold text-danger">Importación Bloqueada</h5>
                    <p class="text-dark fw-bold mb-4">${data.error_critico}</p>
                    <button class="btn btn-danger border-plano shadow-sm" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Entendido, volver a intentar
                    </button>
                `;
                return;
            }

            if(data.error) {
                alert("Error: " + data.error);
                reiniciarDropzone();
                return;
            }
            
            renderizarPreview(data);
        })
        .catch(error => {
            console.error('Error detallado:', error);
            alert("Error de conexión al analizar el archivo. Revisa que el SatsController.php exista y no tenga errores de PHP.");
            reiniciarDropzone();
        });
    }

    function renderizarPreview(data) {
        tarjetaDropzone.classList.add('d-none');
        zonaPrevisualizacion.classList.remove('d-none');

        document.getElementById('resumen-validos').innerText = data.resumen.validos;
        document.getElementById('resumen-advertencias').innerText = data.resumen.advertencias;
        document.getElementById('resumen-errores').innerText = data.resumen.errores;

        if(data.resumen.validos > 0) {
            btnProcesar.disabled = false;
        }

        let htmlCabecera = '<th>Estado</th>';
        data.cabeceras.forEach(col => { htmlCabecera += `<th>${col}</th>`; });
        htmlCabecera += '<th>Detalle del Sistema</th>';
        document.getElementById('cabecera-preview').innerHTML = htmlCabecera;

        let htmlFilas = '';
        data.filas.forEach(fila => {
            let claseFila = '';
            let icono = '';
            
            if (fila.estado === 'valido') {
                claseFila = 'table-success';
                icono = '<i class="fas fa-check-circle text-success"></i>';
            } else if (fila.estado === 'advertencia') {
                claseFila = 'table-warning';
                icono = '<i class="fas fa-exclamation-triangle text-warning"></i>';
            } else {
                claseFila = 'table-danger';
                icono = '<i class="fas fa-times-circle text-danger"></i>';
            }

            htmlFilas += `<tr class="${claseFila}">
                <td class="text-center align-middle">${icono}</td>`;
            
            fila.datos.forEach(celda => {
                htmlFilas += `<td class="align-middle">${celda}</td>`;
            });
            
            htmlFilas += `<td class="align-middle"><small class="fw-bold">${fila.mensaje}</small></td></tr>`;
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
        
        dropzone.style.backgroundColor = '#f8f9fa';
        dropzone.style.borderColor = '#ccc';
        dropzone.innerHTML = `
            <i class="fas fa-file-csv fa-4x text-secondary mb-3"></i>
            <h5 class="fw-bold text-dark">Arrastra tu archivo CSV del SATS aquí</h5>
            <p class="text-muted mb-3">O haz clic para explorar en tu computadora. Asegúrate de usar la plantilla oficial del sistema.</p>
            <div class="d-flex justify-content-center gap-3 mt-4">
                <button class="btn btn-institucional border-plano" onclick="document.getElementById('archivoCsv').click();">
                    <i class="fas fa-folder-open me-1"></i> Seleccionar Archivo
                </button>
                <a href="/tecnologiasweb/controllers/SatsController.php?accion=descargar_plantilla" class="btn btn-outline-success border-plano shadow-sm" id="btnDescargarPlantilla">
                    <i class="fas fa-file-excel me-1"></i> Descargar Plantilla de Referencia
                </a>
            </div>
            <div class="mt-3">
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Descarga el molde, llénalo sin alterar las cabeceras y guárdalo como .CSV (Delimitado por comas).</small>
            </div>
        `;
    }

    btnProcesar.addEventListener('click', function() {
        if(!archivoParaProcesar) return;

        if(!confirm("¿Estás seguro de inyectar estas filas en la base de datos? Solo se guardarán las filas marcadas como Válidas o con Advertencias leves.")) return;

        btnProcesar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';
        btnProcesar.disabled = true;
        btnCancelar.disabled = true;

        let formData = new FormData();
        formData.append('archivo_csv', archivoParaProcesar);
        formData.append('accion', 'importar');

        fetch('/tecnologiasweb/controllers/SatsController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error("Error HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            if(data.exito) {
                alert(`Importación exitosa. Se insertaron ${data.insertados} estudiantes.`);
                window.location.reload(); 
            } else {
                alert("Hubo un problema al importar: " + data.error);
                btnProcesar.innerHTML = '<i class="fas fa-save me-1"></i> Confirmar Importación';
                btnProcesar.disabled = false;
                btnCancelar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error detallado:', error);
            alert("Error de conexión al intentar importar los datos.");
            btnProcesar.innerHTML = '<i class="fas fa-save me-1"></i> Confirmar Importación';
            btnProcesar.disabled = false;
            btnCancelar.disabled = false;
        });
    });
});
</script>