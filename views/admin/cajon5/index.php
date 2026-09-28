<?php
session_start();
require_once __DIR__ . '/../../../includes/verificar_sesion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sección</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <main class="container py-4" style="max-width: 1000px; min-height: 75vh;">
        <div class="alert alert-info border-0 shadow-sm">
            <h1 class="h4 mb-2 text-institucional">Módulo en construcción</h1>
            <p class="mb-0">Este cajón aún no tiene contenido específico, pero la navegación y el acceso ya están habilitados.</p>
        </div>
    </main>
    <?php include __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
