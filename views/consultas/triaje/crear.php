<?php
// views/consultas/triaje/crear.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Triaje Médico
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
                    
                    <!-- Información del paciente -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-user me-2"></i>
                                Información del Paciente
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Nombre:</strong> <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?><br>
                                    <strong>Cédula:</strong> <?php echo htmlspecialchars($paciente['cedula']); ?><br>
                                    <strong>Email:</strong> <?php echo htmlspecialchars($paciente['email']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($paciente['telefono'] ?: 'No registrado'); ?><br>
                                    <strong>Fecha Nacimiento:</strong> <?php echo $paciente['fecha_nacimiento'] ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : 'No registrada'; ?><br>
                                    <strong>Género:</strong> <?php echo htmlspecialchars($paciente['genero'] ?: 'No especificado'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información de la cita (si existe) -->
                    <?php if ($cita): ?>
                        <div class="card bg-info text-white mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Información de la Cita
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?><br>
                                        <strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['nombre_especialidad']); ?><br>
                                        <strong>Médico:</strong> <?php echo htmlspecialchars($cita['nombre_medico']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Citas próximas del paciente -->
                    <?php if (!empty($citasProximas)): ?>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Citas Próximas del Paciente</h6>
                            <div class="row">
                                <?php foreach ($citasProximas as $citaProxima): ?>
                                    <div class="col-md-6 mb-2">
                                        <small>
                                            <strong><?php echo date('d/m/Y H:i', strtotime($citaProxima['fecha_cita'] . ' ' . $citaProxima['hora_cita'])); ?></strong><br>
                                            <?php echo htmlspecialchars($citaProxima['nombre_especialidad']); ?> - <?php echo htmlspecialchars($citaProxima['nombre_medico']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulario de triaje -->
                    <form method="POST" id="triajeForm">
                        <input type="hidden" name="cita_id" value="<?php echo $cita['id_cita'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de Triaje</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_triaje" id="digital" value="digital" checked>
                                        <label class="form-check-label" for="digital">
                                            <i class="fas fa-laptop me-1"></i> Digital
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_triaje" id="presencial" value="presencial">
                                        <label class="form-check-label" for="presencial">
                                            <i class="fas fa-user-md me-1"></i> Presencial
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">
                            <i class="fas fa-question-circle me-2"></i>
                            Preguntas de Triaje
                        </h6>
                        
                        <div class="accordion" id="preguntasAccordion">
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
                                         data-bs-parent="#preguntasAccordion">
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
                                                    <?php if (isset($opciones['etiquetas'])): ?>
                                                        <div class="mt-2">
                                                            <?php foreach ($opciones['etiquetas'] as $valor => $etiqueta): ?>
                                                                <small class="text-muted d-block">
                                                                    <?php echo $valor; ?>: <?php echo htmlspecialchars($etiqueta); ?>
                                                                </small>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
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
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Guardar Triaje
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
document.getElementById('triajeForm').addEventListener('submit', function(e) {
    // Validar que se hayan respondido las preguntas obligatorias
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
        alert('Por favor complete todas las preguntas obligatorias marcadas con *');
        return false;
    }
});
</script>
