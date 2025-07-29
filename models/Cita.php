<?php
// Actualizar models/Cita.php
require_once 'config/database.php';

class Cita {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getAllCitas($search = '', $fecha = '', $medico_filter = '', $estado_filter = '') {
        $sql = "SELECT c.*, 
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_paciente,
                       p.cedula,
                       COALESCE(CONCAT(m.nombre, ' ', m.apellido), 'Sin médico') as nombre_medico,
                       COALESCE(e.nombre_especialidad, 'Sin especialidad') as nombre_especialidad,
                       COALESCE(s.nombre_sucursal, 'Sin sucursal') as nombre_sucursal
                FROM citas c
                INNER JOIN usuarios p ON c.id_paciente = p.id_usuario
                LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
                LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                WHERE p.activo = 1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (p.nombre LIKE :search OR p.apellido LIKE :search OR p.cedula LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($fecha)) {
            $sql .= " AND c.fecha_cita = :fecha";
            $params['fecha'] = $fecha;
        }
        
        if (!empty($medico_filter)) {
            $sql .= " AND c.id_medico = :medico_filter";
            $params['medico_filter'] = $medico_filter;
        }
        
        if (!empty($estado_filter)) {
            $sql .= " AND c.estado_cita = :estado_filter";
            $params['estado_filter'] = $estado_filter;
        }
        
        $sql .= " ORDER BY c.fecha_cita DESC, c.hora_cita DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function crearCita($data, $usuarioRegistro) {
        $this->db->beginTransaction();
        
        try {
            // La validación de horarios se hace automáticamente por el trigger
            $sql = "INSERT INTO citas (
                        id_paciente, id_medico, id_especialidad, id_sucursal,
                        fecha_cita, hora_cita, tipo_cita, estado_cita,
                        motivo_consulta, id_usuario_registro
                    ) VALUES (
                        :id_paciente, :id_medico, :id_especialidad, :id_sucursal,
                        :fecha_cita, :hora_cita, :tipo_cita, 'agendada',
                        :motivo_consulta, :usuario_registro
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id_paciente' => $data['id_paciente'],
                'id_medico' => $data['id_medico'],
                'id_especialidad' => $data['id_especialidad'],
                'id_sucursal' => $data['id_sucursal'],
                'fecha_cita' => $data['fecha_cita'],
                'hora_cita' => $data['hora_cita'],
                'tipo_cita' => $data['tipo_cita'],
                'motivo_consulta' => $data['motivo_consulta'],
                'usuario_registro' => $usuarioRegistro
            ]);
            
            $citaId = $this->db->lastInsertId();
            
            $this->db->commit();
            return $citaId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
   public function verificarDisponibilidadMedico($medicoId, $fecha, $hora, $excluirCitaId = null) {
    // Verificar que el médico tenga horario ese día
    $diaSemana = date('w', strtotime($fecha)) + 1; // Convertir a formato MySQL (1=Lunes)
    if ($diaSemana == 8) $diaSemana = 1; // Domingo = 1 en MySQL
    
    $sql = "SELECT COUNT(*) as tiene_horario
            FROM horarios_medicos h
            WHERE h.id_medico = :medico_id
            AND h.dia_semana = :dia_semana
            AND :hora BETWEEN h.hora_inicio AND h.hora_fin
            AND h.activo = 1";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'medico_id' => $medicoId,
        'dia_semana' => $diaSemana,
        'hora' => $hora
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['tiene_horario'] == 0) {
        return false;
    }
    
    // Verificar que no tenga cita en ese horario
    $sql = "SELECT COUNT(*) as tiene_cita
            FROM citas c
            WHERE c.id_medico = :medico_id
            AND c.fecha_cita = :fecha
            AND c.hora_cita = :hora
            AND c.estado_cita NOT IN ('cancelada', 'no_asistio')";
    
    $params = [
        'medico_id' => $medicoId,
        'fecha' => $fecha,
        'hora' => $hora
    ];
    
    // Excluir cita actual si se está editando
    if ($excluirCitaId) {
        $sql .= " AND c.id_cita != :excluir_cita_id";
        $params['excluir_cita_id'] = $excluirCitaId;
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['tiene_cita'] == 0;
}
    
