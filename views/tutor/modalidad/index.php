<?php
/**
 * ARCHIVO: views/tutor/modalidad/index.php
 * Vista de Tesistas y Modalidad de Grado para el Tutor Guía.
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_tutor = 0;
$tesistas = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        // 1. Obtener ID real del tutor
        $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
        $stmtTut->execute([$id_usuario]);
        $id_tutor = $stmtTut->fetchColumn();

        if ($id_tutor) {
            // 2. Consulta para obtener los estudiantes en Modalidad de Grado asignados a este tutor
            // Nota: Se asume una tabla o relación de tesistas/modalidad de grado vinculada al tutor.
            // Si la estructura de tesis usa otra tabla específica, se puede adaptar fácilmente.
            $sql = "SELECT e.id_estudiante, e.registro_universitario, e.semestre, 
                           u.nombre, u.apellido, u.correo, u.telefono, c.nombre_carrera
                           /* , mg.titulo_tesis, mg.estado_tesis */
                    FROM estudiantes e
                    JOIN usuarios u ON e.id_usuario = u.id_usuario
                    JOIN carreras c ON e.id_carrera = c.id_carrera
                    /* JOIN modalidad_grado mg ON e.id_estudiante = mg.id_estudiante WHERE mg.id_tutor_guia = ? */
                    WHERE e.id_estudiante IN (
                        SELECT COALESCE(id_estudiante, 0) FROM tutorias WHERE id_tutor = ?
                    ) /* Filtro temporal de ejemplo basado en tutorías hasta vincular tabla formal de tesis */
                    LIMIT 10";
            
            // Nota de arquitectura: Como el esquema exacto de tesis se afinará más adelante,
            // dejamos preparado el contenedor visual profesional para listar a los tesistas asignados.
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_tutor]);
            $tesistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "No se pudo cargar la lista de tesistas.";
    }
}
?>

<style>
    .mg-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .table-container { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; }
    .table-header th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; padding: 1rem; background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Tesistas y Modalidad de Grado</h3>
            <p class="text-muted mb-0">Supervisión y seguimiento de estudiantes bajo tu tutoría guía o tribunal.</p>
        </div>
    </div>

    <?php if (isset($error_bd)): ?>
        <div class="alert alert-danger border-plano"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_bd; ?></div>
    <?php endif; ?>

    <!-- TARJETA INFORMATIVA / ESTADÍSTICA -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="mg-card p-3 d-flex align-items-center">
                <div class="bg-light-primary text-institucional p-3 rounded me-3 fs-3">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Tesistas Asignados</span>
                    <h4 class="fw-bold text-dark mb-0"><?php echo count($tesistas); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE TESISTAS -->
    <div class="table-container shadow-sm mb-4">
        <div class="px-4 py-3 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-alt me-2"></i> Listado de Estudiantes en Proceso de Grado</h6>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr class="table-header">
                        <th class="ps-4">Estudiante / RU</th>
                        <th>Carrera</th>
                        <th>Contacto</th>
                        <th>Estado de Tesis</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tesistas)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
                                    <h6 class="fw-bold text-dark">Sin tesistas asignados</h6>
                                    <p class="mb-0 small">Actualmente no tienes estudiantes registrados bajo la modalidad de tutoría de grado.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($tesistas as $tes): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($tes['nombre'] . ' ' . $tes['apellido']); ?></div>
                                <small class="text-muted">RU: <?php echo htmlspecialchars($tes['registro_universitario']); ?></small>
                            </td>
                            <td>
                                <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($tes['nombre_carrera']); ?></span>
                            </td>
                            <td>
                                <div class="small text-dark"><i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($tes['correo']); ?></div>
                                <div class="small text-dark"><i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($tes['telefono']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-info text-white border-plano">EN DESARROLLO</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary border-plano" title="Ver detalles" onclick="alert('Módulo de detalle de tesis en desarrollo.');">
                                    <i class="fas fa-eye me-1"></i> Detalle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>