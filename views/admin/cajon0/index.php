<?php
/**
 * ARCHIVO: views/admin/cajon0/index.php
 * Vista inyectable del Cajón 0: Panel de Contingencias y Auditoría.
 * Gobierna las alertas activas del sistema que requieren mitigación manual.
 */

// 1. INCLUSIÓN CENTRALIZADA DE CONEXIÓN
// Utilizamos __DIR__ para garantizar la ruta exacta sin importar desde dónde se inyecte el include
require_once __DIR__ . '/../../../config/conexion.php';

// 2. IMPORTACIÓN DEL MODELO CON LA NUEVA NOMENCLATURA INDEXADA
require_once __DIR__ . '/../../../models/sistema/AlertaModel/index.php';

// 3. INSTANCIACIÓN Y LECTURA DE DATOS
$alertaModel = new AlertaModel($pdo);

// Extraemos el ID del administrador desde la sesión validada
$id_admin_actual = $_SESSION['id_usuario'] ?? 1;
$alertas_activas = $alertaModel->obtenerAlertasActivasUsuario($id_admin_actual);
?>

<!-- ENCABEZADO DE LA SECCIÓN -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="fw-light mb-0 text-dark">
            Panel de Contingencias 
            <span class="fs-6 text-muted ms-2 fw-normal">gestión de alertas del ecosistema</span>
        </h2>
    </div>
    <div class="text-muted small">
        <i class="fas fa-home"></i> Inicio / <span class="text-institucional fw-bold">Alertas (Cajón 0)</span>
    </div>
</div>

<!-- GRILLA DE ALERTAS (Comportamiento SPA sin recarga externa) -->
<div class="row g-4">
    <?php if (!empty($alertas_activas)): ?>
        <?php foreach ($alertas_activas as $alerta): ?>
            
            <div class="col-md-4">
                <!-- Tarjeta con borde dinámico según criticidad y bordes planos -->
                <div class="card h-100 shadow-sm border-0 border-top border-3 border-plano hover-elevate <?php echo ($alerta['tipo'] === 'critico') ? 'border-danger' : 'border-warning'; ?>">
                    <div class="card-body text-center d-flex flex-column">
                        
                        <div class="mb-3 mt-2">
                            <?php if ($alerta['tipo'] === 'critico'): ?>
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            <?php else: ?>
                                <i class="fas fa-bell fa-2x text-warning"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h6 class="card-title text-uppercase fw-bold mb-2 text-institucional" style="font-size: 0.85rem;">
                            ALERTA <?php echo htmlspecialchars($alerta['tipo']); ?>
                        </h6>
                        
                        <p class="card-text text-muted small flex-grow-1">
                            <?php echo htmlspecialchars($alerta['text_mensaje']); ?>
                        </p>
                        
                        <p class="text-muted mb-3" style="font-size: 0.7rem;">
                            <i class="far fa-clock"></i> Registrada: <?php echo date('d/m/Y H:i', strtotime($alerta['fecha_creacion'])); ?>
                        </p>

                        <!-- Botón disparador del Modal de Mitigación -->
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-auto border-institucional text-institucional border-plano" data-bs-toggle="modal" data-bs-target="#modalMitigar<?php echo $alerta['id_notificacion']; ?>">
                            <i class="fas fa-tools"></i> Mitigar Contingencia
                        </button>

                    </div>
                </div>
            </div>

            <!-- MODAL DE MITIGACIÓN (Formulario Seguro MVC) -->
            <div class="modal fade" id="modalMitigar<?php echo $alerta['id_notificacion']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow border-plano">
                        <div class="modal-header bg-institucional border-0 border-plano">
                            <h5 class="modal-title fs-6 text-white"><i class="fas fa-clipboard-check"></i> Asentar Resolución Técnica</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        
                        <!-- Conexión explícita al Controlador mediante POST -->
                        <form method="POST" action="../../../controllers/AlertaController.php">
                            
                            <!-- Acción interna para el Switch del Controlador -->
                            <input type="hidden" name="accion" value="mitigar_alerta">
                            <input type="hidden" name="id_notificacion" value="<?php echo $alerta['id_notificacion']; ?>">
                            <input type="hidden" name="tipo_alerta" value="<?php echo htmlspecialchars($alerta['tipo']); ?>">
                            <input type="hidden" name="url_enlace" value="<?php echo htmlspecialchars($alerta['url_enlace']); ?>">

                            <div class="modal-body p-4">
                                <p class="small text-muted mb-3">
                                    Estás a punto de mitigar la alerta: <br>
                                    <strong class="text-dark"><?php echo htmlspecialchars($alerta['text_mensaje']); ?></strong>
                                    <br><br>Este movimiento será auditado en la Bitácora del sistema.
                                </p>
                                
                                <div class="mb-2">
                                    <label for="nota_resolucion_<?php echo $alerta['id_notificacion']; ?>" class="form-label small fw-bold text-institucional">Nota de Resolución (Obligatorio)</label>
                                    <textarea class="form-control form-control-sm border-plano bg-light" id="nota_resolucion_<?php echo $alerta['id_notificacion']; ?>" name="nota_resolucion" rows="3" required placeholder="Describe las acciones tomadas para resolver esta contingencia..."></textarea>
                                </div>
                            </div>
                            
                            <div class="modal-footer bg-light border-top-0 border-plano">
                                <button type="button" class="btn btn-sm btn-secondary border-plano" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-sm btn-success border-plano px-3"><i class="fas fa-check"></i> Confirmar Mitigación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        
        <!-- Estado Vacío (Empty State) -->
        <div class="col-12 text-center py-5 mt-4">
            <i class="fas fa-check-circle fa-4x text-success mb-3 opacity-75"></i>
            <h5 class="text-muted fw-light">No hay contingencias activas</h5>
            <p class="text-muted small">El ecosistema está operando de forma estable y dentro de los parámetros normales.</p>
        </div>
        
    <?php endif; ?>
</div>