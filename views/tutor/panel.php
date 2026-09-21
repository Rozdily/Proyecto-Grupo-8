<?php 
// Filtro de seguridad
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

// Si el usuario logueado no es docente, lo expulsamos al login
if ($_SESSION['rol'] !== 'tutor') {
    header('Location: ../login/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// traemos los Datos del usuario, su especialidad y biografía de tutor
$sql_tutor = "SELECT t.id_tutor, t.especialidad, t.biografia, 
                     u.nombre, u.apellido, u.correo, u.telefono, u.estado
              FROM usuarios u
              LEFT JOIN tutores t ON u.id_usuario = t.id_usuario
              WHERE u.id_usuario = :id LIMIT 1";

$stmt_tutor = $pdo->prepare($sql_tutor);
$stmt_tutor->execute([':id' => $id_usuario]);
$tutor = $stmt_tutor->fetch();

$nombre_completo = $tutor['nombre'] . ' ' . $tutor['apellido'];

// Calculamos las iniciales automáticamente
$iniciales = mb_substr($tutor['nombre'] ?? 'U', 0, 1) . mb_substr($tutor['apellido'] ?? 'P', 0, 1);

// Consulta SECUNDARIA: Extraer los horarios usando TIME_FORMAT para omitir los segundos (:00)
$sql_horarios = "SELECT dia_semana, 
                        TIME_FORMAT(hora_inicio, '%H:%i') AS inicio_limpio, 
                        TIME_FORMAT(hora_fin, '%H:%i') AS fin_limpio
                 FROM disponibilidad_tutor 
                 WHERE id_tutor = :id_tutor 
                 ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado')";

$stmt_horarios = $pdo->prepare($sql_horarios);
$stmt_horarios->execute([':id_tutor' => $tutor['id_tutor']]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorias - UPDS</title>
    <link rel="stylesheet" href="../../assets/css/panel.css">
</head>
<body>
    <!-- Barra Nav Superior -->
    <header class="navbar-upds">
        <div class="navbar-marca">
            <p class="logo-texto">UPDS</p>
            <p class="separador">|</p>
            <p class="tutorias">Tutorias Docentes</p>
        </div>
        <div class="navbar-usuario-top">
            <p class="nombre-corto"><?= htmlspecialchars($nombre_completo) ?></p>
            <div class="avatar-mini"><?= htmlspecialchars($iniciales) ?></div>
        </div>
    </header>

    <!-- Contenedor Principal (Menú + Contenido) -->
    <div class="contenedor-portal">
        <!-- Menú Lateral -->
        <aside class="sidebar-upds">
            <nav class="menu-enlaces">
                <a href="panel.php" class="enlace-menu activo">
                    <i>Mi Perfil</i>
                </a>
                <a href="solicitudes_tutor.php" class="enlace-menu">
                    <i>Ver Solicitudes</i> 
                </a>
                <a href="tutorias_asignadas.php" class="enlace-menu">
                    <i>Tutorias Asignadas</i> 
                </a>
            </nav>
            <div class="sidebar-pie">
                <!-- 🚪 Enlace conectado para destruir la sesión de forma segura -->
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>
        
        <!-- Área de Contenido Central -->
        <main class="contenido-panel">
            <div class="tarjeta-perfil">
                <!-- Encabezado del Perfil del Docente -->
                <div class="perfil-cabecera">
                    <div class="avatar-grande"><?= htmlspecialchars($iniciales) ?></div>
                    <div class="perfil-titulos">
                        <h2><?= htmlspecialchars($nombre_completo) ?></h2>
                        <p class="subtitulo-carrera"><?= htmlspecialchars($tutor['especialidad'] ?: 'Sin especialidad') ?></p>
                        
                        <!-- 🚦 Filtro inteligente de estado laboral del docente -->
                        <?php if ($tutor['estado'] === 'activo'): ?>
                            <p class="estado-regular" style="background-color: #d4edda; color: #155724; display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 0.85rem;">Docente Activo</p>
                        <?php else: ?>
                            <p class="estado-regular" style="background-color: #f8d7da; color: #721c24; display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 0.85rem;">Docente Inactivo</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bloques de Información (Estructura Apilada Verticalmente) -->
                <div class="perfil-info-cuadros">
                    
                    <!-- Bloque de Disponibilidad Dinámico con un bucle while -->
                    <div class="cuadro-datos">
                        <h3>Disponibilidad</h3>
                        <?php 
                        $tiene_horarios = false;
                        // 🔄 El ciclo recorre cada fila de disponibilidad del docente en la base de datos
                        while ($horario = $stmt_horarios->fetch(PDO::FETCH_ASSOC)): 
                            $tiene_horarios = true;
                        ?>
                            <div class="fila-dato" style="margin-bottom: 8px;">
                                <p class="clave"><?= htmlspecialchars($horario['dia_semana']) ?>:</p>
                                <p class="valor"><?= htmlspecialchars($horario['inicio_limpio']) ?> - <?= htmlspecialchars($horario['fin_limpio']) ?></p>
                            </div>
                        <?php endwhile; ?>
                        
                        <?php if (!$tiene_horarios): ?>
                            <div class="fila-dato">
                                <p class="clave">Estado:</p>
                                <p class="valor" style="color: #721c24;">No tiene horarios registrados esta semana.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Bloque de Información de Contacto -->
                    <div class="cuadro-datos">
                        <h3>Información de Contacto</h3>
                        <div class="fila-dato">
                            <p class="clave">Correo:</p>
                            <p class="valor"><?= htmlspecialchars($tutor['correo']) ?></p>
                        </div>
                        <div class="fila-dato">
                            <p class="clave">Teléfono:</p>
                            <p class="valor"><?= htmlspecialchars($tutor['telefono']) ?></p>
                        </div>
                    </div>
                    <!-- Bloque de Biografía -->
                    <div class="cuadro-datos">
                        <h3>Biografía Profesional</h3>
                        <div class="fila-dato" style="flex-direction: column; align-items: flex-start;">
                            <p class="valor" style="text-align: left; font-weight: normal; line-height: 1.5; color: #333; white-space: pre-line;">
                                <?= htmlspecialchars($tutor['biografia'] ?: 'El docente no ha redactado su biografía profesional todavía.') ?>
                            </p>
                        </div>
                    </div>
                </div>
                <p class="perfil-lema">UPDS • Profesionales</p>
            </div>
        </main>
    </div>
</body>
</html>