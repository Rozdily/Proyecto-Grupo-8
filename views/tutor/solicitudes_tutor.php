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
$sql_tutor = "SELECT id_tutor, id_usuario FROM tutores WHERE id_usuario = :id LIMIT 1";
$stmt_tutor = $pdo->prepare($sql_tutor);
$stmt_tutor->execute([':id' => $id_usuario]);
$tutor_actual = $stmt_tutor->fetch();
$id_tutor_real = $tutor_actual['id_tutor'] ?? 0;

// Calculamos las iniciales para la barra superior
$iniciales = mb_substr($_SESSION['nombre'] ?? 'T', 0, 1) . mb_substr($_SESSION['apellido'] ?? 'U', 0, 1);
$nombre_completo = ($_SESSION['nombre'] ?? 'Tutor') . ' ' . ($_SESSION['apellido'] ?? '');

// 3. CONSULTA: Traemos solicitudes pendientes
$sql_solicitudes = "SELECT s.id_solicitud, s.fecha_solicitada, s.hora_solicitada, s.motivo, 
                           m.nombre_materia, u.nombre AS est_nombre, u.apellido AS est_apellido
                    FROM solicitudes_tutorias s
                    JOIN materias m ON s.id_materia = m.id_materia
                    JOIN estudiantes e ON s.id_estudiante = e.id_estudiante
                    JOIN usuarios u ON e.id_usuario = u.id_usuario
                    WHERE s.estado_solicitud = 'pendiente'
                    ORDER BY s.fecha_solicitada ASC, s.hora_solicitada ASC";

$stmt_solicitudes = $pdo->query($sql_solicitudes);
$solicitudes = $stmt_solicitudes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Tutor - UPDS</title>
    <!-- Hojas de estilos del ecosistema UPDS -->
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
                <a href="solicitudes_tutor.php" class="enlace-menu activo"><i>Ver Solicitudes</i></a>
                <a href="tutorias_asignadas.php" class="enlace-menu"><i>Tutorias Asignadas</i></a>
            </nav>
            <div class="sidebar-pie">
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>

        <!-- Contenido Central -->
        <main class="contenido-panel">
            
            <div class="seccion-titulo-upds">
                <h2>Solicitudes de Tutoría Disponibles</h2>
                <p>Revise los requerimientos de apoyo técnico enviados por los estudiantes de la sede.</p>
            </div>

            <!-- Alertas de éxito dinámicas -->
            <?php if (isset($_SESSION['tutoria_creada'])): ?>
                <div class="alerta-exito-upds">
                    ✅ <?= htmlspecialchars($_SESSION['tutoria_creada']) ?>
                </div>
                <?php unset($_SESSION['tutoria_creada']); ?>
            <?php endif; ?>

            <!-- Cuadrícula de Tarjetas Modernas -->
            <div class="grid-solicitudes">
                <?php foreach ($solicitudes as $s): ?>
                <div class="tarjeta-solicitud-card">
                    
                    <!-- Cabecera con el nombre de la materia -->
                    <div class="card-materia-header">
                        📘 <?= htmlspecialchars($s['nombre_materia']) ?>
                    </div>
                    
                    <!-- Detalles del Alumno -->
                    <div class="card-contenido-body">
                        <div class="card-info-item">
                            <span class="label-upds">Estudiante</span>
                            <span class="value-upds"><?= htmlspecialchars($s['est_nombre'] . ' ' . $s['est_apellido']) ?></span>
                        </div>
                        
                        <div class="card-info-item">
                            <span class="label-upds">Fecha y Hora Propuesta</span>
                            <span class="value-upds fecha-resaltada">
                                📅 <?= date('d/m/Y', strtotime($s['fecha_solicitada'])) ?> - ⏰ <?= htmlspecialchars(date('H:i', strtotime($s['hora_solicitada']))) ?>
                            </span>
                        </div>

                        <div class="card-info-item">
                            <span class="label-upds">Temas a Tratar</span>
                            <div class="motivo-bloque">
                                <?= htmlspecialchars($s['motivo']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulario de Aceptación -->
                    <div class="card-formulario-footer">
                        <form action="../../controllers/aceptar_tutoria_procesar.php" method="POST">
                            <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">
                            <input type="hidden" name="id_tutor" value="<?= $id_tutor_real ?>">
                            
                            <input type="text" name="enlace_reunion" placeholder="Aula o link de Teams..." class="input-reunion-upds" required>
                            
                            <button type="submit" class="btn-aceptar-upds">
                                Aceptar Tutoría
                            </button>
                        </form>
                    </div>

                </div>
                <?php endforeach; ?>

                <?php if (empty($solicitudes)): ?>
                <div class="bandeja-vacia-upds">
                    📂 No se encuentran solicitudes de tutorías pendientes en la plataforma.
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>