    public function getMedicosPorEspecialidadSucursal($especialidadId, $sucursalId) {
        $sql = "SELECT DISTINCT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo
                FROM usuarios u
                INNER JOIN medico_especialidades me ON u.id_usuario = me.id_medico
                WHERE u.id_rol = 3 
                AND u.activo = 1 
                AND me.id_especialidad = :especialidad_id
                AND me.activo = 1";
        
        $params = ['especialidad_id' => $especialidadId];
        
        if (!empty($sucursalId)) {
            $sql .= " AND (u.id_sucursal = :sucursal_id OR u.id_sucursal IS NULL)";
            $params['sucursal_id'] = $sucursalId;
        }
        
        $sql .= " ORDER BY u.nombre, u.apellido";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getHorariosDisponiblesMedico($medicoId, $fecha) {
        $diaSemana = date('w', strtotime($fecha)) + 1;
        if ($diaSemana == 8) $diaSemana = 1;
        
        $sql = "SELECT h.hora_inicio, h.hora_fin
                FROM horarios_medicos h
                WHERE h.id_medico = :medico_id
                AND h.dia_semana = :dia_semana
                AND h.activo = 1
                ORDER BY h.hora_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'medico_id' => $medicoId,
            'dia_semana' => $diaSemana
        ]);
        
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generar slots de 30 minutos y verificar disponibilidad
        $horariosDisponibles = [];
        
        foreach ($horarios as $horario) {
            $inicio = strtotime($horario['hora_inicio']);
            $fin = strtotime($horario['hora_fin']);
            
            for ($tiempo = $inicio; $tiempo < $fin; $tiempo += 1800) { // 1800 segundos = 30 minutos
                $hora = date('H:i:s', $tiempo);
                
                // Verificar si está disponible
                if ($this->verificarDisponibilidadMedico($medicoId, $fecha, $hora)) {
                    $horariosDisponibles[] = [
                        'hora' => $hora,
                        'hora_formato' => date('H:i', $tiempo)
                    ];
                }
            }
        }
        
        return $horariosDisponibles;
    }
    
    public function getCitaById($id) {
        $sql = "SELECT c.*, 
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_paciente,
                       p.cedula, p.telefono, p.email,
                       COALESCE(CONCAT(m.nombre, ' ', m.apellido), 'Sin médico') as nombre_medico,
                       COALESCE(e.nombre_especialidad, 'Sin especialidad') as nombre_especialidad,
                       COALESCE(s.nombre_sucursal, 'Sin sucursal') as nombre_sucursal
                FROM citas c
                INNER JOIN usuarios p ON c.id_paciente = p.id_usuario
                LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
                LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                WHERE c.id_cita = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function actualizarCita($citaId, $data, $usuarioRegistro) {
    $this->db->beginTransaction();
    
    try {
        $sql = "UPDATE citas SET
                    id_medico = :id_medico,
                    id_especialidad = :id_especialidad,
                    id_sucursal = :id_sucursal,
                    fecha_cita = :fecha_cita,
                    hora_cita = :hora_cita,
                    tipo_cita = :tipo_cita,
                    estado_cita = :estado_cita,
                    motivo_consulta = :motivo_consulta,
                    id_usuario_registro = :usuario_registro,
                    fecha_modificacion = NOW()
                WHERE id_cita = :cita_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cita_id' => $citaId,
            'id_medico' => $data['id_medico'],
            'id_especialidad' => $data['id_especialidad'],
            'id_sucursal' => $data['id_sucursal'],
            'fecha_cita' => $data['fecha_cita'],
            'hora_cita' => $data['hora_cita'],
            'tipo_cita' => $data['tipo_cita'],
            'estado_cita' => $data['estado_cita'],
            'motivo_consulta' => $data['motivo_consulta'],
            'usuario_registro' => $usuarioRegistro
        ]);
        
        $this->db->commit();
        require_once 'models/Notificacion.php';
        $notificacionModel = new Notificacion();
        $notificacionModel->enviarNotificacionCita($citaId, 'cita_modificada', $usuarioRegistro);
        return true;
        
    } catch (Exception $e) {
        $this->db->rollback();
        throw $e;
    }
}
// ACTUALIZAR el método cancelarCita en models/Cita.php:
public function cancelarCita($citaId, $motivo, $usuarioRegistro) {
    $sql = "UPDATE citas SET
                estado_cita = 'cancelada',
                observaciones = :motivo,
                fecha_cancelacion = NOW(),
                id_usuario_registro = :usuario_registro
            WHERE id_cita = :cita_id
            AND estado_cita IN ('agendada', 'confirmada')";
    
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
        'cita_id' => $citaId,
        'motivo' => $motivo,
        'usuario_registro' => $usuarioRegistro
    ]);
    
    if ($stmt->rowCount() == 0) {
        throw new Exception("No se pudo cancelar la cita. Verifique el estado actual.");
    }
    
    // ✅ NUEVA LÍNEA: Enviar notificación de cancelación
    require_once 'models/Notificacion.php';
    $notificacionModel = new Notificacion();
    $notificacionModel->enviarNotificacionCita($citaId, 'cita_cancelada', $usuarioRegistro);
    
    return true;
}
public function getTriajeCita($citaId) {
    $sql = "SELECT COUNT(*) as tiene_triaje,
                   MIN(tr.fecha_respuesta) as fecha_triaje,
                   tr.tipo_triaje
            FROM triaje_respuestas tr
            WHERE tr.id_cita = :cita_id
            GROUP BY tr.tipo_triaje";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['cita_id' => $citaId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// REEMPLAZAR el método getConsultaCita en models/Cita.php (línea 314)

public function getConsultaCita($citaId) {
    $sql = "SELECT c.*
            FROM consultas c
            WHERE c.id_cita = :cita_id";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['cita_id' => $citaId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    public function eliminarCita($citaId, $usuarioRegistro) {
    $this->db->beginTransaction();
    
    try {
        // Primero eliminar respuestas de triaje si existen
        $sql = "DELETE FROM triaje_respuestas WHERE id_cita = :cita_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cita_id' => $citaId]);
        
        // Luego eliminar la cita
        $sql = "DELETE FROM citas WHERE id_cita = :cita_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cita_id' => $citaId]);
        
        if ($stmt->rowCount() == 0) {
            throw new Exception("No se pudo eliminar la cita");
        }
        
        // Log de la eliminación
        $sql = "INSERT INTO logs_sistema (id_usuario, accion, tabla_afectada, id_registro_afectado)
                VALUES (:usuario_id, 'ELIMINAR_CITA', 'citas', :cita_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioRegistro,
            'cita_id' => $citaId
        ]);
        
        $this->db->commit();
        return true;
        
    } catch (Exception $e) {
        $this->db->rollback();
        throw $e;
    }
}

