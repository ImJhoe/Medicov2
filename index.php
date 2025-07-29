<?php
session_start();

// ========== DEBUG INICIO ==========
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ccc; margin: 10px;'>";
echo "<h3>🔍 DEBUG INFORMACIÓN</h3>";
echo "<strong>Acción solicitada:</strong> " . ($_GET['action'] ?? 'NO ACTION') . "<br>";
echo "<strong>Usuario ID:</strong> " . ($_SESSION['user_id'] ?? 'NO USER') . "<br>";
echo "<strong>Usuario Rol:</strong> " . ($_SESSION['role_id'] ?? 'NO ROLE') . "<br>";
echo "<strong>Nombre Usuario:</strong> " . ($_SESSION['nombre_completo'] ?? 'NO NAME') . "<br>";
echo "<strong>URL Completa:</strong> " . $_SERVER['REQUEST_URI'] . "<br>";
echo "</div>";
// ========== DEBUG FIN ==========

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
    echo "<div style='background: yellow; padding: 5px;'>✅ ENTRÓ A SECCIÓN CONSULTAS</div>";
    
    // Rutas de triaje
    if (strpos($action, 'consultas/triaje') === 0) {
        echo "<div style='background: lightblue; padding: 5px;'>📋 ENTRÓ A TRIAJE</div>";
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
    
    // RUTAS PARA ATENDER PACIENTES
    if (strpos($action, 'consultas/atender') === 0) {
        echo "<div style='background: lightgreen; padding: 5px;'>👨‍⚕️ ENTRÓ A ATENDER PACIENTES</div>";
        
        // Verificar si existe el controlador
        if (!file_exists('controllers/ConsultaController.php')) {
            echo "<div style='background: red; color: white; padding: 5px;'>❌ ERROR: No existe controllers/ConsultaController.php</div>";
            exit;
        }
        
        echo "<div style='background: lightgreen; padding: 5px;'>✅ ARCHIVO ConsultaController.php EXISTE</div>";
        
        try {
            require_once 'controllers/ConsultaController.php';
            echo "<div style='background: lightgreen; padding: 5px;'>✅ CONTROLLER INCLUIDO CORRECTAMENTE</div>";
            
            $controller = new ConsultaController();
            echo "<div style='background: lightgreen; padding: 5px;'>✅ CONTROLLER INSTANCIADO CORRECTAMENTE</div>";
            
            switch ($action) {
                case 'consultas/atender':
                    echo "<div style='background: orange; padding: 5px;'>🎯 EJECUTANDO index()</div>";
                    $controller->index();
                    break;
                case 'consultas/atender/form':
                    echo "<div style='background: orange; padding: 5px;'>🎯 EJECUTANDO atender()</div>";
                    $controller->atender();
                    break;
                case 'consultas/atender/historial':
                    echo "<div style='background: orange; padding: 5px;'>🎯 EJECUTANDO historial()</div>";
                    $controller->historial();
                    break;
                default:
                    if (isset($_GET['cita_id'])) {
                        echo "<div style='background: orange; padding: 5px;'>🎯 EJECUTANDO atender() con cita_id</div>";
                        $controller->atender();
                    } else {
                        echo "<div style='background: orange; padding: 5px;'>🎯 EJECUTANDO index() por defecto</div>";
                        $controller->index();
                    }
                    break;
            }
        } catch (Exception $e) {
            echo "<div style='background: red; color: white; padding: 5px;'>❌ ERROR EN CONTROLLER: " . $e->getMessage() . "</div>";
            echo "<div style='background: red; color: white; padding: 5px;'>📍 ARCHIVO: " . $e->getFile() . "</div>";
            echo "<div style='background: red; color: white; padding: 5px;'>📍 LÍNEA: " . $e->getLine() . "</div>";
        }
        exit;
    }
    
    echo "<div style='background: pink; padding: 5px;'>⚠️ NO COINCIDIÓ CON NINGUNA RUTA ESPECÍFICA DE CONSULTAS - Acción: {$action}</div>";
} // <- AQUÍ ESTABA EL PROBLEMA DE LLAVES

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