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
                    <li class="breadcrumb-item active">Historial Clínico</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Información del Paciente -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Historial Clínico - <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($paciente['cedula']); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($paciente['fecha_nacimiento'])); ?></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Edad:</strong> <?php echo date_diff(date_create($paciente['fecha_nacimiento']), date_create('now'))->y; ?> años</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($paciente['telefono']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Consultas -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($historial)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Este paciente no tiene consultas médicas registradas en su historial clínico.
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-medical me-2"></i>
                            Historial de Consultas (<?php echo count($historial); ?> registros)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="timeline">
                            <?php foreach ($historial as $index => $consulta): ?>
                                <div class="timeline-item mb-4 p-3 <?php echo $index % 2 == 0 ? 'bg-light' : ''; ?>">
                                    <div class="timeline-marker">
                                        <i class="fas fa-stethoscope text-primary"></i>
                                    </div>
                                    
                                    <div class="timeline-content">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="text-primary mb-2">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($consulta['fecha_consulta'])); ?>
                                                    - <?php echo htmlspecialchars($consulta['nombre_especialidad']); ?>
                                                </h6>
                                                <p class="mb-1">
                                                    <strong>Médico:</strong> 
                                                    <?php echo htmlspecialchars($consulta['nombre_medico']); ?>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Sucursal:</strong> 
                                                    <?php echo htmlspecialchars($consulta['nombre_sucursal']); ?>
                                                </p>
                                            </div>
                                            
                                            <!-- Signos Vitales -->
                                            <?php if ($consulta['presion_sistolica'] || $consulta['temperatura']): ?>
                                                <div class="col-md-4">
                                                    <div class="card card-sm border-success">
                                                        <div class="card-header bg-success text-white py-2">
                                                            <h6 class="mb-0">
                                                                <i class="fas fa-heartbeat me-1"></i>
                                                                Signos Vitales
                                                            </h6>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <?php if ($consulta['presion_sistolica']): ?>
                                                                <small>
                                                                    <strong>PA:</strong> 
                                                                    <?php echo $consulta['presion_sistolica'] . '/' . $consulta['presion_diastolica']; ?>
                                                                </small><br>
                                                            <?php endif; ?>
                                                            <?php if ($consulta['frecuencia_cardiaca']): ?>
                                                                <small>
                                                                    <strong>FC:</strong> 
                                                                    <?php echo $consulta['frecuencia_cardiaca']; ?> lpm
                                                                </small><br>
                                                            <?php endif; ?>
                                                            <?php if ($consulta['temperatura']): ?>
                                                                <small>
                                                                    <strong>Temp:</strong> 
                                                                    <?php echo $consulta['temperatura']; ?>°C
                                                                </small><br>
                                                            <?php endif; ?>
                                                            <?php if ($consulta['peso']): ?>
                                                                <small>
                                                                    <strong>Peso:</strong> 
                                                                    <?php echo $consulta['peso']; ?> kg
                                                                </small><br>
                                                            <?php endif; ?>
                                                            <?php if ($consulta['saturacion_oxigeno']): ?>
                                                                <small>
                                                                    <strong>SpO2:</strong> 
                                                                    <?php echo $consulta['saturacion_oxigeno']; ?>%
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong class="text-primary">Síntomas:</strong>
                                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($consulta['sintomas'])); ?></p>
                                                </div>
                                                
                                                <?php if ($consulta['examen_fisico']): ?>
                                                    <div class="mb-3">
                                                        <strong class="text-primary">Examen Físico:</strong>
                                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($consulta['examen_fisico'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong class="text-danger">Diagnóstico Principal:</strong>
                                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($consulta['diagnostico_principal'])); ?></p>
                                                </div>
                                                
                                                <?php if ($consulta['diagnosticos_secundarios']): ?>
                                                    <div class="mb-3">
                                                        <strong class="text-warning">Diagnósticos Secundarios:</strong>
                                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($consulta['diagnosticos_secundarios'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($consulta['tratamiento']): ?>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="alert alert-info mb-2">
                                                        <strong><i class="fas fa-pills me-1"></i> Tratamiento:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($consulta['tratamiento'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($consulta['recomendaciones']): ?>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="alert alert-success mb-2">
                                                        <strong><i class="fas fa-lightbulb me-1"></i> Recomendaciones:</strong><br>
                                                        <?php echo nl2br(htmlspecialchars($consulta['recomendaciones'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($consulta['proxima_cita'] || $consulta['observaciones_medicas']): ?>
                                            <div class="row">
                                                <?php if ($consulta['proxima_cita']): ?>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            <strong>Próxima Cita:</strong> 
                                                            <?php echo htmlspecialchars($consulta['proxima_cita']); ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($consulta['observaciones_medicas']): ?>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            <strong>Observaciones:</strong> 
                                                            <?php echo htmlspecialchars($consulta['observaciones_medicas']); ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <hr class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Duración de consulta: <?php echo $consulta['duracion_minutos']; ?> minutos
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Botón para volver -->
            <div class="text-center mt-4">
                <a href="index.php?action=consultas/atender" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>
                    Volver a Atender Pacientes
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    border-left: 3px solid #007bff;
    margin-left: 20px;
}

.timeline-marker {
    position: absolute;
    left: -12px;
    top: 20px;
    width: 24px;
    height: 24px;
    background: white;
    border: 3px solid #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-sm {
    font-size: 0.875rem;
}
</style>
