<?php 
// Filtro de seguridad obligatorio para administradores
require_once __DIR__ . '/../../includes/verificar_sesion.php'; 

// Conexion a la base de datos
$query_carreras = $pdo->query("SELECT id_carrera, nombre_carrera FROM carreras ORDER BY nombre_carrera ASC");
$carreras = $query_carreras->fetchAll(PDO::FETCH_ASSOC);

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
    <style>
        /* Estilos necesarios para el comportamiento de ocultado/mostrado */
        .dinamico-oculto {
            display: none !important;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .dinamico-visible {
            display: flex !important; /* Mantiene el comportamiento de flexbox en las filas */
            opacity: 1;
        }
        .dinamico-visible-bloque {
            display: block !important; /* Mantiene el bloque completo para la biografía */
            opacity: 1;
        }
        .textarea-biografia {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            margin-top: 6px;
        }
    </style>
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

            <?php if (!empty($errores)): ?> 
                <?php foreach ($errores as $e): ?> 
                    <div class="alerta-error" style="margin-bottom: 10px;"> 
                        <p>⚠️ <?= htmlspecialchars($e) ?></p> 
                    </div> 
                <?php endforeach; ?> 
            <?php endif; ?> 

            <!-- Formulario de Registro Estilo UPDS --> 
            <form method="POST" class="formulario-upds" id="formRegistroUsuario"> 
                
                <!-- Selector de Rol -->
                <div class="grupo-campo"> 
                    <label class="etiqueta-formulario">Rol del Usuario</label> 
                    <select name="id_rol" id="selectorRol" class="campo-seleccion" required> 
                        <option value="" disabled selected>Seleccione un rol...</option> 
                        <?php foreach ($roles as $r): ?> 
                            <?php if ((int)$r['id_rol'] === 1) continue; ?> 
                            <option value="<?= $r['id_rol'] ?>" 
                                    data-rol="<?= strtolower(htmlspecialchars($r['nombre_rol'])) ?>"
                                    <?= isset($_POST['id_rol']) && $_POST['id_rol'] == $r['id_rol'] ? 'selected' : '' ?>> 
                                <?= htmlspecialchars(ucfirst($r['nombre_rol'])) ?> 
                            </option> 
                        <?php endforeach; ?> 
                    </select> 
                </div> 

                <!-- FILA 1: Nombres y Apellidos -->
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

                <!-- FILA INTERACTIVA ESTUDIANTE: Teléfono y Carrera lado a lado -->
                <!-- Por defecto se oculta toda la fila usando la clase dinámica -->
                <div class="grupo-fila dinamico-oculto" id="bloqueCarrera"> 
                    <div class="grupo-campo"> 
                        <label class="etiqueta-formulario">Teléfono</label> 
                        <!-- Quitamos el required nativo aquí y dejamos que el JS lo maneje para no bloquear otros roles -->
                        <input type="text" name="telefono" id="inputTelefono" class="campo-entrada" placeholder="Ej. 72140503" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"> 
                    </div> 
                    <div class="grupo-campo"> 
                        <label class="etiqueta-formulario">Carrera</label> 
                        <select name="id_carrera" id="inputCarrera" class="campo-seleccion"> 
                            <option value="" disabled selected>Seleccione una carrera...</option> 
                            <?php foreach ($carreras as $c): ?> 
                                <option value="<?= $c['id_carrera'] ?>" <?= isset($_POST['id_carrera']) && $_POST['id_carrera'] == $c['id_carrera'] ? 'selected' : '' ?>> 
                                    <?= htmlspecialchars(ucfirst($c['nombre_carrera'])) ?> 
                                </option> 
                            <?php endforeach; ?> 
                        </select> 
                    </div> 
                </div> 
                <!-- FILA DE RESPALDO: Teléfono normal si NO es estudiante (Ocupa todo el ancho) -->
                <div class="grupo-campo" id="bloqueTelefonoGeneral"> 
                    <label class="etiqueta-formulario">Teléfono</label> 
                    <input type="text" name="telefono_general" id="inputTelefonoGeneral" class="campo-entrada" placeholder="Ej. 72140503" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required> 
                </div>

                <!-- Correo Electrónico -->
                <div class="grupo-campo"> 
                    <label class="etiqueta-formulario">Correo Electrónico</label> 
                    <input type="email" name="correo" class="campo-entrada" placeholder="juan.perez@upds.net" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required> 
                </div> 

                <!-- FILA: Usuario y Contraseña -->
                <div class="grupo-fila"> 
                    <div class="grupo-campo"> 
                        <label class="etiqueta-formulario">Nombre de Usuario</label> 
                        <input type="text" name="usuario" class="campo-entrada" placeholder="juan.perez" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" required> 
                    </div> 
                    <div class="grupo-campo"> 
                        <label class="etiqueta-formulario">Contraseña</label> 
                        <input type="password" name="clave" class="campo-entrada" placeholder="Mínimo 6 caracteres" required minlength="6"> 
                    </div> 
                </div> 

                <!-- BLOQUE DINÁMICO TUTOR: Biografía abarcando todo el ancho abajo -->
                <div class="grupo-campo dinamico-oculto" id="bloqueBiografia"> 
                    <label class="etiqueta-formulario">Biografía del Tutor</label> 
                    <textarea name="biografia" id="inputBiografia" class="textarea-biografia" placeholder="Escriba una breve experiencia laboral o académica del tutor..."><?= htmlspecialchars($_POST['biografia'] ?? '') ?></textarea>
                </div> 

                <!-- Botones de Acción Inferiores --> 
                <div class="seccion-botones"> 
                    <a href="usuarios_listar.php" class="btn-cancelar">Cancelar</a> 
                    <button type="submit" class="btn-guardar">Guardar Usuario</button> 
                </div> 
            </form> 
        </div> 
    </main>

    <!-- Vinculación del JavaScript Externo -->
    <script src="../assets/js/registro_dinamico.js"></script>
</body>
</html>