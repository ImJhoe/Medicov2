<?php
require_once 'config/database.php';

class SignosVitales {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function crearSignos($datos) {
        $sql = "INSERT INTO signos_vitales (
                    id_cita, presion_sistolica, presion_diastolica,
                    frecuencia_cardiaca, temperatura, peso, talla,
                    saturacion_oxigeno, observaciones, id_usuario_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['id_cita'],
            $datos['presion_sistolica'],
            $datos['presion_diastolica'],
            $datos['frecuencia_cardiaca'],
            $datos['temperatura'],
            $datos['peso'],
            $datos['talla'],
            $datos['saturacion_oxigeno'],
            $datos['observaciones'],
            $datos['id_usuario_registro']
        ]);
    }
    
    public function actualizarSignos($id, $datos) {
        $sql = "UPDATE signos_vitales SET 
                    presion_sistolica = ?, presion_diastolica = ?,
                    frecuencia_cardiaca = ?, temperatura = ?, peso = ?, talla = ?,
                    saturacion_oxigeno = ?, observaciones = ?
                WHERE id_signos = ?";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['presion_sistolica'],
            $datos['presion_diastolica'],
            $datos['frecuencia_cardiaca'],
            $datos['temperatura'],
            $datos['peso'],
            $datos['talla'],
            $datos['saturacion_oxigeno'],
            $datos['observaciones'],
            $id
        ]);
    }
    
    public function getSignosPorCita($citaId) {
        $sql = "SELECT * FROM signos_vitales WHERE id_cita = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$citaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>