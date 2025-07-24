<?php
// views/citas/ver.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-eye me-2"></i>
                            Detalle de Cita Médica
                        </h5>
                        <div class="btn-group">
                            <a href="index.php?action=citas" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver
                            </a>
                            <?php if (in_array($cita['estado_cita'], ['agendada', 'confirmada'])): ?>
                                <a href="index.php?action=citas/editar&id=<?php echo $cita['id_cita']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    Editar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Información principal de la cita -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-user me-2"></i>
                                        Información del Paciente
                                    </h6>
                                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($cita['nombre_paciente']); ?></p>
                                    <p class="mb-1"><strong>Cédula:</strong> <?php echo htmlspecialchars($cita['cedula']); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($cita['email']); ?></p>
                                    <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($cita['telefono'] ?: 'No registrado'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Información de la Cita
                                    </h6>
                                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></p>
                                    <p class="mb-1"><strong>Hora:</strong> <?php echo date('H:i', strtotime($cita['hora_cita'])); ?></p>
                                    <p class="mb-1"><strong>Tipo:</strong> 
                                        <span class="badge bg-<?php echo $cita['tipo_cita'] === 'virtual' ? 'info' : 'success'; ?>">
                                            <?php echo ucfirst($cita['tipo_cita']); ?>
                                        </span>
                                    </p>
                                    <p class="mb-0"><strong>Estado:</strong> 
                                        <?php
                                        $badgeClass = [
                                            'agendada' => 'bg-primary',
                                            'confirmada' => 'bg-info',
                                            'en_curso' => 'bg-warning',
                                            'completada' => 'bg-success',
                                            'cancelada' => 'bg-danger',
                                            'no_asistio' => 'bg-secondary'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $badgeClass[$cita['estado_cita']] ?? 'bg-secondary'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $cita['estado_cita'])); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información médica -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-user-md me-2"></i>
                                        Información Médica
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Médico:</strong> <?php echo htmlspecialchars($cita['nombre_medico']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Especialidad:</strong> <?php echo htmlspecialchars($cita['nombre_especialidad']); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="mb-1"><strong>Sucursal:</strong> <?php echo htmlspecialchars($cita['nombre_sucursal']); ?></p>
                                        </div>
                                    </div>
                                    <?php if ($cita['motivo_consulta']): ?>
                                        <div class="mt-2">
                                            <strong>Motivo de consulta:</strong>
                                            <div class="mt-1 p-2 bg-white rounded border">
                                                <?php echo nl2br(htmlspecialchars($cita['motivo_consulta'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información de triaje y consulta -->
                    <div class="row">
                        <?php if ($triaje && $triaje['tiene_triaje'] > 0): ?>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-clipboard-check me-2"></i>
                                            Triaje Realizado
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($triaje['fecha_triaje'])); ?></p>
                                        <p class="mb-2"><strong>Tipo:</strong> 
                                            <span class="badge bg-<?php echo $triaje['tipo_triaje'] === 'digital' ? 'primary' : 'success'; ?>">
                                                <?php echo ucfirst($triaje['tipo_triaje']); ?>
                                            </span>
                                        </p>
                                        <a href="index.php?action=consultas/triaje/ver&id=<?php echo $cita['id_cita']; ?>" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye me-1"></i>
                                            Ver Triaje Completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Sin Triaje
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">Este paciente aún no tiene triaje realizado.</p>
                                        <a href="index.php?action=consultas/triaje/crear&paciente_id=<?php echo $cita['id_paciente']; ?>&cita_id=<?php echo $cita['id_cita']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-plus me-1"></i>
                                            Realizar Triaje
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($consulta): ?>
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-stethoscope me-2"></i>
                                            Consulta Realizada
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($consulta['fecha_consulta'])); ?></p>
                                        <p class="mb-1"><strong>Duración:</strong> <?php echo $consulta['duracion_minutos']; ?> minutos</p>
                                        <p class="mb-2"><strong>Atendido por:</strong> <?php echo htmlspecialchars($consulta['medico_consulta']); ?></p>
                                        <a href="index.php?action=consultas/ver&id=<?php echo $consulta['id_consulta']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>
                                            Ver Consulta Completa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-clock me-2"></i>
                                            Pendiente de Consulta
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">La consulta médica aún no ha sido realizada.</p>
                                        <?php if ($cita['estado_cita'] === 'confirmada'): ?>
                                            <a href="index.php?action=consultas/atender&cita_id=<?php echo $cita['id_cita']; ?>" 
                                               class="btn btn-sm btn-success">
                                                <i class="fas fa-user-md me-1"></i>
                                                Atender Paciente
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
