<?php
// views/consultas/triaje/nuevo-paciente.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Nuevo Paciente con Triaje Médico
                        </h5>
                        <a href="index.php?action=consultas/triaje" class="btn btn-light btn-sm">
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
                   
                   <form method="POST" id="nuevoPacienteForm">
                       <!-- Datos del paciente -->
                       <div class="card mb-4">
                           <div class="card-header bg-light">
                               <h6 class="mb-0">
                                   <i class="fas fa-user me-2"></i>
                                   Datos del Paciente
                               </h6>
                           </div>
                           <div class="card-body">
                               <div class="row">
                                   <div class="col-md-6 mb-3">
                                       <label class="form-label">
                                           Cédula <span class="text-danger">*</span>
                                       </label>
                                       <input type="text" 
                                              name="cedula" 
                                              class="form-control" 
                                              placeholder="1234567890"
                                              maxlength="10"
                                              pattern="[0-9]{10}"
                                              required>
                                       <div class="form-text">Solo números, 10 dígitos</div>
                                   </div>
                                   
                                   <div class="col-md-6 mb-3">
                                       <label class="form-label">
                                           Email <span class="text-danger">*</span>
                                       </label>
                                       <input type="email" 
                                              name="email" 
                                              class="form-control" 
                                              placeholder="paciente@email.com"
                                              required>
                                   </div>
                               </div>
                               
                               <div class="row">
                                   <div class="col-md-6 mb-3">
                                       <label class="form-label">
                                           Nombre <span class="text-danger">*</span>
                                       </label>
                                       <input type="text" 
                                              name="nombre" 
                                              class="form-control" 
                                              placeholder="Juan"
                                              required>
                                   </div>
                                   
                                   <div class="col-md-6 mb-3">
                                       <label class="form-label">
                                           Apellido <span class="text-danger">*</span>
                                       </label>
                                       <input type="text" 
                                              name="apellido" 
                                              class="form-control" 
                                              placeholder="Pérez"
                                              required>
                                   </div>
                               </div>
                               
                               <div class="row">
                                   <div class="col-md-4 mb-3">
                                       <label class="form-label">Fecha de Nacimiento</label>
                                       <input type="date" 
                                              name="fecha_nacimiento" 
                                              class="form-control"
                                              max="<?php echo date('Y-m-d'); ?>">
                                   </div>
                                   
                                   <div class="col-md-4 mb-3">
                                       <label class="form-label">Género</label>
                                       <select name="genero" class="form-select">
                                           <option value="">Seleccionar...</option>
                                           <option value="M">Masculino</option>
                                           <option value="F">Femenino</option>
                                           <option value="O">Otro</option>
                                       </select>
                                   </div>
                                   
                                   <div class="col-md-4 mb-3">
                                       <label class="form-label">Teléfono</label>
                                       <input type="tel" 
                                              name="telefono" 
                                              class="form-control" 
                                              placeholder="0987654321">
                                   </div>
                               </div>
                               
                               <div class="row">
                                   <div class="col-12 mb-3">
                                       <label class="form-label">Dirección</label>
                                       <textarea name="direccion" 
                                                 class="form-control" 
                                                 rows="2" 
                                                 placeholder="Dirección completa del paciente"></textarea>
                                   </div>
                               </div>
                           </div>
                       </div>
                       
                       <!-- Triaje médico -->
                       <div class="card mb-4">
                           <div class="card-header bg-light">
                               <h6 class="mb-0">
                                   <i class="fas fa-clipboard-list me-2"></i>
                                   Triaje Médico
                               </h6>
                           </div>
                           <div class="card-body">
                               <div class="mb-3">
                                   <label class="form-label">Tipo de Triaje</label>
                                   <div class="row">
                                       <div class="col-md-6">
                                           <div class="form-check">
                                               <input class="form-check-input" type="radio" name="tipo_triaje" id="presencial_nuevo" value="presencial" checked>
                                               <label class="form-check-label" for="presencial_nuevo">
                                                   <i class="fas fa-user-md me-1"></i> Presencial
                                               </label>
                                           </div>
                                       </div>
                                       <div class="col-md-6">
                                           <div class="form-check">
                                               <input class="form-check-input" type="radio" name="tipo_triaje" id="digital_nuevo" value="digital">
                                               <label class="form-check-label" for="digital_nuevo">
                                                   <i class="fas fa-laptop me-1"></i> Digital
                                               </label>
                                           </div>
                                       </div>
                                   </div>
                               </div>
                               
                               <div class="accordion" id="preguntasTriajeAccordion">
                                   <?php foreach ($preguntas as $index => $pregunta): ?>
                                       <div class="accordion-item">
                                           <h2 class="accordion-header" id="heading<?php echo $pregunta['id_pregunta']; ?>">
                                               <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                                       type="button" 
                                                       data-bs-toggle="collapse" 
                                                       data-bs-target="#collapse<?php echo $pregunta['id_pregunta']; ?>"
                                                       aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                                   <?php echo ($index + 1) . '. ' . htmlspecialchars($pregunta['pregunta']); ?>
                                                   <?php if ($pregunta['obligatoria']): ?>
                                                       <span class="text-danger ms-2">*</span>
                                                   <?php endif; ?>
                                               </button>
                                           </h2>
                                           <div id="collapse<?php echo $pregunta['id_pregunta']; ?>" 
                                                class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                                data-bs-parent="#preguntasTriajeAccordion">
                                               <div class="accordion-body">
                                                   <?php
                                                   $inputName = "respuestas[{$pregunta['id_pregunta']}]";
                                                   $required = $pregunta['obligatoria'] ? 'required' : '';
                                                   
                                                   switch ($pregunta['tipo_pregunta']):
                                                       case 'texto':
                                                   ?>
                                                       <textarea name="<?php echo $inputName; ?>" 
                                                                 class="form-control" 
                                                                 rows="3" 
                                                                 placeholder="Escriba su respuesta aquí..."
                                                                 <?php echo $required; ?>></textarea>
                                                   <?php
                                                       break;
                                                       
                                                       case 'numero':
                                                           $opciones = json_decode($pregunta['opciones_json'], true);
                                                   ?>
                                                       <div class="input-group">
                                                           <input type="number" 
                                                                  name="<?php echo $inputName; ?>" 
                                                                  class="form-control"
                                                                  min="<?php echo $opciones['min'] ?? 0; ?>"
                                                                  max="<?php echo $opciones['max'] ?? 999; ?>"
                                                                  step="0.1"
                                                                  placeholder="Ingrese un valor numérico"
                                                                  <?php echo $required; ?>>
                                                           <?php if (isset($opciones['unidad'])): ?>
                                                               <span class="input-group-text"><?php echo htmlspecialchars($opciones['unidad']); ?></span>
                                                           <?php endif; ?>
                                                       </div>
                                                   <?php
                                                       break;
                                                       
                                                       case 'escala':
                                                           $opciones = json_decode($pregunta['opciones_json'], true);
                                                           $min = $opciones['min'] ?? 1;
                                                           $max = $opciones['max'] ?? 10;
                                                   ?>
                                                       <div class="range-container">
                                                           <label for="range<?php echo $pregunta['id_pregunta']; ?>" class="form-label">
                                                               Valor: <span id="valor<?php echo $pregunta['id_pregunta']; ?>"><?php echo $min; ?></span>
                                                           </label>
                                                           <input type="range" 
                                                                  class="form-range" 
                                                                  id="range<?php echo $pregunta['id_pregunta']; ?>"
                                                                  name="<?php echo $inputName; ?>"
                                                                  min="<?php echo $min; ?>" 
                                                                  max="<?php echo $max; ?>" 
                                                                  value="<?php echo $min; ?>"
                                                                  oninput="document.getElementById('valor<?php echo $pregunta['id_pregunta']; ?>').textContent = this.value"
                                                                  <?php echo $required; ?>>
                                                           <div class="d-flex justify-content-between">
                                                               <small class="text-muted"><?php echo $min; ?></small>
                                                               <small class="text-muted"><?php echo $max; ?></small>
                                                           </div>
                                                       </div>
                                                   <?php
                                                       break;
                                                       
                                                       case 'multiple':
                                                           $opciones = json_decode($pregunta['opciones_json'], true);
                                                   ?>
                                                       <select name="<?php echo $inputName; ?>" class="form-select" <?php echo $required; ?>>
                                                           <option value="">Seleccione una opción...</option>
                                                           <?php foreach ($opciones as $opcion): ?>
                                                               <option value="<?php echo htmlspecialchars($opcion); ?>">
                                                                   <?php echo htmlspecialchars($opcion); ?>
                                                               </option>
                                                           <?php endforeach; ?>
                                                       </select>
                                                   <?php
                                                       break;
                                                       
                                                       case 'sino':
                                                   ?>
                                                       <div class="row">
                                                           <div class="col-md-6">
                                                               <div class="form-check">
                                                                   <input class="form-check-input" 
                                                                          type="radio" 
                                                                          name="<?php echo $inputName; ?>" 
                                                                          id="si<?php echo $pregunta['id_pregunta']; ?>" 
                                                                          value="Sí"
                                                                          <?php echo $required; ?>>
                                                                   <label class="form-check-label" for="si<?php echo $pregunta['id_pregunta']; ?>">
                                                                       <i class="fas fa-check text-success me-1"></i> Sí
                                                                   </label>
                                                               </div>
                                                           </div>
                                                           <div class="col-md-6">
                                                               <div class="form-check">
                                                                   <input class="form-check-input" 
                                                                          type="radio" 
                                                                          name="<?php echo $inputName; ?>" 
                                                                          id="no<?php echo $pregunta['id_pregunta']; ?>" 
                                                                          value="No"
                                                                          <?php echo $required; ?>>
                                                                   <label class="form-check-label" for="no<?php echo $pregunta['id_pregunta']; ?>">
                                                                       <i class="fas fa-times text-danger me-1"></i> No
                                                                   </label>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   <?php
                                                       break;
                                                   endswitch;
                                                   ?>
                                               </div>
                                           </div>
                                       </div>
                                   <?php endforeach; ?>
                               </div>
                           </div>
                       </div>
                       
                       <div class="d-flex gap-2">
                           <button type="submit" class="btn btn-success">
                               <i class="fas fa-user-plus me-2"></i>
                               Crear Paciente y Guardar Triaje
                           </button>
                           <a href="index.php?action=consultas/triaje" class="btn btn-secondary">
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
// Validar cédula ecuatoriana
function validarCedula(cedula) {
   if (cedula.length !== 10) return false;
   
   const digitos = cedula.split('').map(Number);
   const provincia = parseInt(cedula.substring(0, 2));
   
   if (provincia < 1 || provincia > 24) return false;
   
   const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
   let suma = 0;
   
   for (let i = 0; i < 9; i++) {
       let resultado = digitos[i] * coeficientes[i];
       if (resultado > 9) resultado -= 9;
       suma += resultado;
   }
   
   const digitoVerificador = ((Math.ceil(suma / 10) * 10) - suma) % 10;
   return digitoVerificador === digitos[9];
}

