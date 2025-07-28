<?php include 'views/layouts/header.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-calendar-alt"></i> Horarios de <?= htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']) ?></h1>
        <div class="header-actions">
            <a href="index.php?action=doctores" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="content-body">
        <!-- Calendario Semanal para Asignar Horarios -->
        <div class="schedule-calendar-container">
            <div class="schedule-header">
                <h2 class="schedule-title"><i class="fas fa-clock"></i> Asignar Horarios Semanales</h2>
                <div class="schedule-controls">
                    <label for="sucursalSelect">Sucursal:</label>
                    <select id="sucursalSelect" class="form-control">
                        <?php foreach ($sucursales as $sucursal): ?>
                        <option value="<?= $sucursal['id_sucursal'] ?>"><?= htmlspecialchars($sucursal['nombre_sucursal']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="week-schedule-grid">
                <div class="time-column">
                    <div class="time-header">Hora</div>
                    <?php for ($hour = 7; $hour <= 20; $hour++): ?>
                    <div class="time-slot"><?= sprintf('%02d:00', $hour) ?></div>
                    <?php endfor; ?>
                </div>
                
                <div class="days-container" id="scheduleGrid">
                    <?php 
                    $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    for ($day = 1; $day <= 7; $day++): 
                    ?>
                    <div class="day-column" data-day="<?= $day ?>">
                        <div class="day-header">
                            <div class="day-name"><?= $days[$day-1] ?></div>
                        </div>
                        
                        <?php for ($hour = 7; $hour <= 20; $hour++): ?>
                        <div class="schedule-slot" 
                             data-day="<?= $day ?>" 
                             data-hour="<?= $hour ?>"
                             data-doctor-id="<?= $doctor['id_usuario'] ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Lista de Horarios Actuales -->
        <div class="current-schedules">
            <h3><i class="fas fa-list"></i> Horarios Actuales</h3>
            <div class="schedules-grid">
                <?php foreach ($horarios as $horario): ?>
                <div class="schedule-item" data-schedule-id="<?= $horario['id_horario'] ?>">
                    <div class="schedule-info">
                        <strong><?= $horario['nombre_dia'] ?></strong>
                        <span><?= date('H:i', strtotime($horario['hora_inicio'])) ?> - <?= date('H:i', strtotime($horario['hora_fin'])) ?></span>
                        <small><?= htmlspecialchars($horario['nombre_sucursal']) ?></small>
                    </div>
                    <button class="btn btn-sm btn-danger delete-schedule" data-id="<?= $horario['id_horario'] ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal para Crear/Editar Horario -->
<div id="scheduleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-clock"></i> <span id="modalTitle">Asignar Horario</span></h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="scheduleForm">
                <input type="hidden" id="doctorId" value="<?= $doctor['id_usuario'] ?>">
                <input type="hidden" id="diaSemana">
                
                <div class="form-group">
                    <label for="sucursal">Sucursal</label>
                    <select id="sucursal" class="form-control" required>
                        <?php foreach ($sucursales as $sucursal): ?>
                        <option value="<?= $sucursal['id_sucursal'] ?>"><?= htmlspecialchars($sucursal['nombre_sucursal']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="horaInicio">Hora Inicio</label>
                        <input type="time" id="horaInicio" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="horaFin">Hora Fin</label>
                        <input type="time" id="horaFin" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="button" class="btn btn-cancel" id="cancelBtn">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.schedule-calendar-container {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.schedule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.schedule-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.week-schedule-grid {
    display: flex;
    gap: 2px;
    overflow-x: auto;
}

.time-column {
    min-width: 80px;
    background: #f8f9fa;
}

.time-header {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    background: #343a40;
    color: white;
}

.time-slot {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #dee2e6;
    font-size: 12px;
    background: #f8f9fa;
}

.days-container {
    flex: 1;
    display: flex;
    gap: 2px;
}

.day-column {
    flex: 1;
    min-width: 120px;
}

.day-header {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #007bff;
    color: white;
    font-weight: bold;
}

.schedule-slot {
    height: 40px;
    border: 1px solid #dee2e6;
    cursor: pointer;
    transition: background-color 0.2s;
    position: relative;
}

.schedule-slot:hover {
    background-color: #e9ecef;
}

.schedule-slot.has-schedule {
    background-color: #d4edda;
    border-color: #28a745;
}

.schedule-slot.has-schedule::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #28a745;
    font-weight: bold;
}

.current-schedules {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.schedules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.schedule-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #f8f9fa;
}

.schedule-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
}

.modal-body {
    padding: 20px;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('scheduleModal');
    const modalTitle = document.getElementById('modalTitle');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const scheduleForm = document.getElementById('scheduleForm');
    const scheduleSlots = document.querySelectorAll('.schedule-slot');
    const deleteButtons = document.querySelectorAll('.delete-schedule');
    
    const days = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    
    // Cargar horarios existentes
    loadExistingSchedules();
    
    // Event listeners para slots del calendario
    scheduleSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            if (!this.classList.contains('has-schedule')) {
                openScheduleModal(this);
            }
        });
    });
    
    // Event listeners para eliminar horarios
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            deleteSchedule(this.getAttribute('data-id'));
        });
    });
    
    // Modal events
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Submit form
    scheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        saveSchedule();
    });
    
    function openScheduleModal(slot) {
        const day = slot.getAttribute('data-day');
        const hour = slot.getAttribute('data-hour');
        
        modalTitle.textContent = `Asignar Horario - ${days[day]}`;
        document.getElementById('diaSemana').value = day;
        document.getElementById('horaInicio').value = hour + ':00';
        document.getElementById('horaFin').value = (parseInt(hour) + 1) + ':00';
        
        modal.style.display = 'block';
    }
    
    function closeModal() {
        modal.style.display = 'none';
        scheduleForm.reset();
    }
    
    function saveSchedule() {
        const formData = new FormData();
        formData.append('id_medico', document.getElementById('doctorId').value);
        formData.append('id_sucursal', document.getElementById('sucursal').value);
        formData.append('dia_semana', document.getElementById('diaSemana').value);
        formData.append('hora_inicio', document.getElementById('horaInicio').value);
        formData.append('hora_fin', document.getElementById('horaFin').value);
        
        fetch('index.php?action=doctores/guardar-horario', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert('Error al guardar el horario. Posible conflicto de horarios.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el horario');
        });
    }
    
    function deleteSchedule(scheduleId) {
        if (confirm('¿Está seguro de eliminar este horario?')) {
            const formData = new FormData();
            formData.append('id_horario', scheduleId);
            
            fetch('index.php?action=doctores/eliminar-horario', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al eliminar el horario');
                }
            });
        }
    }
    
    function loadExistingSchedules() {
        // Marcar slots que ya tienen horarios asignados
        <?php foreach ($horarios as $horario): ?>
        markScheduledSlots(<?= $horario['dia_semana'] ?>, '<?= $horario['hora_inicio'] ?>', '<?= $horario['hora_fin'] ?>');
        <?php endforeach; ?>
    }
    
    function markScheduledSlots(day, startTime, endTime) {
        const startHour = parseInt(startTime.split(':')[0]);
        const endHour = parseInt(endTime.split(':')[0]);
        
        for (let hour = startHour; hour < endHour; hour++) {
            const slot = document.querySelector(`[data-day="${day}"][data-hour="${hour}"]`);
            if (slot) {
                slot.classList.add('has-schedule');
            }
        }
    }
});
</script>

