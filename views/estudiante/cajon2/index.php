<?php
/**
 * ARCHIVO: views/estudiante/cajon2/index.php
 * Vista: Formulario para Solicitar Tutoría (Con soporte para Módulos de Clases dinámicos).
 */

require_once __DIR__ . '/../../../config/conexion.php';

$id_usuario =$_SESSION['id_usuario'] ?? 0;
$id_estudiante = 0;
$id_carrera = 0;
$materias = [];
$bloques = [];$max_tutorias_activas = 1; 
$sesiones_por_modulo = 7; // Valor por defecto

if (isset($pdo) &&$id_usuario > 0) {
    try {
        $stmtEst =$pdo->prepare("SELECT id_estudiante, id_carrera FROM estudiantes WHERE id_usuario = ?");
        $stmtEst->execute([$id_usuario]);
        if ($rowEst =$stmtEst->fetch(PDO::FETCH_ASSOC)) {
            $id_estudiante =$rowEst['id_estudiante'];
            $id_carrera =$rowEst['id_carrera'];
        }

        if ($id_carrera > 0) {
            $stmtMat =$pdo->prepare("SELECT id_materia, nombre_materia FROM materias WHERE id_carrera = ? ORDER BY nombre_materia ASC");
            $stmtMat->execute([$id_carrera]);
            $materias =$stmtMat->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmtBloq =$pdo->query("SELECT id_bloque, nombre_bloque, hora_inicio, hora_fin, descripcion FROM bloques_horarios ORDER BY hora_inicio ASC");
        $bloques =$stmtBloq->fetchAll(PDO::FETCH_ASSOC);

        // Obtener parámetros del sistema dinámicamente
        $stmtParam =$pdo->query("SELECT clave, valor FROM parametros_mg WHERE clave IN ('MAX_TUTORIAS_ACTIVAS', 'SESIONES_POR_MODULO')");
        while ($rowParam =$stmtParam->fetch(PDO::FETCH_ASSOC)) {
            if ($rowParam['clave'] === 'MAX_TUTORIAS_ACTIVAS') {
                $max_tutorias_activas = (int)$rowParam['valor'];
            }
            if ($rowParam['clave'] === 'SESIONES_POR_MODULO') {
                $sesiones_por_modulo = (int)$rowParam['valor'];
            }
        }

    } catch (PDOException $e) {$error_bd = "No se pudieron cargar los catálogos.";
    }
}
?>

<style>
    html, body {
        overscroll-behavior-y: none;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    html::-webkit-scrollbar, body::-webkit-scrollbar {
        display: none;
    }

    .form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden; }
    .form-header { background: linear-gradient(135deg, #0B427B 0%, #1a5c9e 100%); color: white; padding: 2rem; text-align: center; }
    .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .form-control, .form-select { border-color: #cbd5e1; border-radius: 6px; padding: 0.6rem 1rem; font-size: 0.95rem; }
    .form-control:focus, .form-select:focus { border-color: #0B427B; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
    .input-group-text { background-color: #f8fafc; border-color: #cbd5e1; color: #64748b; }
    .btn-submit { background-color: #0B427B; color: white; font-weight: bold; padding: 0.8rem 2rem; border-radius: 8px; transition: 0.3s; }
    .btn-submit:hover { background-color: #08335e; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .alerta-personalizada { background-color: #fef2f2; border-left: 4px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: 6px; font-size: 0.95rem; }
    .alerta-info { background-color: #f0f9ff; border-left: 4px solid #0ea5e9; color: #0369a1; padding: 1rem; border-radius: 6px; font-size: 0.9rem; }
</style>

<div class="animate__animated animate__fadeIn d-flex justify-content-center">
    
    <div class="col-lg-9 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Solicitar Tutoría</h3>
                <p class="text-muted mb-0">Programa una sesión de apoyo académico con un docente especialista.</p>
            </div>
            <a href="index.php?seccion=dashboard" class="btn btn-outline-secondary border-plano">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>

        <div class="form-card mb-5">
            <div class="form-header">
                <div class="bg-white text-primary rounded-circle d-inline-flex justify-content-center align-items-center mb-3 shadow" style="width: 60px; height: 60px;">
                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                </div>
                <h4 class="fw-bold mb-1">Nueva Solicitud</h4>
                <p class="mb-0 text-white-50 small">Rellena los datos a continuación para agendar tu clase.</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                
                <!-- CONTENEDOR INFORMATIVO ACTUALIZADO CON EL MÓDULO -->
                <div class="alerta-info mb-4 shadow-sm">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Reglas del sistema:</strong> Puedes tener hasta <strong><?php echo $max_tutorias_activas; ?> tutoría(s) activa(s)</strong> simultáneamente. Cada solicitud genera un módulo flexible de hasta <strong><?php echo $sesiones_por_modulo; ?> clases</strong> de acompañamiento continuo.
                </div>

                <div id="contenedorError" class="alerta-personalizada d-none mb-4 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong id="textoError"></strong>
                </div>

                <form id="formSolicitarTutoria">
                    <input type="hidden" name="id_estudiante" value="<?php echo $id_estudiante; ?>">
                    <input type="hidden" name="accion" value="solicitar_tutoria">

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label">1. Selecciona la Materia</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="fas fa-book"></i></span>
                                <select class="form-select" id="id_materia" name="id_materia" required>
                                    <option value="" selected disabled>Elige una materia de tu carrera...</option>
                                    <?php foreach($materias as$mat): ?>
                                        <option value="<?php echo $mat['id_materia']; ?>"><?php echo htmlspecialchars($mat['nombre_materia']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">2. Selecciona el Tutor (Opcional)</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                <select class="form-select" id="id_tutor" name="id_tutor" disabled>
                                    <option value="" selected>Primero selecciona una materia...</option>
                                </select>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Si lo dejas en blanco, el sistema te asignará a un docente disponible automáticamente.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">3. Fecha Propuesta</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                <input type="date" class="form-control" id="fecha" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">4. Bloque Horario</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                                <select class="form-select" id="id_bloque" name="id_bloque" required>
                                    <option value="" selected disabled>Elige un horario...</option>
                                    <?php foreach($bloques as$bloque): ?>
                                        <option value="<?php echo $bloque['id_bloque']; ?>">
                                            <?php echo htmlspecialchars($bloque['nombre_bloque']); ?> (<?php echo substr($bloque['hora_inicio'],0,5).' - '.substr($bloque['hora_fin'],0,5); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">5. Modalidad Preferida</label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modalidad" id="modPresencial" value="presencial" checked>
                                    <label class="form-check-label fw-bold text-dark" for="modPresencial">
                                        <i class="fas fa-building text-primary me-1"></i> Presencial (Campus)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="modalidad" id="modVirtual" value="virtual">
                                    <label class="form-check-label fw-bold text-dark" for="modVirtual">
                                        <i class="fas fa-video text-success me-1"></i> Virtual (Teams)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">6. Detalle tu consulta (Opcional pero recomendado)</label>
                            <textarea class="form-control shadow-sm" name="observaciones" rows="3" placeholder="Ej: Necesito ayuda para comprender las consultas anidadas en SQL..."></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="text-end">
                        <button type="button" class="btn btn-light border-plano me-2" onclick="window.location.href='index.php?seccion=dashboard'">Cancelar</button>
                        <button type="submit" class="btn btn-submit border-plano shadow-sm" id="btnGuardarSol">
                            <i class="fas fa-paper-plane me-2"></i> Enviar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectMateria = document.getElementById('id_materia');
    const selectTutor = document.getElementById('id_tutor');
    const formSolicitar = document.getElementById('formSolicitarTutoria');
    const btnGuardar = document.getElementById('btnGuardarSol');
    
    const contenedorError = document.getElementById('contenedorError');
    const textoError = document.getElementById('textoError');

    selectMateria.addEventListener('change', function() {
        const idMateria = this.value;
        
        selectTutor.innerHTML = '<option value="">Cargando tutores...</option>';
        selectTutor.disabled = true;

        fetch(`../../controllers/EstudianteController.php?accion=obtener_tutores_materia&id_materia=${idMateria}`)
        .then(response => response.json())
        .then(data => {
            selectTutor.innerHTML = '<option value="" selected>Sin preferencia (Cualquier docente disponible)</option>';
            
            if(data.tutores && data.tutores.length > 0) {
                data.tutores.forEach(tutor => {
                    selectTutor.innerHTML += `<option value="${tutor.id_tutor}">${tutor.nombre_completo}</option>`;
                });
                selectTutor.disabled = false;
            } else {
                selectTutor.innerHTML = '<option value="" selected>No hay tutores asignados a esta materia.</option>';
            }
        })
        .catch(error => {
            console.error("Error:", error);
            selectTutor.innerHTML = '<option value="">Error al cargar tutores.</option>';
        });
    });

    formSolicitar.addEventListener('submit', function(e) {
        e.preventDefault();
        
        contenedorError.classList.add('d-none');
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';
        btnGuardar.disabled = true;

        let formData = new FormData(this);

        fetch('../../controllers/EstudianteController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.exito) {
                alert("¡Solicitud enviada con éxito! Revisa tu historial.");
                window.location.href = 'index.php?seccion=cajon3';
            } else {
                textoError.textContent = data.error || "No se pudo procesar la solicitud.";
                contenedorError.classList.remove('d-none');
                contenedorError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                btnGuardar.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Enviar Solicitud';
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            textoError.textContent = "Ocurrió un error de conexión al enviar el formulario. Intenta nuevamente.";
            contenedorError.classList.remove('d-none');
            contenedorError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            btnGuardar.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Enviar Solicitud';
            btnGuardar.disabled = false;
        });
    });
});
</script>