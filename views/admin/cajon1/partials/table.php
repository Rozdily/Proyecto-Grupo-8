<?php
/**
 * ARCHIVO: views/admin/cajon1/partials/table.php
 * Tarjeta contenedora y tabla de registros con atributos extendidos para perfiles de estudiantes y tutores,
 * e incluye el acceso a la gestión de horarios para tutores y visualización de RU/Carrera.
 */
?>
<div class="card border-0 shadow-sm border-plano mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre Completo</th>
                        <th>Correo Institucional</th>
                        <th>Estado</th>
                        <?php if ($submodulo === 'estudiantes'): ?>
                            <th>Datos Académicos</th>
                            <th>Progreso</th>
                        <?php endif; ?>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($registros) && is_array($registros)): ?>
                        <?php foreach ($registros as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?php echo htmlspecialchars($row['id_usuario']); ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></div>
                                    <div class="small text-muted">@<?php echo htmlspecialchars($row['usuario']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                <td>
                                    <?php if (($row['estado'] ?? 'activo') === 'activo'): ?>
                                        <span class="badge bg-success border-plano">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger border-plano">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <?php if ($submodulo === 'estudiantes'): ?>
                                    <td>
                                        <!-- Mostramos el RU autogenerado y la carrera conectada en el modelo -->
                                        <div class="small fw-bold text-institucional"><?php echo htmlspecialchars($row['registro_universitario'] ?? 'RU-PENDIENTE'); ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($row['nombre_carrera'] ?? 'Sin carrera asignada'); ?>">
                                            <?php echo htmlspecialchars($row['nombre_carrera'] ?? 'Sin carrera asignada'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php $es_egresado = (isset($row['semestre']) && $row['semestre'] >= 9); ?>
                                        <?php if ($es_egresado): ?>
                                            <span class="badge bg-info text-dark border-plano mb-1"><i class="fas fa-check-circle"></i> Egresado (Sem. <?php echo $row['semestre']; ?>)</span><br>
                                            <button class="btn btn-sm btn-outline-primary border-plano p-1" style="font-size: 0.7rem;" title="Activar Asignación de Grado">
                                                <i class="fas fa-award"></i> Asignar Grado
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-secondary border-plano">Regular (Sem. <?php echo $row['semestre'] ?? 'N/D'; ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <!-- COLUMNA DE ACCIONES CRUD -->
                                <td class="text-end pe-4">
                                    <div class="btn-group border-plano shadow-sm">
                                        <!-- Botón Editar con atributos extendidos para estudiantes (incluyendo data-carrera) y tutores -->
                                        <button type="button" class="btn btn-sm btn-light border btn-editar" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditarUsuario"
                                                data-id="<?php echo $row['id_usuario']; ?>"
                                                data-rol="<?php echo $row['id_rol']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                data-apellido="<?php echo htmlspecialchars($row['apellido']); ?>"
                                                data-correo="<?php echo htmlspecialchars($row['correo']); ?>"
                                                data-usuario="<?php echo htmlspecialchars($row['usuario']); ?>"
                                                data-telefono="<?php echo htmlspecialchars($row['telefono']); ?>"
                                                data-estado="<?php echo $row['estado']; ?>"
                                                data-semestre="<?php echo $row['semestre'] ?? ''; ?>"
                                                data-carrera="<?php echo $row['id_carrera'] ?? ''; ?>"
                                                data-especialidad="<?php echo htmlspecialchars($row['especialidad'] ?? ''); ?>"
                                                data-biografia="<?php echo htmlspecialchars($row['biografia'] ?? ''); ?>"
                                                data-linkedin="<?php echo htmlspecialchars($row['perfil_linkedin'] ?? ''); ?>"
                                                data-certificaciones="<?php echo htmlspecialchars($row['certificaciones'] ?? ''); ?>"
                                                data-expertise="<?php echo htmlspecialchars($row['areas_expertise'] ?? ''); ?>"
                                                title="Editar">
                                            <i class="fas fa-edit text-institucional"></i>
                                        </button>

                                        <!-- Botón Cambiar Contraseña -->
                                        <button type="button" class="btn btn-sm btn-light border btn-clave" 
                                                data-bs-toggle="modal" data-bs-target="#modalCambiarClave"
                                                data-id="<?php echo $row['id_usuario']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($row['nombre']); ?>"
                                                title="Cambiar Clave">
                                            <i class="fas fa-key text-warning"></i>
                                        </button>

                                        <!-- Botón Gestionar Horarios (Aparece Solo en Tutores) -->
                                        <?php if ($submodulo === 'tutores'): ?>
                                            <a href="index.php?seccion=cajon1&sub=horarios&id_tutor=<?php echo $row['id_usuario']; ?>" 
                                               class="btn btn-sm btn-light border text-info" 
                                               title="Gestionar Horarios">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Botón Eliminar -->
                                        <a href="../../controllers/UsuarioController.php?accion=eliminar&id=<?php echo $row['id_usuario']; ?>" 
                                           class="btn btn-sm btn-light border text-danger" 
                                           onclick="return confirm('¿Estás seguro de eliminar este usuario permanentemente?');"
                                           title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($submodulo === 'estudiantes') ? '7' : '5'; ?>" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                                <h6 class="text-muted fw-light">No se encontraron registros en la categoría "<?php echo ucfirst($submodulo); ?>"</h6>
                                <?php if (!empty($buscar) or $filtro_grado !== 'todos'): ?>
                                    <a href="index.php?seccion=cajon1&sub=<?php echo htmlspecialchars($submodulo); ?>" class="btn btn-sm btn-outline-secondary mt-2 border-plano">Limpiar Filtros</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>