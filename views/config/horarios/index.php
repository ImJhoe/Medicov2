<?php
// views/config/horarios/index.php
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
                            <i class="fas fa-clock me-2"></i>
                            Gestión de Horarios Médicos
                        </h5>
                        <a href="index.php?action=config/horarios/create" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Configurar Horarios
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros de búsqueda -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" class="d-flex gap-2 align-items-end">
                                <input type="hidden" name="action" value="config/horarios">
                                
                                <div class="flex-grow-1">
                                    <label class="form-label">Buscar</label>
                                    <input type="text" 
                                           name="search" 
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                           placeholder="Buscar por médico o sucursal..."
                                           class="form-control">
                                </div>
                                
                                <div style="min-width: 200px;">
                                    <label class="form-label">Médico</label>
                                    <select name="medico_filter" class="form-select">
                                        <option value="">Todos los médicos</option>
                                        <?php foreach ($medicos as $medico): ?>
                                            <option value="<?php echo $medico['id_usuario']; ?>"
                                                    <?php echo ($medico_filter == $medico['id_usuario']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div style="min-width: 200px;">
                                    <label class="form-label">Sucursal</label>
                                    <select name="sucursal_filter" class="form-select">
                                        <option value="">Todas las sucursales</option>
                                        <?php foreach ($sucursales as $sucursal): ?>
                                            <option value="<?php echo $sucursal['id_sucursal']; ?>"
                                                    <?php echo ($sucursal_filter == $sucursal['id_sucursal']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sucursal['nombre_sucursal']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                
                                <a href="index.php?action=config/horarios" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de horarios -->
                    <?php if (!empty($horarios)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Médico</th>
                                        <th>Sucursal</th>
                                        <th>Día</th>
                                        <th>Hora Inicio</th>
                                        <th>Hora Fin</th>
                                        <th>Estado</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horarios as $horario): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($horario['nombre_medico']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($horario['nombre_sucursal']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $horario['nombre_dia']; ?></span>
                                            </td>
                                            <td><?php echo date('H:i', strtotime($horario['hora_inicio'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($horario['hora_fin'])); ?></td>
                                            <td>
                                                <?php if ($horario['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?action=config/horarios/edit&id=<?php echo $horario['id_horario']; ?>" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="eliminarHorario(<?php echo $horario['id_horario']; ?>, '<?php echo addslashes($horario['nombre_medico'] . ' - ' . $horario['nombre_dia']); ?>')"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron horarios médicos</h5>
                            <p class="text-muted">No hay horarios configurados que coincidan con los criterios de búsqueda.</p>
                            <a href="index.php?action=config/horarios/create" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Configurar Primer Horario
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarHorario(id, nombre) {
    if (confirm(`¿Está seguro que desea eliminar el horario de ${nombre}?`)) {
        fetch('index.php?action=config/horarios/delete', {
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
            alert('Error al eliminar el horario');
            console.error(error);
        });
    }
}
</script>
