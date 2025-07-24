<?php
// controllers/TriajeController.php
require_once 'models/Triaje.php';
require_once 'models/User.php';
require_once 'models/Cita.php';

class TriajeController {
    private $triajeModel;
    private $userModel;
    private $citaModel;
    
    public function __construct() {
        $this->triajeModel = new Triaje();
        $this->userModel = new User();
        $this->citaModel = new Cita();
    }
    
    public function index() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener pacientes pendientes de triaje
        $pacientesPendientes = $this->triajeModel->getPacientesPendientesTriaje();
        
        // Obtener triajes realizados hoy
        $triajesHoy = $this->triajeModel->getTriajesRealizadosHoy();
        
        include 'views/consultas/triaje/index.php';
    }
    
    public function buscarPaciente() {
        // AJAX - Buscar paciente por cédula para triaje
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
        
        $cedula = trim($_GET['cedula'] ?? '');
        
        if (empty($cedula)) {
            echo json_encode(['success' => false, 'message' => 'Cédula requerida']);
            exit;
        }
        
        try {
            $paciente = $this->userModel->getPacienteByCedula($cedula);
            
            if ($paciente) {
                // Verificar si el paciente tiene citas pendientes de triaje
                $citasPendientes = $this->triajeModel->getCitasPendientesTriaje($paciente['id_usuario']);
                
                echo json_encode([
                    'success' => true, 
                    'paciente' => $paciente,
                    'citas_pendientes' => $citasPendientes
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Paciente no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function crear() {
        // Crear triaje para un paciente específico
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $pacienteId = $_GET['paciente_id'] ?? 0;
        $citaId = $_GET['cita_id'] ?? 0;
        $error = '';
        $success = '';
        
        // Verificar que el paciente existe
        $paciente = $this->userModel->getUserById($pacienteId);
        if (!$paciente || $paciente['id_rol'] != 4) {
            header('Location: index.php?action=consultas/triaje');
            exit;
        }
        
        // Verificar la cita si se especifica
        $cita = null;
        if ($citaId) {
            $cita = $this->citaModel->getCitaById($citaId);
            if (!$cita || $cita['id_paciente'] != $pacienteId) {
                header('Location: index.php?action=consultas/triaje');
                exit;
            }
        }
        
        // Procesar formulario de triaje
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $respuestas = $_POST['respuestas'] ?? [];
                $tipoTriaje = $_POST['tipo_triaje'] ?? 'digital';
                $citaIdForm = $_POST['cita_id'] ?? null;
                
                if (empty($respuestas)) {
                    throw new Exception("Debe responder al menos una pregunta");
                }
                
                $triajeId = $this->triajeModel->guardarTriaje($pacienteId, $citaIdForm, $respuestas, $tipoTriaje, $_SESSION['user_id']);
                
                $success = "Triaje guardado exitosamente. ID: {$triajeId}";
                
                // Redirigir a la vista del triaje guardado
                header("Location: index.php?action=consultas/triaje/ver&id={$triajeId}");
                exit;
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Obtener preguntas de triaje activas
        $preguntas = $this->triajeModel->getPreguntasTriaje();
        
        // Obtener citas próximas del paciente
        $citasProximas = $this->triajeModel->getCitasProximasPaciente($pacienteId);
        
        include 'views/consultas/triaje/crear.php';
    }
    
    public function ver() {
        // Ver triaje realizado
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $triajeId = $_GET['id'] ?? 0;
        
        // Obtener datos del triaje
        $triaje = $this->triajeModel->getTriajeCompleto($triajeId);
        
        if (!$triaje) {
            header('Location: index.php?action=consultas/triaje');
            exit;
        }
        
        include 'views/consultas/triaje/ver.php';
    }
    
   // Actualizar el método historial en controllers/TriajeController.php

public function historial() {
    // Ver historial de triajes de un paciente
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    $pacienteId = $_GET['paciente_id'] ?? 0;
    
    // Verificar que el paciente existe
    $paciente = $this->userModel->getUserById($pacienteId);
    if (!$paciente || $paciente['id_rol'] != 4) {
        header('Location: index.php?action=consultas/triaje');
        exit;
    }
    
    // Obtener historial de triajes
    $triajes = $this->triajeModel->getHistorialTriajes($pacienteId);
    
    include 'views/consultas/triaje/historial.php';
}
    
    public function nuevoPaciente() {
        // Crear triaje para paciente nuevo (sin cita previa)
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Datos del paciente
                $datosUsuario = [
                    'username' => trim($_POST['cedula']), // Usar cédula como username inicial
                    'email' => trim($_POST['email']),
                    'cedula' => trim($_POST['cedula']),
                    'nombre' => trim($_POST['nombre']),
                    'apellido' => trim($_POST['apellido']),
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?: null,
                    'genero' => $_POST['genero'] ?: null,
                    'telefono' => trim($_POST['telefono']) ?: null,
                    'direccion' => trim($_POST['direccion']) ?: null,
                    'id_rol' => 4, // Rol de paciente
                    'id_sucursal' => null
                ];
                
                // Respuestas del triaje
                $respuestas = $_POST['respuestas'] ?? [];
                $tipoTriaje = $_POST['tipo_triaje'] ?? 'presencial';
                
                // Validaciones
                if (empty($datosUsuario['cedula']) || empty($datosUsuario['nombre']) || 
                    empty($datosUsuario['apellido']) || empty($datosUsuario['email'])) {
                    throw new Exception("Por favor complete todos los campos obligatorios del paciente");
                }
                
                if (empty($respuestas)) {
                    throw new Exception("Debe completar el triaje médico");
                }
                
                // Crear paciente y triaje en una transacción
                $pacienteId = $this->triajeModel->crearPacienteConTriaje($datosUsuario, $respuestas, $tipoTriaje, $_SESSION['user_id']);
                
                $success = "Paciente creado y triaje completado exitosamente";
                
                // Redirigir al historial del nuevo paciente
                header("Location: index.php?action=consultas/triaje/historial&paciente_id={$pacienteId}");
                exit;
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Obtener preguntas de triaje
        $preguntas = $this->triajeModel->getPreguntasTriaje();
        
        include 'views/consultas/triaje/nuevo-paciente.php';
    }
}
?>