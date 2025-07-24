<?php
// views/config/horarios/create.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-plus me-2"></i>
                            Configurar Horarios Médicos
                        </h5>
                        <a href="index.php?action=config/horarios" class="btn btn-light btn-sm">
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
                    
                    <form method="POST" id="horarioForm">
                        <!-- Selección de médico y sucursal -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Médico <span class="text-danger">*</span>
                                </label>
                                <select name="id_medico" id="id_medico" class="form-select" required>
                                    <option value="">Seleccione un médico...</option>
                                    <?php foreach ($medicos as $medico): ?>
                                        <option value="<?php echo $medico['id_usuario']; ?>">
                                            <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    Sucursal <span class="text-danger">*</span>
                                </label>
                                <select name="id_sucursal" id="id_sucursal" class="form-select" required>
                                    <option value="">Seleccione una sucursal...</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?php echo $sucursal['id_sucursal']; ?>">
                                            <?php echo htmlspecialchars($sucursal['nombre_sucursal']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Configuración de horarios por día -->
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <i class="fas fa-calendar-week me-2"></i>
                                Configuración de Horarios por Día
                            </h6>
                            
                            <div class="row" id="horariosContainer">
                                <?php 
                                $dias = [
                                    1 => 'Lunes',
                                    2 => 'Martes', 
                                    3 => 'Miércoles',
                                    4 => 'Jueves',
                                    5 => 'Viernes',
                                    6 => 'Sábado',
                                    7 => 'Domingo'
                                ];
                                
                                foreach ($dias as $numero => $nombre): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-body p-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="dia_<?php echo $numero; ?>"
                                                           onchange="toggleDia(<?php echo $numero; ?>)">
                                                    <label class="form-check-label fw-bold" for="dia_<?php echo $numero; ?>">
                                                        <?php echo $nombre; ?>
                                                    </label>
                                                </div>
                                                
                                                <div id="horarios_dia_<?php echo $numero; ?>" style="display: none;">
                                                    <div class="horario-item">
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label class="form-label small">Hora Inicio</label>
                                                                <input type="time" 
                                                                       name="horarios[<?php echo $numero; ?>][hora_inicio]"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small">Hora Fin</label>
                                                                <input type="time" 
                                                                       name="horarios[<?php echo $numero; ?>][hora_fin]"
                                                                       class="form-control form-control-sm">
                                                            </div>
                                                        </div>
                                                        <input type="hidden" 
                                                               name="horarios[<?php echo $numero; ?>][dia_semana]" 
                                                               value="<?php echo $numero; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Guardar Horarios
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
function toggleDia(dia) {
    const checkbox = document.getElementById(`dia_${dia}`);
    const container = document.getElementById(`horarios_dia_${dia}`);
    
    if (checkbox.checked) {
        container.style.display = 'block';
        // Hacer requeridos los campos de hora
        container.querySelectorAll('input[type="time"]').forEach(input => {
            input.required = true;
        });
    } else {
        container.style.display = 'none';
       // Quitar requeridos y limpiar valores
       container.querySelectorAll('input[type="time"]').forEach(input => {
           input.required = false;
           input.value = '';
       });
   }
}

// Verificar que se haya seleccionado al menos un día antes de enviar
document.getElementById('horarioForm').addEventListener('submit', function(e) {
   const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
   
   if (checkboxes.length === 0) {
       e.preventDefault();
       alert('Debe seleccionar al menos un día para configurar horarios');
       return false;
   }
   
   // Validar que todos los días seleccionados tengan horarios completos
   let valid = true;
   checkboxes.forEach(checkbox => {
       const dia = checkbox.id.split('_')[1];
       const horaInicio = document.querySelector(`input[name="horarios[${dia}][hora_inicio]"]`);
       const horaFin = document.querySelector(`input[name="horarios[${dia}][hora_fin]"]`);
       
       if (!horaInicio.value || !horaFin.value) {
           valid = false;
           alert(`Por favor complete los horarios para ${checkbox.nextElementSibling.textContent}`);
           return;
       }
       
       if (horaInicio.value >= horaFin.value) {
           valid = false;
           alert(`La hora de inicio debe ser menor que la hora de fin para ${checkbox.nextElementSibling.textContent}`);
           return;
       }
   });
   
   if (!valid) {
       e.preventDefault();
       return false;
   }
});

// Verificar horarios existentes cuando se selecciona médico y sucursal
function verificarHorariosExistentes() {
   const medicoId = document.getElementById('id_medico').value;
   const sucursalId = document.getElementById('id_sucursal').value;
   
   if (medicoId && sucursalId) {
       // Aquí podrías hacer una llamada AJAX para verificar horarios existentes
       // y mostrar una advertencia si ya existen
   }
}

document.getElementById('id_medico').addEventListener('change', verificarHorariosExistentes);
document.getElementById('id_sucursal').addEventListener('change', verificarHorariosExistentes);
</script>

