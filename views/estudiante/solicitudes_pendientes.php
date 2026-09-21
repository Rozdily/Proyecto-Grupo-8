<?php 
// 1. Filtro de seguridad obligatorio y conexión
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

if ($_SESSION['rol'] !== 'estudiante') {
    header('Location: ../login/login.php');
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

// 2. Extraemos los datos del estudiante logueado para la barra superior
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

// 3. 🔒 CONSULTA BLINDADA DE SEGURIDAD: Solo extrae solicitudes que PERTENEZCAN al alumno logueado
$sql_pendientes = "SELECT s.id_solicitud, s.fecha_solicitada, 
                          TIME_FORMAT(s.hora_solicitada, '%H:%i') AS hora_limpia, 
                          s.motivo, s.estado_solicitud, m.nombre_materia
                   FROM solicitudes_tutorias s
                   JOIN materias m ON s.id_materia = m.id_materia
                   WHERE s.id_estudiante = :id_estudiante 
                     AND s.estado_solicitud = 'pendiente'
                   ORDER BY s.fecha_solicitada ASC, s.hora_solicitada ASC";

$stmt_pendientes = $pdo->prepare($sql_pendientes);
$stmt_pendientes->execute([':id_estudiante' => $id_estudiante_real]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes Pendientes - UPDS</title>
    <!-- 🎨 Enlaces externos centralizados -->
    <link rel="stylesheet" href="../../assets/css/panel.css">
    <link rel="stylesheet" href="../../assets/css/solicitudes_pendientes.css">
</head>
<body>

    <!-- Barra Nav Superior -->
    <header class="navbar-upds">
        <div class="navbar-marca">
            <p class="logo-texto">UPDS</p>
            <p class="separador">|</p>
            <p class="tutorias">Tutorias Estudiantes</p>
        </div>
        <div class="navbar-usuario-top">
            <p class="nombre-corto"><?= htmlspecialchars($nombre_completo) ?></p>
            <div class="avatar-mini"><?= htmlspecialchars($iniciales) ?></div>
        </div>
    </header>

    <!-- Contenedor Principal (Menú + Contenido) -->
    <div class="contenedor-portal">
        <!-- Menú Lateral Izquierdo -->
        <aside class="sidebar-upds">
            <nav class="menu-enlaces">
                <a href="panel.php" class="enlace-menu">
                    <i>Mi Perfil</i>
                </a>
                <a href="mis_tutorias.php" class="enlace-menu">
                    <i>Mis Tutorías</i> 
                </a>
                <a href="solicitudes_pendientes.php" class="enlace-menu activo">
                    <i>Solicitudes Pendientes</i> 
                </a>
                <a href="tutorias_disponibles.php" class="enlace-menu">
                    <i>Tutorias Disponibles</i> 
                </a>
                <a href="solicitar_tutoria.php" class="enlace-menu">
                    <i>Solicitar Tutorias</i>
                </a>
            </nav>
            <div class="sidebar-pie">
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>
        
        <!-- Área de Contenido Central (Tarjeta de la Tabla) -->
        <main class="contenido-panel">
            <div class="tarjeta-tabla">
                <h2 class="tabla-titulo">Solicitudes de Tutoría Pendientes</h2>
                <p class="tabla-subtitulo">Listado de tutorías enviadas que se encuentran a la espera de confirmación por parte del docente.</p>                
                <!-- Alerta dinámica de éxito al registrar -->
                <?php if (isset($_SESSION['solicitud_exito'])): ?>
                    <div class="alerta-exito">
                        ✅ <?= htmlspecialchars($_SESSION['solicitud_exito']) ?>
                    </div>
                    <?php unset($_SESSION['solicitud_exito']); ?>
                <?php endif; ?>

                <div class="tabla-contenedor">
                    <table class="tabla-upds">
                        <thead>
                            <tr>
                                <th>Asignatura</th>
                                <th>Fecha Tentativa</th>
                                <th>Hora</th>
                                <th>Motivo / Temas</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 0;
                            while ($row = $stmt_pendientes->fetch(PDO::FETCH_ASSOC)): 
                                $contador++;
                                $fecha_formateada = date('d/m/Y', strtotime($row['fecha_solicitada']));
                            ?>
                                <tr>
                                    <td class="celda-materia"><?= htmlspecialchars($row['nombre_materia']) ?></td>
                                    <td><?= htmlspecialchars($fecha_formateada) ?></td>
                                    <td><?= htmlspecialchars($row['hora_limpia']) ?></td>
                                    <td>
                                        <div class="texto-motivo"><?= htmlspecialchars($row['motivo']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-pendiente"><?= htmlspecialchars($row['estado_solicitud']) ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if ($contador === 0): ?>
                                <tr>
                                    <td colspan="5" class="sin-registros">
                                        No registras solicitudes de tutorías pendientes en este momento.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>