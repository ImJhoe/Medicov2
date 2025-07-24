<?php
// models/Notificacion.php
require_once 'config/database.php';
require_once 'includes/password-generator.php'; // Para enviar emails

class Notificacion {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function crearNotificacion($usuarioId, $tipo, $titulo, $mensaje, $referenciaId = null) {
        $sql = "INSERT INTO notificaciones (
                    id_usuario_destinatario, tipo_notificacion, titulo, 
                    mensaje, id_referencia, fecha_creacion
                ) VALUES (
                    :usuario_id, :tipo, :titulo, :mensaje, :referencia_id, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'referencia_id' => $referenciaId
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function enviarNotificacionCita($citaId, $tipoEvento, $usuarioRegistro) {
        // Obtener datos de la cita
        $sql = "SELECT c.*, 
                       CONCAT(p.nombre, ' ', p.apellido) as nombre_paciente,
                       p.email as email_paciente,
                       p.telefono as telefono_paciente,
                       COALESCE(CONCAT(m.nombre, ' ', m.apellido), 'No asignado') as nombre_medico,
                       COALESCE(m.email, '') as email_medico,
                       COALESCE(e.nombre_especialidad, 'Sin especialidad') as especialidad,
                       COALESCE(s.nombre_sucursal, 'Sin sucursal') as sucursal
                FROM citas c
                INNER JOIN usuarios p ON c.id_paciente = p.id_usuario
                LEFT JOIN usuarios m ON c.id_medico = m.id_usuario
                LEFT JOIN especialidades e ON c.id_especialidad = e.id_especialidad
                LEFT JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                WHERE c.id_cita = :cita_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cita_id' => $citaId]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cita) return false;
        
        // Configurar mensajes según el tipo de evento
        $configuracion = $this->getConfiguracionNotificacion($tipoEvento, $cita);
        
        // Crear notificación en BD para el paciente
        $this->crearNotificacion(
            $cita['id_paciente'], 
            $configuracion['tipo_bd'], 
            $configuracion['titulo_paciente'], 
            $configuracion['mensaje_paciente'], 
            $citaId
        );
        
        // Enviar email al paciente
        if (!empty($cita['email_paciente'])) {
            $this->enviarEmailCita(
                $cita['email_paciente'],
                $cita['nombre_paciente'],
                $configuracion['asunto_email'],
                $configuracion['contenido_email_paciente'],
                $cita
            );
        }
        
        // Si hay médico asignado, notificar también al médico
        if ($cita['id_medico'] && !empty($cita['email_medico'])) {
            $this->crearNotificacion(
                $cita['id_medico'], 
                $configuracion['tipo_bd'], 
                $configuracion['titulo_medico'], 
                $configuracion['mensaje_medico'], 
                $citaId
            );
            
            $this->enviarEmailCita(
                $cita['email_medico'],
                $cita['nombre_medico'],
                $configuracion['asunto_email_medico'],
                $configuracion['contenido_email_medico'],
                $cita
            );
        }
        
        return true;
    }
    
    private function getConfiguracionNotificacion($tipoEvento, $cita) {
        $fechaFormateada = date('d/m/Y', strtotime($cita['fecha_cita']));
        $horaFormateada = date('H:i', strtotime($cita['hora_cita']));
        
        switch ($tipoEvento) {
            case 'cita_creada':
                return [
                    'tipo_bd' => 'cita_agendada',
                    'titulo_paciente' => '✅ Cita Médica Agendada',
                    'mensaje_paciente' => "Su cita médica ha sido agendada para el {$fechaFormateada} a las {$horaFormateada} en {$cita['sucursal']} con {$cita['nombre_medico']} ({$cita['especialidad']}).",
                    'titulo_medico' => '📅 Nueva Cita Asignada',
                    'mensaje_medico' => "Se le ha asignado una nueva cita con {$cita['nombre_paciente']} para el {$fechaFormateada} a las {$horaFormateada}.",
                    'asunto_email' => 'Confirmación de Cita Médica - ' . $fechaFormateada,
                    'asunto_email_medico' => 'Nueva Cita Asignada - ' . $fechaFormateada,
                    'contenido_email_paciente' => $this->getPlantillaEmailPaciente('creada', $cita),
                    'contenido_email_medico' => $this->getPlantillaEmailMedico('creada', $cita)
                ];
                
            case 'cita_modificada':
                return [
                    'tipo_bd' => 'cita_agendada',
                    'titulo_paciente' => '📝 Cita Médica Modificada',
                    'mensaje_paciente' => "Su cita médica ha sido modificada. Nueva fecha: {$fechaFormateada} a las {$horaFormateada} en {$cita['sucursal']} con {$cita['nombre_medico']}.",
                    'titulo_medico' => '📝 Cita Modificada',
                    'mensaje_medico' => "La cita con {$cita['nombre_paciente']} ha sido modificada para el {$fechaFormateada} a las {$horaFormateada}.",
                    'asunto_email' => 'Cita Médica Modificada - Nueva fecha: ' . $fechaFormateada,
                    'asunto_email_medico' => 'Cita Modificada - ' . $fechaFormateada,
                    'contenido_email_paciente' => $this->getPlantillaEmailPaciente('modificada', $cita),
                    'contenido_email_medico' => $this->getPlantillaEmailMedico('modificada', $cita)
                ];
                
            case 'cita_cancelada':
                return [
                    'tipo_bd' => 'cita_cancelada',
                    'titulo_paciente' => '❌ Cita Médica Cancelada',
                    'mensaje_paciente' => "Su cita médica del {$fechaFormateada} a las {$horaFormateada} ha sido cancelada. Por favor contacte a la clínica para reagendar.",
                    'titulo_medico' => '❌ Cita Cancelada',
                    'mensaje_medico' => "La cita con {$cita['nombre_paciente']} del {$fechaFormateada} a las {$horaFormateada} ha sido cancelada.",
                    'asunto_email' => 'Cita Médica Cancelada - ' . $fechaFormateada,
                    'asunto_email_medico' => 'Cita Cancelada - ' . $fechaFormateada,
                    'contenido_email_paciente' => $this->getPlantillaEmailPaciente('cancelada', $cita),
                    'contenido_email_medico' => $this->getPlantillaEmailMedico('cancelada', $cita)
                ];
                
            case 'cita_confirmada':
                return [
                    'tipo_bd' => 'cita_agendada',
                    'titulo_paciente' => '✅ Cita Médica Confirmada',
                    'mensaje_paciente' => "Su cita médica del {$fechaFormateada} a las {$horaFormateada} ha sido confirmada. Por favor llegue 15 minutos antes.",
                    'titulo_medico' => '✅ Cita Confirmada',
                    'mensaje_medico' => "La cita con {$cita['nombre_paciente']} del {$fechaFormateada} a las {$horaFormateada} ha sido confirmada.",
                    'asunto_email' => 'Cita Médica Confirmada - ' . $fechaFormateada,
                    'asunto_email_medico' => 'Cita Confirmada - ' . $fechaFormateada,
                    'contenido_email_paciente' => $this->getPlantillaEmailPaciente('confirmada', $cita),
                    'contenido_email_medico' => $this->getPlantillaEmailMedico('confirmada', $cita)
                ];
                
            default:
                return [
                    'tipo_bd' => 'sistema',
                    'titulo_paciente' => 'Notificación de Cita',
                    'mensaje_paciente' => "Hay una actualización en su cita del {$fechaFormateada}.",
                    'titulo_medico' => 'Notificación de Cita',
                    'mensaje_medico' => "Hay una actualización en la cita con {$cita['nombre_paciente']}.",
                    'asunto_email' => 'Actualización de Cita Médica',
                    'asunto_email_medico' => 'Actualización de Cita Médica',
                    'contenido_email_paciente' => $this->getPlantillaEmailPaciente('actualizada', $cita),
                    'contenido_email_medico' => $this->getPlantillaEmailMedico('actualizada', $cita)
                ];
        }
    }
    
    private function enviarEmailCita($email, $nombreDestinatario, $asunto, $contenidoHtml, $cita) {
        try {
            return enviarEmailPersonalizado($email, $nombreDestinatario, $asunto, $contenidoHtml);
        } catch (Exception $e) {
            error_log("Error enviando email de cita a {$email}: " . $e->getMessage());
            return false;
        }
    }
    
    private function getPlantillaEmailPaciente($tipoEvento, $cita) {
        $fechaFormateada = date('l, d \d\e F \d\e Y', strtotime($cita['fecha_cita']));
        $horaFormateada = date('H:i', strtotime($cita['hora_cita']));
        
        $iconos = [
            'creada' => '✅',
            'modificada' => '📝',
            'cancelada' => '❌',
            'confirmada' => '✅',
            'actualizada' => '📋'
        ];
        
        $titulos = [
            'creada' => 'Cita Médica Agendada',
            'modificada' => 'Cita Médica Modificada',
            'cancelada' => 'Cita Médica Cancelada',
            'confirmada' => 'Cita Médica Confirmada',
            'actualizada' => 'Actualización de Cita'
        ];
        
        $icono = $iconos[$tipoEvento] ?? '📋';
        $titulo = $titulos[$tipoEvento] ?? 'Notificación de Cita';
        
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$titulo}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .cita-info { background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0; }
                .footer { background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 14px; }
                .btn { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .success { color: #28a745; }
                .warning { color: #ffc107; }
                .danger { color: #dc3545; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$icono} {$titulo}</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <strong>{$cita['nombre_paciente']}</strong>,</p>
                    
                    " . $this->getContenidoSegunTipo($tipoEvento) . "
                    
                    <div class='cita-info'>
                        <h3>📋 Detalles de la Cita</h3>
                        <p><strong>📅 Fecha:</strong> {$fechaFormateada}</p>
                        <p><strong>🕒 Hora:</strong> {$horaFormateada}</p>
                        <p><strong>👨‍⚕️ Médico:</strong> {$cita['nombre_medico']}</p>
                        <p><strong>🏥 Especialidad:</strong> {$cita['especialidad']}</p>
                        <p><strong>📍 Sucursal:</strong> {$cita['sucursal']}</p>
                        <p><strong>💳 Tipo:</strong> " . ucfirst($cita['tipo_cita']) . "</p>
                    </div>
                    
                    " . $this->getInstruccionesSegunTipo($tipoEvento) . "
                    
                    <p>Si tiene alguna consulta, no dude en contactarnos.</p>
                    
                    <p>Saludos cordiales,<br>
                    <strong>Equipo de Clínica Médica</strong></p>
                </div>
                <div class='footer'>
                    <p>© 2025 Clínica Médica Integral. Todos los derechos reservados.</p>
                    <p>📞 +593-2-2234567 | 📧 info@clinica.ec</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function getPlantillaEmailMedico($tipoEvento, $cita) {
        $fechaFormateada = date('l, d \d\e F \d\e Y', strtotime($cita['fecha_cita']));
        $horaFormateada = date('H:i', strtotime($cita['hora_cita']));
        
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Notificación de Cita - Dr/a. {$cita['nombre_medico']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .paciente-info { background-color: #e9ecef; padding: 20px; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>👨‍⚕️ Notificación de Cita</h1>
                </div>
                <div class='content'>
                    <p>Dr/a. <strong>{$cita['nombre_medico']}</strong>,</p>
                    
                    <p>Le informamos sobre una actualización en su agenda médica:</p>
                    
                    <div class='paciente-info'>
                        <h3>👤 Información del Paciente</h3>
                        <p><strong>Nombre:</strong> {$cita['nombre_paciente']}</p>
                        <p><strong>📅 Fecha:</strong> {$fechaFormateada}</p>
                        <p><strong>🕒 Hora:</strong> {$horaFormateada}</p>
                        <p><strong>🏥 Sucursal:</strong> {$cita['sucursal']}</p>
                        <p><strong>💳 Tipo:</strong> " . ucfirst($cita['tipo_cita']) . "</p>
                        " . (!empty($cita['motivo_consulta']) ? "<p><strong>📝 Motivo:</strong> {$cita['motivo_consulta']}</p>" : "") . "
                    </div>
                    
                    <p>Puede revisar más detalles en el sistema de gestión.</p>
                    
                    <p>Atentamente,<br>
                    <strong>Sistema de Gestión Clínica</strong></p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function getContenidoSegunTipo($tipo) {
        switch ($tipo) {
            case 'creada':
                return "<p class='success'>Su cita médica ha sido <strong>agendada exitosamente</strong>.</p>";
            case 'modificada':
                return "<p class='warning'>Su cita médica ha sido <strong>modificada</strong>. Por favor revise los nuevos detalles:</p>";
            case 'cancelada':
                return "<p class='danger'>Lamentamos informarle que su cita médica ha sido <strong>cancelada</strong>.</p>";
            case 'confirmada':
                return "<p class='success'>Su cita médica ha sido <strong>confirmada</strong>.</p>";
            default:
                return "<p>Hay una actualización en su cita médica.</p>";
        }
    }
    
    private function getInstruccionesSegunTipo($tipo) {
        switch ($tipo) {
            case 'creada':
            case 'confirmada':
                return "
                <div style='background-color: #d4edda; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <h4>📋 Instrucciones importantes:</h4>
                    <ul>
                        <li>Por favor llegue <strong>15 minutos antes</strong> de su cita</li>
                        <li>Traiga su documento de identidad</li>
                        <li>Si tiene exámenes previos, tráigalos</li>
                        <li>En caso de no poder asistir, cancele con al menos 24h de anticipación</li>
                    </ul>
                </div>";
            case 'modificada':
                return "
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <p><strong>⚠️ Importante:</strong> Los detalles de su cita han cambiado. Por favor tome nota de la nueva fecha y hora.</p>
                </div>";
            case 'cancelada':
                return "
                <div style='background-color: #f8d7da; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                    <p><strong>📞 Para reagendar:</strong> Por favor contacte a nuestra clínica al +593-2-2234567 o puede agendar una nueva cita a través de nuestro sistema.</p>
                </div>";
            default:
                return "";
        }
    }
    
    public function getNotificacionesUsuario($usuarioId, $limit = 10) {
        $sql = "SELECT * FROM notificaciones 
                WHERE id_usuario_destinatario = :usuario_id 
                ORDER BY fecha_creacion DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function marcarComoLeida($notificacionId, $usuarioId) {
        $sql = "UPDATE notificaciones 
                SET leida = 1, fecha_lectura = NOW() 
                WHERE id_notificacion = :notificacion_id 
                AND id_usuario_destinatario = :usuario_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'notificacion_id' => $notificacionId,
            'usuario_id' => $usuarioId
        ]);
        
        return $stmt->rowCount() > 0;
    }
}
?>