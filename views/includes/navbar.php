<?php include 'views/includes/menu-helper.php'; ?>

<style>
:root {
    --primary-black: #1a1a1a;
    --secondary-black: #2d2d2d;
    --accent-gray: #404040;
    --light-gray: #f8f9fa;
    --border-gray: #e0e0e0;
    --hover-gray: #f5f5f5;
    --text-dark: #2c2c2c;
    --shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    --shadow-hover: 0 4px 25px rgba(0, 0, 0, 0.15);
    --notification-red: #dc3545;
    --notification-blue: #0d6efd;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    margin: 0;
    padding-left: 280px;
    transition: padding-left 0.3s ease;
}

.elegant-navbar {
    background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
    border-right: 3px solid #ffffff;
    box-shadow: var(--shadow);
    padding: 1rem 0;
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    flex-direction: column;
}

.elegant-navbar::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, transparent 0%, #ffffff 50%, transparent 100%);
}

.navbar-brand {
    transition: all 0.3s ease;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
}

.navbar-brand:hover {
    transform: translateY(-2px);
}

.brand-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 2px;
    text-transform: uppercase;
    position: relative;
}

.brand-text::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 0;
    height: 2px;
    background: #ffffff;
    transition: width 0.3s ease;
}

.navbar-brand:hover .brand-text::after {
    width: 100%;
}

.nav-link.menu-item {
    color: #ffffff !important;
    font-weight: 500;
    padding: 0.875rem 1.5rem !important;
    border-radius: 0;
    transition: all 0.3s ease;
    position: relative;
    margin: 0.125rem 1rem;
    border-radius: 8px;
    display: block;
    width: calc(100% - 2rem);
}

.nav-link.menu-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-link.menu-item:hover::before {
    opacity: 1;
}

.nav-link.menu-item:hover {
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
}

.menu-item-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
}

.menu-text {
    font-size: 0.95rem;
    letter-spacing: 0.5px;
}

.dropdown-arrow {
    font-size: 0.75rem;
    transition: transform 0.3s ease;
}

.dropdown-hover:hover .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-menu {
    background: #ffffff;
    border: none;
    border-radius: 8px;
    box-shadow: var(--shadow-hover);
    padding: 0.5rem 0;
    margin: 0.25rem 1rem;
    min-width: calc(100% - 2rem);
    position: static;
    float: none;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
    }
    to {
        opacity: 1;
        max-height: 300px;
    }
}

.dropdown-item {
    color: var(--text-dark);
    padding: 0.625rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 4px;
    margin: 0.125rem 0.5rem;
    display: flex;
    align-items: center;
    position: relative;
}

.dropdown-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--primary-black);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.dropdown-item:hover {
    background: var(--hover-gray);
    color: var(--primary-black);
    padding-left: 2rem;
}

.dropdown-item:hover::before {
    transform: scaleY(1);
}

.dropdown-divider {
    margin: 0.5rem 1rem;
    border-top: 1px solid var(--border-gray);
}

/* Estilos para la campana de notificaciones */
.notification-section {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin: 0.5rem 0 1rem 0;
    padding-top: 0.5rem;
}

.notification-wrapper {
    position: relative;
}

.notification-trigger {
    color: #ffffff !important;
    font-weight: 500;
    padding: 0.875rem 1.5rem !important;
    transition: all 0.3s ease;
    position: relative;
    margin: 0.125rem 1rem;
    border-radius: 8px;
    display: block;
    width: calc(100% - 2rem);
    text-decoration: none !important;
    background: transparent;
    border: none;
    cursor: pointer;
}

.notification-trigger::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.notification-trigger:hover::before {
    opacity: 1;
}

.notification-trigger:hover {
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
}

.notification-content-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
    z-index: 1;
}

.notification-icon {
    font-size: 1.1rem;
    position: relative;
    width: 20px;
    display: flex;
    justify-content: center;
}

.notification-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: var(--notification-red);
    color: white;
    border-radius: 50%;
    min-width: 16px;
    height: 16px;
    font-size: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    animation: pulse 2s infinite;
    border: 2px solid var(--primary-black);
    padding: 0 2px;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 4px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.notification-text {
    font-size: 0.95rem;
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Dropdown de notificaciones mejorado - ELIMINADO */
/* Estilos del dropdown eliminados ya que ahora es solo un enlace directo */

/* User menu */
.user-menu {
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    padding: 0.75rem 1.5rem !important;
    margin: 1rem;
    width: calc(100% - 2rem);
}

.user-menu:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
}

