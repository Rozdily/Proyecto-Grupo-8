<?php 
// 1. Filtro de seguridad obligatorio para administradores
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 

// Si el usuario en sesión no es administrador, lo expulsamos de inmediato
if ($_SESSION['rol'] !== 'administrador') {
    header('Location: ../login/login.php');
    exit;
}

// Calculamos las iniciales del administrador logueado de forma dinámica
$iniciales = mb_substr($_SESSION['nombre'] ?? 'A', 0, 1) . mb_substr($_SESSION['apellido'] ?? 'D', 0, 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - UPDS</title>
    <link rel="stylesheet" href="../assets/css/listar.css">
</head>
<body>

    <!-- Barra Nav Superior Oficial UPDS -->
    <header class="navbar-upds">
        <div class="navbar-marca">
            <p class="logo-texto">UPDS</p>
            <p class="separador">|</p>
            <p class="tutorias">Panel de Administración</p>
        </div>
        <div class="navbar-usuario-top">
            <p class="nombre-corto"><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']) ?></p>
            <div class="avatar-mini"><?= htmlspecialchars($iniciales) ?></div>
        </div>
    </header>

    <!-- Área de Contenido Central -->
    <main class="contenido-panel">
        <div class="tarjeta-tabla">
            
            <!-- Encabezado de la Sección con el Botón de Acción -->
            <div class="tabla-cabecera">
                <div>
                    <h2>Usuarios Registrados</h2>
                    <p class="subtitulo">Gestione las cuentas de estudiantes, docentes y administradores del sistema.</p>
                </div>
                <a href="usuarios_crear.php" class="btn-nuevo">+ Nuevo Usuario</a>
            </div>

            <!-- Contenedor Responsivo para evitar que la tabla rompa en celulares -->
            <div class="contenedor-tabla-adaptable">
                <table class="tabla-upds">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Correo Electrónico</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="col-id">#<?= htmlspecialchars($u['id_usuario']) ?></td>
                            <td class="col-nombre">
                                <strong><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td class="col-usuario"><?= htmlspecialchars($u['usuario']) ?></td>
                            <td>
                                <span class="badge-rol rol-<?= strtolower(htmlspecialchars($u['nombre_rol'])) ?>">
                                    <?= htmlspecialchars(ucfirst($u['nombre_rol'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-estado estado-<?= strtolower(htmlspecialchars($u['estado'])) ?>">
                                    <?= htmlspecialchars(ucfirst($u['estado'])) ?>
                                </span>
                            </td>
                            <td class="col-fecha"><?= htmlspecialchars(date('d/m/Y', strtotime($u['fecha_registro']))) ?></td>
                            <td class="col-acciones">
                                <a href="usuarios_editar.php?id=<?= $u['id_usuario'] ?>" class="accion-link editar">Editar</a>
                                <span class="divisor-accion">|</span>
                                <a href="usuarios_eliminar.php?id=<?= $u['id_usuario'] ?>" 
                                   class="accion-link eliminar"
                                   onclick="return confirm('¿Está seguro de que desea eliminar permanentemente a este usuario?');">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="8" class="tabla-vacia">
                                📂 No hay usuarios registrados todavía en el sistema.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
    <!-- BOTÓN CERRAR SESIÓN -->
    <a href="../login/login.php" class="btn-salir-flotante">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        <p>Cerrar Sesión</p>
    </a>
</body>
</html>
