<?php
// views/citas/crear.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Agendar Nueva Cita Médica
                        </h5>
                        <a href="index.php?action=citas" class="btn btn-light btn-sm">
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
                    
                    <form method="POST" id="citaForm">
                    <!-- Buscar paciente -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-user me-2"></i>
            Buscar Paciente
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <label class="form-label">
                    Cédula del Paciente <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="text" 
                           name="cedula_paciente" 
                           id="cedula_paciente"
                           class="form-control" 
                           placeholder="Ingrese la cédula..."
                           maxlength="10"
                           required>
                    <button type="button" class="btn btn-outline-primary" onclick="buscarPaciente()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <small class="text-muted">
                        Si no existe, podrás crearlo inmediatamente
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Resultado de búsqueda -->
        <div id="resultado_paciente" class="mt-3" style="display: none;"></div>
        
        <!-- Formulario para crear paciente nuevo (inicialmente oculto) -->
        <div id="formulario_nuevo_paciente" class="mt-4" style="display: none;">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Nuevo Paciente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="nuevo_nombre" 
                                   class="form-control" 
                                   placeholder="Nombre del paciente"
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Apellido <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   id="nuevo_apellido" 
                                   class="form-control" 
                                   placeholder="Apellido del paciente"
                                   required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   id="nuevo_email" 
                                   class="form-control" 
                                   placeholder="correo@email.com"
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" 
                                   id="nuevo_telefono" 
                                   class="form-control" 
                                   placeholder="0987654321">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" 
                                   id="nuevo_fecha_nacimiento" 
                                   class="form-control"
                                   max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Género</label>
                            <select id="nuevo_genero" class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-success" onclick="crearPacienteRapido()">
                                    <i class="fas fa-save me-2"></i>
                                    Crear Paciente
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <textarea id="nuevo_direccion" 
                                      class="form-control" 
                                      rows="2" 
                                      placeholder="Dirección completa del paciente"></textarea>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="cancelarCreacionPaciente()">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Variables globales
let pacienteEncontrado = false;
let pacienteSeleccionado = null;

// Buscar paciente por cédula
function buscarPaciente() {
    const cedula = document.getElementById('cedula_paciente').value.trim();
    const resultadoDiv = document.getElementById('resultado_paciente');
    const formularioNuevo = document.getElementById('formulario_nuevo_paciente');
    
    if (cedula.length !== 10) {
        alert('Por favor ingrese una cédula válida de 10 dígitos');
        return;
    }
    
    // Ocultar formulario de nuevo paciente si estaba visible
    formularioNuevo.style.display = 'none';
    
    resultadoDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
    resultadoDiv.style.display = 'block';
    
    fetch(`index.php?action=citas/buscar-paciente&cedula=${cedula}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Paciente encontrado
                pacienteEncontrado = true;
                pacienteSeleccionado = data.paciente;
                
                resultadoDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h6><i class="fas fa-check me-2"></i>Paciente Encontrado</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <strong>${data.paciente.nombre} ${data.paciente.apellido}</strong><br>
                                <small>Email: ${data.paciente.email} | Teléfono: ${data.paciente.telefono || 'No registrado'}</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-success fs-6">✓ Listo para agendar cita</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Paciente no encontrado
                pacienteEncontrado = false;
                pacienteSeleccionado = null;
                
                resultadoDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Paciente no encontrado</strong>
                                <br>
                                <small>No existe un paciente registrado con la cédula: ${cedula}</small>
                            </div>
                            <button type="button" class="btn btn-success" onclick="mostrarFormularioNuevoPaciente()">
                                <i class="fas fa-user-plus me-2"></i>
                                Crear Nuevo Paciente
                            </button>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultadoDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error al buscar el paciente. Intente nuevamente.
                </div>
            `;
        });
}

// Mostrar formulario para crear nuevo paciente
function mostrarFormularioNuevoPaciente() {
    const formularioNuevo = document.getElementById('formulario_nuevo_paciente');
    const cedula = document.getElementById('cedula_paciente').value.trim();
    
    // Pre-llenar la cédula que ya se buscó
    formularioNuevo.style.display = 'block';
    
    // Scroll suave hacia el formulario
    formularioNuevo.scrollIntoView({ behavior: 'smooth' });
}

// Cancelar creación de paciente
function cancelarCreacionPaciente() {
    const formularioNuevo = document.getElementById('formulario_nuevo_paciente');
    formularioNuevo.style.display = 'none';
    
    // Limpiar campos
    document.getElementById('nuevo_nombre').value = '';
    document.getElementById('nuevo_apellido').value = '';
    document.getElementById('nuevo_email').value = '';
    document.getElementById('nuevo_telefono').value = '';
    document.getElementById('nuevo_fecha_nacimiento').value = '';
    document.getElementById('nuevo_genero').value = '';
    document.getElementById('nuevo_direccion').value = '';
}