// Solo permitir números en cédula
document.querySelector('input[name="cedula"]').addEventListener('input', function(e) {
   this.value = this.value.replace(/[^0-9]/g, '');
   
   if (this.value.length === 10) {
       if (validarCedula(this.value)) {
           this.classList.remove('is-invalid');
           this.classList.add('is-valid');
       } else {
           this.classList.remove('is-valid');
           this.classList.add('is-invalid');
       }
   } else {
       this.classList.remove('is-valid', 'is-invalid');
   }
});

// Validar formulario
document.getElementById('nuevoPacienteForm').addEventListener('submit', function(e) {
   const cedula = document.querySelector('input[name="cedula"]').value;
   
   if (!validarCedula(cedula)) {
       e.preventDefault();
       alert('Por favor ingrese una cédula válida');
       return false;
   }
   
   // Validar campos obligatorios
   const required = document.querySelectorAll('[required]');
   let valid = true;
   
   required.forEach(field => {
       if (!field.value && field.type !== 'radio') {
           valid = false;
           field.classList.add('is-invalid');
       } else if (field.type === 'radio') {
           const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
           const checked = Array.from(radioGroup).some(radio => radio.checked);
           if (!checked) {
               valid = false;
               radioGroup.forEach(radio => radio.classList.add('is-invalid'));
           }
       } else {
           field.classList.remove('is-invalid');
       }
   });
   
   if (!valid) {
       e.preventDefault();
       alert('Por favor complete todos los campos obligatorios');
       return false;
   }
});
</script>