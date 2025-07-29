<?php
echo "<h2>🔍 VERIFICACIÓN DE ARCHIVOS</h2>";

$archivos = [
    'controllers/ConsultaController.php',
    'models/Consulta.php', 
    'models/SignosVitales.php',
    'views/consultas/atender/index.php',
    'views/consultas/atender/form.php',
    'views/consultas/atender/historial.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ {$archivo} - EXISTE<br>";
    } else {
        echo "❌ {$archivo} - NO EXISTE<br>";
    }
}

echo "<h2>🔍 VERIFICACIÓN DE SESIÓN</h2>";
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>🔍 VERIFICACIÓN DE USUARIO EN BD</h2>";
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'config/database.php';
        $db = new Database();
        $db = $db->getConnection();
        $stmt = $db->prepare("SELECT id_usuario, username, nombre, apellido, id_rol FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        } else {
            echo "❌ Usuario no encontrado en BD";
        }
    } catch (Exception $e) {
        echo "❌ Error conectando a BD: " . $e->getMessage();
    }
}
?>