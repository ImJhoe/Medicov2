class DoctorScheduleManager {
    constructor() {
        this.selectedSlots = [];
        this.currentDoctor = null;
        this.currentSucursal = null;
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadScheduleData();
    }
    
    bindEvents() {
        // Selección múltiple de slots
        document.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('schedule-slot') && !e.target.classList.contains('has-schedule')) {
                this.startSelection(e);
            }
        });
        
        document.addEventListener('mouseover', (e) => {
            if (this.isSelecting && e.target.classList.contains('schedule-slot')) {
                this.updateSelection(e.target);
            }
        });
        
        document.addEventListener('mouseup', () => {
            if (this.isSelecting) {
                this.endSelection();
            }
        });
        
        // Cambio de sucursal
        document.getElementById('sucursalSelect')?.addEventListener('change', (e) => {
            this.currentSucursal = e.target.value;
            this.refreshScheduleDisplay();
        });
    }
    
    startSelection(e) {
        this.isSelecting = true;
        this.selectionStart = e.target;
        this.clearSelection();
        this.selectSlot(e.target);
    }
    
    updateSelection(slot) {
        // Lógica para selección de rango
        if (this.selectionStart && slot !== this.selectionStart) {
            this.selectRange(this.selectionStart, slot);
        }
    }
    
    endSelection() {
        this.isSelecting = false;
        if (this.selectedSlots.length > 0) {
            this.openBulkScheduleModal();
        }
    }
    
    selectSlot(slot) {
        if (!slot.classList.contains('has-schedule')) {
            slot.classList.add('selected');
            this.selectedSlots.push(slot);
        }
    }
    
    selectRange(start, end) {
        this.clearSelection();
        
        const startDay = parseInt(start.getAttribute('data-day'));
        const startHour = parseInt(start.getAttribute('data-hour'));
        const endDay = parseInt(end.getAttribute('data-day'));
        const endHour = parseInt(end.getAttribute('data-hour'));
        
        // Seleccionar solo en el mismo día
        if (startDay === endDay) {
            const minHour = Math.min(startHour, endHour);
            const maxHour = Math.max(startHour, endHour);
            
            for (let hour = minHour; hour <= maxHour; hour++) {
                const slot = document.querySelector(`[data-day="${startDay}"][data-hour="${hour}"]`);
                if (slot && !slot.classList.contains('has-schedule')) {
                    this.selectSlot(slot);
                }
            }
        }
    }
    
    clearSelection() {
        this.selectedSlots.forEach(slot => {
            slot.classList.remove('selected');
        });
        this.selectedSlots = [];
    }
    
    openBulkScheduleModal() {
        if (this.selectedSlots.length === 0) return;
        
        const firstSlot = this.selectedSlots[0];
        const lastSlot = this.selectedSlots[this.selectedSlots.length - 1];
        
        const day = firstSlot.getAttribute('data-day');
        const startHour = firstSlot.getAttribute('data-hour');
        const endHour = parseInt(lastSlot.getAttribute('data-hour')) + 1;
        
        // Abrir modal con datos prellenados
        this.openScheduleModal(day, startHour, endHour);
    }
    
    openScheduleModal(day, startHour, endHour) {
        const modal = document.getElementById('scheduleModal');
        const days = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        
        document.getElementById('modalTitle').textContent = `Asignar Horario - ${days[day]}`;
        document.getElementById('diaSemana').value = day;
        document.getElementById('horaInicio').value = startHour.toString().padStart(2, '0') + ':00';
        document.getElementById('horaFin').value = endHour.toString().padStart(2, '0') + ':00';
        
        modal.style.display = 'block';
    }
    
    async saveSchedule(formData) {
        try {
            const response = await fetch('index.php?action=doctores/guardar-horario', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Horario guardado exitosamente', 'success');
                this.clearSelection();
                this.refreshScheduleDisplay();
                return true;
            } else {
                this.showNotification('Error al guardar el horario: ' + (result.message || 'Conflicto de horarios'), 'error');
                return false;
            }
        } catch (error) {
            this.showNotification('Error de conexión', 'error');
            return false;
        }
    }
    
    async deleteSchedule(scheduleId) {
        if (!confirm('¿Está seguro de eliminar este horario?')) return;
        
        try {
            const formData = new FormData();
            formData.append('id_horario', scheduleId);
            
            const response = await fetch('index.php?action=doctores/eliminar-horario', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification('Horario eliminado exitosamente', 'success');
                this.refreshScheduleDisplay();
            } else {
                this.showNotification('Error al eliminar el horario', 'error');
            }
        } catch (error) {
            this.showNotification('Error de conexión', 'error');
        }
    }
    
    refreshScheduleDisplay() {
        // Recargar la página o hacer una petición AJAX para actualizar el calendario
        location.reload();
    }
    
    showNotification(message, type = 'info') {
        // Crear sistema de notificaciones
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;
        
        switch (type) {
            case 'success':
                notification.style.background = '#28a745';
                break;
            case 'error':
                notification.style.background = '#dc3545';
                break;
            default:
                notification.style.background = '#007bff';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    loadScheduleData() {
        // Cargar datos existentes y marcar slots ocupados
        this.currentDoctor = document.getElementById('doctorId')?.value;
        this.currentSucursal = document.getElementById('sucursalSelect')?.value;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('scheduleGrid')) {
        window.scheduleManager = new DoctorScheduleManager();
    }
});

// Estilos para las animaciones
const style = document.createElement('style');
style.textContent = `
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.schedule-slot.selected {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white;
    border-color: #004085 !important;
    transform: scale(0.95);
}

.notification {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-left: 4px solid rgba(255,255,255,0.3);
}
`;
document.head.appendChild(style);
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = DoctorScheduleManager;
}