// Agregar este método al final de la clase Cita en models/Cita.php

public function actualizarEstadoCita($citaId, $estado, $usuarioId) {
    $sql = "UPDATE citas SET 
                estado_cita = :estado,
                id_usuario_registro = :usuario_id,
                fecha_modificacion = CURRENT_TIMESTAMP
            WHERE id_cita = :cita_id";
    
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        'cita_id' => $citaId,
        'estado' => $estado,
        'usuario_id' => $usuarioId
    ]);
}
public function getCitasPorRango($fechaInicio, $fechaFinal) {
    $sql = "SELECT c.*, 
                   CONCAT(p.nombre, ' ', p.apellido) as nombre_paciente,
                   p.cedula,
                   CONCAT(m.nombre, ' ', m.apellido) as nombre_medico,
                   e.nombre_especialidad,
                   s.nombre_sucursal
            FROM citas c
            INNER JOIN usuarios p ON c.id_paciente = p.id_usuario
            LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
            LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
            LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
            WHERE c.fecha_cita BETWEEN :fecha_inicio AND :fecha_final
            AND c.estado_cita NOT IN ('cancelada', 'no_asistio')
            ORDER BY c.fecha_cita, c.hora_cita";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'fecha_inicio' => $fechaInicio,
        'fecha_final' => $fechaFinal
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Agregar al final de la clase Cita

public function getCitasPendientesMedico($medicoId) {
    $sql = "SELECT 
                c.*, 
                CONCAT(p.nombre, ' ', p.apellido) as nombre_paciente,
                p.cedula,
                e.nombre_especialidad,
                s.nombre_sucursal
            FROM citas c
            INNER JOIN usuarios p ON c.id_paciente = p.id_usuario
            LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
            LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
            WHERE c.id_medico = ? 
                AND c.estado_cita IN ('confirmada', 'agendada')
                AND c.fecha_cita >= CURDATE()
            ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
            
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$medicoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function cambiarEstadoCita($citaId, $nuevoEstado, $usuarioId) {
    $sql = "UPDATE citas SET 
                estado_cita = ?,
                fecha_modificacion = NOW()
            WHERE id_cita = ?";
            
    $stmt = $this->db->prepare($sql);
    $resultado = $stmt->execute([$nuevoEstado, $citaId]);
    
    if ($resultado) {
        // Registrar en logs directamente en la tabla logs_sistema
        $sqlLog = "INSERT INTO logs_sistema (
                    id_usuario,
                    accion,
                    tabla_afectada,
                    id_registro_afectado,
                    datos_nuevos
                ) VALUES (?, ?, ?, ?, ?)";
                
        $stmtLog = $this->db->prepare($sqlLog);
        $stmtLog->execute([
            $usuarioId,
            'CAMBIAR_ESTADO_CITA',
            'citas',
            $citaId,
            json_encode(['nuevo_estado' => $nuevoEstado])
        ]);
    }
    
    return $resultado;
}
}
?>