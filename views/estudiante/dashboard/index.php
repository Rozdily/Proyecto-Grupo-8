<?php
/**
 * ARCHIVO: views/estudiante/dashboard/index.php
 * Vista de Mi Perfil (Datos Personales y Académicos).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;

// Estructura por defecto para evitar errores si faltan datos
$perfil = [
    'nombre' => 'Estudiante',
    'apellido' => '',
    'correo' => 'No registrado',
    'telefono' => 'No registrado',
    'usuario' => 'N/A',
    'registro_universitario' => 'Sin RU',
    'semestre' => '-',
    'nombre_carrera' => 'No registrada',
    'fecha_registro' => date('Y-m-d')
];

if ($id_usuario > 0 && isset($pdo)) {
    try {
        $sql = "SELECT u.nombre, u.apellido, u.correo, u.telefono, u.usuario, u.fecha_registro,
                       e.registro_universitario, e.semestre, c.nombre_carrera 
                FROM usuarios u
                LEFT JOIN estudiantes e ON u.id_usuario = e.id_usuario
                LEFT JOIN carreras c ON e.id_carrera = c.id_carrera
                WHERE u.id_usuario = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $perfil = array_merge($perfil, $row);
        }
    } catch (PDOException $e) {
        $error_bd = "No se pudieron cargar los datos del perfil.";
    }
}

$iniciales = strtoupper(substr(trim($perfil['nombre']), 0, 1) . substr(trim($perfil['apellido']), 0, 1));
?>

<style>
    /* Estilos Premium para Mi Perfil */
    .profile-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden; }
    .profile-header { background: linear-gradient(135deg, #0B427B 0%, #1a5c9e 100%); padding: 3rem 2rem 4rem 2rem; text-align: center; color: white; position: relative; }
    .profile-avatar-wrapper { margin-top: -3.5rem; text-align: center; position: relative; z-index: 2; }
    .profile-avatar { width: 110px; height: 110px; background-color: #f8fafc; color: #0B427B; font-size: 2.5rem; font-weight: bold; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 4px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .info-group { border-bottom: 1px solid #f1f5f9; padding: 1rem 0; }
    .info-group:last-child { border-bottom: none; }
    .info-label { font-size: 0.8rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 0.3rem; }
    .info-value { font-size: 1rem; color: #1e293b; font-weight: 500; }
    .academic-badge { background-color: #f1f5f9; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 8px; text-align: center; height: 100%; }
</style>

<div class="animate__animated animate__fadeIn">
    
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Mi Perfil</h3>
        <p class="text-muted mb-0">Gestiona tu información personal y verifica tus datos académicos.</p>
    </div>

    <?php if (isset($error_bd)): ?>
        <div class="alert alert-danger border-plano"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error_bd; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- COLUMNA IZQUIERDA -->
        <div class="col-lg-4">
            <div class="profile-card h-100">
                <div class="profile-header">
                    <h5 class="fw-bold mb-0">Expediente Estudiantil</h5>
                    <p class="mb-0 text-white-50 small">UPDS Sede Tarija</p>
                </div>
                
                <div class="profile-avatar-wrapper">
                    <div class="profile-avatar">
                        <?php echo $iniciales; ?>
                    </div>
                </div>
                
                <div class="p-4 text-center mt-2">
                    <h5 class="fw-bold text-dark mb-1">
                        <?php echo htmlspecialchars($perfil['nombre'] . ' ' . $perfil['apellido']); ?>
                    </h5>
                    <p class="text-muted mb-3"><i class="fas fa-user-graduate me-1"></i> Perfil de Estudiante</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-success rounded-pill px-3 py-2 fw-normal">
                            <i class="fas fa-check-circle me-1"></i> Usuario Activo
                        </span>
                    </div>
                    
                    <p class="text-muted small mb-0">
                        <i class="far fa-calendar-alt me-1"></i> Miembro desde <?php echo date('F Y', strtotime($perfil['fecha_registro'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="col-lg-8">
            <!-- Bloque 1: Información Académica (Intocable) -->
            <div class="profile-card mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center">
                    <div class="bg-institucional text-white rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-university"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Información Académica <span class="badge bg-light text-secondary border ms-2">No editable</span></h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="academic-badge">
                                <div class="info-label text-institucional">Registro Universitario</div>
                                <div class="fs-5 fw-bold text-dark"><?php echo htmlspecialchars($perfil['registro_universitario']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="academic-badge">
                                <div class="info-label text-institucional">Carrera</div>
                                <div class="fs-6 fw-bold text-dark lh-sm mt-1"><?php echo htmlspecialchars($perfil['nombre_carrera']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="academic-badge">
                                <div class="info-label text-institucional">Nivel / Semestre</div>
                                <div class="fs-3 fw-bold text-dark mt-1"><?php echo htmlspecialchars($perfil['semestre']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloque 2: Información de Contacto (Editable) -->
            <div class="profile-card">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center">
                    <div class="bg-secondary text-white rounded p-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-address-card"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Datos de Contacto y Acceso</h6>
                    
                    <!-- BOTÓN EDITAR MOVIDO AQUÍ CON MS-AUTO PARA ALINEAR A LA DERECHA -->
                    <button class="btn btn-sm btn-institucional border-plano shadow-sm ms-auto" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                        <i class="fas fa-edit me-2"></i> Editar Perfil
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label"><i class="fas fa-envelope me-1"></i> Correo Electrónico</div>
                                <div class="info-value" id="lbl-correo"><?php echo htmlspecialchars($perfil['correo']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label"><i class="fas fa-phone-alt me-1"></i> Teléfono Móvil</div>
                                <div class="info-value" id="lbl-telefono"><?php echo htmlspecialchars($perfil['telefono']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label"><i class="fas fa-user-shield me-1"></i> Nombre de Usuario (Login)</div>
                                <div class="info-value" id="lbl-usuario"><?php echo htmlspecialchars($perfil['usuario']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <div class="info-label"><i class="fas fa-key me-1"></i> Contraseña</div>
                                <div class="info-value">
                                    <span>••••••••</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR PERFIL -->
<div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-plano border-top border-4 border-institucional shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalEditarPerfilLabel"><i class="fas fa-user-edit text-institucional me-2"></i> Actualizar Datos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formActualizarPerfil">
                    <input type="hidden" name="accion" value="actualizar_perfil">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control border-plano" name="correo" value="<?php echo htmlspecialchars($perfil['correo']); ?>" required maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nombre de Usuario (Login) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control border-plano" name="usuario" value="<?php echo htmlspecialchars($perfil['usuario']); ?>" required minlength="4" maxlength="50">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Teléfono Móvil <span class="text-danger">*</span></label>
                        <input type="text" class="form-control border-plano" name="telefono" value="<?php echo htmlspecialchars($perfil['telefono']); ?>" required maxlength="20">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Nueva Contraseña</label>
                        <input type="password" class="form-control border-plano" name="contrasena" placeholder="Dejar en blanco para conservar la actual" minlength="5">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-institucional border-plano" id="btnGuardarPerfil">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formPerfil = document.getElementById('formActualizarPerfil');
    const btnGuardar = document.getElementById('btnGuardarPerfil');

    formPerfil.addEventListener('submit', function(e) {
        e.preventDefault();
        
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
        btnGuardar.disabled = true;

        let formData = new FormData(this);

        fetch('../../controllers/EstudianteController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.exito) {
                alert("¡Perfil actualizado correctamente!");
                window.location.reload(); // Recargar para ver los cambios aplicados
            } else {
                alert("Error: " + (data.error || "No se pudo actualizar el perfil."));
                btnGuardar.innerHTML = '<i class="fas fa-save me-2"></i> Guardar Cambios';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Ocurrió un error de conexión.");
            btnGuardar.innerHTML = '<i class="fas fa-save me-2"></i> Guardar Cambios';
            btnGuardar.disabled = false;
        });
    });
});
</script>