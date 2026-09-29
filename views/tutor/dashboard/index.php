<?php
/**
 * ARCHIVO: views/tutor/dashboard/index.php
 * Vista Principal del Tutor (Resumen y Agenda).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$nombre_tutor = $_SESSION['nombre'] ?? 'Docente';

$id_tutor = 0;
$stats = [
    'pendientes' => 0, 
    'confirmadas' => 0, 
    'completadas' => 0
];
$agenda_proxima = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        // 1. Obtener el id_tutor real a partir del id_usuario
        $stmtTut = $pdo->prepare("SELECT id_tutor FROM tutores WHERE id_usuario = ?");
        $stmtTut->execute([$id_usuario]);
        if ($rowTut = $stmtTut->fetch(PDO::FETCH_ASSOC)) {
            $id_tutor = $rowTut['id_tutor'];
        }

        if ($id_tutor > 0) {
            // 2. Calcular estadísticas de tutorías asignadas a este tutor
            $sql_stats = "SELECT estado, COUNT(*) as total FROM tutorias WHERE id_tutor = ? GROUP BY estado";
            $stmtStats = $pdo->prepare($sql_stats);
            $stmtStats->execute([$id_tutor]);
            
            while ($r = $stmtStats->fetch(PDO::FETCH_ASSOC)) {
                if ($r['estado'] === 'pendiente') $stats['pendientes'] = $r['total'];
                if ($r['estado'] === 'confirmada') $stats['confirmadas'] = $r['total'];
                if ($r['estado'] === 'realizada') $stats['completadas'] = $r['total'];
            }

            // 3. Obtener la agenda próxima (Solo las CONFIRMADAS que están por venir)
            $sql_agenda = "SELECT t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, t.lugar_o_enlace, 
                                  m.nombre_materia, u.nombre as est_nom, u.apellido as est_ape, 
                                  b.nombre_bloque
                           FROM tutorias t
                           JOIN materias m ON t.id_materia = m.id_materia
                           JOIN estudiantes e ON t.id_estudiante = e.id_estudiante
                           JOIN usuarios u ON e.id_usuario = u.id_usuario
                           JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                           WHERE t.id_tutor = ? AND t.estado = 'confirmada' AND t.fecha >= CURRENT_DATE
                           ORDER BY t.fecha ASC, t.hora_inicio ASC LIMIT 5";
            
            $stmtAgenda = $pdo->prepare($sql_agenda);
            $stmtAgenda->execute([$id_tutor]);
            $agenda_proxima = $stmtAgenda->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "No se pudo cargar la información del panel.";
    }
}
?>

<style>
    .banner-tutor {
        background: linear-gradient(135deg, #0B427B 0%, #1a5c9e 100%);
        color: white; 
        border-radius: 8px; padding: 2rem 2.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .stat-card {
        background: #fff; border: 1px solid #e2e8f0; border-top-width: 4px;
        border-radius: 8px; padding: 1.5rem; text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon {
        width: 48px; height: 48px; border-radius: 8px; 
        display: inline-flex; justify-content: center; align-items: center; 
        font-size: 1.2rem; margin-bottom: 1rem;
    }
    .stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1; margin-bottom: 0.5rem; }
    .stat-label { font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; }

    /* Colores */
    .card-yellow { border-top-color: #f59e0b; } .icon-yellow { background-color: #fef3c7; color: #d97706; }
    .card-blue { border-top-color: #0ea5e9; } .icon-blue { background-color: #e0f2fe; color: #0284c7; }
    .card-green { border-top-color: #10b981; } .icon-green { background-color: #d1fae5; color: #059669; }

    /* Agenda */
    .agenda-container { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; }
    .agenda-header { background-color: #f8fafc; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #334155; }
    .agenda-item { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; transition: background 0.2s; }
    .agenda-item:hover { background-color: #f8fafc; }
    .agenda-item:last-child { border-bottom: none; }
    .agenda-date { min-width: 90px; text-align: center; border-right: 2px solid #e2e8f0; padding-right: 1rem; margin-right: 1rem; }
    .agenda-date .day { font-size: 1.5rem; font-weight: bold; color: #0B427B; line-height: 1; }
    .agenda-date .month { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <!-- BANNER DE BIENVENIDA -->
    <div class="banner-tutor d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Bienvenido, Prof. <?php echo htmlspecialchars(explode(' ', $nombre_tutor)[0]); ?></h2>
            <p class="mb-0 text-white-50">
                Panel de control de tutorías de apoyo académico.
            </p>
        </div>
        <div>
            <a href="index.php?seccion=solicitudes" class="btn btn-light fw-bold px-4 py-2 text-dark border-plano shadow-sm">
                <i class="fas fa-inbox me-2"></i> Revisar Solicitudes
                <?php if($stats['pendientes'] > 0): ?>
                    <span class="badge bg-danger ms-2 rounded-pill"><?php echo $stats['pendientes']; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- TARJETAS ESTADÍSTICAS -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card card-yellow h-100">
                <div class="stat-icon icon-yellow"><i class="fas fa-bell"></i></div>
                <div class="stat-number"><?php echo $stats['pendientes']; ?></div>
                <div class="stat-label">Solicitudes Pendientes</div>
                <?php if($stats['pendientes'] > 0): ?>
                    <div class="mt-3 text-warning small fw-semibold"><i class="fas fa-exclamation-circle me-1"></i> Requieren tu aprobación</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-blue h-100">
                <div class="stat-icon icon-blue"><i class="far fa-calendar-check"></i></div>
                <div class="stat-number"><?php echo $stats['confirmadas']; ?></div>
                <div class="stat-label">Clases Confirmadas</div>
                <div class="mt-3 text-muted small">Tutorías agendadas por impartir</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-green h-100">
                <div class="stat-icon icon-green"><i class="fas fa-check-double"></i></div>
                <div class="stat-number"><?php echo $stats['completadas']; ?></div>
                <div class="stat-label">Tutorías Realizadas</div>
                <div class="mt-3 text-muted small">Historial de clases completadas</div>
            </div>
        </div>
    </div>

    <!-- AGENDA DE CLASES CONFIRMADAS -->
    <div class="row">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-3"><i class="far fa-calendar-alt text-institucional me-2"></i> Mi Agenda Próxima</h5>
            
            <div class="agenda-container shadow-sm mb-4">
                <div class="agenda-header d-flex justify-content-between align-items-center">
                    <span>Próximas Tutorías Programadas (Top 5)</span>
                </div>
                
                <div>
                    <?php if (empty($agenda_proxima)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="far fa-smile-beam fa-3x mb-3 opacity-50"></i>
                            <h6 class="fw-bold text-dark">Agenda Libre</h6>
                            <p class="mb-0 small">No tienes tutorías confirmadas para los próximos días.</p>
                        </div>
                    <?php else: ?>
                        <?php 
                        $meses = ['01'=>'Ene', '02'=>'Feb', '03'=>'Mar', '04'=>'Abr', '05'=>'May', '06'=>'Jun', '07'=>'Jul', '08'=>'Ago', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dic'];
                        foreach($agenda_proxima as $agenda): 
                            $fecha_partes = explode('-', $agenda['fecha']);
                            $dia = $fecha_partes[2];
                            $mes = $meses[$fecha_partes[1]];
                        ?>
                            <div class="agenda-item">
                                <div class="agenda-date">
                                    <div class="day"><?php echo $dia; ?></div>
                                    <div class="month"><?php echo $mes; ?></div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($agenda['nombre_materia']); ?></h6>
                                        <span class="badge bg-light border <?php echo ($agenda['modalidad'] == 'virtual') ? 'text-primary border-primary' : 'text-success border-success'; ?>">
                                            <?php echo ($agenda['modalidad'] == 'virtual') ? '<i class="fas fa-video me-1"></i> Virtual' : '<i class="fas fa-building me-1"></i> Presencial'; ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small mb-1">
                                        <i class="fas fa-user-graduate me-1"></i> Estudiante: <strong><?php echo htmlspecialchars($agenda['est_nom'] . ' ' . $agenda['est_ape']); ?></strong>
                                    </div>
                                    <div class="d-flex align-items-center small text-secondary gap-3">
                                        <span><i class="far fa-clock me-1"></i> <?php echo substr($agenda['hora_inicio'], 0, 5) . ' - ' . substr($agenda['hora_fin'], 0, 5); ?></span>
                                        <span><i class="fas fa-map-marker-alt me-1"></i> 
                                            <?php 
                                            if ($agenda['modalidad'] == 'virtual' && filter_var($agenda['lugar_o_enlace'], FILTER_VALIDATE_URL)) {
                                                echo '<a href="'.htmlspecialchars($agenda['lugar_o_enlace']).'" target="_blank">Enlace de reunión</a>';
                                            } else {
                                                echo htmlspecialchars($agenda['lugar_o_enlace']); 
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>