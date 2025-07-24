<?php
// views/consultas/triaje/ver.php
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
                            Triaje Médico - Detalle
                        </h5>
                        <div class="btn-group">
                            <a href="index.php?action=consultas/triaje" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver
                            </a>
                            <button onclick="window.print()" class="btn btn-light btn-sm">
                                <i class="fas fa-print me-1"></i>
                                Imprimir
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Información del paciente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-user me-2"></i>
                                        Información del Paciente
                                    </h6>
                                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($triaje['nombre_paciente']); ?></p>
                                    <p class="mb-1"><strong>Cédula:</strong> <?php echo htmlspecialchars($triaje['cedula']); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($triaje['email']); ?></p>
                                    <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($triaje['telefono'] ?: 'No registrado'); ?></p>
                                    <?php if ($triaje['fecha_nacimiento']): ?>
                                        <p class="mb-1"><strong>Fecha Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($triaje['fecha_nacimiento'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($triaje['genero']): ?>
                                        <p class="mb-0"><strong>Género:</strong> <?php echo $triaje['genero'] === 'M' ? 'Masculino' : ($triaje['genero'] === 'F' ? 'Femenino' : 'Otro'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Información del Triaje
                                    </h6>
                                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($triaje['fecha_respuesta'])); ?></p>
                                    <p class="mb-1"><strong>Hora:</strong> <?php echo date('H:i', strtotime($triaje['fecha_respuesta'])); ?></p>
                                    <p class="mb-1"><strong>Tipo:</strong> 
                                        <span class="badge bg-<?php echo $triaje['tipo_triaje'] === 'digital' ? 'primary' : 'success'; ?>">
                                            <?php echo ucfirst($triaje['tipo_triaje']); ?>
                                        </span>
                                    </p>
                                    <p class="mb-1"><strong>Realizado por:</strong> <?php echo htmlspecialchars($triaje['realizado_por']); ?></p>
                                    <?php if ($triaje['nombre_especialidad']): ?>
                                        <p class="mb-1"><strong>Especialidad:</strong> <?php echo htmlspecialchars($triaje['nombre_especialidad']); ?></p>
                                        <p class="mb-0"><strong>Médico:</strong> <?php echo htmlspecialchars($triaje['nombre_medico']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Respuestas del triaje -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Respuestas del Triaje
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($triaje['respuestas'] as $index => $respuesta): ?>
                                <div class="mb-4 p-3 border rounded">
                                    <h6 class="text-primary mb-2">
                                        <?php echo ($index + 1) . '. ' . htmlspecialchars($respuesta['pregunta']); ?>
                                    </h6>
                                    
                                    <div class="ms-3">
                                        <?php if ($respuesta['tipo_pregunta'] === 'escala' && $respuesta['valor_numerico']): ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <strong class="me-3">Valor:</strong>
                                                <span class="badge bg-primary fs-6"><?php echo $respuesta['valor_numerico']; ?></span>
                                                
                                                <?php
                                                $opciones = json_decode($respuesta['opciones_json'], true);
                                                if (isset($opciones['etiquetas'][$respuesta['valor_numerico']])) {
                                                    echo '<span class="ms-2 text-muted">(' . htmlspecialchars($opciones['etiquetas'][$respuesta['valor_numerico']]) . ')</span>';
                                                }
                                                ?>
                                            </div>
                                        <?php elseif ($respuesta['tipo_pregunta'] === 'numero' && $respuesta['valor_numerico']): ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <strong class="me-3">Valor:</strong>
                                                <span class="badge bg-info fs-6">
                                                    <?php echo $respuesta['valor_numerico']; ?>
                                                    <?php
                                                    $opciones = json_decode($respuesta['opciones_json'], true);
                                                    if (isset($opciones['unidad'])) {
                                                        echo ' ' . htmlspecialchars($opciones['unidad']);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <strong>Respuesta:</strong>
                                            <div class="mt-1 p-2 bg-light rounded">
                                                <?php echo nl2br(htmlspecialchars($respuesta['respuesta'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Resumen del triaje -->
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Resumen del Triaje
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Total de Preguntas:</strong> <?php echo count($triaje['respuestas']); ?></p>
                                    <p><strong>Fecha Realización:</strong> <?php echo date('d/m/Y H:i', strtotime($triaje['fecha_respuesta'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-success">Completado</span>
                                    </p>
                                    <p><strong>Motivo Consulta:</strong> <?php echo htmlspecialchars($triaje['motivo_consulta'] ?: 'Triaje inicial'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Acciones adicionales -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="index.php?action=consultas/triaje/historial&paciente_id=<?php echo $triaje['nombre_paciente']; ?>" 
                           class="btn btn-outline-info">
                            <i class="fas fa-history me-2"></i>
                            Ver Historial del Paciente
                        </a>
                        
                        <?php if ($triaje['nombre_medico']): ?>
                            <a href="index.php?action=citas/ver&id=<?php echo $triaje['id_cita']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Ver Cita Médica
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .btn-group, .card-header .btn {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    body {
        font-size: 12px;
    }
}
</style>