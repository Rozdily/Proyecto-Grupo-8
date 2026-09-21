<?php 
// 1. Filtro de seguridad obligatorio para estudiantes y conexión
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

if ($_SESSION['rol'] !== 'estudiante') {
    header('Location: ../login/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 2. Extraemos los datos del estudiante logueado (Nombre, Carrera e ID de carrera/estudiante)
$sql_alumno = "SELECT e.id_estudiante, e.id_carrera, u.nombre, u.apellido 
               FROM usuarios u
               LEFT JOIN estudiantes e ON u.id_usuario = e.id_usuario
               WHERE u.id_usuario = :id LIMIT 1";
$stmt_alumno = $pdo->prepare($sql_alumno);
$stmt_alumno->execute([':id' => $id_usuario]);
$alumno = $stmt_alumno->fetch();

$nombre_completo = $alumno['nombre'] . ' ' . $alumno['apellido'];
$iniciales = mb_substr($alumno['nombre'] ?? 'U', 0, 1) . mb_substr($alumno['apellido'] ?? 'P', 0, 1);
$id_carrera_estudiante = $alumno['id_carrera'] ?? 0;
$id_estudiante_real = $alumno['id_estudiante'] ?? 0;

// 3. CONSULTA CON FILTRO DE EXCLUSIÓN: 
//    Trae tutorías ya 'aceptadas' por un tutor, correspondientes a su carrera,
//    pero EXCLUYE estrictamente aquellas que fueron creadas por este mismo estudiante (:id_estudiante)
$sql_disponibles = "SELECT s.id_solicitud, s.fecha_solicitada, s.hora_solicitada, s.motivo, s.enlace_reunion,
                           m.nombre_materia, ut.nombre AS tutor_nom, ut.apellido AS tutor_ape
                    FROM solicitudes_tutorias s
                    JOIN materias m ON s.id_materia = m.id_materia
                    JOIN tutores t ON s.id_tutor = t.id_tutor
                    JOIN usuarios ut ON t.id_usuario = ut.id_usuario
                    WHERE m.id_carrera = :id_carrera
                      AND s.estado_solicitud = 'aceptada'
                      AND s.id_estudiante != :id_estudiante
                    ORDER BY s.fecha_solicitada ASC, s.hora_solicitada ASC";

$stmt_disponibles = $pdo->prepare($sql_disponibles);
$stmt_disponibles->execute([
    ':id_carrera' => $id_carrera_estudiante,
    ':id_estudiante' => $id_estudiante_real
]);
$tutorias_disponibles = $stmt_disponibles->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorías Disponibles - UPDS</title>
    <!-- Hojas de estilos corporativas -->
    <link rel="stylesheet" href="../../assets/css/panel.css">
    <link rel="stylesheet" href="../../assets/css/tutorias_disponibles.css">
</head>
<body>

    <!-- Barra Nav Superior Oficial UPDS -->
    <header class="navbar-upds">
        <div class="navbar-marca">
            <p class="logo-texto">UPDS</p>
            <p class="separador">|</p>
            <p class="tutorias">Tutorías Estudiantes</p>
        </div>
        <div class="navbar-usuario-top">
            <p class="nombre-corto"><?= htmlspecialchars($nombre_completo) ?></p>
            <div class="avatar-mini"><?= htmlspecialchars($iniciales) ?></div>
        </div>
    </header>

    <div class="contenedor-portal">
        <!-- Menú Lateral Estudiante -->
        <aside class="sidebar-upds">
            <nav class="menu-enlaces">
                <a href="panel.php" class="enlace-menu"><i>Mi Perfil</i></a>
                <a href="mis_tutorias.php" class="enlace-menu"><i>Mis Tutorías</i></a>
                <a href="solicitudes_pendientes.php" class="enlace-menu"><i>Solicitudes Pendientes</i></a>
                <a href="tutorias_disponibles.php" class="enlace-menu activo"><i>Tutorías Disponibles</i></a>
                <a href="solicitar_tutoria.php" class="enlace-menu"><i>Solicitar Tutorías</i></a>
            </nav>
            <div class="sidebar-pie">
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>

        <!-- Contenido Central -->
        <main class="contenido-panel">
            
            <div class="seccion-titulo-upds">
                <h2>Tutorías Grupales Disponibles</h2>
                <p>Únete a las sesiones de apoyo ya confirmadas por los tutores en las asignaturas de tu plan de estudios.</p>
            </div>

            <!-- Mensajes flotantes de éxito de sesión -->
            <?php if (isset($_SESSION['inscripcion_exito'])): ?>
                <div class="alerta-exito-upds">
                    ✅ <?= htmlspecialchars($_SESSION['inscripcion_exito']) ?>
                </div>
                <?php unset($_SESSION['inscripcion_exito']); ?>
            <?php endif; ?>

            <!-- Rejilla Adaptable de Tutorías Colectivas -->
            <div class="grid-disponibles">
                <?php foreach ($tutorias_disponibles as $t): ?>
                <div class="tarjeta-disponible-card">
                    
                    <!-- Cabecera de la Tarjeta con el Azul Corporativo -->
                    <div class="card-materia-header">
                        <span>📘 <?= htmlspecialchars($t['nombre_materia']) ?></span>
                        <span class="badge-disponible">Disponible</span>
                    </div>
                    
                    <!-- Cuerpo de Detalles -->
                    <div class="card-contenido-body">
                        <div class="card-info-item">
                            <span class="label-upds">Tutor a Cargo</span>
                            <span class="value-upds">Prof. <?= htmlspecialchars($t['tutor_nom'] . ' ' . $t['tutor_ape']) ?></span>
                        </div>
                        
                        <div class="card-info-item">
                            <span class="label-upds">Horario de la Sesión</span>
                            <span class="value-upds fecha-resaltada">
                                📅 <?= date('d/m/Y', strtotime($t['fecha_solicitada'])) ?> - ⏰ <?= htmlspecialchars(date('H:i', strtotime($t['hora_solicitada']))) ?>
                            </span>
                        </div>

                        <div class="card-info-item">
                            <span class="label-upds">Temas y Objetivos</span>
                            <div class="motivo-bloque">
                                <?= htmlspecialchars($t['motivo']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Acción para inscribirse / unirse a la tutoría grupal -->
                    <div class="card-formulario-footer">
                        <form action="../../controllers/unirse_tutoria_procesar.php" method="POST">
                            <input type="hidden" name="id_solicitud" value="<?= $t['id_solicitud'] ?>">
                            <input type="hidden" name="id_estudiante" value="<?= $id_estudiante_real ?>">
                            
                            <button type="submit" class="btn-unirse-upds">
                                Inscribirse a esta Tutoría
                            </button>
                        </form>
                    </div>

                </div>
                <?php endforeach; ?>

                <?php if (empty($tutorias_disponibles)): ?>
                <div class="bandeja-vacia-upds">
                    📂 No hay otras tutorías programadas para tu carrera en este momento.
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>
