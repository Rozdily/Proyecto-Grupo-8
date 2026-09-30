<?php
/**
 * ARCHIVO: views/admin/cajon5/partials/reportes_rendimiento.php
 * Interfaz para el Dashboard Analítico de Rendimiento Académico y Tutorías.
 * Adaptado para cargar periodos dinámicamente mediante PeriodoModel.
 */

// 1. Cargar dependencias
require_once __DIR__ . '/../../../../config/conexion.php';
require_once __DIR__ . '/../../../../models/sistema/ReporteModel/index.php';
// NUEVO: Importar modelo de periodos
require_once __DIR__ . '/../../../../models/estructuras/PeriodoModel/index.php';

// 2. Instanciar modelos
$periodoModel = new PeriodoModel($pdo);
$reporteModel = new ReporteModel($pdo);

// 3. Obtener lista de periodos activos para el selector
$lista_periodos = [];
try {
    $lista_periodos =$periodoModel->listarTodos();
} catch (Exception $e) {
    // Si falla, se queda vacío
}

// 4. Determinar qué periodo mostrar (por defecto, el primero de la lista o ID 1)
$id_periodo_seleccionado =$_GET['id_periodo'] ?? (!empty($lista_periodos) ?$lista_periodos[0]['id_periodo'] : 1);

// Encontrar el nombre del periodo para mostrarlo en el título
$nombre_periodo_actual = 'Actual';
foreach($lista_periodos as$per) {
    if($per['id_periodo'] ==$id_periodo_seleccionado) {
        $nombre_periodo_actual =$per['codigo'];
        break;
    }
}

// 5. Obtener datos reales de la BD pasando el ID numérico
try {
    $datos_dashboard = $reporteModel->obtenerDatosDashboard($id_periodo_seleccionado);
} catch (Exception $e) {$datos_dashboard = [];
}

// 6. Si la base de datos devuelve vacío para ese periodo, cargamos ceros para evitar errores visuales
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
            <h6 class="mb-0 text-dark fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Dashboard de Rendimiento (Periodo <?php echo htmlspecialchars($nombre_periodo_actual); ?>)</h6>
        </div>
        <div class="col-md-6 d-flex justify-content-end gap-2">
            <!-- Formulario dinámico para el filtro -->
            <form action="" method="GET" class="d-flex gap-2 m-0" id="form-filtro-periodo">
                <input type="hidden" name="seccion" value="cajon5">
                <input type="hidden" name="tab" value="reportes">
                <select class="form-select form-select-sm border-plano w-auto fw-bold text-institucional" name="id_periodo" id="filtro-periodo" onchange="document.getElementById('form-filtro-periodo').submit();">
                    <?php if (!empty($lista_periodos)): ?>
                        <?php foreach($lista_periodos as$per): ?>
                            <option value="<?php echo $per['id_periodo']; ?>" <?php echo $id_periodo_seleccionado ==$per['id_periodo'] ? 'selected' : ''; ?>>
                                Periodo <?php echo htmlspecialchars($per['codigo']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">Sin periodos registrados</option>
                    <?php endif; ?>
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

    function generarPaletaDinamica(cantidad) {
        const paleta = [];
        const coloresBase = [
            '#1a3b5c', '#2c5b8e', '#e28743', '#4180c5', '#198754', 
            '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'
        ];
        
        for (let i = 0; i < cantidad; i++) {
            if (i < coloresBase.length) {
                paleta.push(coloresBase[i]);
            } else {
                const hue = Math.floor((i * 137.508) % 360);
                paleta.push(`hsl(${hue}, 70%, 50%)`);
            }
        }
        return paleta;
    }

    function renderizarGrafico(canvasId, contenedorId, dataLabels, initChartCallback) {
        const canvas = document.getElementById(canvasId);
        const contenedor = document.getElementById(contenedorId);

        if (dataLabels && dataLabels.length > 0) {
            initChartCallback(canvas);
        } else {
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