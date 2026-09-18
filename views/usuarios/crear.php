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
    <link rel="stylesheet" href="../assets/css/registro.css">
</head>
<body>

    <!-- Barra Nav Superior -->
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
                <h2>Registrar Nuevo Usuario</h2>
                <p class="subtitulo">Asigne credenciales y roles dentro del sistema de Tutorías.</p>
            </div>

            <!-- 🚨 CAJA DE ALERTA DE ERRORES DINÁMICA DE PHP -->
            <?php if (!empty($errores)): ?>
                <?php foreach ($errores as $e): ?>
                    <div class="alerta-error" style="margin-bottom: 10px;">
                        <p>⚠️ <?= htmlspecialchars($e) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Formulario de Registro Estilo UPDS integrado con el Backend -->
            <form method="POST" class="formulario-upds">
                
                <div class="grupo-campo">
                    <label class="etiqueta-formulario">Rol del Usuario</label>
                    <select name="id_rol" class="campo-seleccion" required>
                      <option value="" disabled selected>Seleccione un rol...</option>
                      <?php foreach ($roles as $r): ?>
                          <?php if ((int)$r['id_rol'] === 1) continue; ?>
                          <option value="<?= $r['id_rol'] ?>" <?= isset($_POST['id_rol']) && $_POST['id_rol'] == $r['id_rol'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars(ucfirst($r['nombre_rol'])) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
                </div>

                <div class="grupo-fila">
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Nombre</label>
                        <input type="text" name="nombre" class="campo-entrada" placeholder="Ej. Juan" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Apellido</label>
                        <input type="text" name="apellido" class="campo-entrada" placeholder="Ej. Pérez" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="grupo-campo">
                    <label class="etiqueta-formulario">Correo Electrónico</label>
                    <input type="email" name="correo" class="campo-entrada" placeholder="juan.perez@upds.net" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
                </div>

                <div class="grupo-fila">
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Nombre de Usuario</label>
                        <input type="text" name="usuario" class="campo-entrada" placeholder="juan.perez" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" required>
                    </div>
                    <div class="grupo-campo">
                        <label class="etiqueta-formulario">Contraseña</label>
                        <!-- Se asume el name="clave" sugerido por tu HTML. Asegúrate que coincida con el $_POST del controlador original -->
                        <input type="password" name="clave" class="campo-entrada" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                </div>

                <!-- Botones de Acción Inferiores -->
                <div class="seccion-botones">
                    <a href="usuarios_listar.php" class="btn-cancelar">Cancelar</a>
                    <button type="submit" class="btn-guardar">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
