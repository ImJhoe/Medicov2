<?php
// controllers/NotificacionController.php
require_once 'models/Notificacion.php';

class NotificacionController {
    private $notificacionModel;
    
    public function __construct() {
        $this->notificacionModel = new Notificacion();
    }
    
    public function index() {
        // Ver notificaciones del usuario
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        $notificaciones = $this->notificacionModel->getNotificacionesUsuario($_SESSION['user_id'], 20);
        
        include 'views/notificaciones/index.php';
    }
    
    public function marcarLeida() {
        // Marcar una notificación como leída
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificacionId = $_POST['id'] ?? 0;
            
            try {
                $resultado = $this->notificacionModel->marcarComoLeida($notificacionId, $_SESSION['user_id']);
                
                if ($resultado) {
                    echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        exit;
    }
    
    public function marcarTodasLeidas() {
        // Marcar todas las notificaciones como leídas
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $sql = "UPDATE notificaciones 
                        SET leida = 1, fecha_lectura = NOW() 
                        WHERE id_usuario_destinatario = :usuario_id AND leida = 0";
                
                $database = new Database();
                $db = $database->getConnection();
                $stmt = $db->prepare($sql);
                $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
                
                echo json_encode(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        exit;
    }
    
    public function getContadorNoLeidas() {
        // AJAX - Obtener contador de notificaciones no leídas para mostrar en navbar
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'count' => 0]);
            exit;
        }
        
        try {
            $sql = "SELECT COUNT(*) as no_leidas 
                    FROM notificaciones 
                    WHERE id_usuario_destinatario = :usuario_id AND leida = 0";
            
            $database = new Database();
            $db = $database->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'count' => $result['no_leidas']]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'count' => 0]);
        }
        exit;
    }
    // AGREGAR estos métodos al final de NotificacionController.php

public function contador() {
    // AJAX - Obtener contador de notificaciones no leídas
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'count' => 0]);
        exit;
    }
    
    try {
        $sql = "SELECT COUNT(*) as no_leidas 
                FROM notificaciones 
                WHERE id_usuario_destinatario = :usuario_id AND leida = 0";
        
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'count' => intval($result['no_leidas'])]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'count' => 0]);
    }
    exit;
}

public function recientes() {
    // AJAX - Obtener notificaciones recientes para dropdown
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'notificaciones' => []]);
        exit;
    }
    
    try {
        $notificaciones = $this->notificacionModel->getNotificacionesUsuario($_SESSION['user_id'], 5);
        echo json_encode(['success' => true, 'notificaciones' => $notificaciones]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'notificaciones' => []]);
    }
    exit;
}
}
?>