.user-menu-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 35px;
    height: 35px;
    background: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-black);
    font-size: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-name {
    font-weight: 600;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

.navbar-toggler {
    border: 2px solid #ffffff;
    border-radius: 8px;
    padding: 0.5rem;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Responsive */
@media (max-width: 991.98px) {
    body {
        padding-left: 0;
    }
    
    .elegant-navbar {
        position: relative;
        width: 100%;
        height: auto;
        flex-direction: row;
        padding: 0.5rem 0;
    }
    
    .elegant-navbar::before {
        top: auto;
        bottom: 0;
        right: 0;
        left: 0;
        width: auto;
        height: 2px;
        background: linear-gradient(90deg, transparent 0%, #ffffff 50%, transparent 100%);
    }
    
    .navbar-brand {
        padding: 0;
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .navbar-collapse {
        background: var(--primary-black);
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }
    
    .nav-link.menu-item {
        margin: 0.25rem 0;
        width: 100%;
    }
    
    .notification-item {
        margin: 0.25rem 0;
        width: 100%;
    }
    
    .user-menu {
        margin: 0.5rem 0 0 0;
        width: 100%;
    }
    
    .dropdown-menu,
    .notifications-dropdown {
        position: relative;
        margin: 0.25rem 0;
        min-width: 100%;
    }
    
    .notification-section {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark elegant-navbar">
    <div class="d-flex flex-column w-100 h-100">
        <a class="navbar-brand" href="index.php?action=dashboard">
            <div class="brand-content">
                <span class="brand-text">Bienvenido</span>
            </div>
        </a>

        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse d-lg-flex flex-lg-column flex-lg-grow-1" id="navbarNav">
            <!-- Menús principales -->
            <ul class="navbar-nav flex-lg-column w-100">
                <?php if (isset($menus) && !empty($menus)): ?>
                    <?php foreach ($menus as $menu): ?>
                        <li class="nav-item dropdown dropdown-hover">
                            <a class="nav-link menu-item" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="menu-item-content">
                                    <span class="menu-text"><?php echo $menu['nombre_menu']; ?></span>
                                    <i class="fas fa-chevron-down dropdown-arrow ms-auto"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($menu['submenus'] as $submenu): ?>
                                    <li>
                                        <a class="dropdown-item" href="index.php?action=<?php echo $submenu['uri_submenu']; ?>">
                                            <?php echo $submenu['nombre_submenu']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Menú de usuario -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown dropdown-hover">
                    <a class="nav-link menu-item user-menu" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-menu-content">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name"><?php echo $_SESSION['nombre_completo'] ?? 'Usuario'; ?></span>
                            <i class="fas fa-chevron-down dropdown-arrow ms-auto"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?action=logout">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- Sección de notificaciones -->
            <div class="notification-section">
                <div class="notification-wrapper">
                    <a href="index.php?action=notificaciones" class="notification-trigger">
                        <div class="notification-content-wrapper">
                            <div class="notification-icon">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notification-count" style="display: none;">0</span>
                            </div>
                            <span class="notification-text">Notificaciones</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Manejo de dropdowns con hover
    const dropdowns = document.querySelectorAll('.dropdown-hover');
    const mediaQuery = window.matchMedia('(min-width: 992px)');

    function handleDropdowns() {
        if (mediaQuery.matches) {
            dropdowns.forEach(dropdown => {
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                let timeout;

                dropdown.addEventListener('mouseenter', function () {
                    clearTimeout(timeout);
                    dropdownMenu.classList.add('show');
                });

                dropdown.addEventListener('mouseleave', function () {
                    timeout = setTimeout(() => {
                        dropdownMenu.classList.remove('show');
                    }, 200);
                });

                dropdownMenu.addEventListener('mouseenter', function () {
                    clearTimeout(timeout);
                });

                dropdownMenu.addEventListener('mouseleave', function () {
                    timeout = setTimeout(() => {
                        dropdownMenu.classList.remove('show');
                    }, 200);
                });
            });
        }
    }

    handleDropdowns();
    mediaQuery.addListener(handleDropdowns);

    // Inicializar notificaciones
    inicializarNotificaciones();
});

// Sistema de notificaciones simplificado
function inicializarNotificaciones() {
    cargarContadorNotificaciones();
    
    // Actualizar cada 30 segundos
    setInterval(() => {
        cargarContadorNotificaciones();
    }, 30000);
}

function cargarContadorNotificaciones() {
    fetch('index.php?action=notificaciones/contador')
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta');
            return response.json();
        })
        .then(data => {
            const countBadge = document.getElementById('notification-count');
            if (data.success && data.count > 0) {
                countBadge.textContent = data.count > 99 ? '99+' : data.count;
                countBadge.style.display = 'flex';
            } else {
                countBadge.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error cargando contador:', error);
            // En caso de error, ocultar el badge
            document.getElementById('notification-count').style.display = 'none';
        });
}

// Función para actualizar notificaciones manualmente (opcional)
function actualizarNotificaciones() {
    cargarContadorNotificaciones();
}
</script>