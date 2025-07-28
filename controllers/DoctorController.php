<?php
require_once 'models/Doctor.php';

class DoctorController {
    private $doctorModel;
    
    public function __construct() {
        $this->doctorModel = new Doctor();
    }
    
    public function index() {
        $doctores = $this->doctorModel->obtenerTodosLosDoctores();
        include 'views/doctores/index.php';
    }
    
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'],
                'apellido' => $_POST['apellido'],
                'cedula' => $_POST['cedula'],
                'email' => $_POST['email'],
                'telefono' => $_POST['telefono'],
                'especialidades' => $_POST['especialidades'] ?? [],
                'id_sucursal' => $_POST['id_sucursal']
            ];
            
            $resultado = $this->doctorModel->crearDoctor($data);
            if ($resultado) {
                header('Location: index.php?action=doctores&success=created');
            } else {
                header('Location: index.php?action=doctores&error=create_failed');
            }
        } else {
            $especialidades = $this->doctorModel->obtenerEspecialidades();
            $sucursales = $this->doctorModel->obtenerSucursales();
            include 'views/doctores/crear.php';
        }
    }
    
    public function horarios() {
        $doctorId = $_GET['id'] ?? null;
        if (!$doctorId) {
            header('Location: index.php?action=doctores');
            return;
        }
        
        $doctor = $this->doctorModel->obtenerDoctorPorId($doctorId);
        $horarios = $this->doctorModel->obtenerHorarios($doctorId);
        $sucursales = $this->doctorModel->obtenerSucursales();
        
        include 'views/doctores/horarios.php';
    }
    
    public function guardarHorario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_medico' => $_POST['id_medico'],
                'id_sucursal' => $_POST['id_sucursal'],
                'dia_semana' => $_POST['dia_semana'],
                'hora_inicio' => $_POST['hora_inicio'],
                'hora_fin' => $_POST['hora_fin']
            ];
            
            $resultado = $this->doctorModel->guardarHorario($data);
            echo json_encode(['success' => $resultado]);
        }
    }
    
    public function eliminarHorario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_horario'];
            $resultado = $this->doctorModel->eliminarHorario($id);
            echo json_encode(['success' => $resultado]);
        }
    }
}