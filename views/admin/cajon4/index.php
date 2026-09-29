<?php
/**
 * ARCHIVO: views/admin/cajon4/index.php
 * Vista central del Cajón 4: Parámetros del Sistema.
 * Arquitectura SPA (Single Page Application).
 */

require_once __DIR__ . '/../../../config/conexion.php';

// Importar el modelo de Parámetros del Sistema
require_once __DIR__ . '/../../../models/sistema/ParametroModel/index.php'; 

// Manejo de pestaña activa (inicia en la configuración de parámetros)
$tab_activo = $_GET['tab'] ?? 'parametros';
$mensaje_exito = $_GET['exito'] ?? '';
$mensaje_error = $_GET['error'] ?? '';

$lista_parametros = [];

try {
    $parametroModel = new ParametroModel($pdo);
    $lista_parametros = $parametroModel->listarTodosAsociativos(); // Carga las reglas en formato ['CLAVE' => 'VALOR']
} catch (Throwable $e) {
    $mensaje_error = "Error al cargar los parámetros del sistema: " . $e->getMessage();
}
?>

<!-- ENCABEZADO DE LA SECCIÓN -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-light mb-0 text-dark">
            <i class="fas fa-cogs text-institucional me-2"></i>Parámetros del Sistema
        </h2>
    </div>
    <div class="text-muted small">
        <i class="fas fa-home text-institucional"></i> Inicio / <span class="fw-bold">Configuración Técnica</span>
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
<ul class="nav nav-tabs mb-4 border-plano" id="cajon4Tabs" role="tablist">
    <!-- Subapartado A: Reglas Base -->
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'parametros') ? 'active fw-bold' : ''; ?>" 
                id="parametros-tab" data-bs-toggle="tab" data-bs-target="#tab-parametros" type="button" role="tab">
            <i class="fas fa-sliders-h me-2"></i>Reglas Base y Límites
        </button>
    </li>
</ul>

<!-- CONTENIDO DE LAS PESTAÑAS -->
<div class="tab-content" id="cajon4TabsContent">
    
    <!-- CONTENIDO: REGLAS BASE -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'parametros') ? 'show active' : ''; ?>" id="tab-parametros" role="tabpanel">
        <?php 
        $archivo_parametros = __DIR__ . '/partials/parametros_configurar.php';
        if (file_exists($archivo_parametros)) {
            include $archivo_parametros;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> El formulario de Reglas Base está en construcción.</div>';
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