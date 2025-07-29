<?php
require_once 'config/database.php';

class Consulta {
    private $db;
    
   public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function crearConsulta($datos) {
        $sql = "INSERT INTO consultas (
                    id_cita, sintomas, examen_fisico, diagnostico_principal,
                    diagnosticos_secundarios, tratamiento, recomendaciones,
                    proxima_cita, observaciones_medicas, duracion_minutos
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['id_cita'],
            $datos['sintomas'],
            $datos['examen_fisico'],
            $datos['diagnostico_principal'],
            $datos['diagnosticos_secundarios'],
            $datos['tratamiento'],
            $datos['recomendaciones'],
            $datos['proxima_cita'],
            $datos['observaciones_medicas'],
            $datos['duracion_minutos']
        ]);
    }
    
    public function actualizarConsulta($id, $datos) {
        $sql = "UPDATE consultas SET 
                    sintomas = ?, examen_fisico = ?, diagnostico_principal = ?,
                    diagnosticos_secundarios = ?, tratamiento = ?, recomendaciones = ?,
                    proxima_cita = ?, observaciones_medicas = ?, duracion_minutos = ?
                WHERE id_consulta = ?";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['sintomas'],
            $datos['examen_fisico'],
            $datos['diagnostico_principal'],
            $datos['diagnosticos_secundarios'],
            $datos['tratamiento'],
            $datos['recomendaciones'],
            $datos['proxima_cita'],
            $datos['observaciones_medicas'],
            $datos['duracion_minutos'],
            $id
        ]);
    }
    
    public function getConsultaPorCita($citaId) {
        $sql = "SELECT * FROM consultas WHERE id_cita = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$citaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getHistorialPaciente($pacienteId) {
        $sql = "SELECT 
                    c.*, con.*, 
                    CONCAT(m.nombre, ' ', m.apellido) as nombre_medico,
                    e.nombre_especialidad,
                    s.nombre_sucursal,
                    sv.presion_sistolica, sv.presion_diastolica, 
                    sv.frecuencia_cardiaca, sv.temperatura,
                    sv.peso, sv.talla, sv.saturacion_oxigeno
                FROM citas c
                INNER JOIN consultas con ON c.id_cita = con.id_cita
                LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
                LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                LEFT JOIN signos_vitales sv ON c.id_cita = sv.id_cita
                WHERE c.id_paciente = ?
                ORDER BY c.fecha_cita DESC, c.hora_cita DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pacienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>