<?php
// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

// Verificar si requiere cambio de contraseña
if (isset($_SESSION['requiere_cambio_contrasena']) && $_SESSION['requiere_cambio_contrasena'] == 1) {
    header('Location: index.php?action=auth/change-password');
    exit;
}

include 'views/includes/header.php';
include 'views/includes/navbar.php'; // Los menús ya se cargan aquí

// Función para obtener saludo según la hora
function getSaludo() {
    $hora = (int) date('H');
    if ($hora >= 6 && $hora < 12) {
        return ['saludo' => 'Bienvenido', 'icono' => 'fas fa-sun', 'color' => 'warning'];
    } elseif ($hora >= 12 && $hora < 18) {
        return ['saludo' => 'Bienvenido', 'icono' => 'fas fa-cloud-sun', 'color' => 'info'];
    } else {
        return ['saludo' => 'Bienvenido', 'icono' => 'fas fa-moon', 'color' => 'dark'];
    }
}

$saludoData = getSaludo();
?>
<script src="js/bloquear.js"></script>
<style>
    /* Variables CSS simplificadas */
    :root {
        --primary-black: #000000;
        --soft-black: #1a1a1a;
        --medium-grey: #666666;
        --light-grey: #e0e0e0;
        --pure-white: #ffffff;
        --off-white: #fafafa;
    }

    /* Reset y base */
    * {
        box-sizing: border-box;
    }

    body {
        background: var(--off-white);
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--soft-black);
        line-height: 1.6;
        margin: 0;
        padding-top: 60px;
    }

    /* Container principal */
    .dashboard-container {
        min-height: 100vh;
        padding: 2rem;
        background: var(--off-white);
    }

    /* Header Principal simplificado */
    .header-section {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .title-text h1 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: var(--primary-black);
    }

    .title-text .subtitle {
        color: var(--medium-grey);
        font-size: 1rem;
    }

    .datetime-cluster {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .date-capsule, .time-capsule {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 8px;
        padding: 0.6rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    /* Panel de Bienvenida simplificado */
    .welcome-panel {
        margin-bottom: 2rem;
    }

    .welcome-glass {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .user-greeting {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .greeting-content h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
    }

    .greeting-content p {
        color: var(--medium-grey);
        margin: 0;
    }

    .user-meta-chips {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .meta-chip {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 6px;
        padding: 0.5rem 0.8rem;
        font-size: 0.9rem;
    }

    /* Grid de Métricas simplificado */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 10px;
        padding: 1.2rem;
    }

    .metric-label {
        color: var(--medium-grey);
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
    }

    .metric-value {
        font-size: 1.8rem;
        font-weight: 700;
    }

    /* Sección de Menús simplificada */
    .menus-section {
        margin-top: 2rem;
    }

    .section-title-text h3 {
        font-size: 1.5rem;
        margin: 0 0 0.3rem 0;
    }

    .section-title-text span {
        color: var(--medium-grey);
        font-size: 0.9rem;
    }

    /* Grid de Menús simplificado */
    .menus-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .menu-card-modern {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 10px;
    }

    .menu-header-wave {
        background: var(--soft-black);
        color: var(--pure-white);
        padding: 1rem;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .menu-title-area h4 {
        font-size: 1.2rem;
        margin: 0 0 0.2rem 0;
    }

    .menu-title-area span {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .menu-options-flow {
        padding: 1rem;
    }

    .option-tile {
        background: var(--pure-white);
        border: 1px solid var(--light-grey);
        border-radius: 8px;
        padding: 0.8rem 1rem;
        margin-bottom: 0.5rem;
        display: block;
        color: var(--soft-black);
        text-decoration: none;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .datetime-cluster {
            justify-content: center;
        }

        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .menus-grid {
            grid-template-columns: 1fr;
        }
    }
     /* Añade estas nuevas clases al CSS existente */
    
    /* Estilo rectangular para el saludo */
    .greeting-rectangle {
        background: var(--pure-white);
        border: 2px solid var(--light-grey);
        border-radius: 0; /* Elimina bordes redondeados */
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .greeting-rectangle h2 {
        font-size: 1.8rem;
        margin: 0;
        padding: 0.5rem 0;
        border-bottom: 2px solid var(--light-grey);
    }
    
    .greeting-rectangle p {
        padding-top: 0.5rem;
        color: var(--medium-grey);
    }
    
    /* Estilo rectangular para las opciones */
    .options-rectangle {
        background: var(--pure-white);
        border: 2px solid var(--light-grey);
        border-radius: 0;
        margin-top: 2rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .options-header {
        background: var(--soft-black);
        color: var(--pure-white);
        padding: 1rem 1.5rem;
        border-bottom: 2px solid var(--light-grey);
    }
    
    .options-header h3 {
        margin: 0;
        font-size: 1.5rem;
    }
    
    .options-header span {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    .options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
    }
    
    .option-card {
        background: var(--pure-white);
        border: 2px solid var(--light-grey);
        padding: 1rem;
        transition: all 0.3s ease;
    }
    
    .option-card:hover {
        border-color: var(--medium-grey);
        transform: translateY(-3px);
    }
    
    .option-card h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1.2rem;
    }
    
    .option-items {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .option-item {
        padding: 0.8rem;
        border: 1px solid var(--light-grey);
        text-decoration: none;
        color: var(--soft-black);
        transition: all 0.2s ease;
    }
    
    .option-item:hover {
        background: #f5f5f5;
        border-color: var(--medium-grey);
    }
     /* Estilos para el menú rectangular básico en columna */
    .basic-column-menu {
        margin-top: 2rem;
        border: 1px solid #ddd;
        background: #fff;
    }
    
    .column-menu-header {
        background: #333;
        color: white;
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
    }
    
    .column-menu-header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .column-menu-header span {
        font-size: 13px;
        opacity: 0.8;
    }
    
    .column-options-container {
        padding: 15px;
    }
    
    .column-option-card {
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }
    
    .column-option-card:last-child {
        margin-bottom: 0;
    }
    
    .column-option-card h4 {
        background: #f5f5f5;
        margin: 0;
        padding: 10px 15px;
        font-size: 16px;
        border-bottom: 1px solid #ddd;
    }
    
    .column-option-items {
        display: flex;
        flex-direction: column;
    }
    
    .column-option-item {
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        border-bottom: 1px solid #eee;
        transition: background 0.2s;
    }
    
    .column-option-item:hover {
        background: #f9f9f9;
    }
    
    .column-option-item:last-child {
        border-bottom: none;
    }
    
    /* Estilo para cuando no hay menús */
    .column-no-permissions {
        padding: 20px;
        text-align: center;
        border: 1px solid #ddd;
        background: #fff;
        margin-top: 2rem;
    }
    
    .column-no-permissions h3 {
        margin-top: 0;
        color: #555;
    }
    
    .column-no-permissions p {
        color: #777;
        margin-bottom: 10px;
    }
    
</style>

<div class="dashboard-container">
    <!-- Header Principal simplificado -->
    <div class="header-section">
        <div class="header-content">
            <div class="title-text">
                <h1>Dashboard</h1>
                <span class="subtitle">Panel de Control</span>
            </div>
            <div class="datetime-cluster">
                <div class="date-capsule">
                    <span>
                        <?php
                        $meses = [
                            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
                        ];
                        $dias = [
                            'Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miércoles',
                            'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sábado', 'Sunday' => 'domingo'
                        ];

                        $fecha_actual = new DateTime();
                        $dia_semana = $dias[$fecha_actual->format('l')];
                        $dia = $fecha_actual->format('d');
                        $mes = $meses[(int) $fecha_actual->format('n')];
                        $año = $fecha_actual->format('Y');

                        echo ucfirst($dia_semana) . ', ' . $dia . ' de ' . $mes . ' de ' . $año;
                        ?>
                    </span>
                </div>
                <!-- <div class="time-capsule">
                    <span id="current-time"><?php echo date('H:i:s'); ?></span>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Panel de Bienvenida simplificado -->
    <div class="welcome-panel">
        <div class="welcome-glass">
            <div class="user-greeting">
                <div class="greeting-rectangle">
                    <h2><?php echo $saludoData['saludo']; ?>, <?php echo explode(' ', $_SESSION['nombre_completo'])[0]; ?>!</h2>
                    <p>Bienvenido al sistema</p>
                </div>
            </div>
            <div class="user-meta-chips">
                <div class="meta-chip role-chip">
                    <span><?php echo $_SESSION['role_name']; ?></span>
                </div>
                <div class="meta-chip id-chip">
                    <span>ID: <?php echo $_SESSION['user_id']; ?></span>
                </div>
                <div class="meta-chip session-chip">
                    <span>Última sesión: <?php echo isset($_SESSION['ultimo_acceso']) ? date('d/m/Y H:i', strtotime($_SESSION['ultimo_acceso'])) : 'Primera vez'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas simplificadas -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-content">
                <div class="metric-label">Menús Disponibles</div>
                <div class="metric-value"><?php echo count($menus); ?></div>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-content">
                <div class="metric-label">Opciones Totales</div>
                <div class="metric-value">
                    <?php
                    $totalSubmenus = 0;
                    foreach ($menus as $menu) {
                        $totalSubmenus += count($menu['submenus']);
                    }
                    echo $totalSubmenus;
                    ?>
                </div>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-content">
                <div class="metric-label">Sesión Activa</div>
                <div class="metric-value" id="session-time">00:00</div>
            </div>
        </div>
        <div class="metric-card">
           
        </div>
    </div>

   <!-- Código actualizado para mostrar todo en una columna -->
<?php if (!empty($menus)): ?>
    <div class="basic-column-menu">
        <div class="column-menu-header">
            <h3>Opciones Disponibles</h3>
            <span>Accede a las funcionalidades según tus permisos</span>
        </div>
        
        <div class="column-options-container">
            <?php foreach ($menus as $index => $menu): ?>
                <div class="column-option-card">
                    <h4><?php echo $menu['nombre_menu']; ?></h4>
                    <div class="column-option-items">
                        <?php foreach ($menu['submenus'] as $submenu): ?>
                            <a href="index.php?action=<?php echo $submenu['uri_submenu']; ?>" class="column-option-item">
                                <?php echo $submenu['nombre_submenu']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="column-no-permissions">
        <h3>Sin Permisos Asignados</h3>
        <p>No tienes permisos asignados o no hay menús disponibles para tu rol actual.</p>
        <p>Contacta al administrador del sistema para obtener los permisos necesarios.</p>
    </div>
<?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Actualizar reloj en tiempo real
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Actualizar cada segundo
        setInterval(updateClock, 1000);

        // Contador de tiempo de sesión
        let sessionStart = new Date();
        function updateSessionTime() {
            const now = new Date();
            const diff = now - sessionStart;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(minutes / 60);
            const displayMinutes = minutes % 60;

            const timeStr = hours > 0
                    ? `${hours.toString().padStart(2, '0')}:${displayMinutes.toString().padStart(2, '0')}`
                    : `${displayMinutes} min`;

            document.getElementById('session-time').textContent = timeStr;
        }

        // Actualizar tiempo de sesión cada minuto
        setInterval(updateSessionTime, 60000);
        updateSessionTime();

        // Efectos de hover mejorados para las tarjetas
        const menuCards = document.querySelectorAll('.menu-card');
        menuCards.forEach(card => {
            card.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Animación de entrada para las tarjetas
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        // Aplicar animación a todas las tarjetas
        document.querySelectorAll('.card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        // Efecto de typewriter para el saludo (opcional)
        const welcomeText = document.querySelector('.welcome-card h3');
        if (welcomeText) {
            const text = welcomeText.textContent;
            welcomeText.textContent = '';
            let i = 0;

            function typeWriter() {
                if (i < text.length) {
                    welcomeText.textContent += text.charAt(i);
                    i++;
                    setTimeout(typeWriter, 50);
                }
            }

            setTimeout(typeWriter, 500);
        }
    });

    // Función para mostrar notificaciones de bienvenida
    function showWelcomeNotification() {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('¡Bienvenido!', {
                body: 'Has iniciado sesión correctamente en el sistema.',
                icon: '/path/to/icon.png'
            });
        }
    }

    // Solicitar permisos de notificación
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showWelcomeNotification();
            }
        });
    }
</script>

</body>
</html>