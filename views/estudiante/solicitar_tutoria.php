<?php 
// 1. Filtro de seguridad obligatorio y conexión
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';

if ($_SESSION['rol'] !== 'estudiante') {
    header('Location: ../login/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// 2. Extraemos los datos del estudiante logueado (Nombre, Carrera e ID de carrera)
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

// Protegemos el ID real del estudiante para que nunca viaje vacío al controlador
$id_estudiante_seguro = $alumno['id_estudiante'] ?? 0;

// 3. CONSULTA DINÁMICA: Traemos todas las materias de la carrera de este estudiante
$sql_materias = "SELECT id_materia, nombre_materia 
                 FROM materias 
                 WHERE id_carrera = :id_carrera 
                 ORDER BY nombre_materia ASC";
$stmt_materias = $pdo->prepare($sql_materias);
$stmt_materias->execute([':id_carrera' => $id_carrera_estudiante]);

// Guardamos las materias en un array para controlar si la lista está vacía
$materias_lista = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Tutoría - UPDS</title>
    <link rel="stylesheet" href="../../assets/css/panel.css">
    <link rel="stylesheet" href="../../assets/css/solicitar_tutoria.css">
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

    <!-- Contenedor Principal -->
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
                <a href="solicitudes_pendientes.php" class="enlace-menu">
                    <i>Solicitudes Pendientes</i> 
                </a>
                <a href="tutorias_disponibles.php" class="enlace-menu">
                    <i>Tutorias Disponibles</i> 
                </a>
                <a href="solicitar_tutoria.php" class="enlace-menu activo">
                    <i>Solicitar Tutorias</i>
                </a>
            </nav>
            <div class="sidebar-pie">
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>
        
        <!-- Área de Contenido Central -->
        <main class="contenido-panel">
            <div class="tarjeta-formulario">
                <h2 class="formulario-titulo">Nueva Solicitud de Tutoría</h2>
                <p class="formulario-subtitulo">Complete los campos académicos para agendar una sesión de apoyo técnico.</p>
                
                <!-- Gestión de alertas de error en la misma página por si el controlador devuelve un fallo -->
                <?php if (isset($_GET['error']) && $_GET['error'] === 'sql'): ?>
                    <div style="background-color: #fce8e6; color: #b3261e; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; font-weight: 500;">
                        ⚠️ Error al registrar en la Base de Datos. Verifique la estructura de las tablas.
                    </div>
                <?php endif; ?>

                <form action="../../controllers/solicitar_tutoria_procesar.php" method="POST">
                    
                    <!-- ID Oculto del alumno corregido y protegido -->
                    <input type="hidden" name="id_estudiante" value="<?= htmlspecialchars($id_estudiante_seguro) ?>">

                    <!-- Selector de Materias Adaptable -->
                    <div class="campo-grupo">
                        <label for="id_materia">Asignatura o Materia</label>
                        <select name="id_materia" id="id_materia" class="input-upds" required>
                            <?php if (empty($materias_lista)): ?>
                                <option value="" disabled selected>No hay materias asignadas a tu carrera actualmente...</option>
                            <?php else: ?>
                                <option value="" disabled selected>Seleccione la materia que requiere apoyo...</option>
                                <?php foreach ($materias_lista as $materia): ?>
                                    <option value="<?= $materia['id_materia'] ?>">
                                        <?= htmlspecialchars($materia['nombre_materia']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Fila Doble: Fecha y Hora -->
                    <div class="fila-doble">
                        <div class="campo-grupo">
                            <label for="fecha_solicitada">Fecha Tentativa</label>
                            <input type="date" name="fecha_solicitada" id="fecha_solicitada" class="input-upds" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="campo-grupo">
                            <label for="hora_solicitada">Hora Solicitada</label>
                            <input type="time" name="hora_solicitada" id="hora_solicitada" class="input-upds" required>
                        </div>
                    </div>

                    <!-- Motivo -->
                    <div class="campo-grupo">
                        <label for="motivo">Motivo o Temas a Tratar</label>
                        <textarea name="motivo" id="motivo" class="input-upds" rows="4" placeholder="Ej: Dudas con el modelado de bases de datos relacionales y consultas con JOINs..." required></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="area-botones">
                        <a href="panel.php" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-enviar">Enviar Solicitud</button>
                    </div>

                </form>
            </div>
        </main>
    </div>

</body>
</html>
