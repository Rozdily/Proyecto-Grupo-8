<?php
/**
 * ARCHIVO: views/admin/cajon1/partials/toolbar.php
 * Barra de herramientas con buscador, filtro de grado condicional y botón de creación.
 */
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="index.php" method="GET" class="d-flex gap-2" style="width: <?php echo ($submodulo === 'estudiantes') ? '550px' : '350px'; ?>;">
        <input type="hidden" name="seccion" value="cajon1">
        <input type="hidden" name="sub" value="<?php echo htmlspecialchars($submodulo); ?>">

        <?php if ($submodulo === 'estudiantes'): ?>
            <select name="filtro_grado" class="form-select form-select-sm border-plano" style="width: 220px;" onchange="this.form.submit()">
                <option value="todos" <?php echo $filtro_grado === 'todos' ? 'selected' : ''; ?>>Todos los estudiantes</option>
                <option value="regulares" <?php echo $filtro_grado === 'regulares' ? 'selected' : ''; ?>>Alumnos Regulares</option>
                <option value="egresados" <?php echo $filtro_grado === 'egresados' ? 'selected' : ''; ?>>Listos para Grado (Egresados)</option>
            </select>
        <?php endif; ?>

        <div class="input-group">
            <input type="text" name="buscar" class="form-control form-control-sm border-plano" placeholder="Buscar por nombre o correo..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button class="btn btn-institucional btn-sm border-plano" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>

    <button class="btn btn-institucional btn-sm border-plano shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
        <i class="fas fa-plus me-1"></i> Nuevo <?php echo $etiqueta_singular; ?>
    </button>
</div>