<?php
// models/Triaje.php
require_once 'config/database.php';
require_once 'includes/password-generator.php';

class Triaje {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getPreguntasTriaje() {
        $sql = "SELECT * FROM preguntas_triaje WHERE activo = 1 ORDER BY orden ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPacientesPendientesTriaje() {
        $sql = "SELECT DISTINCT 
                    u.id_usuario, 
                    CONCAT(u.nombre, ' ', u.apellido) as nombre_completo,
                    u.cedula, u.telefono, u.email,
                    c.id_cita, c.fecha_cita, c.hora_cita,
                    e.nombre_especialidad,
                    CONCAT(m.nombre, ' ', m.apellido) as nombre_medico
                FROM citas c
                INNER JOIN usuarios u ON c.id_paciente = u.id_usuario
                INNER JOIN usuarios m ON c.id_medico = m.id_usuario
                INNER JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN triaje_respuestas tr ON c.id_cita = tr.id_cita
                WHERE c.fecha_cita = CURDATE() 
                AND c.estado_cita IN ('agendada', 'confirmada')
                AND tr.id_cita IS NULL
                AND u.activo = 1
                ORDER BY c.hora_cita ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTriajesRealizadosHoy() {
        $sql = "SELECT 
                    CONCAT(u.nombre, ' ', u.apellido) as nombre_paciente,
                    u.cedula,
                    tr.fecha_respuesta,
                    tr.tipo_triaje,
                    CONCAT(ur.nombre, ' ', ur.apellido) as realizado_por,
                    c.id_cita,
                    COUNT(tr.id_respuesta) as total_respuestas
                FROM triaje_respuestas tr
                INNER JOIN citas c ON tr.id_cita = c.id_cita
                INNER JOIN usuarios u ON c.id_paciente = u.id_usuario
                LEFT JOIN usuarios ur ON tr.id_usuario_registro = ur.id_usuario
                WHERE DATE(tr.fecha_respuesta) = CURDATE()
                GROUP BY tr.id_cita, tr.fecha_respuesta
                ORDER BY tr.fecha_respuesta DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCitasPendientesTriaje($pacienteId) {
        $sql = "SELECT c.*, e.nombre_especialidad,
                       CONCAT(m.nombre, ' ', m.apellido) as nombre_medico,
                       s.nombre_sucursal
                FROM citas c
                INNER JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                INNER JOIN usuarios m ON c.id_medico = m.id_usuario
                INNER JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                LEFT JOIN triaje_respuestas tr ON c.id_cita = tr.id_cita
                WHERE c.id_paciente = :paciente_id
                AND c.fecha_cita >= CURDATE()
                AND c.estado_cita IN ('agendada', 'confirmada')
                AND tr.id_cita IS NULL
                ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['paciente_id' => $pacienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCitasProximasPaciente($pacienteId) {
        $sql = "SELECT c.*, e.nombre_especialidad,
                       CONCAT(m.nombre, ' ', m.apellido) as nombre_medico,
                       s.nombre_sucursal
                FROM citas c
                INNER JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                INNER JOIN usuarios m ON c.id_medico = m.id_usuario
                INNER JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                WHERE c.id_paciente = :paciente_id
                AND c.fecha_cita >= CURDATE()
                AND c.estado_cita IN ('agendada', 'confirmada')
                ORDER BY c.fecha_cita ASC, c.hora_cita ASC
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['paciente_id' => $pacienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
   // Reemplazar el método guardarTriaje en models/Triaje.php

public function guardarTriaje($pacienteId, $citaId, $respuestas, $tipoTriaje, $usuarioRegistro) {
    $this->db->beginTransaction();
    
    try {
        // Si no hay cita ID, crear una cita temporal para el triaje SIN MÉDICO
        if (empty($citaId)) {
            // Insertar directamente evitando el trigger usando NULL para campos opcionales
            $sql = "INSERT INTO citas (
                        id_paciente, id_medico, id_especialidad, id_sucursal,
                        fecha_cita, hora_cita, tipo_cita, 
                        estado_cita, motivo_consulta, id_usuario_registro
                    ) VALUES (
                        :paciente_id, NULL, NULL, NULL,
                        CURDATE(), CURTIME(), 'presencial',
                        'triaje_completado', 'Triaje médico', :usuario_registro
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'paciente_id' => $pacienteId,
                'usuario_registro' => $usuarioRegistro
            ]);
            
            $citaId = $this->db->lastInsertId();
        } else {
            // Verificar que la cita existe y pertenece al paciente
            $sql = "SELECT id_cita FROM citas WHERE id_cita = :cita_id AND id_paciente = :paciente_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cita_id' => $citaId, 'paciente_id' => $pacienteId]);
            
            if (!$stmt->fetch()) {
                throw new Exception("La cita especificada no existe o no pertenece al paciente");
            }
        }
        
       // Eliminar respuestas anteriores si existen
        $sql = "DELETE FROM triaje_respuestas WHERE id_cita = :cita_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cita_id' => $citaId]);
        
        // Insertar las nuevas respuestas
        foreach ($respuestas as $preguntaId => $respuesta) {
            if (empty($respuesta)) continue;
            
            $pregunta = $this->getPreguntaById($preguntaId);
            $valorNumerico = null;
            
            if ($pregunta && in_array($pregunta['tipo_pregunta'], ['numero', 'escala'])) {
                $valorNumerico = is_numeric($respuesta) ? floatval($respuesta) : null;
            }
            
            $sql = "INSERT INTO triaje_respuestas 
                    (id_cita, id_pregunta, respuesta, valor_numerico, tipo_triaje, id_usuario_registro)
                    VALUES (:cita_id, :pregunta_id, :respuesta, :valor_numerico, :tipo_triaje, :usuario_registro)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'cita_id' => $citaId,
                'pregunta_id' => $preguntaId,
                'respuesta' => $respuesta,
                'valor_numerico' => $valorNumerico,
                'tipo_triaje' => $tipoTriaje,
                'usuario_registro' => $usuarioRegistro
            ]);
        }
        
        $this->db->commit();
        return $citaId;
        
    } catch (Exception $e) {
        $this->db->rollback();
        throw $e;
    }
}
    
   // Reemplazar el método crearPacienteConTriaje en models/Triaje.php

public function crearPacienteConTriaje($datosUsuario, $respuestas, $tipoTriaje, $usuarioRegistro) {
    $this->db->beginTransaction();
    
    try {
        // Verificar si la cédula o email ya existen
        $sql = "SELECT id_usuario FROM usuarios WHERE cedula = :cedula OR email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cedula' => $datosUsuario['cedula'], 'email' => $datosUsuario['email']]);
        $existeUsuario = $stmt->fetch();
        
        if ($existeUsuario) {
            throw new Exception("Ya existe un paciente con esa cédula o email");
        }
        
        // Generar contraseña temporal
        $passwordTemporal = generarPasswordTemporal(8);
        
        // Crear usuario/paciente
        $sql = "INSERT INTO usuarios (
                    username, email, password, cedula, nombre, apellido,
                    fecha_nacimiento, genero, telefono, direccion,
                    id_rol, requiere_cambio_contrasena, clave_temporal
                ) VALUES (
                    :username, :email, :password, :cedula, :nombre, :apellido,
                    :fecha_nacimiento, :genero, :telefono, :direccion,
                    :id_rol, 1, :clave_temporal
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $datosUsuario['username'],
            'email' => $datosUsuario['email'],
            'password' => base64_encode($passwordTemporal),
            'cedula' => $datosUsuario['cedula'],
            'nombre' => $datosUsuario['nombre'],
            'apellido' => $datosUsuario['apellido'],
            'fecha_nacimiento' => $datosUsuario['fecha_nacimiento'],
            'genero' => $datosUsuario['genero'],
            'telefono' => $datosUsuario['telefono'],
            'direccion' => $datosUsuario['direccion'],
            'id_rol' => $datosUsuario['id_rol'],
            'clave_temporal' => $passwordTemporal
        ]);
        
        $pacienteId = $this->db->lastInsertId();
        
       // Crear cita para el triaje SIN MÉDICO para evitar validación
        $sql = "INSERT INTO citas (
                    id_paciente, id_medico, id_especialidad, id_sucursal,
                    fecha_cita, hora_cita, tipo_cita, 
                    estado_cita, motivo_consulta, id_usuario_registro
                ) VALUES (
                    :paciente_id, NULL, NULL, NULL,
                    CURDATE(), CURTIME(), 'presencial',
                    'triaje_completado', 'Triaje médico inicial', :usuario_registro
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'paciente_id' => $pacienteId,
            'usuario_registro' => $usuarioRegistro
        ]);
        
