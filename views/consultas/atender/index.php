
<?php
require_once 'includes/header.php';
require_once 'models/Cita.php';

// Solo médicos pueden acceder
if ($_SESSION['user_role'] != 3) {
    header('Location: index.php?action=dashboard');
    exit;
}

$citaModel = new Cita();
$medicoId = $_SESSION['user_id'];
$citas = $citaModel->getCitasPendientesMedico($medicoId);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-nurse me-2"></i>
                        Atender Pacientes
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($citas)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No tienes citas pendientes de atención en este momento.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Fecha/Hora</th>
                                        <th>Especialidad</th>
                                        <th>Motivo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citas as $cita): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cita['nombre_paciente']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-card me-1"></i>
                                                    <?php echo htmlspecialchars($cita['cedula']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?>
                                                <br>
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($cita['nombre_especialidad']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($cita['motivo_consulta']); ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $estadoClass = [
                                                    'agendada' => 'bg-warning',
                                                    'confirmada' => 'bg-success',
                                                    'en_curso' => 'bg-primary',
                                                    'completada' => 'bg-secondary'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $estadoClass[$cita['estado_cita']] ?? 'bg-secondary'; ?>">
                                                    <?php echo ucfirst($cita['estado_cita']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?action=consultas/atender&cita_id=<?php echo $cita['id_cita']; ?>" 
                                                       class="btn btn-sm btn-primary"
                                                       title="Atender">
                                                        <i class="fas fa-user-md"></i>
                                                        Atender
                                                    </a>
                                                    
                                                    <a href="index.php?action=consultas/atender/historial&paciente_id=<?php echo $cita['id_paciente']; ?>" 
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Ver Historial">
                                                        <i class="fas fa-file-medical"></i>
                                                        Historial
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>