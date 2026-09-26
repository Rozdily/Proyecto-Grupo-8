<?php 
    session_start(); 
    if (isset($_SESSION['id_usuario'])) {
        $destinos = [
        'administrador' => '../../controllers/usuarios_listar.php',
        'tutor' => '../tutor/panel.php',
        'estudiante' => '../estudiante/panel.php',
        ];
        header('Location: ' . ($destinos[$_SESSION['rol'] ?? ''] ?? '../../controllers/usuarios_listar.php'));
        exit;
    }
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - UPDS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
</head>
<body>
    <img
        src="https://aadcdn.msftauthimages.net/dbd5a2dd-j544gcpowfd-mh4-x2rlrt99ozyb5y02fat3bb3nue/logintenantbranding/0/illustration?ts=637861602345472860"
        class="position-fixed top-0 start-0 w-100 h-100 object-fit-cover z-0"
        alt=""
        aria-hidden="true"
    >
    <main class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-4 position-relative z-1">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
                <section class="card border-0 rounded-4 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h1 class="text-primary fw-bold mb-2">UPDS</h1>
                            <h2 class="h4 text-dark mb-0">Iniciar sesión</h2>
                        </div>

                        <!-- MENSAJE DE ERROR DINÁMICO DE PHP -->
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['login_error']) ?>
                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    aria-label="Cerrar"
                                ></button>
                            </div>
                            <?php unset($_SESSION['login_error']); ?>
                        <?php endif; ?>

                        <!-- Conectamos el formulario con el controlador real mediante POST -->
                        <form action="../../controllers/login_procesar.php" method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label fw-semibold">
                                    Usuario o correo electrónico
                                </label>
                                <input
                                    type="text"
                                    name="usuario"
                                    id="usuario"
                                    class="form-control form-control-lg"
                                    placeholder="Ingrese su usuario o correo"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="contrasena" class="form-label fw-semibold">
                                    Contraseña
                                </label>
                                <input
                                    type="password"
                                    name="contrasena"
                                    id="contrasena"
                                    class="form-control form-control-lg"
                                    placeholder="Ingrese su contraseña"
                                    required
                                >
                            </div>

                            <div class="d-grid">
                                <!-- Cambiamos el texto a 'Ingresar' para que coincida con la acción definitiva -->
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Ingresar
                                </button>
                            </div>
                        </form>

                        <div class="border-top text-body-secondary text-center mt-4 pt-3">
                            <p class="mb-0 fw-semibold">
                                Universidad Privada Domingo Savio
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>
</body>
</html>