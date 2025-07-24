<?php
// views/citas/index.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Gestión de Citas Médicas
                        </h5>
                        <a href="index.php?action=citas/crear" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Cita
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros de búsqueda -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" class="d-flex gap-2 align-items-end">
                                <input type="hidden" name="action" value="citas">
                                
                                <div class="flex-grow-1">
                                    <label class="form-label">Buscar Paciente</label>
                                    <input type="text" 
                                           name="search" 
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                           placeholder="Nombre, apellido o cédula..."
                                           class="form-control">
                                </div>
                                
                                <div style="min-width: 150px;">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" 
                                           name="fecha" 
                                           value="<?php echo htmlspecialchars($_GET['fecha'] ?? ''); ?>"
                                           class="form-control">
                                </div>
                                
                                <div style="min-width: 180px;">
                                    <label class="form-label">Médico</label>
                                    <select name="medico_filter" class="form-select">
                                        <option value="">Todos los médicos</option>
                                        <?php foreach ($medicos as $medico): ?>
                                            <option value="<?php echo $medico['id_usuario']; ?>"
                                                    <?php echo ($_GET['medico_filter'] ?? '') == $medico['id_usuario'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div style="min-width: 130px;">
                                    <label class="form-label">Estado</label>
                                    <select name="estado_filter" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="agendada" <?php echo ($_GET['estado_filter'] ?? '') == 'agendada' ? 'selected' : ''; ?>>Agendada</option>
                                        <option value="confirmada" <?php echo ($_GET['estado_filter'] ?? '') == 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                        <option value="en_curso" <?php echo ($_GET['estado_filter'] ?? '') == 'en_curso' ? 'selected' : ''; ?>>En Curso</option>
                                        <option value="completada" <?php echo ($_GET['estado_filter'] ?? '') == 'completada' ? 'selected' : ''; ?>>Completada</option>
                                        <option value="cancelada" <?php echo ($_GET['estado_filter'] ?? '') == 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                
                                <a href="index.php?action=citas" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de citas -->
                    <?php if (!empty($citas)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Médico</th>
                                        <th>Especialidad</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citas as $cita): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cita['nombre_paciente']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($cita['cedula']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo htmlspecialchars($cita['nombre_medico']); ?></td>
                                            <td><?php echo htmlspecialchars($cita['nombre_especialidad']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($cita['hora_cita'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $cita['tipo_cita'] === 'virtual' ? 'info' : 'success'; ?>">
                                                    <?php echo ucfirst($cita['tipo_cita']); ?>
                                                </span>
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?action=citas/ver&id=<?php echo $cita['id_cita']; ?>" 
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if (in_array($cita['estado_cita'], ['agendada', 'confirmada'])): ?>
                                                        <a href="index.php?action=citas/editar&id=<?php echo $cita['id_cita']; ?>" 
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($cita['estado_cita'] === 'confirmada'): ?>
                                                        <a href="index.php?action=consultas/atender&cita_id=<?php echo $cita['id_cita']; ?>" 
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Atender">
                                                            <i class="fas fa-user-md"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- NUEVO BOTÓN ELIMINAR -->
                                                    <?php if (in_array($cita['estado_cita'], ['agendada', 'confirmada'])): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="eliminarCita(<?php echo $cita['id_cita']; ?>, '<?php echo addslashes($cita['nombre_paciente']); ?>')"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron citas médicas</h5>
                            <p class="text-muted">No hay citas que coincidan con los criterios de búsqueda.</p>
                            <a href="index.php?action=citas/crear" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Agendar Primera Cita
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function eliminarCita(id, nombrePaciente) {
    if (confirm(`¿Está seguro que desea eliminar la cita de ${nombrePaciente}?\n\nEsta acción no se puede deshacer.`)) {
        fetch('index.php?action=citas/eliminar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al eliminar la cita');
            console.error(error);
        });
    }
}
</script>
