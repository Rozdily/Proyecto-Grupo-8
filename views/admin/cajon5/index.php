<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ... aquí sigue el resto de tu código (require_once, etc.)
/**
 * ARCHIVO: views/admin/cajon5/index.php
 * Vista central del Cajón 5: Reportes Estadísticos y Bitácora de Auditoría.
 * Arquitectura SPA (Single Page Application).
 */

require_once __DIR__ . '/../../../config/conexion.php';

// Importación de los modelos
require_once __DIR__ . '/../../../models/sistema/ReporteModel/index.php';
require_once __DIR__ . '/../../../models/sistema/AuditoriaModel/index.php';

// Manejo de pestaña activa (por defecto inicia en 'reportes')
$tab_activo = $_GET['tab'] ?? 'reportes';
$mensaje_exito = $_GET['exito'] ?? '';
$mensaje_error = $_GET['error'] ?? '';

// Variables globales para alimentar las vistas
$datos_dashboard = [];
$registros_bitacora = [];

try {
    // Instanciar modelos
    $reporteModel = new ReporteModel($pdo);
    $auditoriaModel = new AuditoriaModel($pdo);

    // 1. Carga de datos reales para Reportes (Gráficos)
    $datos_dashboard = $reporteModel->obtenerDatosDashboard('II-2026'); // Se puede hacer dinámico por GET/POST

    // 2. Carga de datos reales para Auditoría (Caja Negra)
    $registros_bitacora = $auditoriaModel->listarHistorialCompleto();

} catch (Throwable $e) {
    $mensaje_error = "Error al cargar los módulos de análisis: " . $e->getMessage();
}
?>

<!-- ENCABEZADO DE LA SECCIÓN -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-light mb-0 text-dark">
            <i class="fas fa-chart-line text-institucional me-2"></i>Reportes y Auditoría
        </h2>
    </div>
    <div class="text-muted small">
        <i class="fas fa-home text-institucional"></i> Inicio / <span class="fw-bold">Inteligencia y Seguridad</span>
    </div>
</div>

<!-- ALERTAS DE SISTEMA -->
<?php if (!empty($mensaje_exito)): ?>
    <div class="alert alert-success border-0 border-start border-4 border-success border-plano shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <strong>¡Éxito!</strong> <?php echo htmlspecialchars($mensaje_exito); ?>
    </div>
<?php endif; ?>

<?php if (!empty($mensaje_error)): ?>
    <div class="alert alert-danger border-0 border-start border-4 border-danger border-plano shadow-sm mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Aviso:</strong> <?php echo htmlspecialchars($mensaje_error); ?>
    </div>
<?php endif; ?>

<!-- NAVEGACIÓN POR PESTAÑAS (BOOTSTRAP 5) -->
<ul class="nav nav-tabs mb-4 border-plano" id="cajon5Tabs" role="tablist">
    <!-- Pestaña A: Reportes de Rendimiento -->
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'reportes') ? 'active fw-bold' : ''; ?>" 
                id="reportes-tab" data-bs-toggle="tab" data-bs-target="#tab-reportes" type="button" role="tab">
            <i class="fas fa-chart-pie me-2"></i>Reportes de Rendimiento
        </button>
    </li>
    
    <!-- Pestaña B: Bitácora de Auditoría -->
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'bitacora') ? 'active fw-bold' : ''; ?>" 
                id="bitacora-tab" data-bs-toggle="tab" data-bs-target="#tab-bitacora" type="button" role="tab">
            <i class="fas fa-user-shield me-2"></i>Bitácora de Auditoría
        </button>
    </li>
</ul>

<!-- CONTENIDO DE LAS PESTAÑAS -->
<div class="tab-content" id="cajon5TabsContent">
    
    <!-- SUBAPARTADO A: REPORTES ESTADÍSTICOS -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'reportes') ? 'show active' : ''; ?>" id="tab-reportes" role="tabpanel">
        <?php 
        $archivo_reportes = __DIR__ . '/partials/reportes_rendimiento.php';
        if (file_exists($archivo_reportes)) {
            include $archivo_reportes;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> El módulo de Reportes de Rendimiento está en construcción.</div>';
        }
        ?>
    </div>

    <!-- SUBAPARTADO B: BITÁCORA DE AUDITORÍA -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'bitacora') ? 'show active' : ''; ?>" id="tab-bitacora" role="tabpanel">
        <?php 
        $archivo_bitacora = __DIR__ . '/partials/auditoria_bitacora.php';
        if (file_exists($archivo_bitacora)) {
            include $archivo_bitacora;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> La Bitácora de Auditoría está en construcción.</div>';
        }
        ?>
    </div>

</div>

<!-- SCRIPT ADICIONAL PARA ACTUALIZAR URL SIN RECARGAR -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabElements.forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function (event) {
            var targetId = event.target.getAttribute('data-bs-target').replace('#tab-', '');
            var url = new URL(window.location);
            url.searchParams.set('tab', targetId);
            window.history.replaceState({}, '', url);
        });
    });
});
</script>