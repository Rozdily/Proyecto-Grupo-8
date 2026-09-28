<?php
/**
 * ARCHIVO: views/admin/cajon1/index.php
 * Vista central del Cajón 1 refactorizada y modularizada.
 * Soporta vistas dinámicas para la lista CRUD de Usuarios, y ahora carga
 * información de modelos secundarios como el de Estudiantes (Carreras).
 */

require_once __DIR__ . '/../../../config/conexion.php';
require_once __DIR__ . '/../../../models/usuarios/UsuarioModel/index.php';
// CORRECCIÓN: Ruta apuntando al index.php dentro de la carpeta EstudianteModel
require_once __DIR__ . '/../../../models/usuarios/EstudianteModel/index.php'; 

$submodulo = $_GET['sub'] ?? 'usuarios';
$buscar = $_GET['buscar'] ?? '';
$filtro_grado = $_GET['filtro_grado'] ?? 'todos';

$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

$mensaje_exito = $_GET['exito'] ?? '';
$mensaje_error = $_GET['error'] ?? '';

$etiqueta_singular = 'Usuario';
if ($submodulo === 'estudiantes') $etiqueta_singular = 'Estudiante';
if ($submodulo === 'tutores') $etiqueta_singular = 'Tutor';
if ($submodulo === 'horarios') $etiqueta_singular = 'Horario';

$registros = [];
$total_registros = 0;
$error_modelo = null;
$requiere_actualizar_modelo = false;

// Variable global para almacenar las carreras y enviarlas a los modales
$lista_carreras = []; 

// OPTIMIZACIÓN: Solo consultamos la base de datos si NO estamos en la vista de horarios
if ($submodulo !== 'horarios') {
    if (isset($pdo)) {
        try {
            $usuarioModel = new UsuarioModel($pdo);
            $estudianteModel = new EstudianteModel($pdo);

            // Si estamos en la pestaña de estudiantes, precargamos las carreras
            if ($submodulo === 'estudiantes') {
                $lista_carreras = $estudianteModel->obtenerCarreras();
            }

            $id_rol_filtro = 1; // Por defecto: Administradores ('usuarios')
            if ($submodulo === 'estudiantes') $id_rol_filtro = 3;
            if ($submodulo === 'tutores') $id_rol_filtro = 2;

            $reflector = new ReflectionMethod($usuarioModel, 'contarUsuariosPorRol');
            
            if ($reflector->getNumberOfParameters() >= 3) {
                $total_registros = $usuarioModel->contarUsuariosPorRol($id_rol_filtro, $buscar, $filtro_grado);
                $registros = $usuarioModel->obtenerUsuariosPorRol($id_rol_filtro, $buscar, $limite, $offset, $filtro_grado);
            } else {
                $total_registros = $usuarioModel->contarUsuariosPorRol($id_rol_filtro, $buscar);
                $registros = $usuarioModel->obtenerUsuariosPorRol($id_rol_filtro, $buscar, $limite, $offset);
                $requiere_actualizar_modelo = true;
            }

        } catch (Throwable $e) {
            $error_modelo = $e->getMessage();
        }
    }
}

$total_paginas = ($total_registros > 0) ? ceil($total_registros / $limite) : 1;
$pestana_activa = ($submodulo === 'horarios') ? 'tutores' : $submodulo;
?>

<!-- ENCABEZADO DE LA SECCIÓN -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-light mb-0 text-dark">
            Gestión de Usuarios 
            <span class="fs-6 text-muted ms-2 fw-normal">/ <?php echo ucfirst($pestana_activa); ?></span>
        </h2>
    </div>
    <div class="text-muted small">
        <i class="fas fa-home text-institucional"></i> Inicio / <span class="fw-bold">Gestión de Usuarios</span>
    </div>
</div>

<!-- MENÚ DE PESTAÑAS (SUB-RUTAS) -->
<?php 
$sub_original = $_GET['sub'] ?? 'usuarios';
if ($sub_original === 'horarios') {
    $_GET['sub'] = 'tutores';
}
include __DIR__ . '/partials/tabs.php';
$_GET['sub'] = $sub_original; 
?>

<!-- ALERTAS DE SISTEMA -->
<?php if (!empty($mensaje_exito)): ?>
    <div class="alert alert-success border-0 border-start border-4 border-success border-plano shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> <strong>¡Éxito!</strong> La operación se realizó correctamente (<?php echo htmlspecialchars($mensaje_exito); ?>).
    </div>
<?php endif; ?>

<?php if ($error_modelo): ?>
    <div class="alert alert-danger border-0 border-start border-4 border-danger border-plano shadow-sm mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Fallo Crítico del Sistema:</strong> <?php echo htmlspecialchars($error_modelo); ?>
    </div>
<?php endif; ?>

<!-- COMPONENTES ENSAMBLADOS DINÁMICAMENTE -->
<?php 
if ($submodulo === 'horarios') {
    include __DIR__ . '/partials/horarios.php';
} else {
    include __DIR__ . '/partials/toolbar.php';
    include __DIR__ . '/partials/table.php';
    include __DIR__ . '/partials/pagination.php';
    include __DIR__ . '/partials/modales.php';
}
?>