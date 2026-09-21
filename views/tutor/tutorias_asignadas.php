<?php 
// 1. Filtro de seguridad obligatorio para tutores y conexión
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

if ($_SESSION['rol'] !== 'tutor') {
    header('Location: ../login/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 2. Extraemos el ID del tutor logueado mediante su cuenta de usuario
$sql_tutor = "SELECT id_tutor FROM tutores WHERE id_usuario = :id LIMIT 1";
$stmt_tutor = $pdo->prepare($sql_tutor);
$stmt_tutor->execute([':id' => $id_usuario]);
$tutor_actual = $stmt_tutor->fetch();
$id_tutor_real = $tutor_actual['id_tutor'] ?? 0;

// Calculamos las iniciales y nombre completo para la barra superior
$iniciales = mb_substr($_SESSION['nombre'] ?? 'T', 0, 1) . mb_substr($_SESSION['apellido'] ?? 'U', 0, 1);
$nombre_completo = ($_SESSION['nombre'] ?? 'Tutor') . ' ' . ($_SESSION['apellido'] ?? '');

// 3. CONSULTA: Traemos únicamente las tutorías que ya fueron ACEPTADAS por este tutor
$sql_agendadas = "SELECT s.id_solicitud, s.fecha_solicitada, s.hora_solicitada, s.motivo, s.enlace_reunion, s.estado_solicitud,
                          m.nombre_materia, u.nombre AS est_nombre, u.apellido AS est_apellido
                   FROM solicitudes_tutorias s
                   JOIN materias m ON s.id_materia = m.id_materia
                   JOIN estudiantes e ON s.id_estudiante = e.id_estudiante
                   JOIN usuarios u ON e.id_usuario = u.id_usuario
                   WHERE s.id_tutor = :id_tutor 
                     AND s.estado_solicitud = 'aceptada'
                   ORDER BY s.fecha_solicitada ASC, s.hora_solicitada ASC";

$stmt_agendadas = $pdo->prepare($sql_agendadas);
$stmt_agendadas->execute([':id_tutor' => $id_tutor_real]);
$tutorias_aceptadas = $stmt_agendadas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorías Agendadas - UPDS</title>
    <!-- Vinculación de estilos del ecosistema UPDS -->
    <link rel="stylesheet" href="../../assets/css/panel.css">
    <link rel="stylesheet" href="../../assets/css/solicitudes_tutor.css">
</head>
<body>

    <!-- Barra Nav Superior Oficial UPDS -->
    <header class="navbar-upds">
        <div class="navbar-marca">
            <p class="logo-texto">UPDS</p>
            <p class="separador">|</p>
            <p class="tutorias">Portal Tutores</p>
        </div>
        <div class="navbar-usuario-top">
            <p class="nombre-corto"><?= htmlspecialchars($nombre_completo) ?></p>
            <div class="avatar-mini"><?= htmlspecialchars($iniciales) ?></div>
        </div>
    </header>

    <div class="contenedor-portal">
        <!-- Menú Lateral Tutor -->
        <aside class="sidebar-upds">
            <nav class="menu-enlaces">
                <a href="panel.php" class="enlace-menu"><i>Mi Perfil</i></a>
                <a href="solicitudes_tutor.php" class="enlace-menu"><i>Ver Solicitudes</i></a>
                <a href="tutorias_asignadas.php" class="enlace-menu activo"><i>Tutorias Asignadas</i></a>
            </nav>
            <div class="sidebar-pie">
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>

        <!-- Contenido Central -->
        <main class="contenido-panel">
            
            <div class="seccion-titulo-upds">
                <h2>Mis Tutorías Asignadas</h2>
                <p>Cronograma de sesiones de apoyo académico que has aceptado y tienes pendientes por dictar.</p>
            </div>

            <!-- Cuadrícula de Tarjetas de Agenda -->
            <div class="grid-solicitudes">
                <?php foreach ($tutorias_aceptadas as $t): ?>
                <div class="tarjeta-solicitud-card" style="border-top: 4px solid #137333;">
                    
                    <!-- Cabecera de la Materia con indicador de estado Aceptado -->
                    <div class="card-materia-header" style="background-color: #137333; display: flex; justify-content: space-between; align-items: center;">
                        <span>📘 <?= htmlspecialchars($t['nombre_materia']) ?></span>
                        <span style="background-color: #e6f4ea; color: #137333; font-size: 11px; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">Confirmada</span>
                    </div>
                    
                    <!-- Cuerpo con datos del estudiante y el agendamiento -->
                    <div class="card-contenido-body">
                        <div class="card-info-item">
                            <span class="label-upds">Estudiante Asignado</span>
                            <span class="value-upds"><?= htmlspecialchars($t['est_nombre'] . ' ' . $t['est_apellido']) ?></span>
                        </div>
                        
                        <div class="card-info-item">
                            <span class="label-upds">Horario Programado</span>
                            <span class="value-upds" style="color: #137333;">
                                📅 <?= date('d/m/Y', strtotime($t['fecha_solicitada'])) ?> - ⏰ <?= htmlspecialchars(date('H:i', strtotime($t['hora_solicitada']))) ?>
                            </span>
                        </div>

                        <div class="card-info-item">
                            <span class="label-upds">Lugar / Enlace de Reunión</span>
                            <!-- Resaltamos el aula o link de Teams ingresado por el tutor -->
                            <span class="value-upds" style="color: #0067b8; font-family: monospace; background: #f0f4f8; padding: 6px; border-radius: 4px; border: 1px dashed #0067b8;">
                                🔗 <?= htmlspecialchars($t['enlace_reunion']) ?>
                            </span>
                        </div>

                        <div class="card-info-item">
                            <span class="label-upds">Temas a Desarrollar</span>
                            <div class="motivo-bloque">
                                <?= htmlspecialchars($t['motivo']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer netamente visual o informativo -->
                    <div class="card-formulario-footer" style="text-align: center; font-size: 13px; color: #666; font-weight: 600; background-color: #fafafa;">
                        Sesión programada correctamente
                    </div>

                </div>
                <?php endforeach; ?>

                <?php if (empty($tutorias_aceptadas)): ?>
                <div class="bandeja-vacia-upds">
                    📅 No tienes tutorías agendadas ni aceptadas en tu calendario actual.
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>
