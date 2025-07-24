<?php
// views/notificaciones/index.php
include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Mis Notificaciones
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="marcarTodasLeidas()">
                            <i class="fas fa-check-double me-1"></i>
                            Marcar todas como leídas
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($notificaciones)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notificaciones as $notificacion): ?>
                                <div class="list-group-item <?php echo $notificacion['leida'] ? '' : 'list-group-item-info'; ?> notificacion-item" 
                                     data-id="<?php echo $notificacion['id_notificacion']; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php
                                            $iconos = [
                                                'cita_agendada' => 'fas fa-calendar-plus text-success',
                                                'cita_recordatorio' => 'fas fa-clock text-warning',
                                                'cita_cancelada' => 'fas fa-calendar-times text-danger',
                                                'sistema' => 'fas fa-info-circle text-info'
                                            ];
                                            $icono = $iconos[$notificacion['tipo_notificacion']] ?? 'fas fa-bell text-secondary';
                                            ?>
                                            <i class="<?php echo $icono; ?> me-2"></i>
                                            <?php echo htmlspecialchars($notificacion['titulo']); ?>
                                            <?php if (!$notificacion['leida']): ?>
                                                <span class="badge bg-primary ms-2">Nuevo</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($notificacion['mensaje'])); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Tipo: <?php echo ucfirst(str_replace('_', ' ', $notificacion['tipo_notificacion'])); ?>
                                        </small>
                                        
                                        <div class="btn-group btn-group-sm">
                                          <?php if (!$notificacion['leida']): ?>
                                               <button class="btn btn-outline-primary" onclick="marcarLeida(<?php echo $notificacion['id_notificacion']; ?>)">
                                                   <i class="fas fa-check"></i> Marcar como leída
                                               </button>
                                           <?php endif; ?>
                                           
                                           <?php if ($notificacion['id_referencia']): ?>
                                               <a href="index.php?action=citas/ver&id=<?php echo $notificacion['id_referencia']; ?>" 
                                                  class="btn btn-outline-secondary">
                                                   <i class="fas fa-eye"></i> Ver Cita
                                               </a>
                                           <?php endif; ?>
                                       </div>
                                   </div>
                               </div>
                           <?php endforeach; ?>
                       </div>
                       
                       <!-- Paginación si es necesaria -->
                       <div class="text-center mt-3">
                           <button class="btn btn-outline-primary" onclick="cargarMasNotificaciones()">
                               <i class="fas fa-chevron-down me-2"></i>
                               Cargar más notificaciones
                           </button>
                       </div>
                       
                   <?php else: ?>
                       <div class="text-center py-5">
                           <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                           <h5 class="text-muted">No hay notificaciones</h5>
                           <p class="text-muted">Cuando reciba notificaciones sobre sus citas, aparecerán aquí.</p>
                       </div>
                   <?php endif; ?>
               </div>
           </div>
       </div>
   </div>
</div>

<script>
function marcarLeida(notificacionId) {
   fetch('index.php?action=notificaciones/marcar-leida', {
       method: 'POST',
       headers: {
           'Content-Type': 'application/x-www-form-urlencoded',
       },
       body: `id=${notificacionId}`
   })
   .then(response => response.json())
   .then(data => {
       if (data.success) {
           // Actualizar visualmente la notificación
           const item = document.querySelector(`[data-id="${notificacionId}"]`);
           item.classList.remove('list-group-item-info');
           
           // Remover badge "Nuevo"
           const badge = item.querySelector('.badge');
           if (badge) badge.remove();
           
           // Remover botón "Marcar como leída"
           const btn = item.querySelector('.btn-outline-primary');
           if (btn) btn.remove();
           
       } else {
           alert('Error al marcar la notificación como leída');
       }
   })
   .catch(error => {
       console.error('Error:', error);
       alert('Error al procesar la solicitud');
   });
}

function marcarTodasLeidas() {
   if (confirm('¿Está seguro que desea marcar todas las notificaciones como leídas?')) {
       fetch('index.php?action=notificaciones/marcar-todas-leidas', {
           method: 'POST'
       })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               location.reload();
           } else {
               alert('Error al marcar las notificaciones como leídas');
           }
       })
       .catch(error => {
           console.error('Error:', error);
           alert('Error al procesar la solicitud');
       });
   }
}

function cargarMasNotificaciones() {
   // Implementar paginación si es necesario
   alert('Función de cargar más notificaciones pendiente de implementar');
}
</script>
