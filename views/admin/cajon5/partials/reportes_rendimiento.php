<?php
/**
 * ARCHIVO: views/admin/cajon5/partials/reportes_rendimiento.php
 * Interfaz para el Dashboard Analítico de Rendimiento Académico y Tutorías.
 */

// 1. Cargar dependencias directamente usando tu ruta raíz confirmada
require_once __DIR__ . '/../../../../config/conexion.php';
require_once __DIR__ . '/../../../../models/sistema/ReporteModel/index.php';

// 2. Obtener el periodo seleccionado en el selector (o usar I-2026 por defecto)
$periodo_seleccionado =$_GET['periodo'] ?? 'I-2026';

// 3. Instanciar el modelo y obtener datos reales de tu BD
$reporteModel = new ReporteModel($pdo);$datos_dashboard = $reporteModel->obtenerDatosDashboard($periodo_seleccionado);

// 4. Si la base de datos devuelve vacío para ese periodo, cargamos ceros para evitar errores visuales
if (empty($datos_dashboard) || empty($datos_dashboard['kpis']['total_sesiones'])) {$datos_dashboard = [
        'kpis' => [
            'total_sesiones' => 0,
            'horas_dictadas' => 0,
            'asistencia_pct' => 0,
            'satisfaccion_promedio' => 0,
            'alumnos_activos_mg' => 0
        ],
        'materias_demanda' => ['labels' => [], 'data' => []],
        'tutorias_estado' => ['labels' => [], 'data' => []],
        'distribucion_tutor' => ['labels' => [], 'data' => []],
        'sesiones_mes' => ['labels' => [], 'data' => []],
        'desempeno_docente' => ['labels' => [], 'data' => []]
    ];
}
?>

