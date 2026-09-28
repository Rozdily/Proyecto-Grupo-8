<?php
/**
 * ARCHIVO: views/admin/cajon1/partials/modales.php
 * Modales con persistencia de datos (memoria), validaciones HTML5 y campos dinámicos
 * para perfiles extendidos (Estudiantes y Tutores).
 */
$error_actual = $_GET['error'] ?? '';
$modal_activo = $_GET['modal'] ?? '';

// Recuperar datos anteriores para la memoria del formulario (CREAR)
$old_nombre = $_GET['old_nombre'] ?? '';
$old_apellido = $_GET['old_apellido'] ?? '';
$old_correo = $_GET['old_correo'] ?? '';
$old_usuario = $_GET['old_usuario'] ?? '';
$old_telefono = $_GET['old_telefono'] ?? '';
$old_semestre = $_GET['old_semestre'] ?? '';
$old_carrera = $_GET['old_carrera'] ?? '';
$old_especialidad = $_GET['old_especialidad'] ?? '';
$old_biografia = $_GET['old_biografia'] ?? '';
$old_linkedin = $_GET['old_linkedin'] ?? '';
$old_certificaciones = $_GET['old_certificaciones'] ?? '';
$old_expertise = $_GET['old_expertise'] ?? '';

// Recuperar datos anteriores para la memoria del formulario (EDITAR)
$old_edit_id = $_GET['old_edit_id'] ?? '';
$old_edit_rol = $_GET['old_edit_rol'] ?? '';
$old_edit_nombre = $_GET['old_edit_nombre'] ?? '';
$old_edit_apellido = $_GET['old_edit_apellido'] ?? '';
$old_edit_correo = $_GET['old_edit_correo'] ?? '';
$old_edit_usuario = $_GET['old_edit_usuario'] ?? '';
$old_edit_telefono = $_GET['old_edit_telefono'] ?? '';
$old_edit_estado = $_GET['old_edit_estado'] ?? '';
$old_edit_semestre = $_GET['old_edit_semestre'] ?? '';
$old_edit_carrera = $_GET['old_edit_carrera'] ?? '';
$old_edit_especialidad = $_GET['old_edit_especialidad'] ?? '';
$old_edit_biografia = $_GET['old_edit_biografia'] ?? '';
$old_edit_linkedin = $_GET['old_edit_linkedin'] ?? '';
$old_edit_certificaciones = $_GET['old_edit_certificaciones'] ?? '';
$old_edit_expertise = $_GET['old_edit_expertise'] ?? '';
?>

