<?php
// controllers/HorarioController.php
require_once 'models/User.php';
require_once 'models/Sucursal.php';
require_once 'models/HorarioMedico.php';

class HorarioController {
    private $userModel;
    private $sucursalModel;
    private $horarioModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->sucursalModel = new Sucursal();
        $this->horarioModel = new HorarioMedico();
    }

    
    public function index() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener horarios médicos
        $search = $_GET['search'] ?? '';
        $medico_filter = $_GET['medico_filter'] ?? '';
        $sucursal_filter = $_GET['sucursal_filter'] ?? '';
        
        $horarios = $this->horarioModel->getAllHorarios($search, $medico_filter, $sucursal_filter);
        $medicos = $this->userModel->getMedicos();
        $sucursales = $this->sucursalModel->getAllSucursales();
        
        include 'views/config/horarios/index.php';
    }
    
    public function create() {
        // Verificar autenticación y permisos
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'id_medico' => $_POST['id_medico'],
                    'id_sucursal' => $_POST['id_sucursal'],
                    'horarios' => $_POST['horarios'] ?? []
                ];
                
                // Validar que se haya seleccionado médico y sucursal
                if (empty($data['id_medico']) || empty($data['id_sucursal'])) {
                    throw new Exception("Debe seleccionar médico y sucursal");
                }
                
                // Validar que se hayan configurado horarios
                if (empty($data['horarios'])) {
                    throw new Exception("Debe configurar al menos un horario");
                }
                
                $this->horarioModel->createHorarios($data);
                $success = "Horarios médicos configurados exitosamente";
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Obtener datos para el formulario
        $medicos = $this->userModel->getMedicos();
        $sucursales = $this->sucursalModel->getAllSucursales();
        
        include 'views/config/horarios/create.php';
    }
    
    public function edit() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $horarioId = $_GET['id'] ?? 0;
        $error = '';
        $success = '';
        
        // Obtener horario a editar
        $horario = $this->horarioModel->getHorarioById($horarioId);
        if (!$horario) {
            header('Location: index.php?action=config/horarios');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'dia_semana' => $_POST['dia_semana'],
                    'hora_inicio' => $_POST['hora_inicio'],
                    'hora_fin' => $_POST['hora_fin'],
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                // Validaciones
                if (empty($data['dia_semana']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
                    throw new Exception("Todos los campos son obligatorios");
                }
                
                // Validar que la hora de inicio sea menor que la de fin
                if ($data['hora_inicio'] >= $data['hora_fin']) {
                    throw new Exception("La hora de inicio debe ser menor que la hora de fin");
                }
                
                $this->horarioModel->updateHorario($horarioId, $data);
                $success = "Horario actualizado exitosamente";
                
                // Recargar datos
                $horario = $this->horarioModel->getHorarioById($horarioId);
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        $medicos = $this->userModel->getMedicos();
        $sucursales = $this->sucursalModel->getAllSucursales();
        
        include 'views/config/horarios/edit.php';
    }
    
    public function delete() {
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $horarioId = $_POST['id'] ?? 0;
            
            try {
                $this->horarioModel->deleteHorario($horarioId);
                echo json_encode(['success' => true, 'message' => 'Horario eliminado exitosamente']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        exit;
    }
    
    public function getHorariosMedico() {
        // AJAX - Obtener horarios de un médico específico
        $medicoId = $_GET['medico_id'] ?? 0;
        $sucursalId = $_GET['sucursal_id'] ?? 0;
        
        try {
            $horarios = $this->horarioModel->getHorariosByMedicoSucursal($medicoId, $sucursalId);
            echo json_encode(['success' => true, 'horarios' => $horarios]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>