<!-- Importar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="seccion-reportes">
    
    <!-- BARRA DE FILTROS -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h6 class="mb-0 text-dark fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Dashboard de Rendimiento (Periodo <?php echo htmlspecialchars($periodo_seleccionado); ?>)</h6>
        </div>
        <div class="col-md-6 d-flex justify-content-end gap-2">
            <!-- Formulario para el filtro -->
            <form action="" method="GET" class="d-flex gap-2 m-0" id="form-filtro-periodo">
                <input type="hidden" name="seccion" value="cajon5">
                <input type="hidden" name="tab" value="reportes">
                <select class="form-select form-select-sm border-plano w-auto" name="periodo" id="filtro-periodo" onchange="document.getElementById('form-filtro-periodo').submit();">
                    <option value="I-2026" <?php echo $periodo_seleccionado == 'I-2026' ? 'selected' : ''; ?>>Periodo I-2026</option>
                    <option value="II-2026" <?php echo $periodo_seleccionado == 'II-2026' ? 'selected' : ''; ?>>Periodo II-2026</option>
                </select>
            </form>
            <button class="btn btn-sm btn-outline-secondary border-plano" onclick="window.print()"><i class="fas fa-print me-1"></i> Exportar PDF</button>
        </div>
    </div>

    <!-- TARJETAS DE INDICADORES (KPIs) -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm border-plano border-bottom border-4 border-primary h-100">
                <div class="card-body text-center p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Sesiones Totales</div>
                    <h3 class="mb-0 text-dark fw-bold"><?php echo $datos_dashboard['kpis']['total_sesiones']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm border-plano border-bottom border-4 border-info h-100">
                <div class="card-body text-center p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Horas Dictadas</div>
                    <h3 class="mb-0 text-dark fw-bold"><?php echo $datos_dashboard['kpis']['horas_dictadas']; ?>h</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm border-plano border-bottom border-4 border-success h-100">
                <div class="card-body text-center p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Asistencia Estudiantil</div>
                    <h3 class="mb-0 text-dark fw-bold"><?php echo $datos_dashboard['kpis']['asistencia_pct']; ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm border-plano border-bottom border-4 border-warning h-100">
                <div class="card-body text-center p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Satisfacción Promedio</div>
                    <h3 class="mb-0 text-dark fw-bold"><i class="fas fa-star text-warning me-1" style="font-size: 18px;"></i><?php echo $datos_dashboard['kpis']['satisfaccion_promedio']; ?> / 5</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm border-plano border-bottom border-4 border-institucional h-100">
                <div class="card-body text-center p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Tesistas Activos</div>
                    <h3 class="mb-0 text-dark fw-bold"><?php echo $datos_dashboard['kpis']['alumnos_activos_mg']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE GRÁFICOS (Fila 1) -->
    <div class="row mb-4">
        <!-- Gráfico: Materias con Mayor Demanda -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-institucional me-2"></i>Materias con Mayor Demanda de Tutorías</h6>
                </div>
                <div class="card-body p-4" style="position: relative; height:300px;" id="contenedorMaterias">
                    <canvas id="chartMaterias"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico: Estado de Tutorías -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie text-institucional me-2"></i>Tutorías por Estado</h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="position: relative; height:300px;" id="contenedorEstado">
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE GRÁFICOS (Fila 2) -->
    <div class="row mb-4">
        <!-- Gráfico: Desempeño Docente -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chalkboard-teacher text-institucional me-2"></i>Desempeño Docente (Basado en Evaluaciones)</h6>
                </div>
                <div class="card-body p-4" style="position: relative; height:300px;" id="contenedorDesempeno">
                    <canvas id="chartDesempeno"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE GRÁFICOS (Fila 3) -->
    <div class="row mb-4">
        <!-- Gráfico: Distribución por Tutor -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users text-institucional me-2"></i>Distribución de Sesiones por Tutor</h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="position: relative; height:300px;" id="contenedorTutores">
                    <canvas id="chartTutores"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico: Sesiones por Mes -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm border-plano h-100">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-alt text-institucional me-2"></i>Cantidad de Sesiones por Mes</h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center" style="position: relative; height:300px;" id="contenedorMeses">
                    <canvas id="chartMeses"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const dbData = <?php echo json_encode($datos_dashboard); ?>;

    /**
     * FUNCIÓN 1: Generador Inteligente de Paletas de Colores
     * Garantiza colores únicos sin importar si hay 5, 10 o 100 elementos en el gráfico.
     */
    function generarPaletaDinamica(cantidad) {
        const paleta = [];
        // Base institucional principal (10 colores muy distintivos)
        const coloresBase = [
            '#1a3b5c', '#2c5b8e', '#e28743', '#4180c5', '#198754', 
            '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'
        ];
        
        for (let i = 0; i < cantidad; i++) {
            if (i < coloresBase.length) {
                // Usar colores predefinidos si estamos dentro de los primeros 10
                paleta.push(coloresBase[i]);
            } else {
                // Si la BD devuelve más de 10 datos, generar colores únicos proceduralmente 
                // usando el "Ángulo Dorado" (137.5 grados) para separarlos drásticamente en la rueda HSL
                const hue = Math.floor((i * 137.508) % 360);
                paleta.push(`hsl(${hue}, 70%, 50%)`);
            }
        }
        return paleta;
    }

    /**
     * FUNCIÓN 2: Validador y Renderizador de Gráficos (Empty States)
     * Si no hay datos, oculta el canvas y pinta un mensaje elegante en su lugar.
     */
    function renderizarGrafico(canvasId, contenedorId, dataLabels, initChartCallback) {
        const canvas = document.getElementById(canvasId);
        const contenedor = document.getElementById(contenedorId);

        if (dataLabels && dataLabels.length > 0) {
            // Hay datos: Llamamos a la función constructora del gráfico
            initChartCallback(canvas);
        } else {
            // No hay datos: Mostramos el Empty State formal
            canvas.style.display = 'none';
            contenedor.innerHTML = `
                <div class="d-flex flex-column h-100 align-items-center justify-content-center text-muted">
                    <i class="fas fa-chart-line fa-2x mb-3 opacity-25"></i>
                    <span class="small fw-bold text-uppercase">Información no disponible</span>
                    <span class="small mt-1 text-center px-3">No hay suficientes registros en este periodo para generar la gráfica.</span>
                </div>
            `;
        }
    }

    // ==========================================
    // RENDERIZADO DE GRÁFICOS
    // ==========================================

    // 1. Gráfico de Barras: Materias
    renderizarGrafico('chartMaterias', 'contenedorMaterias', dbData.materias_demanda.labels, function(ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dbData.materias_demanda.labels,
                datasets: [{
                    label: 'Número de Solicitudes',
                    data: dbData.materias_demanda.data,
                    backgroundColor: generarPaletaDinamica(dbData.materias_demanda.labels.length),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    });

    // 2. Gráfico de Torta: Estado (Aquí los estados son fijos, pero usamos generador por seguridad)
    renderizarGrafico('chartEstado', 'contenedorEstado', dbData.tutorias_estado.labels, function(ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: dbData.tutorias_estado.labels,
                datasets: [{
                    data: dbData.tutorias_estado.data,
                    backgroundColor: generarPaletaDinamica(dbData.tutorias_estado.labels.length),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } },
                cutout: '60%'
            }
        });
    });

    // 3. Gráfico de Barras: Desempeño
    renderizarGrafico('chartDesempeno', 'contenedorDesempeno', dbData.desempeno_docente.labels, function(ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dbData.desempeno_docente.labels,
                datasets: [{
                    label: 'Calificación Promedio (1-5)',
                    data: dbData.desempeno_docente.data,
                    backgroundColor: generarPaletaDinamica(dbData.desempeno_docente.labels.length),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5, ticks: { stepSize: 1 } } }
            }
        });
    });

    // 4. Gráfico de Torta: Tutores
    renderizarGrafico('chartTutores', 'contenedorTutores', dbData.distribucion_tutor.labels, function(ctx) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: dbData.distribucion_tutor.labels,
                datasets: [{
                    data: dbData.distribucion_tutor.data,
                    backgroundColor: generarPaletaDinamica(dbData.distribucion_tutor.labels.length),
                    borderWidth: 1, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    });

    // 5. Gráfico de Torta: Meses
    renderizarGrafico('chartMeses', 'contenedorMeses', dbData.sesiones_mes.labels, function(ctx) {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: dbData.sesiones_mes.labels,
                datasets: [{
                    data: dbData.sesiones_mes.data,
                    backgroundColor: generarPaletaDinamica(dbData.sesiones_mes.labels.length),
                    borderWidth: 1, borderColor: '#fff'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    });
});
</script>