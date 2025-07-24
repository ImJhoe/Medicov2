<?php
// views/citas/editar.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Editar Cita Médica
                        </h5>
                       <a href="index.php?action=citas" class="btn btn-dark btn-sm">
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
                    
                    <!-- Información del paciente (no editable) -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-user me-2"></i>
                                Información del Paciente (No editable)
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($cita['nombre_paciente']); ?></p>
                                    <p class="mb-0"><strong>Cédula:</strong> <?php echo htmlspecialchars($cita['cedula']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($cita['email']); ?></p>
                                    <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono'] ?: 'No registrado'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" id="editarCitaForm">
                        <!-- Datos editables de la cita -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Especialidad <span class="text-danger">*</span>
                                </label>
                                <select name="id_especialidad" id="id_especialidad" class="form-select" required>
                                    <option value="">Seleccione especialidad...</option>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <option value="<?php echo $esp['id_especialidad']; ?>"
                                                <?php echo ($esp['id_especialidad'] == $cita['id_especialidad']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($esp['nombre_especialidad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Sucursal <span class="text-danger">*</span>
                                </label>
                                <select name="id_sucursal" id="id_sucursal" class="form-select" required>
                                    <option value="">Seleccione sucursal...</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                        <option value="<?php echo $sucursal['id_sucursal']; ?>"
                                                <?php echo ($sucursal['id_sucursal'] == $cita['id_sucursal']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sucursal['nombre_sucursal']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Médico <span class="text-danger">*</span>
                                </label>
                                <select name="id_medico" id="id_medico" class="form-select" required>
                                    <option value="">Seleccione un médico...</option>
                                    <?php foreach ($medicos as $medico): ?>
                                        <option value="<?php echo $medico['id_usuario']; ?>"
                                                <?php echo ($medico['id_usuario'] == $cita['id_medico']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($medico['nombre_completo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Fecha de la Cita <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="fecha_cita" 
                                       id="fecha_cita"
                                       value="<?php echo $cita['fecha_cita']; ?>"
                                       class="form-control" 
                                       min="<?php echo date('Y-m-d'); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    Hora <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       name="hora_cita" 
                                       id="hora_cita"
                                       value="<?php echo $cita['hora_cita']; ?>"
                                       class="form-control" 
                                       required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    Tipo de Cita <span class="text-danger">*</span>
                                </label>
                                <select name="tipo_cita" class="form-select" required>
                                    <option value="presencial" <?php echo ($cita['tipo_cita'] === 'presencial') ? 'selected' : ''; ?>>Presencial</option>
                                    <option value="virtual" <?php echo ($cita['tipo_cita'] === 'virtual') ? 'selected' : ''; ?>>Virtual</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                 Estado <span class="text-danger">*</span>
                               </label>
                               <select name="estado_cita" class="form-select" required>
                                   <option value="agendada" <?php echo ($cita['estado_cita'] === 'agendada') ? 'selected' : ''; ?>>Agendada</option>
                                   <option value="confirmada" <?php echo ($cita['estado_cita'] === 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                   <option value="cancelada" <?php echo ($cita['estado_cita'] === 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                               </select>
                           </div>
                       </div>
                       
                       <div class="row">
                           <div class="col-12 mb-3">
                               <label class="form-label">Motivo de la Consulta</label>
                               <textarea name="motivo_consulta" 
                                         class="form-control" 
                                         rows="3" 
                                         placeholder="Describa brevemente el motivo de la consulta..."><?php echo htmlspecialchars($cita['motivo_consulta']); ?></textarea>
                           </div>
                       </div>
                       
                       <div class="d-flex gap-2">
                           <button type="submit" class="btn btn-warning">
                               <i class="fas fa-save me-2"></i>
                               Actualizar Cita
                           </button>
                           <a href="index.php?action=citas/ver&id=<?php echo $cita['id_cita']; ?>" class="btn btn-secondary">
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
// Cargar médicos cuando cambien especialidad o sucursal
function cargarMedicos() {
   const especialidadId = document.getElementById('id_especialidad').value;
   const sucursalId = document.getElementById('id_sucursal').value;
   const medicoSelect = document.getElementById('id_medico');
   const medicoActual = '<?php echo $cita['id_medico']; ?>';
   
   if (!especialidadId) {
       medicoSelect.innerHTML = '<option value="">Primero seleccione especialidad</option>';
       return;
   }
   
   medicoSelect.innerHTML = '<option value="">Cargando médicos...</option>';
   
   fetch(`index.php?action=citas/get-medicos&especialidad_id=${especialidadId}&sucursal_id=${sucursalId}`)
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               medicoSelect.innerHTML = '<option value="">Seleccione un médico...</option>';
               data.medicos.forEach(medico => {
                   const selected = (medico.id_usuario == medicoActual) ? 'selected' : '';
                   medicoSelect.innerHTML += `<option value="${medico.id_usuario}" ${selected}>${medico.nombre_completo}</option>`;
               });
           } else {
               medicoSelect.innerHTML = '<option value="">No hay médicos disponibles</option>';
           }
       })
       .catch(error => {
           console.error('Error:', error);
           medicoSelect.innerHTML = '<option value="">Error cargando médicos</option>';
       });
}

// Event listeners
document.getElementById('id_especialidad').addEventListener('change', cargarMedicos);
document.getElementById('id_sucursal').addEventListener('change', cargarMedicos);

// Validación básica del formulario
document.getElementById('editarCitaForm').addEventListener('submit', function(e) {
   const medico = document.getElementById('id_medico').value;
   const fecha = document.getElementById('fecha_cita').value;
   const hora = document.getElementById('hora_cita').value;
   
   if (!medico || !fecha || !hora) {
       e.preventDefault();
       alert('Por favor complete todos los campos obligatorios');
       return false;
   }
});
</script>
