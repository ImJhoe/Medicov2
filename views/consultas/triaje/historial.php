<?php
// views/consultas/triaje/historial.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Historial de Triajes - <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                        </h5>
                        <a href="index.php?action=consultas/triaje" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Información del paciente -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-user me-2"></i>
                                                Información del Paciente
                                            </h6>
                                            <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></p>
                                            <p class="mb-1"><strong>Cédula:</strong> <?php echo htmlspecialchars($paciente['cedula']); ?></p>
                                            <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($paciente['email']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($paciente['telefono'] ?: 'No registrado'); ?></p>
                                            <?php if ($paciente['fecha_nacimiento']): ?>
                                                <p class="mb-1"><strong>Fecha Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($paciente['fecha_nacimiento'])); ?></p>
                                            <?php endif; ?>
                                            <div class="mt-2">
                                                <a href="index.php?action=consultas/triaje/crear&paciente_id=<?php echo $paciente['id_usuario']; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus me-1"></i>
                                                    Nuevo Triaje
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historial de triajes -->
                    <?php if (!empty($triajes)): ?>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Historial de Triajes 
                                    <span class="badge bg-primary"><?php echo count($triajes); ?></span>
                                </h6>
                                
                                <div class="timeline">
                                    <?php foreach ($triajes as $index => $triaje): ?>
                                        <div class="timeline-item mb-4">
                                            <div class="card border-start border-4 border-primary">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <h6 class="card-title mb-2">
                                                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                                                Triaje #<?php echo $triaje['id_cita']; ?>
                                                                <span class="badge bg-<?php echo $triaje['tipo_triaje'] === 'digital' ? 'info' : 'success'; ?> ms-2">
                                                                    <?php echo ucfirst($triaje['tipo_triaje']); ?>
                                                                </span>
                                                            </h6>
                                                            
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-clock me-1"></i>
                                                                    <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($triaje['fecha_respuesta'])); ?>
                                                                </small>
                                                            </div>
                                                            
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user-md me-1"></i>
                                                                    <strong>Realizado por:</strong> <?php echo htmlspecialchars($triaje['realizado_por']); ?>
                                                                </small>
                                                            </div>
                                                            
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-list-ol me-1"></i>
                                                                    <strong>Preguntas respondidas:</strong> <?php echo $triaje['total_respuestas']; ?>
                                                                </small>
                                                            </div>
                                                            
                                                            <?php if ($triaje['nombre_especialidad']): ?>
                                                                <div class="mb-2">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-stethoscope me-1"></i>
                                                                        <strong>Especialidad:</strong> <?php echo htmlspecialchars($triaje['nombre_especialidad']); ?>
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($triaje['nombre_medico']): ?>
                                                                <div class="mb-2">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-user-md me-1"></i>
                                                                        <strong>Médico asignado:</strong> <?php echo htmlspecialchars($triaje['nombre_medico']); ?>
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="col-md-4 text-end">
                                                            <div class="d-flex flex-column gap-2">
                                                                <a href="index.php?action=consultas/triaje/ver&id=<?php echo $triaje['id_cita']; ?>" 
                                                                   class="btn btn-outline-primary btn-sm">
                                                                    <i class="fas fa-eye me-1"></i>
                                                                    Ver Detalle
                                                                </a>
                                                                
                                                                <small class="text-muted">
                                                                    <?php
                                                                    $diasTranscurridos = floor((time() - strtotime($triaje['fecha_respuesta'])) / (60 * 60 * 24));
                                                                    if ($diasTranscurridos == 0) {
                                                                        echo 'Hoy';
                                                                    } elseif ($diasTranscurridos == 1) {
                                                                        echo 'Ayer';
                                                                    } else {
                                                                        echo "Hace {$diasTranscurridos} días";
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Sin Triajes Registrados</h5>
                            <p class="text-muted">Este paciente aún no tiene triajes médicos registrados en el sistema.</p>
                            <a href="index.php?action=consultas/triaje/crear&paciente_id=<?php echo $paciente['id_usuario']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Crear Primer Triaje
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: -8px;
    top: 100%;
    width: 2px;
    height: 30px;
    background-color: #dee2e6;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -12px;
    top: 20px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #0d6efd;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.border-start {
    border-left-width: 4px !important;
}
</style>

