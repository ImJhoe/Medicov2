<?php
// models/HorarioMedico.php
require_once 'config/database.php';

class HorarioMedico {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getAllHorarios($search = '', $medico_filter = '', $sucursal_filter = '') {
        $sql = "SELECT h.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_medico,
                       s.nombre_sucursal,
                       CASE h.dia_semana 
                           WHEN 1 THEN 'Lunes'
                           WHEN 2 THEN 'Martes'
                           WHEN 3 THEN 'Miércoles'
                           WHEN 4 THEN 'Jueves'
                           WHEN 5 THEN 'Viernes'
                           WHEN 6 THEN 'Sábado'
                           WHEN 7 THEN 'Domingo'
                       END as nombre_dia
                FROM horarios_medicos h
                INNER JOIN usuarios u ON h.id_medico = u.id_usuario
                INNER JOIN sucursales s ON h.id_sucursal = s.id_sucursal
                WHERE u.activo = 1 AND s.activo = 1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (u.nombre LIKE :search OR u.apellido LIKE :search OR s.nombre_sucursal LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($medico_filter)) {
            $sql .= " AND h.id_medico = :medico_filter";
            $params['medico_filter'] = $medico_filter;
        }
        
        if (!empty($sucursal_filter)) {
            $sql .= " AND h.id_sucursal = :sucursal_filter";
            $params['sucursal_filter'] = $sucursal_filter;
        }
        
        $sql .= " ORDER BY u.nombre, u.apellido, h.dia_semana, h.hora_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getHorarioById($id) {
        $sql = "SELECT h.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_medico,
                       s.nombre_sucursal
                FROM horarios_medicos h
                INNER JOIN usuarios u ON h.id_medico = u.id_usuario
                INNER JOIN sucursales s ON h.id_sucursal = s.id_sucursal
                WHERE h.id_horario = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getHorariosByMedicoSucursal($medicoId, $sucursalId) {
        $sql = "SELECT * FROM horarios_medicos 
                WHERE id_medico = :medico_id AND id_sucursal = :sucursal_id AND activo = 1
                ORDER BY dia_semana, hora_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['medico_id' => $medicoId, 'sucursal_id' => $sucursalId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createHorarios($data) {
        $this->db->beginTransaction();
        
        try {
            // Eliminar horarios existentes para este médico y sucursal
            $sql = "DELETE FROM horarios_medicos WHERE id_medico = :medico_id AND id_sucursal = :sucursal_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['medico_id' => $data['id_medico'], 'sucursal_id' => $data['id_sucursal']]);
            
            // Insertar nuevos horarios
            $sql = "INSERT INTO horarios_medicos (id_medico, id_sucursal, dia_semana, hora_inicio, hora_fin, activo) 
                    VALUES (:medico_id, :sucursal_id, :dia_semana, :hora_inicio, :hora_fin, 1)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data['horarios'] as $horario) {
                // Validar que el horario esté completo
                if (empty($horario['dia_semana']) || empty($horario['hora_inicio']) || empty($horario['hora_fin'])) {
                    continue;
                }
                
                // Validar que la hora de inicio sea menor que la de fin
                if ($horario['hora_inicio'] >= $horario['hora_fin']) {
                    throw new Exception("La hora de inicio debe ser menor que la hora de fin para el día " . $this->getDiaNombre($horario['dia_semana']));
                }
                
                $stmt->execute([
                    'medico_id' => $data['id_medico'],
                    'sucursal_id' => $data['id_sucursal'],
                    'dia_semana' => $horario['dia_semana'],
                    'hora_inicio' => $horario['hora_inicio'],
                    'hora_fin' => $horario['hora_fin']
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function updateHorario($id, $data) {
        $sql = "UPDATE horarios_medicos 
                SET dia_semana = :dia_semana, 
                    hora_inicio = :hora_inicio, 
                    hora_fin = :hora_fin, 
                    activo = :activo
                WHERE id_horario = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'dia_semana' => $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'activo' => $data['activo']
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    public function deleteHorario($id) {
        // Verificar que no haya citas agendadas para este horario
        $sql = "SELECT COUNT(*) as total 
                FROM citas c
                INNER JOIN horarios_medicos h ON c.id_medico = h.id_medico
                WHERE h.id_horario = :id 
                AND c.fecha_cita >= CURDATE()
                AND c.estado_cita NOT IN ('cancelada', 'no_asistio')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar el horario porque tiene citas agendadas");
        }
        
        // Eliminar horario
        $sql = "DELETE FROM horarios_medicos WHERE id_horario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }
    
    private function getDiaNombre($diaSemana) {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        
        return $dias[$diaSemana] ?? 'Día desconocido';
    }
}
?>