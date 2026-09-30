<?php
/**
 * ARCHIVO: views/admin/cajon3/index.php
 * Vista central del Cajón 3: Hitos, Defensas, Reuniones (MG) y Tutorías de Materias.
 * Arquitectura SPA (Single Page Application).
 */

require_once __DIR__ . '/../../../config/conexion.php';

// Importar Modelos Activos
require_once __DIR__ . '/../../../models/cronograma/HitoModel/index.php'; 
require_once __DIR__ . '/../../../models/estructuras/GrupoTesisModel/index.php';
require_once __DIR__ . '/../../../models/titulacion/DefensaModel/index.php';
require_once __DIR__ . '/../../../models/tutorias/ReunionModel/index.php'; 
require_once __DIR__ . '/../../../models/tutorias/TutoriaModel/index.php'; 

// Manejo de pestaña activa (por defecto inicia en 'hitos')
$tab_activo = $_GET['tab'] ?? 'hitos';
$mensaje_exito = $_GET['exito'] ?? '';
$mensaje_error = $_GET['error'] ?? '';

// Variables globales para alimentar las vistas (Modalidad de Grado)
$hitos_entregas = [];
$lista_cohortes = [];
$defensas = [];
$lista_expedientes = [];
$reuniones_mg = [];
$lista_asignaciones = [];

// Variables globales para Tutorías Regulares (Académico - Lógica Grupal)
$tutorias_regulares = [];
$lista_tutores = [];
$lista_materias = [];
$lista_bloques = [];
$lista_estudiantes = []; // Se mantiene vacía por defecto para no romper el partial si aún lo busca

// Extracción segura de datos
try {
    // Instancias de modelos
    $hitoModel = new HitoModel($pdo);
    $grupoTesisModel = new GrupoTesisModel($pdo);
    $defensaModel = new DefensaModel($pdo);
    $reunionModel = new ReunionModel($pdo);
    $tutoriaModel = new TutoriaModel($pdo);

    // 1. Hitos y Entregas (MG)
    $hitos_entregas = $hitoModel->listarTodos();
    $lista_cohortes = $grupoTesisModel->listarTodos();

    // 2. Programación de Defensas (MG)
    $defensas = $defensaModel->listarTodas();
    $lista_expedientes = $defensaModel->obtenerExpedientesParaFormulario();

    // 3. Reuniones de Proyecto / Tesis (MG)
    $reuniones_mg = $reunionModel->listarTodas();
    $lista_asignaciones = $reunionModel->obtenerAsignacionesParaFormulario();

    // 4. Tutorías de Materias Regulares (Académico - Grupos)
    // El modelo ya fue actualizado para traer conteo y concatenación de estudiantes
    $tutorias_regulares = $tutoriaModel->listarTodas();
    $lista_tutores = $tutoriaModel->obtenerTutores();
    $lista_materias = $tutoriaModel->obtenerMaterias();
    $lista_bloques = $tutoriaModel->obtenerBloques();
    
    // Dejamos disponible la lista de estudiantes por si la necesitas para el "Gestor de Grupos" más adelante
    $lista_estudiantes = $tutoriaModel->obtenerEstudiantes();

} catch (Throwable $e) {
    $mensaje_error = "Error al cargar los datos del cronograma: " . $e->getMessage();
}
?>

<!-- ENCABEZADO DE LA SECCIÓN -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-light mb-0 text-dark">
            <i class="fas fa-calendar-alt text-institucional me-2"></i>Cronograma General
        </h2>
    </div>
    <div class="text-muted small">
        <i class="fas fa-home text-institucional"></i> Inicio / <span class="fw-bold">Cronograma General</span>
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
<ul class="nav nav-tabs mb-4 border-plano" id="cajon3Tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'hitos') ? 'active fw-bold' : ''; ?>" id="hitos-tab" data-bs-toggle="tab" data-bs-target="#tab-hitos" type="button" role="tab"><i class="fas fa-flag-checkered me-2"></i>Hitos y Entregas</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'defensas') ? 'active fw-bold' : ''; ?>" id="defensas-tab" data-bs-toggle="tab" data-bs-target="#tab-defensas" type="button" role="tab"><i class="fas fa-user-graduate me-2"></i>Programación de Defensas</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'reuniones') ? 'active fw-bold' : ''; ?>" id="reuniones-tab" data-bs-toggle="tab" data-bs-target="#tab-reuniones" type="button" role="tab"><i class="fas fa-comments me-2"></i>Reuniones de Proyecto</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark border-plano <?php echo ($tab_activo === 'tutorias') ? 'active fw-bold' : ''; ?>" id="tutorias-tab" data-bs-toggle="tab" data-bs-target="#tab-tutorias" type="button" role="tab"><i class="fas fa-chalkboard-teacher me-2"></i>Tutorías de Materias</button>
    </li>
</ul>

<!-- CONTENIDO DE LAS PESTAÑAS -->
<div class="tab-content" id="cajon3TabsContent">
    
    <!-- PESTAÑA 1: HITOS Y ENTREGAS -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'hitos') ? 'show active' : ''; ?>" id="tab-hitos" role="tabpanel">
        <?php include __DIR__ . '/partials/tab_hitos.php'; ?>
    </div>

    <!-- PESTAÑA 2: PROGRAMACIÓN DE DEFENSAS -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'defensas') ? 'show active' : ''; ?>" id="tab-defensas" role="tabpanel">
        <?php include __DIR__ . '/partials/tab_defensas.php'; ?>
    </div>

    <!-- PESTAÑA 3: REUNIONES DE PROYECTO (Tesis / MG) -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'reuniones') ? 'show active' : ''; ?>" id="tab-reuniones" role="tabpanel">
        <?php include __DIR__ . '/partials/tab_reuniones.php'; ?>
    </div>

    <!-- PESTAÑA 4: TUTORÍAS DE MATERIAS (Apoyo Regular) -->
    <div class="tab-pane fade <?php echo ($tab_activo === 'tutorias') ? 'show active' : ''; ?>" id="tab-tutorias" role="tabpanel">
        <?php 
        $archivo_tutorias = __DIR__ . '/partials/tab_tutorias.php';
        if (file_exists($archivo_tutorias)) {
            include $archivo_tutorias;
        } else {
            echo '<div class="alert alert-info border-plano"><i class="fas fa-info-circle me-2"></i> El módulo de Tutorías de Materias está en construcción.</div>';
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