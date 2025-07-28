<?php
require_once 'config/Database.php';

class Doctor {
    private $db;
    
    public function __construct() {
     $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function crearDoctor($data) {
        $this->db->beginTransaction();
        
        try {
            // Crear usuario con rol de médico
            $sql = "INSERT INTO usuarios (nombre, apellido, cedula, email, telefono, id_rol, activo, password) 
                    VALUES (:nombre, :apellido, :cedula, :email, :telefono, 3, 1, :password)";
            
            $stmt = $this->db->prepare($sql);
            $password = password_hash($data['cedula'], PASSWORD_DEFAULT); // Contraseña temporal
            
            $stmt->execute([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'cedula' => $data['cedula'],
                'email' => $data['email'],
                'telefono' => $data['telefono'],
                'password' => $password
            ]);
            
            $doctorId = $this->db->lastInsertId();
            
            // Asignar especialidades
            foreach ($data['especialidades'] as $especialidadId) {
                $sql = "INSERT INTO medico_especialidades (id_medico, id_especialidad, activo) 
                        VALUES (:id_medico, :id_especialidad, 1)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'id_medico' => $doctorId,
                    'id_especialidad' => $especialidadId
                ]);
            }
            
            $this->db->commit();
            return $doctorId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    public function obtenerTodosLosDoctores() {
        $sql = "SELECT u.*, GROUP_CONCAT(e.nombre_especialidad SEPARATOR ', ') as especialidades
                FROM usuarios u
                LEFT JOIN medico_especialidades me ON u.id_usuario = me.id_medico
                LEFT JOIN especialidades e ON me.id_especialidad = e.id_especialidad
                WHERE u.id_rol = 3 AND u.activo = 1
                GROUP BY u.id_usuario
                ORDER BY u.nombre, u.apellido";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerDoctorPorId($id) {
        $sql = "SELECT u.*, GROUP_CONCAT(e.nombre_especialidad SEPARATOR ', ') as especialidades
                FROM usuarios u
                LEFT JOIN medico_especialidades me ON u.id_usuario = me.id_medico
                LEFT JOIN especialidades e ON me.id_especialidad = e.id_especialidad
                WHERE u.id_usuario = :id AND u.id_rol = 3
                GROUP BY u.id_usuario";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerHorarios($doctorId) {
        $sql = "SELECT h.*, s.nombre_sucursal,
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
                JOIN sucursales s ON h.id_sucursal = s.id_sucursal
                WHERE h.id_medico = :doctor_id AND h.activo = 1
                ORDER BY h.dia_semana, h.hora_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['doctor_id' => $doctorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function guardarHorario($data) {
        // Verificar si ya existe un horario en ese día/hora
        $sql = "SELECT id_horario FROM horarios_medicos 
                WHERE id_medico = :id_medico AND dia_semana = :dia_semana 
                AND id_sucursal = :id_sucursal
                AND ((hora_inicio <= :hora_inicio AND hora_fin > :hora_inicio) 
                OR (hora_inicio < :hora_fin AND hora_fin >= :hora_fin)
                OR (hora_inicio >= :hora_inicio AND hora_fin <= :hora_fin))
                AND activo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        if ($stmt->rowCount() > 0) {
            return false; // Conflicto de horarios
        }
        
        // Insertar nuevo horario
        $sql = "INSERT INTO horarios_medicos (id_medico, id_sucursal, dia_semana, hora_inicio, hora_fin, activo) 
                VALUES (:id_medico, :id_sucursal, :dia_semana, :hora_inicio, :hora_fin, 1)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function eliminarHorario($id) {
        $sql = "UPDATE horarios_medicos SET activo = 0 WHERE id_horario = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function obtenerEspecialidades() {
        $sql = "SELECT * FROM especialidades WHERE activo = 1 ORDER BY nombre_especialidad";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerSucursales() {
        $sql = "SELECT * FROM sucursales WHERE activo = 1 ORDER BY nombre_sucursal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}