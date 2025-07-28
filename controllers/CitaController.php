<?php
// controllers/CitaController.php
require_once 'models/Cita.php';
require_once 'models/User.php';
require_once 'models/Especialidad.php';
// require_once 'models/Horario.php';
require_once 'models/Sucursal.php';
require_once 'models/Notificacion.php';

class CitaController {
    private $citaModel;
    private $userModel;
    private $especialidadModel;
    private $sucursalModel;
    // private $horarioModel;
    private $notificacionModel;
    
    public function __construct() {
        $this->citaModel = new Cita();
        $this->userModel = new User();
        $this->especialidadModel = new Especialidad();
        $this->sucursalModel = new Sucursal();
        $this->notificacionModel = new Notificacion();
    }
    
    public function index() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Filtros
        $search = $_GET['search'] ?? '';
        $fecha = $_GET['fecha'] ?? '';
        $medico_filter = $_GET['medico_filter'] ?? '';
        $estado_filter = $_GET['estado_filter'] ?? '';
        
        // Obtener citas
        $citas = $this->citaModel->getAllCitas($search, $fecha, $medico_filter, $estado_filter);
        
        // Obtener datos para filtros
        $medicos = $this->userModel->getMedicos();
        
        include 'views/citas/index.php';
    }
    
    public function crear() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'cedula_paciente' => trim($_POST['cedula_paciente']),
                    'id_medico' => $_POST['id_medico'],
                    'id_especialidad' => $_POST['id_especialidad'],
                    'id_sucursal' => $_POST['id_sucursal'],
                    'fecha_cita' => $_POST['fecha_cita'],
                    'hora_cita' => $_POST['hora_cita'],
                    'tipo_cita' => $_POST['tipo_cita'],
                    'motivo_consulta' => trim($_POST['motivo_consulta'])
                ];
                
                // Validaciones
                if (empty($data['cedula_paciente']) || empty($data['id_medico']) || 
                    empty($data['fecha_cita']) || empty($data['hora_cita'])) {
                    throw new Exception("Todos los campos obligatorios deben ser completados");
                }
                
                // Buscar paciente por cédula
                $paciente = $this->userModel->getPacienteByCedula($data['cedula_paciente']);
                if (!$paciente) {
                    throw new Exception("No se encontró paciente con la cédula: " . $data['cedula_paciente']);
                }
                
                // Verificar disponibilidad del médico
                $disponible = $this->citaModel->verificarDisponibilidadMedico(
                    $data['id_medico'], 
                    $data['fecha_cita'], 
                    $data['hora_cita']
                );
                
                if (!$disponible) {
                    throw new Exception("El médico no tiene disponibilidad en esa fecha y hora");
                }
                
                        // Crear la cita
                $data['id_paciente'] = $paciente['id_usuario'];
                $citaId = $this->citaModel->crearCita($data, $_SESSION['user_id']);
                
                //  NUEVA LÍNEA: Enviar notificaciones
                $this->notificacionModel->enviarNotificacionCita($citaId, 'cita_creada', $_SESSION['user_id']);
                
                $success = "Cita médica creada exitosamente. ID: {$citaId}. Se han enviado las notificaciones.";
                
                // Limpiar formulario
                $_POST = [];
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Obtener datos para formulario
        $especialidades = $this->especialidadModel->getAllEspecialidades();
        $sucursales = $this->sucursalModel->getAllSucursales();
        
        include 'views/citas/crear.php';
    }
    
    public function getMedicosPorEspecialidad() {
        // AJAX - Obtener médicos por especialidad
        $especialidadId = $_GET['especialidad_id'] ?? 0;
        $sucursalId = $_GET['sucursal_id'] ?? 0;
        
        try {
            $medicos = $this->citaModel->getMedicosPorEspecialidadSucursal($especialidadId, $sucursalId);
            echo json_encode(['success' => true, 'medicos' => $medicos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function getHorariosMedico() {
        // AJAX - Obtener horarios disponibles de un médico
        $medicoId = $_GET['medico_id'] ?? 0;
        $fecha = $_GET['fecha'] ?? '';
        
        try {
            $horarios = $this->citaModel->getHorariosDisponiblesMedico($medicoId, $fecha);
            echo json_encode(['success' => true, 'horarios' => $horarios]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function buscarPaciente() {
        // AJAX - Buscar paciente por cédula
        $cedula = trim($_GET['cedula'] ?? '');
        
        if (empty($cedula)) {
            echo json_encode(['success' => false, 'message' => 'Cédula requerida']);
            exit;
        }
        
        try {
            $paciente = $this->userModel->getPacienteByCedula($cedula);
            
            if ($paciente) {
                echo json_encode(['success' => true, 'paciente' => $paciente]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Paciente no encontrado',
                    'allow_create' => true
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    // Agregar estos métodos al final de la clase CitaController en controllers/CitaController.php

public function ver() {
    // Ver detalle de una cita
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    $citaId = $_GET['id'] ?? 0;
    
    // Obtener datos de la cita
    $cita = $this->citaModel->getCitaById($citaId);
    
    if (!$cita) {
        header('Location: index.php?action=citas');
        exit;
    }
    
    // Verificar si tiene triaje
    $triaje = $this->citaModel->getTriajeCita($citaId);
    
    // Verificar si tiene consulta
    $consulta = $this->citaModel->getConsultaCita($citaId);
    
    include 'views/citas/ver.php';
}

public function editar() {
    // Editar una cita existente
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    $citaId = $_GET['id'] ?? 0;
    $error = '';
    $success = '';
    
    // Obtener datos de la cita
    $cita = $this->citaModel->getCitaById($citaId);
    
    if (!$cita) {
        header('Location: index.php?action=citas');
        exit;
    }
    
    // Verificar que se pueda editar
    if (!in_array($cita['estado_cita'], ['agendada', 'confirmada'])) {
        header('Location: index.php?action=citas/ver&id=' . $citaId);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $data = [
                'id_medico' => $_POST['id_medico'],
                'id_especialidad' => $_POST['id_especialidad'],
                'id_sucursal' => $_POST['id_sucursal'],
                'fecha_cita' => $_POST['fecha_cita'],
                'hora_cita' => $_POST['hora_cita'],
                'tipo_cita' => $_POST['tipo_cita'],
                'estado_cita' => $_POST['estado_cita'],
                'motivo_consulta' => trim($_POST['motivo_consulta'])
            ];
            
            // Validaciones
            if (empty($data['id_medico']) || empty($data['fecha_cita']) || empty($data['hora_cita'])) {
                throw new Exception("Todos los campos obligatorios deben ser completados");
            }
            
            // Si cambió médico, fecha u hora, verificar disponibilidad
            if ($data['id_medico'] != $cita['id_medico'] || 
                $data['fecha_cita'] != $cita['fecha_cita'] || 
                $data['hora_cita'] != $cita['hora_cita']) {
                
                $disponible = $this->citaModel->verificarDisponibilidadMedico(
                    $data['id_medico'], 
                    $data['fecha_cita'], 
                    $data['hora_cita'],
                    $citaId // Excluir la cita actual de la verificación
                );
                
                if (!$disponible) {
                    throw new Exception("El médico no tiene disponibilidad en esa fecha y hora");
                }
            }
            
            // Actualizar la cita
            $this->citaModel->actualizarCita($citaId, $data, $_SESSION['user_id']);
            
            $success = "Cita actualizada exitosamente";
            
            // Recargar datos
            $cita = $this->citaModel->getCitaById($citaId);
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Obtener datos para formulario
    $especialidades = $this->especialidadModel->getAllEspecialidades();
    $sucursales = $this->sucursalModel->getAllSucursales();
    $medicos = $this->citaModel->getMedicosPorEspecialidadSucursal($cita['id_especialidad'], $cita['id_sucursal']);
    
    include 'views/citas/editar.php';
}

public function cancelar() {
    // Cancelar una cita
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $citaId = $_POST['id'] ?? 0;
        $motivo = trim($_POST['motivo_cancelacion'] ?? '');
        
        try {
            $this->citaModel->cancelarCita($citaId, $motivo, $_SESSION['user_id']);
            echo json_encode(['success' => true, 'message' => 'Cita cancelada exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    exit;
}
public function eliminar() {
    // Eliminar una cita
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $citaId = $_POST['id'] ?? 0;
        
        try {
            // Verificar que la cita existe y se puede eliminar
            $cita = $this->citaModel->getCitaById($citaId);
            
            if (!$cita) {
                throw new Exception("La cita no existe");
            }
            
            if (!in_array($cita['estado_cita'], ['agendada', 'confirmada'])) {
                throw new Exception("Solo se pueden eliminar citas agendadas o confirmadas");
            }
            
            // Verificar que no tenga consulta realizada
            $consulta = $this->citaModel->getConsultaCita($citaId);
            if ($consulta) {
                throw new Exception("No se puede eliminar una cita que ya tiene consulta médica realizada");
            }
            
            $this->citaModel->eliminarCita($citaId, $_SESSION['user_id']);
            echo json_encode(['success' => true, 'message' => 'Cita eliminada exitosamente']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    exit;
}
public function crearPacienteRapido() {
    // Crear paciente rápido desde formulario de citas
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Leer datos JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                throw new Exception("Datos inválidos");
            }
            
            // Validaciones
            if (empty($data['cedula']) || empty($data['nombre']) || 
                empty($data['apellido']) || empty($data['email'])) {
                throw new Exception("Por favor complete todos los campos obligatorios");
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("El email no tiene un formato válido");
            }
            
            // Verificar que no existe ya un paciente con esa cédula o email
            $pacienteExistente = $this->userModel->getPacienteByCedula($data['cedula']);
            if ($pacienteExistente) {
                throw new Exception("Ya existe un paciente con esa cédula");
            }
            
            // Verificar email
            $usuarioExistente = $this->userModel->getUserByEmail($data['email']);
            if ($usuarioExistente) {
                throw new Exception("Ya existe un usuario con ese email");
            }
            
            // Preparar datos para crear usuario
            $datosUsuario = [
                'username' => $data['cedula'], // Usar cédula como username
                'email' => $data['email'],
                'cedula' => $data['cedula'],
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?: null,
                'genero' => $data['genero'] ?: null,
                'telefono' => $data['telefono'] ?: null,
                'direccion' => $data['direccion'] ?: null,
                'id_rol' => 4, // Rol de paciente
                'id_sucursal' => null
            ];
            
            // Crear el paciente
            $pacienteId = $this->userModel->createUser($datosUsuario);
            
            // Obtener datos del paciente creado
            $pacienteCreado = $this->userModel->getUserById($pacienteId);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Paciente creado exitosamente',
                'paciente' => [
                    'id_usuario' => $pacienteCreado['id_usuario'],
                    'nombre' => $pacienteCreado['nombre'],
                    'apellido' => $pacienteCreado['apellido'],
                    'cedula' => $pacienteCreado['cedula'],
                    'email' => $pacienteCreado['email'],
                    'telefono' => $pacienteCreado['telefono']
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    exit;
}
public function calendario() {
    // Vista del calendario dinámico
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    try {
        // Obtener datos necesarios para el calendario
        $medicos = $this->userModel->getMedicos();
        $especialidades = $this->especialidadModel->getAllEspecialidades();
        $sucursales = $this->sucursalModel->getAllSucursales();
        
        // Debug - Verificar que se están obteniendo los datos
        error_log('Médicos: ' . print_r($medicos, true));
        error_log('Especialidades: ' . print_r($especialidades, true));
        error_log('Sucursales: ' . print_r($sucursales, true));
        
        // Obtener citas del mes actual
        $fechaActual = date('Y-m-01');
        $fechaFinal = date('Y-m-t');
        $citas = $this->citaModel->getCitasPorRango($fechaActual, $fechaFinal);
        
        include 'views/citas/calendario.php';
        
    } catch (Exception $e) {
        error_log('Error en calendario: ' . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}
}
?>