// Crear paciente rápido
function crearPacienteRapido() {
    const cedula = document.getElementById('cedula_paciente').value.trim();
    const nombre = document.getElementById('nuevo_nombre').value.trim();
    const apellido = document.getElementById('nuevo_apellido').value.trim();
    const email = document.getElementById('nuevo_email').value.trim();
    const telefono = document.getElementById('nuevo_telefono').value.trim();
    const fechaNacimiento = document.getElementById('nuevo_fecha_nacimiento').value;
    const genero = document.getElementById('nuevo_genero').value;
    const direccion = document.getElementById('nuevo_direccion').value.trim();
    
    // Validaciones
    if (!nombre || !apellido || !email) {
        alert('Por favor complete los campos obligatorios: Nombre, Apellido y Email');
        return;
    }
    
    if (!validarCedula(cedula)) {
        alert('La cédula ingresada no es válida');
        return;
    }
    
    if (!validarEmail(email)) {
        alert('Por favor ingrese un email válido');
        return;
    }
    
    // Mostrar loading
    const btnCrear = event.target;
    const textoOriginal = btnCrear.innerHTML;
    btnCrear.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
    btnCrear.disabled = true;
    
    // Datos para enviar
    const datosNuevoPaciente = {
        cedula: cedula,
        nombre: nombre,
        apellido: apellido,
        email: email,
        telefono: telefono,
        fecha_nacimiento: fechaNacimiento,
        genero: genero,
        direccion: direccion
    };
    
    // Enviar datos
    fetch('index.php?action=citas/crear-paciente-rapido', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datosNuevoPaciente)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Paciente creado exitosamente
            pacienteEncontrado = true;
            pacienteSeleccionado = data.paciente;
            
            // Mostrar mensaje de éxito
            const resultadoDiv = document.getElementById('resultado_paciente');
            resultadoDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check me-2"></i>¡Paciente Creado Exitosamente!</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <strong>${data.paciente.nombre} ${data.paciente.apellido}</strong><br>
                            <small>Email: ${data.paciente.email} | Cédula: ${data.paciente.cedula}</small><br>
                            <small class="text-success"><i class="fas fa-info-circle me-1"></i>Se enviaron las credenciales por email</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-success fs-6">✓ Listo para agendar cita</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Ocultar formulario de creación
            document.getElementById('formulario_nuevo_paciente').style.display = 'none';
            
            // Scroll hacia el resultado
            resultadoDiv.scrollIntoView({ behavior: 'smooth' });
            
        } else {
            alert('Error al crear paciente: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear el paciente. Intente nuevamente.');
    })
    .finally(() => {
        // Restaurar botón
        btnCrear.innerHTML = textoOriginal;
        btnCrear.disabled = false;
    });
}

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

// Validar email
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Cargar médicos cuando cambien especialidad o sucursal
function cargarMedicos() {
    const especialidadId = document.getElementById('id_especialidad').value;
    const sucursalId = document.getElementById('id_sucursal').value;
    const medicoSelect = document.getElementById('id_medico');
    
    if (!especialidadId) {
        medicoSelect.innerHTML = '<option value="">Primero seleccione especialidad y sucursal</option>';
        medicoSelect.disabled = true;
        return;
    }
    
    medicoSelect.innerHTML = '<option value="">Cargando médicos...</option>';
    medicoSelect.disabled = true;
    
    fetch(`index.php?action=citas/get-medicos&especialidad_id=${especialidadId}&sucursal_id=${sucursalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                medicoSelect.innerHTML = '<option value="">Seleccione un médico...</option>';
                data.medicos.forEach(medico => {
                    medicoSelect.innerHTML += `<option value="${medico.id_usuario}">${medico.nombre_completo}</option>`;
                });
                medicoSelect.disabled = false;
            } else {
                medicoSelect.innerHTML = '<option value="">No hay médicos disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            medicoSelect.innerHTML = '<option value="">Error cargando médicos</option>';
        });
}

// Cargar horarios disponibles
function cargarHorarios() {
    const medicoId = document.getElementById('id_medico').value;
    const fecha = document.getElementById('fecha_cita').value;
    const horaSelect = document.getElementById('hora_cita');
    
    if (!medicoId || !fecha) {
        horaSelect.innerHTML = '<option value="">Primero seleccione médico y fecha</option>';
        horaSelect.disabled = true;
        return;
    }
    
    horaSelect.innerHTML = '<option value="">Cargando horarios...</option>';
    horaSelect.disabled = true;
    
    fetch(`index.php?action=citas/get-horarios&medico_id=${medicoId}&fecha=${fecha}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                horaSelect.innerHTML = '<option value="">Seleccione una hora...</option>';
                if (data.horarios.length > 0) {
                    data.horarios.forEach(horario => {
                        horaSelect.innerHTML += `<option value="${horario.hora}">${horario.hora_formato}</option>`;
                    });
                    horaSelect.disabled = false;
                } else {
                    horaSelect.innerHTML = '<option value="">No hay horarios disponibles</option>';
                }
            } else {
                horaSelect.innerHTML = '<option value="">Error cargando horarios</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            horaSelect.innerHTML = '<option value="">Error cargando horarios</option>';
        });
}

// Event listeners
document.getElementById('id_especialidad').addEventListener('change', cargarMedicos);
document.getElementById('id_sucursal').addEventListener('change', cargarMedicos);
document.getElementById('id_medico').addEventListener('change', cargarHorarios);
document.getElementById('fecha_cita').addEventListener('change', cargarHorarios);

// Validar formulario antes de enviar
document.getElementById('citaForm').addEventListener('submit', function(e) {
    if (!pacienteEncontrado) {
        e.preventDefault();
        alert('Debe buscar y confirmar un paciente válido antes de agendar la cita');
        return false;
    }
});

// Solo números en cédula
document.getElementById('cedula_paciente').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Buscar con Enter
document.getElementById('cedula_paciente').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        buscarPaciente();
    }
});
</script>