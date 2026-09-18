<?php 
// Filtro de seguridad
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 
require_once __DIR__ . '/../../config/conexion.php';
// Si el usuario logueado no es estudiante, lo expulsamos al login
if ($_SESSION['rol'] !== 'estudiante') {
    header('Location: ../login/login.php');
    exit;
}
// Traemos la información académica y de contacto real del estudiante
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT e.registro_universitario, e.semestre, c.nombre_carrera, 
               u.nombre, u.apellido, u.correo, u.telefono, u.estado
        FROM estudiantes e
        JOIN usuarios u ON e.id_usuario = u.id_usuario
        JOIN carreras c ON e.id_carrera = c.id_carrera
        WHERE u.id_usuario = :id LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_usuario]);
$alumno = $stmt->fetch();

// 3. Formateamos los datos para la interfaz visual
$nombre_completo = $alumno['nombre'] . ' ' . $alumno['apellido'];

// Generamos las iniciales automáticamente (ej: "Maria Estudiante" -> "ME")
$iniciales = mb_substr($alumno['nombre'] ?? 'U', 0, 1) . mb_substr($alumno['apellido'] ?? 'P', 0, 1);

// Convertimos el número de semestre a romano de forma elegante para evitar redundancias
$semestres_romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X'];
$nivel_romano = $semestres_romanos[$alumno['semestre']] ?? $alumno['semestre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorias - UPDS</title>
    <link rel="stylesheet" href="../../assets/css/estudiante.css">
</head>
<body>
    <!-- Barra Nav Superior -->
    <header class="navbar-upds">
        <div class="navbar-marca">
            <p class="logo-texto">UPDS</p>
            <p class="separador">|</p>
            <p class="tutorias">Tutorias</p>
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
                <a href="#" class="enlace-menu activo">
                    <i>Mi Perfil</i>
                </a>
                <a href="#" class="enlace-menu">
                    <i>Solicitudes Pendientes</i> 
                </a>
                <a href="#" class="enlace-menu">
                    <i>Tutorias Disponibles</i> 
                </a>
                <a href="#" class="enlace-menu">
                    <i>Solicitar Tutorias</i>
                </a>
            </nav>
            <div class="sidebar-pie">
                <!-- 🚪 Agregamos el botón de salir real integrado en el diseño de la sede -->
                <a href="../../controllers/logout.php" style="color: #bbb; text-decoration: none; display: block; margin-bottom: 5px;">Cerrar Sesión</a>
                <span>Sede Tarija</span>
            </div>
        </aside>
        
        <!-- Área de Contenido Central -->
        <main class="contenido-panel">
            <div class="tarjeta-perfil">
                <!-- Encabezado del Perfil -->
                <div class="perfil-cabecera">
                    <div class="avatar-grande"><?= htmlspecialchars($iniciales) ?></div>
                    <div class="perfil-titulos">
                        <h2><?= htmlspecialchars($nombre_completo) ?></h2>
                        <p class="subtitulo-carrera"><?= htmlspecialchars($alumno['nombre_carrera']) ?></p>
                        <?php if ($alumno['estado'] === 'activo'): ?>
                            <span class="estado-regular">Estudiante Regular</span>
                        <?php else: ?>
                            <span class="estado-irregular">Estudiante Irregular</span>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Bloques de Información -->
                <div class="perfil-info-cuadros">
                    <div class="cuadro-datos">
                        <h3>Información Académica</h3>
                        <div class="fila-dato">
                            <span class="clave">Registro: </span>
                            <span class="valor"><?= htmlspecialchars($alumno['registro_universitario']) ?></span>
                        </div>
                        <div class="fila-dato">
                            <span class="clave">Modalidad: </span>
                            <span class="valor">Presencial (Módulo)</span>
                        </div>
                        <div class="fila-dato">
                            <span class="clave">Nivel Académico: </span>
                            <span class="valor"><?= htmlspecialchars($nivel_romano) ?> Semestre</span>
                        </div>
                    </div>

                    <div class="cuadro-datos">
                        <h3>Información de Contacto</h3>
                        <div class="fila-dato">
                            <span class="clave">Correo: </span>
                            <span class="valor"><?= htmlspecialchars($alumno['correo']) ?></span>
                        </div>
                        <div class="fila-dato">
                            <span class="clave">Teléfono: </span>
                            <span class="valor"><?= htmlspecialchars($alumno['telefono'] ?: 'No registrado') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Lema Institucional en el pie -->
                <div class="perfil-lema">
                    <p>UPDS • Profesionales</p>
                </div>

            </div>
        </main>

    </div>

</body>
</html>
