<?php
require_once 'models/Cita.php';
require_once 'models/User.php';
require_once 'models/Consulta.php';
require_once 'models/SignosVitales.php';

class ConsultaController {
    private $citaModel;
    private $userModel;
    private $consultaModel;
    private $signosModel;
    
    public function __construct() {
        $this->citaModel = new Cita();
        $this->userModel = new User();
        $this->consultaModel = new Consulta();
        $this->signosModel = new SignosVitales();
    }
    
    // Listar citas pendientes del médico logueado
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Solo médicos pueden acceder
        if ($_SESSION['user_role'] != 3) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        
        $medicoId = $_SESSION['user_id'];
        $citas = $this->citaModel->getCitasPendientesMedico($medicoId);
        
        include 'views/consultas/atender/index.php';
    }
    
    // Atender un paciente específico
    public function atender() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $citaId = $_GET['cita_id'] ?? 0;
        $error = '';
        $success = '';
        
        // Obtener datos de la cita
        $cita = $this->citaModel->getCitaById($citaId);
        
        if (!$cita) {
            header('Location: index.php?action=consultas/atender');
            exit;
        }
        
        // Verificar que sea el médico de la cita
        if ($cita['id_medico'] != $_SESSION['user_id']) {
            header('Location: index.php?action=consultas/atender');
            exit;
        }
        
        // Verificar si ya tiene consulta
        $consultaExistente = $this->consultaModel->getConsultaPorCita($citaId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $datosConsulta = [
                    'id_cita' => $citaId,
                    'sintomas' => trim($_POST['sintomas']),
                    'examen_fisico' => trim($_POST['examen_fisico']),
                    'diagnostico_principal' => trim($_POST['diagnostico_principal']),
                    'diagnosticos_secundarios' => trim($_POST['diagnosticos_secundarios']),
                    'tratamiento' => trim($_POST['tratamiento']),
                    'recomendaciones' => trim($_POST['recomendaciones']),
                    'proxima_cita' => trim($_POST['proxima_cita']),
                    'observaciones_medicas' => trim($_POST['observaciones_medicas']),
                    'duracion_minutos' => (int)($_POST['duracion_minutos'] ?? 30)
                ];
                
                // Validaciones
                if (empty($datosConsulta['sintomas']) || empty($datosConsulta['diagnostico_principal'])) {
                    throw new Exception("Síntomas y diagnóstico principal son obligatorios");
                }
                
                // Signos vitales (opcional)
                $signosVitales = null;
                if (!empty($_POST['presion_sistolica']) || !empty($_POST['temperatura'])) {
                    $signosVitales = [
                        'id_cita' => $citaId,
                        'presion_sistolica' => !empty($_POST['presion_sistolica']) ? (int)$_POST['presion_sistolica'] : null,
                        'presion_diastolica' => !empty($_POST['presion_diastolica']) ? (int)$_POST['presion_diastolica'] : null,
                        'frecuencia_cardiaca' => !empty($_POST['frecuencia_cardiaca']) ? (int)$_POST['frecuencia_cardiaca'] : null,
                        'temperatura' => !empty($_POST['temperatura']) ? (float)$_POST['temperatura'] : null,
                        'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
                        'talla' => !empty($_POST['talla']) ? (float)$_POST['talla'] : null,
                        'saturacion_oxigeno' => !empty($_POST['saturacion_oxigeno']) ? (int)$_POST['saturacion_oxigeno'] : null,
                        'observaciones' => trim($_POST['observaciones_signos'] ?? ''),
                        'id_usuario_registro' => $_SESSION['user_id']
                    ];
                }
                
                // Guardar consulta
                if ($consultaExistente) {
                    $this->consultaModel->actualizarConsulta($consultaExistente['id_consulta'], $datosConsulta);
                } else {
                    $this->consultaModel->crearConsulta($datosConsulta);
                }
                
                // Guardar signos vitales si se proporcionaron
                if ($signosVitales) {
                    $signosExistentes = $this->signosModel->getSignosPorCita($citaId);
                    if ($signosExistentes) {
                        $this->signosModel->actualizarSignos($signosExistentes['id_signos'], $signosVitales);
                    } else {
                        $this->signosModel->crearSignos($signosVitales);
                    }
                }
                
                // Cambiar estado de la cita a "completada"
                $this->citaModel->cambiarEstadoCita($citaId, 'completada', $_SESSION['user_id']);
                
                $success = "Consulta guardada exitosamente en el historial clínico del paciente";
                
                // Recargar datos
                $consultaExistente = $this->consultaModel->getConsultaPorCita($citaId);
                $cita = $this->citaModel->getCitaById($citaId);
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Obtener triaje si existe
        $triaje = $this->citaModel->getTriajeCita($citaId);
        
        // Obtener signos vitales si existen
        $signosVitales = $this->signosModel->getSignosPorCita($citaId);
        
        include 'views/consultas/atender/form.php';
    }
    
    // Ver historial clínico completo de un paciente
    public function historial() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $pacienteId = $_GET['paciente_id'] ?? 0;
        
        if (!$pacienteId) {
            header('Location: index.php?action=consultas/atender');
            exit;
        }
        
        // Obtener datos del paciente
        $paciente = $this->userModel->getUserById($pacienteId);
        
        if (!$paciente) {
            header('Location: index.php?action=consultas/atender');
            exit;
        }
        
        // Obtener historial clínico
        $historial = $this->consultaModel->getHistorialPaciente($pacienteId);
        
        include 'views/consultas/atender/historial.php';
    }
}
?>