        $citaId = $this->db->lastInsertId();
        
        // Guardar respuestas del triaje
        foreach ($respuestas as $preguntaId => $respuesta) {
            if (empty($respuesta)) continue;
            
            $pregunta = $this->getPreguntaById($preguntaId);
            $valorNumerico = null;
            
            if ($pregunta && in_array($pregunta['tipo_pregunta'], ['numero', 'escala'])) {
                $valorNumerico = is_numeric($respuesta) ? floatval($respuesta) : null;
            }
            
            $sql = "INSERT INTO triaje_respuestas 
                    (id_cita, id_pregunta, respuesta, valor_numerico, tipo_triaje, id_usuario_registro)
                    VALUES (:cita_id, :pregunta_id, :respuesta, :valor_numerico, :tipo_triaje, :usuario_registro)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'cita_id' => $citaId,
                'pregunta_id' => $preguntaId,
                'respuesta' => $respuesta,
                'valor_numerico' => $valorNumerico,
                'tipo_triaje' => $tipoTriaje,
                'usuario_registro' => $usuarioRegistro
            ]);
        }
        
        // Enviar credenciales por email (opcional)
        try {
            enviarCredencialesPorEmail(
                $datosUsuario['email'], 
                $datosUsuario['username'], 
                $passwordTemporal, 
                $datosUsuario['nombre'] . ' ' . $datosUsuario['apellido']
            );
        } catch (Exception $e) {
            // Log del error pero no fallar la transacción
            error_log("Error enviando email de credenciales: " . $e->getMessage());
        }
        
        $this->db->commit();
        return $pacienteId;
        
    } catch (Exception $e) {
        $this->db->rollback();
        throw $e;
    }
}
    
    public function getTriajeCompleto($citaId) {
        $sql = "SELECT 
                    c.id_cita, c.fecha_cita, c.hora_cita, c.motivo_consulta,
                    CONCAT(p.nombre, ' ', p.apellido) as nombre_paciente,
                    p.cedula, p.telefono, p.email, p.fecha_nacimiento, p.genero,
                    CONCAT(m.nombre, ' ', m.apellido) as nombre_medico,
                    e.nombre_especialidad,
                    s.nombre_sucursal,
                    tr.tipo_triaje, tr.fecha_respuesta,
                    CONCAT(ur.nombre, ' ', ur.apellido) as realizado_por
                FROM citas c
                INNER JOIN usuarios p ON c.id_paciente = p.id_usuario
                LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
                LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                INNER JOIN triaje_respuestas tr ON c.id_cita = tr.id_cita
                LEFT JOIN usuarios ur ON tr.id_usuario_registro = ur.id_usuario
                WHERE c.id_cita = :cita_id
                GROUP BY c.id_cita
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cita_id' => $citaId]);
        $triaje = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($triaje) {
            // Obtener las respuestas del triaje
            $sql = "SELECT tr.*, pt.pregunta, pt.tipo_pregunta, pt.opciones_json
                    FROM triaje_respuestas tr
                    INNER JOIN preguntas_triaje pt ON tr.id_pregunta = pt.id_pregunta
                    WHERE tr.id_cita = :cita_id
                    ORDER BY pt.orden ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['cita_id' => $citaId]);
            $triaje['respuestas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $triaje;
    }
    
    public function getHistorialTriajes($pacienteId) {
        $sql = "SELECT 
                    c.id_cita, c.fecha_cita, c.hora_cita,
                    tr.tipo_triaje, tr.fecha_respuesta,
                    CONCAT(ur.nombre, ' ', ur.apellido) as realizado_por,
                    COUNT(tr.id_respuesta) as total_respuestas,
                    e.nombre_especialidad,
                    CONCAT(m.nombre, ' ', m.apellido) as nombre_medico
                FROM triaje_respuestas tr
                INNER JOIN citas c ON tr.id_cita = c.id_cita
                LEFT JOIN usuarios ur ON tr.id_usuario_registro = ur.id_usuario
                LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
                WHERE c.id_paciente = :paciente_id
                GROUP BY tr.id_cita, tr.fecha_respuesta
                ORDER BY tr.fecha_respuesta DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['paciente_id' => $pacienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getPreguntaById($id) {
        $sql = "SELECT * FROM preguntas_triaje WHERE id_pregunta = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>