<?php
session_start();
require_once __DIR__ . '/../../includes/verificar_sesion.php';
$rol = $_SESSION['id_rol'] ?? null;
if ($rol !== null && (int) $rol !== 2) {
    header('Location: ../login/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del tutor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include __DIR__ . '/../../includes/header.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <main class="container py-4" style="max-width: 1000px; min-height: 75vh;">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h3 mb-3 text-institucional">Panel del tutor</h1>
                <p class="mb-0 text-muted">Módulo en construcción. El flujo de autenticación ya está corregido y queda listo para enlazar con el contenido del tutor.</p>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