<!-- ======================================================================= -->
<!-- 1. MODAL CREAR USUARIO -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered <?php echo ($submodulo === 'tutores' || $submodulo === 'estudiantes') ? 'modal-lg' : ''; ?>">
        <div class="modal-content border-plano shadow">
            <form action="../../controllers/UsuarioController.php?accion=crear" method="POST">
                <div class="modal-header bg-institucional border-0 border-plano text-white">
                    <h5 class="modal-title fs-6"><i class="fas fa-plus-circle me-2"></i>Crear Nuevo <?php echo $etiqueta_singular; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <!-- Alerta de Error Exclusiva del Modal -->
                    <div id="alerta-error-crear" class="alert alert-danger py-2 small mb-3 <?php echo ($modal_activo === 'crear') ? '' : 'd-none'; ?>">
                        <i class="fas fa-exclamation-triangle me-1"></i> <span><?php echo ($modal_activo === 'crear') ? htmlspecialchars($error_actual) : ''; ?></span>
                    </div>

                    <input type="hidden" name="id_rol" value="<?php echo ($submodulo === 'tutores') ? 2 : (($submodulo === 'estudiantes') ? 3 : 1); ?>">
                    
                    <div class="row">
                        <!-- Campos Base Obligatorios -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" name="nombre" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_nombre); ?>" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" name="apellido" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_apellido); ?>" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Correo Institucional</label>
                            <input type="email" name="correo" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_correo); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre de Usuario</label>
                            <input type="text" name="usuario" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_usuario); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Contraseña Inicial</label>
                            <input type="password" name="contrasena" class="form-control form-control-sm border-plano" minlength="6" <?php echo ($modal_activo === 'crear') ? '' : 'required'; ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_telefono); ?>" minlength="8" maxlength="15" pattern="[0-9]+" title="Mínimo 8 dígitos numéricos." required>
                        </div>

                        <!-- SECCIÓN: CREAR ESTUDIANTE -->
                        <?php if ($submodulo === 'estudiantes'): ?>
                            <div class="col-12 mt-2">
                                <h6 class="text-institucional small fw-bold border-bottom pb-2"><i class="fas fa-graduation-cap me-1"></i> Datos Académicos</h6>
                                <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> El Registro Universitario (RU) será autogenerado al guardar.</p>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label small fw-bold">Carrera</label>
                                <select name="id_carrera" class="form-select form-select-sm border-plano" required>
                                    <option value="" disabled <?php echo empty($old_carrera) ? 'selected' : ''; ?>>Seleccione una carrera...</option>
                                    <?php if (!empty($lista_carreras)): ?>
                                        <?php foreach ($lista_carreras as $carrera): ?>
                                            <option value="<?php echo $carrera['id_carrera']; ?>" <?php echo ($old_carrera == $carrera['id_carrera']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="1">Sin carreras (Configure la BD)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Semestre Actual</label>
                                <input type="number" name="semestre" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_semestre !== '' ? $old_semestre : '1'); ?>" min="1" max="12" required>
                            </div>
                        <?php endif; ?>

                        <!-- SECCIÓN: CREAR TUTOR (Campos Opcionales) -->
                        <?php if ($submodulo === 'tutores'): ?>
                            <div class="col-12 mt-2">
                                <h6 class="text-institucional small fw-bold border-bottom pb-2"><i class="fas fa-chalkboard-teacher me-1"></i> Perfil Profesional (Opcional)</h6>
                            </div>
                            <div class="col-md-6 mb-3 mt-2">
                                <label class="form-label small fw-bold">Especialidad</label>
                                <input type="text" name="especialidad" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_especialidad); ?>" placeholder="Ej. Ingeniería de Software">
                            </div>
                            <div class="col-md-6 mb-3 mt-2">
                                <label class="form-label small fw-bold">Perfil LinkedIn</label>
                                <input type="url" name="perfil_linkedin" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_linkedin); ?>" placeholder="URL completa">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Áreas de Expertise</label>
                                <input type="text" name="areas_expertise" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_expertise); ?>" placeholder="Ej. Redes, Base de Datos">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Certificaciones</label>
                                <input type="text" name="certificaciones" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_certificaciones); ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label small fw-bold">Biografía</label>
                                <textarea name="biografia" class="form-control form-control-sm border-plano" rows="2"><?php echo htmlspecialchars($old_biografia); ?></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer bg-light border-plano">
                    <button type="button" class="btn btn-secondary btn-sm border-plano" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-institucional btn-sm border-plano">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- 2. MODAL EDITAR USUARIO (DINÁMICO CON MEMORIA) -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-plano shadow">
            <form action="../../controllers/UsuarioController.php?accion=actualizar" method="POST">
                <input type="hidden" name="id_usuario" id="edit_id_usuario" value="<?php echo htmlspecialchars($old_edit_id); ?>">
                <div class="modal-header bg-institucional border-0 border-plano text-white">
                    <h5 class="modal-title fs-6"><i class="fas fa-edit me-2"></i>Editar Perfil / Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <!-- Alerta de Error Exclusiva del Modal -->
                    <div id="alerta-error-editar" class="alert alert-danger py-2 small mb-3 <?php echo ($modal_activo === 'editar') ? '' : 'd-none'; ?>">
                        <i class="fas fa-exclamation-triangle me-1"></i> <span><?php echo ($modal_activo === 'editar') ? htmlspecialchars($error_actual) : ''; ?></span>
                    </div>

                    <div class="row">
                        <!-- Campos Generales -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Rol</label>
                            <select name="id_rol" id="edit_id_rol" class="form-select form-select-sm border-plano">
                                <option value="1" <?php echo ($old_edit_rol == '1') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="2" <?php echo ($old_edit_rol == '2') ? 'selected' : ''; ?>>Tutor</option>
                                <option value="3" <?php echo ($old_edit_rol == '3') ? 'selected' : ''; ?>>Estudiante</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Estado</label>
                            <select name="estado" id="edit_estado" class="form-select form-select-sm border-plano">
                                <option value="activo" <?php echo ($old_edit_estado == 'activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactivo" <?php echo ($old_edit_estado == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-sm border-plano" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" value="<?php echo htmlspecialchars($old_edit_nombre); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" name="apellido" id="edit_apellido" class="form-control form-control-sm border-plano" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" value="<?php echo htmlspecialchars($old_edit_apellido); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Correo Institucional</label>
                            <input type="email" name="correo" id="edit_correo" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_edit_correo); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre de Usuario</label>
                            <input type="text" name="usuario" id="edit_usuario" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_edit_usuario); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="tel" name="telefono" id="edit_telefono" class="form-control form-control-sm border-plano" minlength="8" maxlength="15" pattern="[0-9]+" value="<?php echo htmlspecialchars($old_edit_telefono); ?>" required>
                        </div>

                        <!-- SECCIÓN: EDITAR ESTUDIANTE (Se muestra vía JS) -->
                        <div class="col-12" id="seccion-estudiante" style="display: none;">
                            <hr class="my-2">
                            <h6 class="text-institucional small fw-bold mb-3"><i class="fas fa-graduation-cap me-1"></i> Datos Académicos del Estudiante</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label small fw-bold">Carrera</label>
                                    <select name="id_carrera" id="edit_carrera" class="form-select form-select-sm border-plano">
                                        <?php if (!empty($lista_carreras)): ?>
                                            <?php foreach ($lista_carreras as $carrera): ?>
                                                <option value="<?php echo $carrera['id_carrera']; ?>" <?php echo ($old_edit_carrera == $carrera['id_carrera']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold">Semestre Actual</label>
                                    <input type="number" name="semestre" id="edit_semestre" class="form-control form-control-sm border-plano" min="1" max="12" value="<?php echo htmlspecialchars($old_edit_semestre); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN: EDITAR TUTOR (Se muestra vía JS) -->
                        <div class="col-12" id="seccion-tutor" style="display: none;">
                            <hr class="my-2">
                            <h6 class="text-institucional small fw-bold mb-2"><i class="fas fa-chalkboard-teacher me-1"></i> Perfil Profesional del Tutor</h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Especialidad</label>
                                <input type="text" name="especialidad" id="edit_especialidad" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_edit_especialidad); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Biografía</label>
                                <textarea name="biografia" id="edit_biografia" class="form-control form-control-sm border-plano" rows="2"><?php echo htmlspecialchars($old_edit_biografia); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Perfil LinkedIn</label>
                                <input type="url" name="perfil_linkedin" id="edit_linkedin" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_edit_linkedin); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Certificaciones</label>
                                <textarea name="certificaciones" id="edit_certificaciones" class="form-control form-control-sm border-plano" rows="2"><?php echo htmlspecialchars($old_edit_certificaciones); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Áreas de Expertise</label>
                                <input type="text" name="areas_expertise" id="edit_expertise" class="form-control form-control-sm border-plano" value="<?php echo htmlspecialchars($old_edit_expertise); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-plano">
                    <button type="button" class="btn btn-secondary btn-sm border-plano" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-institucional btn-sm border-plano">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- 3. MODAL CAMBIAR CONTRASEÑA -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalCambiarClave" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-plano shadow">
            <form action="../../controllers/UsuarioController.php?accion=cambiar_clave" method="POST">
                <input type="hidden" name="id_usuario" id="clave_id_usuario">
                <div class="modal-header bg-warning border-0 border-plano text-dark">
                    <h5 class="modal-title fs-6"><i class="fas fa-key me-2"></i>Cambiar Contraseña para: <span id="clave_nombre_usuario" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nueva Contraseña</label>
                        <input type="password" name="nueva_contrasena" class="form-control form-control-sm border-plano" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-plano">
                    <button type="button" class="btn btn-secondary btn-sm border-plano" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm border-plano text-dark fw-bold">Actualizar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- SCRIPT JS: AUTO-ABRIR, MOSTRAR/OCULTAR CAMPOS Y RELLENAR DATOS -->
