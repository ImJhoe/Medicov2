<?php
// views/config/horarios/edit.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Editar Horario Médico
                        </h5>
                        <a href="index.php?action=config/horarios" class="btn btn-dark btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Información del horario -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Información del Horario</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Médico:</strong><br>
                                    <?php echo htmlspecialchars($horario['nombre_medico']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Sucursal:</strong><br>
                                    <?php echo htmlspecialchars($horario['nombre_sucursal']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">
                                    Día de la Semana <span class="text-danger">*</span>
                                </label>
                                <select name="dia_semana" class="form-select" required>
                                    <option value="">Seleccione un día...</option>
                                    <option value="1" <?php echo ($horario['dia_semana'] == 1) ? 'selected' : ''; ?>>Lunes</option>
                                    <option value="2" <?php echo ($horario['dia_semana'] == 2) ? 'selected' : ''; ?>>Martes</option>
                                    <option value="3" <?php echo ($horario['dia_semana'] == 3) ? 'selected' : ''; ?>>Miércoles</option>
                                    <option value="4" <?php echo ($horario['dia_semana'] == 4) ? 'selected' : ''; ?>>Jueves</option>
                                    <option value="5" <?php echo ($horario['dia_semana'] == 5) ? 'selected' : ''; ?>>Viernes</option>
                                    <option value="6" <?php echo ($horario['dia_semana'] == 6) ? 'selected' : ''; ?>>Sábado</option>
                                    <option value="7" <?php echo ($horario['dia_semana'] == 7) ? 'selected' : ''; ?>>Domingo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Hora de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       name="hora_inicio" 
                                       value="<?php echo htmlspecialchars($horario['hora_inicio']); ?>"
                                       class="form-control" 
                                       required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    Hora de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       name="hora_fin" 
                                       value="<?php echo htmlspecialchars($horario['hora_fin']); ?>"
                                       class="form-control" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="activo" 
                                           id="activo"
                                           <?php echo ($horario['activo']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">
                                        Horario activo
                                    </label>
                                    <div class="form-text">
                                        Desactivar temporalmente este horario sin eliminarlo
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>
                                Actualizar Horario
                            </button>
                            <a href="index.php?action=config/horarios" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validar que la hora de inicio sea menor que la de fin
document.querySelector('form').addEventListener('submit', function(e) {
    const horaInicio = document.querySelector('input[name="hora_inicio"]').value;
    const horaFin = document.querySelector('input[name="hora_fin"]').value;
    
    if (horaInicio >= horaFin) {
        e.preventDefault();
        alert('La hora de inicio debe ser menor que la hora de fin');
        return false;
    }
});
</script>
