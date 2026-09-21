<?php 
// 1. Filtro de seguridad obligatorio para estudiantes y conexión
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

if ($_SESSION['rol'] !== 'estudiante') {
    header('Location: ../login/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 2. Extraemos los datos del estudiante logueado (Nombre, ID de estudiante)
$sql_alumno = "SELECT e.id_estudiante, u.nombre, u.apellido 
               FROM usuarios u
               LEFT JOIN estudiantes e ON u.id_usuario = e.id_usuario
               WHERE u.id_usuario = :id LIMIT 1";
$stmt_alumno = $pdo->prepare($sql_alumno);
$stmt_alumno->execute([':id' => $id_usuario]);
$alumno = $stmt_alumno->fetch();

$nombre_completo = $alumno['nombre'] . ' ' . $alumno['apellido'];
$iniciales = mb_substr($alumno['nombre'] ?? 'U', 0, 1) . mb_substr($alumno['apellido'] ?? 'P', 0, 1);
$id_estudiante_real = $alumno['id_estudiante'] ?? 0;

// 3. CONSULTA REAL: Traemos TODAS las solicitudes de este alumno (pendientes, aceptadas, completadas, rechazadas)
//    e incluimos el nombre del tutor asignado si es que ya la aceptaron.
$sql_tutorias = "SELECT s.id_solicitud, s.fecha_solicitada, s.hora_solicitada, s.motivo, s.enlace_reunion, s.estado_solicitud,
                        m.nombre_materia, ut.nombre AS tutor_nom, ut.apellido AS tutor_ape
                 FROM solicitudes_tutorias s
                 JOIN materias m ON s.id_materia = m.id_materia
                 LEFT JOIN tutores t ON s.id_tutor = t.id_tutor
                 LEFT JOIN usuarios ut ON t.id_usuario = ut.id_usuario
                 WHERE s.id_estudiante = :id_estudiante
                 ORDER BY s.fecha_solicitada DESC, s.hora_solicitada DESC";

$stmt_tutorias = $pdo->prepare($sql_tutorias);
$stmt_tutorias->execute([':id_estudiante' => $id_estudiante_real]);
$mis_tutorias = $stmt_tutorias->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tutorías - UPDS</title>
    <!-- Hojas de estilos del ecosistema UPDS -->
    <link rel="stylesheet" href="../../assets/css/panel.css">
    <link rel="stylesheet" href="../../assets/css/mis_tutorias_alumno.css">
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
                <a href="mis_tutorias.php" class="enlace-menu activo"><i>Mis Tutorías</i></a>
                <a href="solicitudes_pendientes.php" class="enlace-menu"><i>Solicitudes Pendientes</i></a>
                <a href="tutorias_disponibles.php" class="enlace-menu"><i>Tutorías Disponibles</i></a>
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
                <h2>Historial de Mis Tutorías</h2>
                <p>Aquí puedes realizar el seguimiento de tus solicitudes, revisar las aulas asignadas o unirte a los enlaces de Teams.</p>
            </div>

            <!-- Cuadrícula de Tarjetas -->
            <div class="grid-tutorias">
                <?php foreach ($mis_tutorias as $t): ?>
                
                <!-- Borde dinámico según el estado para encajar con el ecosistema visual -->
                <div class="tarjeta-tutoria-card estado-borde-<?= $t['estado_solicitud'] ?>">
                    
                    <!-- Encabezado con la Asignatura y el Badge de Estado -->
                    <div class="card-materia-header bg-estado-<?= $t['estado_solicitud'] ?>">
                        <span>📘 <?= htmlspecialchars($t['nombre_materia']) ?></span>
                        <span class="badge-estado-texto color-txt-<?= $t['estado_solicitud'] ?>">
                            <?= htmlspecialchars(ucfirst($t['estado_solicitud'])) ?>
                        </span>
                    </div>
                    
                    <!-- Detalles Internos -->
                    <div class="card-contenido-body">
                        
                        <div class="card-info-item">
                            <span class="label-upds">Tutor Asignado</span>
                            <span class="value-upds">
                                <?= $t['tutor_nom'] ? htmlspecialchars($t['tutor_nom'] . ' ' . $t['tutor_ape']) : '⏳ Esperando asignación...' ?>
                            </span>
                        </div>
                        
                        <div class="card-info-item">
                            <span class="label-upds">Fecha y Hora Programada</span>
                            <span class="value-upds txt-resaltado-<?= $t['estado_solicitud'] ?>">
                                📅 <?= date('d/m/Y', strtotime($t['fecha_solicitada'])) ?> - ⏰ <?= htmlspecialchars(date('H:i', strtotime($t['hora_solicitada']))) ?>
                            </span>
                        </div>

                        <div class="card-info-item">
                            <span class="label-upds">Ubicación / Enlace de Clase</span>
                            <?php if ($t['estado_solicitud'] === 'aceptada' && !empty($t['enlace_reunion'])): ?>
                                <!-- Si está aceptada, se destaca el aula/link con diseño interactivo -->
                                <div class="bloque-enlace-activo">
                                    📍 <?= htmlspecialchars($t['enlace_reunion']) ?>
                                </div>
                            <?php elseif ($t['estado_solicitud'] === 'completada'): ?>
                                <span class="value-upds" style="color: #666;">Sesión finalizada</span>
                            <?php else: ?>
                                <span class="value-upds text-mutado">Pendiente de confirmación</span>
                            <?php endif; ?>
                        </div>

                        <div class="card-info-item">
                            <span class="label-upds">Tus temas consultados</span>
                            <div class="motivo-bloque">
                                <?= htmlspecialchars($t['motivo']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de acción interactivo según el estado -->
                    <div class="card-footer-upds">
                        <?php if ($t['estado_solicitud'] === 'aceptada'): ?>
                            <!-- Botón azul oficial para unirse o ir al aula si está lista -->
                            <span class="btn-footer-info activo">¡Asiste a tu sesión de apoyo!</span>
                        <?php elseif ($t['estado_solicitud'] === 'completada'): ?>
                            <span class="btn-footer-info completado">Concluida</span>
                        <?php elseif ($t['estado_solicitud'] === 'rechazada'): ?>
                            <span class="btn-footer-info rechazado">Cancelada</span>
                        <?php else: ?>
                            <span class="btn-footer-info pendiente">A la espera del tutor</span>
                        <?php endif; ?>
                    </div>

                </div>
                <?php endforeach; ?>

                <?php if (empty($mis_tutorias)): ?>
                <div class="bandeja-vacia-upds">
                    📂 Aún no has registrado ni solicitado ninguna tutoría académica.
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>
