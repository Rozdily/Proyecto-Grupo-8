<?php
/**
 * ARCHIVO: views/estudiante/mistutorias/index.php
 * Vista de Mis Tutorías (Resumen Estadístico y Top 5 recientes).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$nombre_est = $_SESSION['nombre'] ?? 'Estudiante';

// Variables por defecto
$carrera = "No registrada";
$semestre = "-";
$ru = "Sin RU";
$id_estudiante = 0;

// Inicializar contadores en 0
$stats = [
    'espera' => 0, 
    'confirmadas' => 0, 
    'completadas' => 0, 
    'proceso' => 0, 
    'detenidas' => 0
];
$tutorias_recientes = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        // 1. Obtener datos del perfil académico del estudiante[cite: 1]
        $sql_perfil = "SELECT e.id_estudiante, e.registro_universitario, e.semestre, c.nombre_carrera 
                       FROM estudiantes e 
                       INNER JOIN carreras c ON e.id_carrera = c.id_carrera 
                       WHERE e.id_usuario = ?";
        $stmt = $pdo->prepare($sql_perfil);
        $stmt->execute([$id_usuario]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id_estudiante = $row['id_estudiante'];
            $carrera = $row['nombre_carrera'];
            $semestre = $row['semestre'];
            $ru = $row['registro_universitario'];
        }

        // 2. Calcular estadísticas de tutorías según su estado[cite: 1]
        if ($id_estudiante > 0) {
            $sql_stats = "SELECT estado, COUNT(*) as total FROM tutorias WHERE id_estudiante = ? GROUP BY estado";
            $stmt = $pdo->prepare($sql_stats);
            $stmt->execute([$id_estudiante]);
            
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                switch($r['estado']) {
                    case 'pendiente': $stats['espera'] = $r['total']; break;
                    case 'confirmada': $stats['confirmadas'] = $r['total']; break;
                    case 'realizada': $stats['completadas'] = $r['total']; break;
                    case 'en_proceso': $stats['proceso'] = $r['total']; break;
                    case 'detenido': $stats['detenidas'] = $r['total']; break;
                }
            }

            // 3. Obtener las últimas 5 tutorías para la tabla rápida[cite: 1]
            $sql_tabla = "SELECT t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, t.lugar_o_enlace, t.estado, 
                                 m.nombre_materia, u.nombre as tutor_nom, u.apellido as tutor_ape, b.nombre_bloque
                          FROM tutorias t
                          JOIN materias m ON t.id_materia = m.id_materia
                          JOIN tutores tu ON t.id_tutor = tu.id_tutor
                          JOIN usuarios u ON tu.id_usuario = u.id_usuario
                          JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                          WHERE t.id_estudiante = ? 
                          ORDER BY t.fecha_solicitud DESC LIMIT 5";
            $stmt = $pdo->prepare($sql_tabla);
            $stmt->execute([$id_estudiante]);
            $tutorias_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // En caso de error de BD, las variables quedan con sus valores por defecto
    }
}
?>

<style>
    .banner-estudiante {
        background-color: #2b4964; color: white; 
        border-radius: 8px; padding: 2rem 2.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .stat-card {
        background: #fff; border: 1px solid #e2e8f0; border-top-width: 4px;
        border-radius: 8px; padding: 2rem 1rem; text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon {
        width: 48px; height: 48px; border-radius: 8px; 
        display: inline-flex; justify-content: center; align-items: center; 
        font-size: 1.2rem; margin-bottom: 1rem;
    }
    .stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1; margin-bottom: 0.5rem; }
    .stat-label { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }

    /* Colores */
    .card-yellow { border-top-color: #fcd34d; } .icon-yellow { background-color: #fef3c7; color: #d97706; }
    .card-blue { border-top-color: #7dd3fc; } .icon-blue { background-color: #e0f2fe; color: #0284c7; }
    .card-green { border-top-color: #6ee7b7; } .icon-green { background-color: #d1fae5; color: #059669; }
    .card-purple { border-top-color: #c4b5fd; } .icon-purple { background-color: #ede9fe; color: #7c3aed; }
    .card-grey { border-top-color: #94a3b8; } .icon-grey { background-color: #f1f5f9; color: #475569; }

    /* Tabla */
    .table-container { border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; overflow: hidden; }
    .table-header th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; padding: 1rem; border-bottom: 2px solid #f1f5f9; }
    .empty-state { padding: 4rem 2rem; text-align: center; color: #64748b; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <!-- BANNER DE BIENVENIDA -->
    <div class="banner-estudiante d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-2">Mis Tutorías</h2>
            <p class="mb-0 text-white-50" style="font-size: 0.9rem;">
                Resumen estadístico de tus solicitudes y avance académico.
            </p>
        </div>
        <div>
            <a href="index.php?seccion=cajon2" class="btn btn-light fw-bold px-4 py-2 text-dark border-plano shadow-sm">
                <i class="far fa-calendar-plus me-2"></i> Solicitar Tutoría
            </a>
        </div>
    </div>

    <!-- TARJETAS ESTADÍSTICAS SUPERIORES -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card card-yellow hover-elevate">
                <div class="stat-icon icon-yellow"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-number"><?php echo $stats['espera']; ?></div>
                <div class="stat-label">Solicitudes en Espera</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-blue hover-elevate">
                <div class="stat-icon icon-blue"><i class="far fa-calendar-check"></i></div>
                <div class="stat-number"><?php echo $stats['confirmadas']; ?></div>
                <div class="stat-label">Tutorías Confirmadas</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-green hover-elevate">
                <div class="stat-icon icon-green"><i class="fas fa-medal"></i></div>
                <div class="stat-number"><?php echo $stats['completadas']; ?></div>
                <div class="stat-label">Tutorías Completadas</div>
            </div>
        </div>
    </div>

    <!-- TARJETAS ESTADÍSTICAS INFERIORES -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card card-purple hover-elevate">
                <div class="stat-icon icon-purple"><i class="fas fa-sync-alt"></i></div>
                <div class="stat-number"><?php echo $stats['proceso']; ?></div>
                <div class="stat-label">Tutorías En Proceso</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card card-grey hover-elevate">
                <div class="stat-icon icon-grey"><i class="fas fa-pause"></i></div>
                <div class="stat-number"><?php echo $stats['detenidas']; ?></div>
                <div class="stat-label">Tutorías Detenidas</div>
            </div>
        </div>
    </div>

    <!-- TABLA DE SOLICITUDES RECIENTES -->
    <div class="table-container mb-3 shadow-sm">
        <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="far fa-clock me-2"></i> Mis Solicitudes Recientes (Top 5)</h6>
            <a href="index.php?seccion=cajon3" class="btn btn-sm btn-outline-primary border-plano">Ver todo el historial</a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr class="table-header">
                        <th class="ps-4">Fecha y Horario</th>
                        <th>Materia</th>
                        <th>Docente Tutor</th>
                        <th>Modalidad / Lugar</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tutorias_recientes)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="far fa-calendar-plus fa-3x text-secondary opacity-50 mb-3"></i>
                                    <p class="mb-0">No tienes solicitudes de tutoría registradas. Puedes solicitar tu primera sesión haciendo clic en <strong>Solicitar Tutoría</strong>.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($tutorias_recientes as $t): 
                            $badge_class = 'bg-secondary';
                            if($t['estado'] == 'pendiente') $badge_class = 'bg-warning text-dark';
                            if($t['estado'] == 'confirmada') $badge_class = 'bg-info text-white';
                            if($t['estado'] == 'realizada') $badge_class = 'bg-success';
                            if($t['estado'] == 'cancelada') $badge_class = 'bg-danger';
                            if($t['estado'] == 'en_proceso') $badge_class = 'bg-primary';
                        ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark"><?php echo date('d/m/Y', strtotime($t['fecha'])); ?></div>
                                <small class="text-muted"><?php echo substr($t['hora_inicio'], 0, 5) . ' - ' . substr($t['hora_fin'], 0, 5); ?> (<?php echo htmlspecialchars($t['nombre_bloque']); ?>)</small>
                            </td>
                            <td class="fw-semibold text-secondary"><?php echo htmlspecialchars($t['nombre_materia']); ?></td>
                            <td>
                                <i class="fas fa-chalkboard-teacher text-primary me-1"></i> 
                                <?php echo htmlspecialchars($t['tutor_nom'] . ' ' . $t['tutor_ape']); ?>
                            </td>
                            <td>
                                <?php if($t['modalidad'] == 'virtual'): ?>
                                    <span class="badge border border-secondary text-secondary bg-light"><i class="fas fa-video me-1"></i> Virtual</span>
                                <?php else: ?>
                                    <span class="badge border border-secondary text-secondary bg-light"><i class="fas fa-building me-1"></i> Presencial</span>
                                <?php endif; ?>
                                <br><small class="text-muted d-inline-block mt-1 text-truncate" style="max-width:150px;" title="<?php echo htmlspecialchars($t['lugar_o_enlace']); ?>"><?php echo htmlspecialchars($t['lugar_o_enlace']); ?></small>
                            </td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?> px-2 py-1 border-plano">
                                    <?php echo strtoupper($t['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>