<?php
/**
 * ARCHIVO: views/admin/cajon2/index.php
 * Vista central del Cajón 2: Infraestructura Escolar, Oferta y Expedientes.
 * Arquitectura SPA (Single Page Application) basada en Tabs de Bootstrap 5.
 */

require_once __DIR__ . '/../../../config/conexion.php';

// Importar los modelos correspondientes a la Pestaña 1
require_once __DIR__ . '/../../../models/estructuras/PeriodoModel/index.php';
require_once __DIR__ . '/../../../models/estructuras/GrupoTesisModel/index.php';

// Importar los modelos correspondientes a la Pestaña 2
require_once __DIR__ . '/../../../models/estructuras/CarreraModel/index.php';
require_once __DIR__ . '/../../../models/estructuras/MateriaModel/index.php';

// Manejo de pestaña activa tras un CRUD (por defecto inicia en 'periodos')
$tab_activo = $_GET['tab'] ?? 'periodos';
$mensaje_exito = $_GET['exito'] ?? '';
$mensaje_error = $_GET['error'] ?? '';

// Variables globales para alimentar las vistas
$periodos = [];
$cohortes = [];
$carreras = [];
$materias = [];

// Extracción segura de datos desde la BD
try {
    $periodoModel = new PeriodoModel($pdo);
    $grupoTesisModel = new GrupoTesisModel($pdo);
    $carreraModel = new CarreraModel($pdo);
    $materiaModel = new MateriaModel($pdo);

    // SOLUCIÓN: Al ser una vista SPA, todas las pestañas se renderizan en el DOM al mismo tiempo.
    // Por lo tanto, debemos cargar toda la información base sin importar en qué pestaña iniciemos.
    $periodos = $periodoModel->listarTodos();
    $cohortes = $grupoTesisModel->listarTodos();
    $carreras = $carreraModel->listarTodas();
    $materias = $materiaModel->listarTodas();

} catch (Throwable $e) {
    $mensaje_error = "Error al cargar los datos desde la BD: " . $e->getMessage();
}

?>

<!-- ENCABEZADO DE LA SECCIÓN -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-light mb-0 text-dark">
            <i class="fas fa-university text-institucional me-2"></i>Infraestructura y Expedientes
        </h2>
    </div>
    <div class="text-muted small">
        <i class="fas fa-home text-institucional"></i> Inicio / <span class="fw-bold">Gestión Académica</span>
    </div>
</div>

<!-- ALERTAS DE SISTEMA (Globales para todo el Cajón 2) -->
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

<!-- NAVEGACIÓN POR PESTAÑAS (BOOTSTRAP 5 / JS NATIVO) -->
<ul class="nav nav-tabs mb-4 border-plano" id="cajon2Tabs" role="tablist">
    <!-- Pestaña 1: Periodos (Engloba Periodos de Tutoría y Cohortes de Grado) -->
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'periodos' || $tab_activo === 'cohortes') ? 'active fw-bold' : ''; ?>" 
                id="periodos-tab" data-bs-toggle="tab" data-bs-target="#tab-periodos" type="button" role="tab">
            <i class="fas fa-calendar-alt me-2"></i>Periodos y Cohortes
        </button>
    </li>
    <!-- Pestaña 2: Oferta Universitaria (Carreras y Materias) -->
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'oferta') ? 'active fw-bold' : ''; ?>" 
                id="oferta-tab" data-bs-toggle="tab" data-bs-target="#tab-oferta" type="button" role="tab">
            <i class="fas fa-book-open me-2"></i>Oferta Universitaria
        </button>
    </li>
    <!-- Pestaña 3: Expedientes de Tesis -->
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'expedientes') ? 'active fw-bold' : ''; ?>" 
                id="expedientes-tab" data-bs-toggle="tab" data-bs-target="#tab-expedientes" type="button" role="tab">
            <i class="fas fa-folder-open me-2"></i>Expedientes de Tesis
        </button>
    </li>
</ul>

<!-- CONTENIDO DE LAS PESTAÑAS -->
<div class="tab-content" id="cajon2TabsContent">
    
    <!-- =================================================================== -->
    <!-- PESTAÑA 1: PERIODOS Y COHORTES                                      -->
    <!-- =================================================================== -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'periodos' || $tab_activo === 'cohortes') ? 'show active' : ''; ?>" id="tab-periodos" role="tabpanel">
        <?php 
        $archivo_periodos = __DIR__ . '/partials/tab_periodos.php';
        if (file_exists($archivo_periodos)) {
            include $archivo_periodos;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> El módulo de Periodos y Cohortes está en construcción.</div>';
        }
        ?>
    </div>

    <!-- =================================================================== -->
    <!-- PESTAÑA 2: OFERTA UNIVERSITARIA (CARRERAS Y MATERIAS)               -->
    <!-- =================================================================== -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'oferta') ? 'show active' : ''; ?>" id="tab-oferta" role="tabpanel">
        <?php 
        $archivo_oferta = __DIR__ . '/partials/tab_oferta.php';
        if (file_exists($archivo_oferta)) {
            include $archivo_oferta;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> El módulo de Oferta Universitaria está en construcción.</div>';
        }
        ?>
    </div>

    <!-- =================================================================== -->
    <!-- PESTAÑA 3: EXPEDIENTES DE TESIS                                     -->
    <!-- =================================================================== -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'expedientes') ? 'show active' : ''; ?>" id="tab-expedientes" role="tabpanel">
        <?php 
        $archivo_expedientes = __DIR__ . '/partials/tab_expedientes.php';
        if (file_exists($archivo_expedientes)) {
            include $archivo_expedientes;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> El tablero de Expedientes de Tesis está en construcción.</div>';
        }
        ?>
    </div>

</div>

<!-- SCRIPT ADICIONAL PARA ACTUALIZAR URL SIN RECARGAR (Mejora de UX) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Escucha el evento de Bootstrap cuando se cambia de pestaña superior
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