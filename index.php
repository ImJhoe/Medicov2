<?php
session_start();

// Obtener la acción de la URL
$action = $_GET['action'] ?? 'login';

// Rutas públicas que no requieren login
$publicRoutes = ['login', 'logout'];

// Verificar autenticación
$isPublic = in_array($action, $publicRoutes);
if (!$isPublic && !isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

// Convertir la acción en ruta de archivo
$viewPath = "views/{$action}.php";

// Si la vista no existe, ir a la página apropiada por defecto
if (!file_exists($viewPath)) {
    if (!isset($_SESSION['user_id'])) {
        $viewPath = "views/auth/login.php";
    } else {
        $viewPath = "views/dashboard/index.php";
    }
}

// Rutas para consultas médicas (TODAS - triaje y atender pacientes)
if (strpos($action, 'consultas') === 0) {
    // Rutas de triaje
    if (strpos($action, 'consultas/triaje') === 0) {
        require_once 'controllers/TriajeController.php';
        $controller = new TriajeController();
        
        switch ($action) {
            case 'consultas/triaje':
                $controller->index();
                break;
            case 'consultas/triaje/buscar-paciente':
                $controller->buscarPaciente();
                break;
            case 'consultas/triaje/crear':
                $controller->crear();
                break;
            case 'consultas/triaje/ver':
                $controller->ver();
                break;
            case 'consultas/triaje/historial':
                $controller->historial();
                break;
            case 'consultas/triaje/nuevo-paciente':
                $controller->nuevoPaciente();
                break;
            default:
                $controller->index();
                break;
        }
        exit;
    }
}

// Rutas para gestión de citas médicas
if (strpos($action, 'citas') === 0) {
    require_once 'controllers/CitaController.php';
    $controller = new CitaController();
    
    switch ($action) {
        case 'citas':
            $controller->index();
            break;
        case 'citas/crear':
            $controller->crear();
            break;
        case 'citas/ver':
            $controller->ver();
            break;
        case 'citas/editar':
            $controller->editar();
            break;
        case 'citas/cancelar':
            $controller->cancelar();
            break;
        case 'citas/buscar-paciente':
            $controller->buscarPaciente();
            break;
        case 'citas/get-medicos':
            $controller->getMedicosPorEspecialidad();
            break;
        case 'citas/get-horarios':
            $controller->getHorariosMedico();
            break;
        case 'citas/eliminar':
            $controller->eliminar();
            break;
        case 'citas/crear-paciente-rapido':
            $controller->crearPacienteRapido();
            break;
        case 'citas/calendario':
            $controller->calendario();
            break;           
        default:
            $controller->index();
            break;
    }
    exit;
}

// Rutas para notificaciones
if (strpos($action, 'notificaciones') === 0) {
    require_once 'controllers/NotificacionController.php';
    $controller = new NotificacionController();
    
    switch ($action) {
        case 'notificaciones':
            $controller->index();
            break;
        case 'notificaciones/marcar-leida':
            $controller->marcarLeida();
            break;
        case 'notificaciones/marcar-todas-leidas':
            $controller->marcarTodasLeidas();
            break;
        case 'notificaciones/contador':
            $controller->contador();
            break;
        case 'notificaciones/recientes':
            $controller->recientes();
            break;
        default:
            $controller->index();
            break;
    }
    exit;
}

// Rutas para gestión de doctores
if (strpos($action, 'doctores') === 0) {
    require_once 'controllers/DoctorController.php';
    $controller = new DoctorController();
    
    switch ($action) {
        case 'doctores':
            $controller->index();
            break;
        case 'doctores/crear':
            $controller->crear();
            break;
        case 'doctores/horarios':
            $controller->horarios();
            break;
        case 'doctores/guardar-horario':
            $controller->guardarHorario();
            break;
        case 'doctores/eliminar-horario':
            $controller->eliminarHorario();
            break;
        default:
            $controller->index();
            break;
    }
    exit;
}

// Incluir la vista (que manejará toda su lógica)
include $viewPath;
?>