<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - UPDS</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>
    <!-- Contenedor central principal -->
    <main class="bloque-central">
        <!-- Tarjeta blanca de login -->
        <section class="contenedor-login">
            <h1 class="logo-upds">UPDS</h1>
            <h2 class="titulo">Iniciar sesión</h2>
            <!-- MENSAJE DE ERROR DINÁMICO DE PHP -->
            <?php if (isset($_SESSION['login_error'])): ?>
                    <p class="mensaje-error"><?= htmlspecialchars($_SESSION['login_error']) ?></p>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
            <!--  Conectamos el formulario con el controlador real mediante POST -->
            <form action="../../controllers/login_procesar.php" method="POST">
                <!--  Agregamos el name="usuario" requerido por el backend -->
                <input type="text" name="usuario" class="campo-entrada" placeholder="Usuario o correo electrónico" required>
                <!--  Agregamos el casillero de Contraseña con name="contrasena" -->
                <input type="password" name="contrasena" class="campo-entrada" placeholder="Contraseña" required style="margin-top: 15px;">
                <div class="seccion-botones">
                    <!-- Cambiamos el texto a 'Ingresar' para que coincida con la acción definitiva -->
                    <button type="submit" class="btn-siguiente">Ingresar</button>
                </div>
            </form>
            <p class="pie-institucional">Universidad Privada Domingo Savio</p>
        </section>
    </main>
</body>
</html>
