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
    <title>Administración - UPDS</title>
    <!-- Compartimos la misma hoja de estilos que el formulario de crear para ahorrar código -->
    <link rel="stylesheet" href="../assets/css/registro.css">
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
        <div class="tarjeta-formulario">
            
            <!-- Encabezado de la Sección -->
            <div class="formulario-cabecera">
                <h2>Editar Usuario</h2>
                <p class="subtitulo">Modifique los permisos, datos personales o estado de la cuenta seleccionada.</p>
            </div>

            <!-- 🚨 CAJA DE ALERTA DE ERRORES DINÁMICA DE PHP -->
            <?php if (!empty($errores)): ?>
                <?php foreach ($errores as $e): ?>
                    <div class="alerta-error" style="margin-bottom: 10px;">
                        <p>⚠️ <?= htmlspecialchars($e) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Formulario de Edición Estilo UPDS integrado con el Backend -->
            <form method="POST" class="formulario-upds">
                
                <!-- ID de usuario oculto obligatorio para procesar el UPDATE en SQL -->
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario_actual['id_usuario']) ?>">

                <div class="grupo-fila">
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Rol del Usuario</label>
                        <select name="id_rol" class="campo-seleccion" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id_rol'] ?>" <?= $r['id_rol'] == $usuario_actual['id_rol'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($r['nombre_rol'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Estado de la Cuenta</label>
                        <select name="estado" class="campo-seleccion" required>
                            <option value="activo" <?= $usuario_actual['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $usuario_actual['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="grupo-fila">
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Nombre</label>
                        <input type="text" name="nombre" class="campo-entrada" value="<?= htmlspecialchars($usuario_actual['nombre']) ?>" required>
                    </div>
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Apellido</label>
                        <input type="text" name="apellido" class="campo-entrada" value="<?= htmlspecialchars($usuario_actual['apellido']) ?>" required>
                    </div>
                </div>

                <div class="grupo-campo">
                    <label class="etiqueta-formulario">Correo Electrónico</label>
                    <input type="email" name="correo" class="campo-entrada" value="<?= htmlspecialchars($usuario_actual['correo']) ?>" required>
                </div>

                <div class="grupo-campo">
                    <label class="etiqueta-formulario">Nombre de Usuario</label>
                    <input type="text" name="usuario" class="campo-entrada" value="<?= htmlspecialchars($usuario_actual['usuario']) ?>" required>
                </div>

                <!-- Botones de Acción Inferiores -->
                <div class="seccion-botones">
                    <a href="usuarios_listar.php" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
