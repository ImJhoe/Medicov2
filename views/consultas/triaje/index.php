<?php
// views/consultas/triaje/index.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-clipboard-list text-primary me-2"></i>
                    Sistema de Triaje Médico
                </h2>
                <div class="btn-group">
                    <a href="index.php?action=consultas/triaje/nuevo-paciente" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>
                        Nuevo Paciente con Triaje
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Búsqueda rápida de paciente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        Búsqueda Rápida de Paciente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" 
                                       id="buscar_cedula" 
                                       class="form-control" 
                                       placeholder="Ingrese la cédula del paciente..."
                                       maxlength="10">
                                <button class="btn btn-primary" onclick="buscarPaciente()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">
                                Busque un paciente existente para realizar el triaje
                            </small>
                        </div>
                    </div>
                    
                    <!-- Resultado de búsqueda -->
                    <div id="resultado_busqueda" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pacientes pendientes de triaje -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Pacientes Pendientes de Triaje Hoy
                        <span class="badge bg-dark ms-2"><?php echo count($pacientesPendientes); ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pacientesPendientes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Cédula</th>
                                        <th>Hora Cita</th>
                                        <th>Especialidad</th>
                                        <th>Médico</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pacientesPendientes as $paciente): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($paciente['nombre_completo']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($paciente['telefono']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($paciente['cedula']); ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo date('H:i', strtotime($paciente['hora_cita'])); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($paciente['nombre_especialidad']); ?></td>
                                            <td><?php echo htmlspecialchars($paciente['nombre_medico']); ?></td>
                                            <td>
                                                <a href="index.php?action=consultas/triaje/crear&paciente_id=<?php echo $paciente['id_usuario']; ?>&cita_id=<?php echo $paciente['id_cita']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-clipboard-list"></i> Realizar Triaje
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">No hay pacientes pendientes</h5>
                            <p class="text-muted">Todos los pacientes con citas para hoy ya tienen triaje realizado.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Triajes realizados hoy -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check me-2"></i>
                        Triajes Realizados Hoy
                        <span class="badge bg-light text-dark ms-2"><?php echo count($triajesHoy); ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($triajesHoy)): ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($triajesHoy as $triaje): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <strong><?php echo htmlspecialchars($triaje['nombre_paciente']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($triaje['cedula']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($triaje['fecha_respuesta'])); ?>
                                        • <?php echo ucfirst($triaje['tipo_triaje']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        Por: <?php echo htmlspecialchars($triaje['realizado_por']); ?>
                                    </small>
                                    <div class="mt-1">
                                        <a href="index.php?action=consultas/triaje/ver&id=<?php echo $triaje['id_cita']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-clipboard fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No hay triajes realizados hoy</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function buscarPaciente() {
    const cedula = document.getElementById('buscar_cedula').value.trim();
    const resultadoDiv = document.getElementById('resultado_busqueda');
    
    if (cedula.length < 10) {
        alert('Por favor ingrese una cédula válida de 10 dígitos');
        return;
    }
    
    // Mostrar loading
    resultadoDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
    resultadoDiv.style.display = 'block';
    
    fetch(`index.php?action=consultas/triaje/buscar-paciente&cedula=${cedula}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="alert alert-success">
                        <h6><i class="fas fa-user me-2"></i>Paciente Encontrado</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <strong>${data.paciente.nombre} ${data.paciente.apellido}</strong><br>
                                <small>Cédula: ${data.paciente.cedula} | Email: ${data.paciente.email}</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="index.php?action=consultas/triaje/crear&paciente_id=${data.paciente.id_usuario}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-clipboard-list"></i> Realizar Triaje
                                </a>
                                <a href="index.php?action=consultas/triaje/historial&paciente_id=${data.paciente.id_usuario}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-history"></i> Historial
                                </a>
                            </div>
                        </div>
                `;
                
                if (data.citas_pendientes && data.citas_pendientes.length > 0) {
                    html += `
                        <div class="mt-2">
                            <small class="text-muted">Citas pendientes de triaje:</small>
                            <ul class="list-unstyled mt-1">
                    `;
                    data.citas_pendientes.forEach(cita => {
                        html += `
                            <li>
                                <small>
                                    ${cita.fecha_cita} ${cita.hora_cita} - ${cita.nombre_especialidad} 
                                    <a href="index.php?action=consultas/triaje/crear&paciente_id=${data.paciente.id_usuario}&cita_id=${cita.id_cita}" 
                                       class="btn btn-xs btn-outline-primary ms-2">Triaje para esta cita</a>
                                </small>
                            </li>
                        `;
                    });
                    html += `</ul>`;
                }
                
                html += `</div>`;
                resultadoDiv.innerHTML = html;
            } else {
                resultadoDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message}
                        <div class="mt-2">
                            <a href="index.php?action=consultas/triaje/nuevo-paciente" class="btn btn-success btn-sm">
                                <i class="fas fa-user-plus"></i> Crear Nuevo Paciente
                            </a>
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

// Permitir buscar con Enter
document.getElementById('buscar_cedula').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        buscarPaciente();
    }
});

// Solo permitir números en el campo de cédula
document.getElementById('buscar_cedula').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

