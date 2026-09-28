<?php 
session_start(); 

// Si el usuario ya está autenticado, redirigir automáticamente según la nueva arquitectura de carpetas
if (isset($_SESSION['id_usuario']) && isset($_SESSION['id_rol'])) {
    if ($_SESSION['id_rol'] == 1) {
        header('Location: ../admin/index.php');
    } elseif ($_SESSION['id_rol'] == 2) {
        header('Location: ../tutor/index.php');
    } elseif ($_SESSION['id_rol'] == 3) {
        header('Location: ../estudiante/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - UPDS.NET v5.1</title>

    <!-- Bootstrap 5.3 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        .border-plano { border-radius: 0 !important; }
        .btn-institucional { background-color: #0B427B; color: white; border: none; }
        .btn-institucional:hover { background-color: #08335e; color: white; }
        .text-institucional { color: #0B427B; }
    </style>
</head>
<body>
    <!-- Imagen de fondo institucional[cite: 3] -->
    <img src="https://aadcdn.msftauthimages.net/dbd5a2dd-j544gcpowfd-mh4-x2rlrt99ozyb5y02fat3bb3nue/logintenantbranding/0/illustration?ts=637861602345472860" class="position-fixed top-0 start-0 w-100 h-100 object-fit-cover z-0" alt="" aria-hidden="true">
    
    <main class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-4 position-relative z-1">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                
                <section class="card border-0 shadow-lg border-plano">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h1 class="fw-bold mb-2 text-institucional">UPDS</h1>
                            <h2 class="h5 text-dark mb-0 fw-light">Plataforma de Tutorías y Grado</h2>
                        </div>

                        <!-- MENSAJE DE ERROR DINÁMICO DE PHP[cite: 3] -->
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show border-0 border-start border-4 border-danger border-plano" role="alert">
                                <?= htmlspecialchars($_SESSION['login_error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                            <?php unset($_SESSION['login_error']); ?>
                        <?php endif; ?>

                        <!-- Formulario conectado mediante POST al controlador MVC[cite: 3] -->
                        <form action="../../controllers/LoginController.php" method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label small fw-semibold text-muted">Usuario o correo electrónico</label>
                                <input type="text" name="usuario" id="usuario" class="form-control form-control-lg border-plano" placeholder="Ingrese su cuenta institucional" required>
                            </div>

                            <div class="mb-4">
                                <label for="contrasena" class="form-label small fw-semibold text-muted">Contraseña</label>
                                <input type="password" name="contrasena" id="contrasena" class="form-control form-control-lg border-plano" placeholder="Ingrese su contraseña segura" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-institucional btn-lg border-plano shadow-sm">
                                    Ingresar al Sistema
                                </button>
                            </div>
                        </form>

                        <div class="border-top text-body-secondary text-center mt-4 pt-3">
                            <p class="mb-0 small">Universidad Privada Domingo Savio &copy; <?php echo date('Y'); ?></p>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>