<?php include 'views/includes/header.php'; ?>
<?php include 'views/includes/navbar.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-calendar-alt"></i> Calendario Dinámico</h1>
        <div class="header-actions">
            <button class="btn btn-primary" id="nuevaReservaBtn">
                <i class="fas fa-plus"></i>
                Nueva Reserva
            </button>
        </div>
    </div>

    <div class="content-body">
        <!-- Calendario semanal dinámico -->
        <div class="calendar-week-container">
            <div class="calendar-week-header">
                <h2 class="calendar-week-title"><i class="fas fa-calendar-week"></i> Calendario Semanal</h2>
                <div class="calendar-week-nav">
                    <button id="prevWeek">
                        <i class="fas fa-chevron-left"></i>
                        <span>Semana anterior</span>
                    </button>
                    <div class="current-week" id="currentWeek">13 - 19 Mayo, 2025</div>
                    <button id="nextWeek">
                        <span>Próxima semana</span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="week-grid">
                <div class="time-column">
                    <div class="time-header">Hora</div>
                    <div class="time-slot">8:00</div>
                    <div class="time-slot">9:00</div>
                    <div class="time-slot">10:00</div>
                    <div class="time-slot">11:00</div>
                    <div class="time-slot">12:00</div>
                    <div class="time-slot">13:00</div>
                    <div class="time-slot">14:00</div>
                    <div class="time-slot">15:00</div>
                    <div class="time-slot">16:00</div>
                    <div class="time-slot">17:00</div>
                </div>
                
                <div class="days-container" id="calendarDaysContainer">
                    <!-- Los días se generarán con JavaScript -->
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="calendar-filters">
            <div class="filter-group">
                <label for="filtroMedico">Médico:</label>
                <select id="filtroMedico" class="form-control">
                    <option value="">Todos los médicos</option>
                    <?php foreach ($medicos as $medico): ?>
                    <option value="<?= $medico['id_usuario'] ?>">
                        <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filtroEspecialidad">Especialidad:</label>
                <select id="filtroEspecialidad" class="form-control">
                    <option value="">Todas las especialidades</option>
                    <?php foreach ($especialidades as $especialidad): ?>
                    <option value="<?= $especialidad['id_especialidad'] ?>">
                        <?= htmlspecialchars($especialidad['nombre_especialidad']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filtroSucursal">Sucursal:</label>
                <select id="filtroSucursal" class="form-control">
                    <option value="">Todas las sucursales</option>
                    <?php foreach ($sucursales as $sucursal): ?>
                    <option value="<?= $sucursal['id_sucursal'] ?>">
                        <?= htmlspecialchars($sucursal['nombre_sucursal']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button id="aplicarFiltros" class="btn btn-secondary">
                <i class="fas fa-filter"></i>
                Aplicar Filtros
            </button>
        </div>
    </div>
</main>

<!-- Modal para eventos -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-calendar-plus"></i> <span id="modalTitle">Nueva Cita</span></h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="eventForm">
                <input type="hidden" id="eventId">
                <input type="hidden" id="eventDate">
                <input type="hidden" id="eventTime">
                
                <div class="form-group">
                    <label for="eventTitle">Título de la cita</label>
                    <input type="text" class="form-control" id="eventTitle" required>
                </div>
                
                <div class="form-group">
                    <label for="pacienteCedula">Cédula del paciente</label>
                    <input type="text" class="form-control" id="pacienteCedula" required>
                </div>
                
                <div class="form-group">
                    <label for="eventoMedico">Médico</label>
                    <select class="form-control" id="eventoMedico" required>
                        <option value="">Seleccionar médico</option>
                        <?php foreach ($medicos as $medico): ?>
                        <option value="<?= $medico['id_usuario'] ?>">
                            <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellido']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="eventoEspecialidad">Especialidad</label>
                    <select class="form-control" id="eventoEspecialidad" required>
                        <option value="">Seleccionar especialidad</option>
                        <?php foreach ($especialidades as $especialidad): ?>
                        <option value="<?= $especialidad['id_especialidad'] ?>">
                            <?= htmlspecialchars($especialidad['nombre_especialidad']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="eventoSucursal">Sucursal</label>
                    <select class="form-control" id="eventoSucursal" required>
                        <option value="">Seleccionar sucursal</option>
                        <?php foreach ($sucursales as $sucursal): ?>
                        <option value="<?= $sucursal['id_sucursal'] ?>">
                            <?= htmlspecialchars($sucursal['nombre_sucursal']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="eventDescription">Motivo de consulta</label>
                    <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                </div>
                
                <div class="form-footer">
                    <button type="button" class="btn btn-danger" id="deleteEventBtn">
                        <i class="fas fa-trash-alt"></i>
                        Eliminar
                    </button>
                    <div>
                        <button type="button" class="btn btn-cancel" id="cancelBtn">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para evitar conflictos */
.main-content {
    padding: 20px;
    background: #f8f9fa;
    min-height: calc(100vh - 60px);
    margin-top: 60px;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.content-header h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.content-body {
    max-width: 100%;
    overflow-x: auto;
}

.calendar-week-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    border: 1px solid #e9ecef;
}

.calendar-week-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f3f4;
}

.calendar-week-title {
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.calendar-week-nav {
    display: flex;
    align-items: center;
    gap: 20px;
}

.calendar-week-nav button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.calendar-week-nav button:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.current-week {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.1rem;
    min-width: 200px;
    text-align: center;
}

.week-grid {
    display: flex;
    gap: 2px;
    overflow-x: auto;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.time-column {
    min-width: 90px;
    background: #f8f9fa;
    border-right: 2px solid #dee2e6;
}

.time-header {
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.time-slot {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #dee2e6;
    font-size: 13px;
    font-weight: 600;
    background: #f8f9fa;
    color: #6c757d;
}

.days-container {
    flex: 1;
    display: flex;
    gap: 2px;
}

.day-column {
    flex: 1;
    min-width: 150px;
}

.day-header {
    height: 65px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 13px;
}

.day-header.today {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.day-name {
    font-size: 12px;
    margin-bottom: 2px;
}

.day-date {
    font-size: 16px;
    font-weight: 800;
}

.calendar-slot {
    height: 60px;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 4px;
}

.calendar-slot:hover {
    background-color: #e3f2fd;
    border-color: #2196f3;
    transform: scale(1.02);
}

.calendar-slot.has-event {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-color: #28a745;
    cursor: pointer;
}

.calendar-slot.has-event:hover {
    background: linear-gradient(135deg, #c3e6cb 0%, #b8dabd 100%);
}

.event-title {
    font-size: 11px;
    font-weight: 600;
    color: #2c3e50;
    text-align: center;
    line-height: 1.2;
    margin-bottom: 2px;
}

.event-time {
    font-size: 10px;
    color: #6c757d;
    font-weight: 500;
}

.calendar-filters {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 180px;
}

.filter-group label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
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
    margin: 2% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-body {
    padding: 20px;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #6c757d;
    transition: color 0.3s;
}

.close:hover {
    color: #dc3545;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #495057;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #545b62;
}

/* Responsive */
@media (max-width: 768px) {
    .content-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .calendar-week-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .calendar-week-nav {
        justify-content: space-between;
    }
    
    .week-grid {
        flex-direction: column;
    }
    
    .days-container {
        flex-direction: column;
    }
    
    .calendar-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .main-content {
        padding: 10px;
        margin-top: 60px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración del calendario semanal
    const daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    let currentDate = new Date();
    let currentWeekStart = getMonday(currentDate);
    
    // Elementos del DOM
    const calendarDaysContainer = document.getElementById('calendarDaysContainer');
    const currentWeekEl = document.getElementById('currentWeek');
    const prevWeekBtn = document.getElementById('prevWeek');
    const nextWeekBtn = document.getElementById('nextWeek');
    
    // Modal
    const modal = document.getElementById('eventModal');
    const modalTitle = document.getElementById('modalTitle');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const deleteBtn = document.getElementById('deleteEventBtn');
    const eventForm = document.getElementById('eventForm');
    const nuevaReservaBtn = document.getElementById('nuevaReservaBtn');
    
    // Inicializar calendario
    renderWeekCalendar();
    
    // Event listeners
    prevWeekBtn.addEventListener('click', function() {
        currentWeekStart.setDate(currentWeekStart.getDate() - 7);
        renderWeekCalendar();
    });
    
    nextWeekBtn.addEventListener('click', function() {
        currentWeekStart.setDate(currentWeekStart.getDate() + 7);
        renderWeekCalendar();
    });
    
    nuevaReservaBtn.addEventListener('click', function() {
        openEventModal(null);
    });
    
    // Modal events
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Eliminar evento
    deleteBtn.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que deseas eliminar esta cita?')) {
            const eventId = document.getElementById('eventId').value;
            console.log('Eliminar cita ID:', eventId);
            closeModal();
        }
    });
    
    // Enviar formulario
    eventForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id: document.getElementById('eventId').value,
            titulo: document.getElementById('eventTitle').value,
            cedula: document.getElementById('pacienteCedula').value,
            medico: document.getElementById('eventoMedico').value,
            especialidad: document.getElementById('eventoEspecialidad').value,
            sucursal: document.getElementById('eventoSucursal').value,
            descripcion: document.getElementById('eventDescription').value,
            fecha: document.getElementById('eventDate').value,
            hora: document.getElementById('eventTime').value
        };
        
        console.log('Guardar cita:', formData);
        alert('Cita guardada exitosamente');
        closeModal();
        renderWeekCalendar();
    });
    
    // Función para renderizar el calendario semanal
    function renderWeekCalendar() {
        const weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(weekEnd.getDate() + 6);
        
        let weekTitle = '';
        if (currentWeekStart.getMonth() === weekEnd.getMonth()) {
            weekTitle = `${currentWeekStart.getDate()} - ${weekEnd.getDate()} ${months[currentWeekStart.getMonth()]}, ${currentWeekStart.getFullYear()}`;
        } else {
            weekTitle = `${currentWeekStart.getDate()} ${months[currentWeekStart.getMonth()]} - ${weekEnd.getDate()} ${months[weekEnd.getMonth()]}, ${currentWeekStart.getFullYear()}`;
        }
        currentWeekEl.textContent = weekTitle;
        
        calendarDaysContainer.innerHTML = '';
        
        for (let i = 0; i < 7; i++) {
            const dayDate = new Date(currentWeekStart);
            dayDate.setDate(dayDate.getDate() + i);
            
            const dayCol = document.createElement('div');
            dayCol.className = 'day-column';
            
            const dayHeader = document.createElement('div');
            dayHeader.className = 'day-header';
            if (isToday(dayDate)) {
                dayHeader.classList.add('today');
            }
            
            dayHeader.innerHTML = `
                <div class="day-name">${daysOfWeek[i]}</div>
                <div class="day-date">${dayDate.getDate()}</div>
            `;
            dayCol.appendChild(dayHeader);
            
            for (let hour = 8; hour <= 17; hour++) {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'calendar-slot';
                const slotId = `slot-${i}-${hour}`;
                timeSlot.setAttribute('data-slot-id', slotId);
                timeSlot.setAttribute('data-day', i);
                timeSlot.setAttribute('data-hour', hour);
                timeSlot.setAttribute('data-date', formatDate(dayDate));
                
                if (Math.random() > 0.85) {
                    const fakeEventTitle = getRandomEventTitle();
                    const fakePatient = getRandomPatient();
                    
                    timeSlot.classList.add('has-event');
                    timeSlot.innerHTML = `
                        <div class="event-title">${fakeEventTitle}</div>
                        <div class="event-time">${hour}:00</div>
                    `;
                    
                    timeSlot.setAttribute('data-event-title', fakeEventTitle);
                    timeSlot.setAttribute('data-event-patient', fakePatient);
                    timeSlot.setAttribute('data-event-description', 'Consulta médica programada');
                }
                
                timeSlot.addEventListener('click', function() {
                    openEventModal(this);
                });
                
                dayCol.appendChild(timeSlot);
            }
            
            calendarDaysContainer.appendChild(dayCol);
        }
    }
    
    function openEventModal(slotElement) {
        if (slotElement) {
            const dayIndex = parseInt(slotElement.getAttribute('data-day'));
            const hourSlot = parseInt(slotElement.getAttribute('data-hour'));
            const slotId = slotElement.getAttribute('data-slot-id');
            const eventDate = slotElement.getAttribute('data-date');
            
            document.getElementById('eventId').value = slotId;
            document.getElementById('eventDate').value = eventDate;
            document.getElementById('eventTime').value = `${hourSlot}:00`;
            
            if (slotElement.classList.contains('has-event')) {
                modalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Cita';
                document.getElementById('eventTitle').value = slotElement.getAttribute('data-event-title') || '';
                document.getElementById('pacienteCedula').value = slotElement.getAttribute('data-event-patient') || '';
                document.getElementById('eventDescription').value = slotElement.getAttribute('data-event-description') || '';
                deleteBtn.style.display = 'block';
            } else {
                modalTitle.innerHTML = '<i class="fas fa-calendar-plus"></i> Nueva Cita';
                document.getElementById('eventTitle').value = '';
                document.getElementById('pacienteCedula').value = '';
                document.getElementById('eventDescription').value = '';
                document.getElementById('eventoMedico').value = '';
                document.getElementById('eventoEspecialidad').value = '';
                document.getElementById('eventoSucursal').value = '';
                deleteBtn.style.display = 'none';
            }
        } else {
            modalTitle.innerHTML = '<i class="fas fa-calendar-plus"></i> Nueva Cita';
            document.getElementById('eventId').value = '';
            document.getElementById('eventDate').value = formatDate(new Date());
            document.getElementById('eventTime').value = '09:00';
            document.getElementById('eventTitle').value = '';
            document.getElementById('pacienteCedula').value = '';
            document.getElementById('eventDescription').value = '';
            document.getElementById('eventoMedico').value = '';
            document.getElementById('eventoEspecialidad').value = '';
            document.getElementById('eventoSucursal').value = '';
            deleteBtn.style.display = 'none';
        }
        
        modal.style.display = 'block';
    }
    
    function closeModal() {
        modal.style.display = 'none';
        eventForm.reset();
    }
    
    function getMonday(d) {
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    }
    
    function isToday(date) {
        const today = new Date();
        return date.getDate() === today.getDate() && 
               date.getMonth() === today.getMonth() && 
               date.getFullYear() === today.getFullYear();
    }
    
    function formatDate(date) {
        return `${date.getFullYear()}-${padZero(date.getMonth() + 1)}-${padZero(date.getDate())}`;
    }
    
    function padZero(num) {
        return num.toString().padStart(2, '0');
    }
    
    function getRandomEventTitle() {
        const events = [
            'Consulta Medicina General',
            'Revisión Cardiológica',
            'Consulta Pediátrica',
            'Examen Dermatológico',
            'Control Ginecológico',
            'Revisión Traumatológica',
            'Consulta Psicológica',
            'Evaluación Nutricional',
            'Examen Oftalmológico',
            'Consulta Odontológica'
        ];
        return events[Math.floor(Math.random() * events.length)];
    }
    
    function getRandomPatient() {
        const patients = [
            '1712345678',
            '1798765432',
            '1723456789',
            '1787654321',
            '1734567890',
            '1776543210',
            '1745678901',
            '1765432109 ',
            '1756789012',
           '1754321098'
       ];
       return patients[Math.floor(Math.random() * patients.length)];
   }
   
   // Filtros
   document.getElementById('aplicarFiltros').addEventListener('click', function() {
       const filtroMedico = document.getElementById('filtroMedico').value;
       const filtroEspecialidad = document.getElementById('filtroEspecialidad').value;
       const filtroSucursal = document.getElementById('filtroSucursal').value;
       
       console.log('Aplicar filtros:', {
           medico: filtroMedico,
           especialidad: filtroEspecialidad,
           sucursal: filtroSucursal
       });
       
       renderWeekCalendar();
   });
   
   // Búsqueda de pacientes en tiempo real
   document.getElementById('pacienteCedula').addEventListener('input', function() {
       const cedula = this.value;
       if (cedula.length >= 10) {
           console.log('Buscar paciente con cédula:', cedula);
       }
   });
});
</script>