<!-- ======================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Función para alternar secciones dinámicas (Estudiante vs Tutor)
    function actualizarSeccionesRol(rolId) {
        const secEstudiante = document.getElementById('seccion-estudiante');
        const secTutor = document.getElementById('seccion-tutor');

        if (rolId == '3') { // Estudiante
            secEstudiante.style.display = 'block';
            secTutor.style.display = 'none';
        } else if (rolId == '2') { // Tutor
            secEstudiante.style.display = 'none';
            secTutor.style.display = 'block';
        } else { // Administrador
            secEstudiante.style.display = 'none';
            secTutor.style.display = 'none';
        }
    }

    const urlParams = new URLSearchParams(window.location.search);
    const modalConError = urlParams.get('modal');

    // 2. Auto-abrir el modal correcto si el backend devolvió un error
    if (modalConError === 'crear') {
        new bootstrap.Modal(document.getElementById('modalCrearUsuario')).show();
    } else if (modalConError === 'editar') {
        new bootstrap.Modal(document.getElementById('modalEditarUsuario')).show();
        // Cuando falla la edición, obligamos al JS a abrir la sección correcta leyendo el Rol
        const rolFallo = document.getElementById('edit_id_rol').value;
        actualizarSeccionesRol(rolFallo);
    }

    // 3. Detectar cambio manual en el select de rol al editar
    document.getElementById('edit_id_rol').addEventListener('change', function() {
        actualizarSeccionesRol(this.value);
    });

    // 4. Inyectar datos en el modal de Edición al hacer clic en un botón de la tabla
    document.querySelectorAll('.btn-editar').forEach(button => {
        button.addEventListener('click', function() {
            const rol = this.getAttribute('data-rol');
            
            // Datos generales
            document.getElementById('edit_id_usuario').value = this.getAttribute('data-id');
            document.getElementById('edit_id_rol').value = rol;
            document.getElementById('edit_nombre').value = this.getAttribute('data-nombre');
            document.getElementById('edit_apellido').value = this.getAttribute('data-apellido');
            document.getElementById('edit_correo').value = this.getAttribute('data-correo');
            document.getElementById('edit_usuario').value = this.getAttribute('data-usuario');
            document.getElementById('edit_telefono').value = this.getAttribute('data-telefono');
            document.getElementById('edit_estado').value = this.getAttribute('data-estado');

            // Datos específicos de Estudiante
            const carrera = this.getAttribute('data-carrera');
            if(carrera) document.getElementById('edit_carrera').value = carrera;
            document.getElementById('edit_semestre').value = this.getAttribute('data-semestre');
            
            // Datos específicos de Tutor
            document.getElementById('edit_especialidad').value = this.getAttribute('data-especialidad');
            document.getElementById('edit_biografia').value = this.getAttribute('data-biografia');
            document.getElementById('edit_linkedin').value = this.getAttribute('data-linkedin');
            document.getElementById('edit_certificaciones').value = this.getAttribute('data-certificaciones');
            document.getElementById('edit_expertise').value = this.getAttribute('data-expertise');

            // Mostrar/Ocultar campos extra basados en el rol original
            actualizarSeccionesRol(rol);
        });
    });

    // 5. Inyectar datos en el modal de Cambio de Clave
    document.querySelectorAll('.btn-clave').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('clave_id_usuario').value = this.getAttribute('data-id');
            document.getElementById('clave_nombre_usuario').textContent = this.getAttribute('data-nombre');
        });
    });
});
</script>