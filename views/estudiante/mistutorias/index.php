<?php
/**
 * ARCHIVO: views/estudiante/mistutorias/index.php
 * Vista de Mis Tutorías (Resumen Estadístico y Tutorías Activas en Tarjetas pulidas).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$nombre_est = $_SESSION['nombre'] ?? 'Estudiante';

// Variables por defecto
$carrera = "No registrada";
$semestre = "-";
$ru = "Sin RU";
$id_estudiante = 0;

// Inicializar contadores en 0 (Incluyendo 'canceladas')
$stats = [
    'espera' => 0, 
    'confirmadas' => 0, 
    'completadas' => 0, 
    'proceso' => 0, 
    'detenidas' => 0,
    'canceladas' => 0
];
$tutorias_activas = [];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        // 1. Obtener datos del perfil
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

        // 2. Calcular estadísticas de tutorías
        if ($id_estudiante > 0) {
            $sql_stats = "SELECT t.estado, COUNT(*) as total 
                          FROM tutorias t
                          INNER JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
                          WHERE te.id_estudiante = ? 
                          GROUP BY t.estado";
            $stmt = $pdo->prepare($sql_stats);
            $stmt->execute([$id_estudiante]);
            
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                switch($r['estado']) {
                    case 'pendiente': $stats['espera'] = $r['total']; break;
                    case 'confirmada': $stats['confirmadas'] = $r['total']; break;
                    case 'realizada': $stats['completadas'] = $r['total']; break;
                    case 'en_proceso': $stats['proceso'] = $r['total']; break;
                    case 'detenido': $stats['detenidas'] = $r['total']; break;
                    case 'cancelada': $stats['canceladas'] = $r['total']; break;
                }
            }

            // 3. Obtener solo las tutorías ACTIVAS (Confirmadas o En Proceso)
            $sql_tabla = "SELECT t.fecha, t.hora_inicio, t.hora_fin, t.modalidad, t.lugar_o_enlace, t.estado, 
                                 m.nombre_materia, u.nombre as tutor_nom, u.apellido as tutor_ape, b.nombre_bloque
                          FROM tutorias t
                          JOIN materias m ON t.id_materia = m.id_materia
                          JOIN tutores tu ON t.id_tutor = tu.id_tutor
                          JOIN usuarios u ON tu.id_usuario = u.id_usuario
                          JOIN bloques_horarios b ON t.id_bloque = b.id_bloque
                          JOIN tutoria_estudiantes te ON t.id_tutoria = te.id_tutoria
                          WHERE te.id_estudiante = ? AND t.estado IN ('confirmada', 'en_proceso')
                          ORDER BY t.fecha ASC, t.hora_inicio ASC";
            $stmt = $pdo->prepare($sql_tabla);
            $stmt->execute([$id_estudiante]);
            $tutorias_activas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error_bd = "No se pudieron cargar los datos estadísticos.";
    }
}
?>

<style>
    /* Ocultar barra de desplazamiento globalmente */
    html, body {
        overscroll-behavior-y: none; 
        scrollbar-width: none;       
        -ms-overflow-style: none;    
    }
    html::-webkit-scrollbar, body::-webkit-scrollbar {
        display: none;               
    }

    .banner-estudiante {
        background-color: #2b4964; color: white; 
        border-radius: 8px; padding: 2rem 2.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    
    /* Tarjetas Estadísticas */
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

    .card-yellow { border-top-color: #fcd34d; } .icon-yellow { background-color: #fef3c7; color: #d97706; }
    .card-blue { border-top-color: #7dd3fc; } .icon-blue { background-color: #e0f2fe; color: #0284c7; }
    .card-green { border-top-color: #6ee7b7; } .icon-green { background-color: #d1fae5; color: #059669; }
    .card-purple { border-top-color: #c4b5fd; } .icon-purple { background-color: #ede9fe; color: #7c3aed; }
    .card-grey { border-top-color: #94a3b8; } .icon-grey { background-color: #f1f5f9; color: #475569; }
    .card-red { border-top-color: #f87171; } .icon-red { background-color: #fee2e2; color: #dc2626; }

    /* Tarjetas de Tutorías Activas (Diseño Pulido) */
    .tutoria-card {
        border-radius: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .tutoria-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important;
    }
    .badge-estado {
        border-radius: 3px; 
        padding: 0.4rem 0.75rem; 
        font-size: 0.75rem; 
        letter-spacing: 0.5px;
    }
    .empty-state { padding: 4rem 2rem; text-align: center; color: #64748b; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1; }
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
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card card-purple hover-elevate">
                <div class="stat-icon icon-purple"><i class="fas fa-sync-alt"></i></div>
                <div class="stat-number"><?php echo $stats['proceso']; ?></div>
                <div class="stat-label">Tutorías En Proceso</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-grey hover-elevate">
                <div class="stat-icon icon-grey"><i class="fas fa-pause"></i></div>
                <div class="stat-number"><?php echo $stats['detenidas']; ?></div>
                <div class="stat-label">Tutorías Detenidas</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card card-red hover-elevate">
                <div class="stat-icon icon-red"><i class="fas fa-times-circle"></i></div>
                <div class="stat-number"><?php echo $stats['canceladas']; ?></div>
                <div class="stat-label">Tutorías Canceladas</div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE TUTORÍAS ACTIVAS (TARJETAS) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-chalkboard-teacher me-2 text-institucional"></i> Mis Tutorías Activas</h5>
        <a href="index.php?seccion=cajon3" class="btn btn-sm btn-outline-primary border-plano">Ver todo el historial</a>
    </div>

    <div class="row g-4 mb-5">
        <?php if (empty($tutorias_activas)): ?>
            <div class="col-12">
                <div class="empty-state shadow-sm">
                    <i class="far fa-calendar-check fa-3x text-secondary opacity-50 mb-3"></i>
                    <p class="mb-0 text-muted">No tienes tutorías confirmadas o en proceso en este momento.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach($tutorias_activas as $t): 
                
                // Configuración de colores mejorada para alto contraste
                $estado = $t['estado'];
                if ($estado == 'pendiente') {
                    $border_color = '#f59e0b'; $bg_icon = '#fef3c7'; $text_icon = '#b45309'; 
                    $bg_badge = '#fbbf24'; $text_badge = '#78350f'; $icono = 'fas fa-clock';
                } elseif ($estado == 'confirmada') {
                    $border_color = '#0ea5e9'; $bg_icon = '#e0f2fe'; $text_icon = '#0369a1'; 
                    $bg_badge = '#38bdf8'; $text_badge = '#0c4a6e'; $icono = 'far fa-calendar-check';
                } elseif ($estado == 'en_proceso') {
                    $border_color = '#8b5cf6'; $bg_icon = '#ede9fe'; $text_icon = '#5b21b6'; 
                    $bg_badge = '#a78bfa'; $text_badge = '#2e1065'; $icono = 'fas fa-sync-alt';
                } else {
                    $border_color = '#94a3b8'; $bg_icon = '#f1f5f9'; $text_icon = '#334155'; 
                    $bg_badge = '#cbd5e1'; $text_badge = '#0f172a'; $icono = 'fas fa-info-circle';
                }

                // Configuración de modalidad
                $mod_is_virtual = (strtolower($t['modalidad']) == 'virtual');
                $mod_color = $mod_is_virtual ? 'text-primary' : 'text-success';
                $mod_icon = $mod_is_virtual ? 'fas fa-video' : 'fas fa-building';
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 tutoria-card shadow-sm bg-white" style="border-left: 6px solid <?php echo $border_color; ?> !important;">
                    <div class="card-body p-4">
                        
                        <!-- Cabecera: Icono y Etiqueta -->
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="rounded-circle shadow-sm d-flex justify-content-center align-items-center" style="background-color: <?php echo $bg_icon; ?>; color: <?php echo $text_icon; ?>; width: 48px; height: 48px; font-size: 1.25rem;">
                                <i class="<?php echo $icono; ?>"></i>
                            </div>
                            <span class="badge badge-estado shadow-sm fw-bold" style="background-color: <?php echo $bg_badge; ?>; color: <?php echo $text_badge; ?>;">
                                <?php echo strtoupper(str_replace('_', ' ', $estado)); ?>
                            </span>
                        </div>

                        <!-- Cuerpo: Materia y Tutor -->
                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 1.15rem;"><?php echo htmlspecialchars($t['nombre_materia']); ?></h5>
                        <div class="d-flex align-items-center text-muted mb-4" style="font-size: 0.95rem;">
                            <i class="fas fa-chalkboard-teacher me-2 text-secondary"></i> Prof. <?php echo htmlspecialchars($t['tutor_nom'] . ' ' . $t['tutor_ape']); ?>
                        </div>

                        <!-- Pie: Caja Logística gris claro -->
                        <div class="rounded-3" style="background-color: #f8fafc; padding: 1.25rem; border: 1px solid #f1f5f9;">
                            <div class="mb-2 d-flex align-items-center" style="font-size: 0.95rem;">
                                <i class="far fa-calendar-alt me-3 text-secondary" style="width: 16px; text-align: center;"></i> 
                                <span class="fw-semibold text-dark"><?php echo date('d/m/Y', strtotime($t['fecha'])); ?></span>
                            </div>
                            <div class="mb-2 d-flex align-items-center" style="font-size: 0.95rem;">
                                <i class="far fa-clock me-3 text-secondary" style="width: 16px; text-align: center;"></i> 
                                <span class="text-dark"><?php echo substr($t['hora_inicio'], 0, 5) . ' - ' . substr($t['hora_fin'], 0, 5); ?> <span class="text-muted">(<?php echo htmlspecialchars($t['nombre_bloque']); ?>)</span></span>
                            </div>
                            <div class="d-flex align-items-center <?php echo $mod_color; ?> fw-bold" style="font-size: 0.95rem;">
                                <i class="<?php echo $mod_icon; ?> me-3" style="width: 16px; text-align: center;"></i> 
                                <?php echo ucfirst($t['modalidad']); ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>