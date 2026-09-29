<?php
/**
 * ARCHIVO: views/admin/cajon4/partials/parametros_configurar.php
 * Interfaz para la configuración de los parámetros y reglas de negocio.
 */

// $lista_parametros vendrá del index.php como un array asociativo ['CLAVE' => 'VALOR']
$parametros = $lista_parametros ?? [];

// Función auxiliar para obtener el valor actual o mostrar un valor por defecto seguro
function getParam($clave, $default, $parametros) {
    return isset($parametros[$clave]) ? htmlspecialchars($parametros[$clave]) : $default;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <form action="../../controllers/ParametroController.php" method="POST">
            <input type="hidden" name="accion" value="actualizar_multiples">

            <!-- BLOQUE 1: REGLAS DE TUTORÍAS SUELTAS (APOYO REGULAR) -->
            <div class="card border-0 shadow-sm border-plano border-start border-4 border-info mb-4">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 text-dark fw-bold">
                        <i class="fas fa-book-reader text-info me-2"></i>Límites de Tutorías Sueltas
                    </h6>
                    <small class="text-muted">Configuración de los topes de apoyo académico regular por materia.</small>
                </div>
                <div class="card-body bg-light p-4">
                    
                    <!-- Parámetro: Horas máximas -->
                    <div class="row align-items-center mb-4 pb-3 border-bottom border-plano">
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-0">Máximo de horas permitidas por semana</label>
                            <div class="form-text text-muted mt-0">Define el tope de horas semanales que un estudiante puede solicitar para tutorías de refuerzo.</div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="parametros[MAX_HORAS_TUTORIA_SEMANA]" class="form-control border-plano text-center fw-bold" 
                                       value="<?php echo getParam('MAX_HORAS_TUTORIA_SEMANA', '4', $parametros); ?>" min="1" max="20" required>
                                <span class="input-group-text bg-white border-plano">horas / sem</span>
                            </div>
                        </div>
                    </div>

                    <!-- Parámetro: Mínimo de días anticipación -->
                    <div class="row align-items-center mb-4 pb-3 border-bottom border-plano">
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-0">Mínimo de anticipación para solicitar</label>
                            <div class="form-text text-muted mt-0">Días de margen obligatorios (Ej: 2 días significa que hoy no se puede pedir para mañana).</div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="parametros[MIN_DIAS_ANTICIPACION_TUTORIA]" class="form-control border-plano text-center fw-bold" 
                                       value="<?php echo getParam('MIN_DIAS_ANTICIPACION_TUTORIA', '2', $parametros); ?>" min="0" max="30" required>
                                <span class="input-group-text bg-white border-plano">días</span>
                            </div>
                        </div>
                    </div>

                    <!-- Parámetro: Máximo de días a futuro -->
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-0">Máximo de días a futuro</label>
                            <div class="form-text text-muted mt-0">Límite máximo de tiempo para agendar (Ej: 60 días restringe el calendario a no más de 2 meses).</div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="parametros[MAX_DIAS_ANTICIPACION_TUTORIA]" class="form-control border-plano text-center fw-bold" 
                                       value="<?php echo getParam('MAX_DIAS_ANTICIPACION_TUTORIA', '60', $parametros); ?>" min="1" max="365" required>
                                <span class="input-group-text bg-white border-plano">días</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- BLOQUE 2: REGLAS DE TESIS Y PROYECTO DE GRADO (MG) -->
            <div class="card border-0 shadow-sm border-plano border-start border-4 border-institucional mb-4">
                <div class="card-header bg-white border-bottom border-plano py-3">
                    <h6 class="mb-0 text-institucional fw-bold">
                        <i class="fas fa-graduation-cap me-2"></i>Reglas de Modalidad de Grado (Tesis)
                    </h6>
                    <small class="text-muted">Parámetros operativos para el control de tribunales, defensas y tutores guía.</small>
                </div>
                <div class="card-body bg-light p-4">
                    
                    <!-- Parámetro: Carga de alumnos -->
                    <div class="row align-items-center mb-4 pb-3 border-bottom border-plano">
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-0">Carga máxima recomendada por Tutor Guía</label>
                            <div class="form-text text-muted mt-0">Número límite de tesistas que un tutor puede guiar simultáneamente en un mismo periodo.</div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="parametros[MAX_ALUMNOS_POR_TUTOR_MG]" class="form-control border-plano text-center fw-bold" 
                                       value="<?php echo getParam('MAX_ALUMNOS_POR_TUTOR_MG', '5', $parametros); ?>" min="1" max="15" required>
                                <span class="input-group-text bg-white border-plano">alumnos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Parámetro: Días de anticipación -->
                    <div class="row align-items-center mb-4 pb-3 border-bottom border-plano">
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-0">Días de anticipación para asignar Tribunales</label>
                            <div class="form-text text-muted mt-0">Tiempo mínimo requerido entre la entrega de cartas al jurado y la fecha de defensa.</div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="parametros[DIAS_ANTICIPACION_JURADO]" class="form-control border-plano text-center fw-bold" 
                                       value="<?php echo getParam('DIAS_ANTICIPACION_JURADO', '14', $parametros); ?>" min="1" max="30" required>
                                <span class="input-group-text bg-white border-plano">días</span>
                            </div>
                        </div>
                    </div>

                    <!-- Parámetro: Nota mínima -->
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <label class="form-label fw-bold mb-0">Nota mínima aprobatoria de Defensa</label>
                            <div class="form-text text-muted mt-0">Puntaje mínimo sobre 100 para aprobar la defensa formal de la Modalidad de Grado.</div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="number" name="parametros[NOTA_MINIMA_APROBACION_MG]" class="form-control border-plano text-center fw-bold" 
                                       value="<?php echo getParam('NOTA_MINIMA_APROBACION_MG', '51', $parametros); ?>" min="1" max="100" required>
                                <span class="input-group-text bg-white border-plano">puntos</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- BOTONERA DE ACCIÓN -->
            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-institucional border-plano shadow-sm px-4 py-2">
                    <i class="fas fa-save me-2"></i>Guardar Configuración
                </button>
            </div>
            
        </form>

    </div>
</div>