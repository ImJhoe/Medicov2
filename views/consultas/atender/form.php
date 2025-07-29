<?php
require_once 'includes/header.php';

// Solo médicos pueden acceder
if ($_SESSION['user_role'] != 3) {
    header('Location: index.php?action=dashboard');
    exit;
}
?>
<link rel="stylesheet" href="assets/css/consultas.css">
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php?action=consultas/atender">Atender Pacientes</a>
                    </li>
                    <li class="breadcrumb-item active">Consulta Médica</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Información del Paciente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Información del Paciente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cita['nombre_paciente']); ?></p>
                            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($cita['cedula']); ?></p>
                            <p><strong>Fecha de Cita:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha_cita'] . ' ' . $cita['hora_cita'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['nombre_especialidad']); ?></p>
                            <p><strong>Motivo:</strong> <?php echo htmlspecialchars($cita['motivo_consulta']); ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-<?php echo $cita['estado_cita'] == 'completada' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($cita['estado_cita']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Triaje (si existe) -->
    <?php if ($triaje): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Información del Triaje
                        </h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Prioridad:</strong> 
                            <span class="badge bg-<?php echo $triaje['prioridad'] == 'alta' ? 'danger' : ($triaje['prioridad'] == 'media' ? 'warning' : 'success'); ?>">
                                <?php echo ucfirst($triaje['prioridad']); ?>
                            </span>
                        </p>
                        <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($triaje['observaciones'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulario de Consulta Médica -->
    <form method="POST" action="">
        <div class="row">
            <!-- Signos Vitales -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-heartbeat me-2"></i>
                            Signos Vitales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Presión Sistólica</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="presion_sistolica" 
                                       value="<?php echo $signosVitales['presion_sistolica'] ?? ''; ?>"
                                       placeholder="120">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Presión Diastólica</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="presion_diastolica" 
                                       value="<?php echo $signosVitales['presion_diastolica'] ?? ''; ?>"
                                       placeholder="80">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Frecuencia Cardíaca</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="frecuencia_cardiaca" 
                                       value="<?php echo $signosVitales['frecuencia_cardiaca'] ?? ''; ?>"
                                       placeholder="70">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" class="form-control form-control-sm" 
                                       name="temperatura" 
                                       value="<?php echo $signosVitales['temperatura'] ?? ''; ?>"
                                       placeholder="36.5">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" step="0.1" class="form-control form-control-sm" 
                                       name="peso" 
                                       value="<?php echo $signosVitales['peso'] ?? ''; ?>"
                                       placeholder="70.0">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Talla (m)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                       name="talla" 
                                       value="<?php echo $signosVitales['talla'] ?? ''; ?>"
                                       placeholder="1.70">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label">Saturación O2 (%)</label>
                                <input type="number" class="form-control form-control-sm" 
                                       name="saturacion_oxigeno" 
                                       value="<?php echo $signosVitales['saturacion_oxigeno'] ?? ''; ?>"
                                       placeholder="98">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consulta Médica -->
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-stethoscope me-2"></i>
                            Consulta Médica
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Síntomas <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="sintomas" rows="3" required
                                          placeholder="Describa los síntomas presentados por el paciente..."><?php echo htmlspecialchars($consultaExistente['sintomas'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Examen Físico</label>
                                <textarea class="form-control" name="examen_fisico" rows="3"
                                          placeholder="Resultados del examen físico realizado..."><?php echo htmlspecialchars($consultaExistente['examen_fisico'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Diagnóstico Principal <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="diagnostico_principal" rows="2" required
                                          placeholder="Diagnóstico principal del paciente..."><?php echo htmlspecialchars($consultaExistente['diagnostico_principal'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Diagnósticos Secundarios</label>
                                <textarea class="form-control" name="diagnosticos_secundarios" rows="2"
                                          placeholder="Diagnósticos secundarios (si aplica)..."><?php echo htmlspecialchars($consultaExistente['diagnosticos_secundarios'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Tratamiento</label>
                                <textarea class="form-control" name="tratamiento" rows="4"
                                          placeholder="Medicamentos, dosificación, duración del tratamiento..."><?php echo htmlspecialchars($consultaExistente['tratamiento'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Recomendaciones</label>
                                <textarea class="form-control" name="recomendaciones" rows="3"
                                          placeholder="Recomendaciones generales, cuidados, restricciones..."><?php echo htmlspecialchars($consultaExistente['recomendaciones'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Próxima Cita</label>
                                <input type="text" class="form-control" name="proxima_cita" 
                                       value="<?php echo htmlspecialchars($consultaExistente['proxima_cita'] ?? ''); ?>"
                                       placeholder="Ej: Control en 1 semana">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duración (minutos)</label>
                                <input type="number" class="form-control" name="duracion_minutos" 
                                       value="<?php echo $consultaExistente['duracion_minutos'] ?? 30; ?>"
                                       min="15" max="180">
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Observaciones Médicas</label>
                                <textarea class="form-control" name="observaciones_medicas" rows="2"
                                          placeholder="Observaciones adicionales..."><?php echo htmlspecialchars($consultaExistente['observaciones_medicas'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Observaciones Signos Vitales</label>
                                <textarea class="form-control" name="observaciones_signos" rows="2"
                                          placeholder="Observaciones sobre los signos vitales..."><?php echo htmlspecialchars($signosVitales['observaciones'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-success btn-lg me-3">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $consultaExistente ? 'Actualizar Consulta' : 'Guardar Consulta'; ?>
                        </button>
                        
                        <a href="index.php?action=consultas/atender" class="btn btn-secondary btn-lg me-3">
                            <i class="fas fa-arrow-left me-2"></i>
                            Volver a Lista
                        </a>
                        
                        <a href="index.php?action=consultas/atender/historial&paciente_id=<?php echo $cita['id_paciente']; ?>" 
                           class="btn btn-info btn-lg">
                            <i class="fas fa-file-medical me-2"></i>
                            Ver Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-cálculo del IMC si se ingresa peso y talla
document.addEventListener('DOMContentLoaded', function() {
    const pesoInput = document.querySelector('input[name="peso"]');
    const tallaInput = document.querySelector('input[name="talla"]');
    
    function calcularIMC() {
        const peso = parseFloat(pesoInput.value);
        const talla = parseFloat(tallaInput.value);
        
        if (peso && talla && talla > 0) {
            const imc = peso / (talla * talla);
            console.log('IMC calculado:', imc.toFixed(2));
            // Aquí podrías mostrar el IMC en algún lugar de la interfaz
        }
    }
    
    pesoInput?.addEventListener('input', calcularIMC);
    tallaInput?.addEventListener('input', calcularIMC);
});
</script>