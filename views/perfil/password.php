<?php
require_once 'models/User.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
   header('Location: index.php?action=login');
   exit;
}

$userModel = new User();
$error = '';
$success = '';

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $currentPassword = trim($_POST['current_password'] ?? '');
   $newPassword = trim($_POST['new_password'] ?? '');
   $confirmPassword = trim($_POST['confirm_password'] ?? '');
   
   try {
       // Validaciones
       if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
           throw new Exception("Por favor complete todos los campos");
       }
       
       // Validar que la nueva contraseña tenga mínimo 6 caracteres
       if (strlen($newPassword) < 6) {
           throw new Exception("La nueva contraseña debe tener al menos 6 caracteres");
       }
       
       if ($newPassword !== $confirmPassword) {
           throw new Exception("Las contraseñas no coinciden");
       }
       
       // Verificar contraseña actual
       $usuario = $userModel->getUserById($_SESSION['user_id']);
       if (base64_decode($usuario['password']) !== $currentPassword) {
           throw new Exception("La contraseña actual es incorrecta");
       }
       
       // Cambiar contraseña
       if ($userModel->changeUserPassword($_SESSION['user_id'], $newPassword)) {
           $success = "Contraseña cambiada exitosamente";
       } else {
           throw new Exception("Error al cambiar la contraseña");
       }
       
   } catch (Exception $e) {
       $error = $e->getMessage();
   }
}

include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>
<style>
    body {
        background-color: #ffffff;
        color: #000000;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .password-change-container {
        padding: 20px;
        max-width: 800px;
        margin: auto;
    }

    .security-header {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-glass, .btn-primary, .btn-secondary {
        background: #000000;
        color: #ffffff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        margin: 5px;
    }

    .btn-glass:hover, .btn-primary:hover, .btn-secondary:hover {
        background: #333333;
    }

    .floating-message {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .floating-message.error {
        background-color: #ffe6e6;
        color: #cc0000;
    }

    .floating-message.success {
        background-color: #e6ffe6;
        color: #006600;
    }

    .close-btn {
        background: transparent;
        border: none;
        font-size: 18px;
        cursor: pointer;
    }

    .security-sidebar {
        margin-bottom: 20px;
    }

    .security-card {
        background: #f2f2f2;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .security-nav ul {
        list-style: none;
        padding: 0;
    }

    .security-nav li {
        margin: 10px 0;
    }

    .security-nav a {
        text-decoration: none;
        color: #000000;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nav-badge {
        background: #000000;
        color: #fff;
        padding: 2px 6px;
        font-size: 12px;
        border-radius: 3px;
    }

    .security-tips ul {
        padding-left: 20px;
    }

    .password-form-container {
        background: #f2f2f2;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .form-header h2 {
        margin-top: 0;
    }

    .password-alert {
        background: #ebebeb;
        border-left: 4px solid #000000;
        padding: 15px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .password-alert i {
        font-size: 24px;
        color: #000000;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #ffffff;
        color: #000000;
    }

    .input-with-icon {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 6px;
        background: transparent;
        border: none;
        cursor: pointer;
    }

    .password-strength {
        margin-top: 5px;
    }

    .strength-meter {
        height: 6px;
        background: #ddd;
        border-radius: 3px;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        width: 0;
        background: #000000;
        transition: width 0.3s ease;
    }

    .password-match {
        margin-top: 5px;
        font-size: 14px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .security-widget {
        background: #f2f2f2;
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .widget-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 5px 0;
    }

    .status-value {
        margin-left: auto;
        font-weight: bold;
    }

    .status-value.inactive {
        color: red;
    }
</style>

<script src="js/bloquear.js"></script>

<div class="password-change-container">
    <!-- Header -->
    <header class="security-header">
        <div class="header-content">
            <div class="title-group">
                <i class="fas fa-shield-alt"></i>
                <h1>Seguridad de Cuenta</h1>
                <p>Protege tu acceso con una contraseña segura</p>
            </div>
            <div class="header-actions">
                <a href="index.php?action=dashboard" class="btn btn-glass">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </header>

    <!-- Mensajes flotantes -->
    <?php if (!empty($error)): ?>
        <div class="floating-message error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
            <button class="close-btn">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="floating-message success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success; ?></span>
            <button class="close-btn">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <aside class="security-sidebar">
        <div class="security-card">
            <h3><i class="fas fa-user-lock"></i> Configuración de Seguridad</h3>
            <nav class="security-nav">
                <ul>
                    <li>
                        <a href="index.php?action=perfil/datos">
                            <i class="fas fa-id-card"></i>
                            <span>Datos Personales</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="index.php?action=perfil/password">
                            <i class="fas fa-key"></i>
                            <span>Cambiar Contraseña</span>
                            <span class="nav-badge">Actual</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=perfil/notificaciones">
                            <i class="fas fa-bell"></i>
                            <span>Notificaciones</span>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?action=perfil/seguridad">
                            <i class="fas fa-shield-alt"></i>
                            <span>Privacidad</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="security-tips">
                <h4><i class="fas fa-lightbulb"></i> Consejos de Seguridad</h4>
                <ul>
                    <li>Usa una contraseña única</li>
                    <li>Combina letras, números y símbolos</li>
                    <li>No uses información personal</li>
                    <li>Cambia tu contraseña periódicamente</li>
                </ul>
            </div>
        </div>
    </aside>

    <!-- Formulario principal -->
    <main class="security-content">
        <div class="password-form-container">
            <div class="form-header">
                <h2><i class="fas fa-key"></i> Cambiar Contraseña</h2>
                <p>Actualiza tus credenciales de acceso</p>
            </div>

            <div class="password-alert">
                <i class="fas fa-lock-open"></i>
                <div>
                    <h4>Requisitos de Seguridad</h4>
                    <p>La nueva contraseña debe tener al menos 6 caracteres. Para mayor seguridad, recomendamos usar mayúsculas, números y símbolos.</p>
                </div>
            </div>

            <form method="POST" id="passwordForm" class="security-form">
                <div class="form-group">
                    <label for="current_password">
                        <i class="fas fa-lock"></i> Contraseña Actual
                        <span class="required">*</span>
                    </label>
                    <div class="input-with-icon">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">
                        <i class="fas fa-key"></i> Nueva Contraseña
                        <span class="required">*</span>
                    </label>
                    <div class="input-with-icon">
                        <input type="password" id="new_password" name="new_password" minlength="6" required oninput="validatePassword()">
                        <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <span id="passwordHelp">Ingresa al menos 6 caracteres</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-check-double"></i> Confirmar Contraseña
                        <span class="required">*</span>
                    </label>
                    <div class="input-with-icon">
                        <input type="password" id="confirm_password" name="confirm_password" required oninput="validatePasswordMatch()">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-match">
                        <span id="matchHelp"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> Actualizar Contraseña
                    </button>
                    <a href="index.php?action=perfil/datos" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Widget de seguridad -->
        <div class="security-widget">
            <div class="widget-header">
                <i class="fas fa-shield-alt"></i>
                <h3>Estado de Seguridad</h3>
            </div>
            <div class="widget-content">
                <div class="security-status">
                    <div class="status-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Autenticación en dos pasos</span>
                        <span class="status-value inactive">Inactivo</span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Último cambio de contraseña</span>
                        <span class="status-value"><?php echo date('d M Y'); ?></span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Actividad reciente</span>
                        <span class="status-value">Hoy, <?php echo date('H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>



<script>
   function validatePassword() {
       const newPassword = document.getElementById('new_password');
       const passwordHelp = document.getElementById('passwordHelp');
       const confirmPassword = document.getElementById('confirm_password');
       
       if (newPassword.value.length >= 6) {
           newPassword.classList.remove('is-invalid');
           newPassword.classList.add('is-valid');
           passwordHelp.innerHTML = '<i class="fas fa-check text-success"></i> Contraseña válida';
           passwordHelp.className = 'text-success';
       } else if (newPassword.value.length > 0) {
           newPassword.classList.remove('is-valid');
           newPassword.classList.add('is-invalid');
           passwordHelp.innerHTML = `<i class="fas fa-times text-danger"></i> ${newPassword.value.length}/6 caracteres mínimo`;
           passwordHelp.className = 'text-danger';
       } else {
           newPassword.classList.remove('is-valid', 'is-invalid');
           passwordHelp.innerHTML = 'Ingrese al menos 6 caracteres';
           passwordHelp.className = '';
       }
       
       // Limpiar confirmación si había algo
       if (confirmPassword.value) {
           validatePasswordMatch();
       }
       
       toggleSubmitButton();
   }
   
   function validatePasswordMatch() {
       const newPassword = document.getElementById('new_password');
       const confirmPassword = document.getElementById('confirm_password');
       const matchHelp = document.getElementById('matchHelp');
       
       if (confirmPassword.value && newPassword.value) {
           if (confirmPassword.value === newPassword.value) {
               confirmPassword.classList.remove('is-invalid');
               confirmPassword.classList.add('is-valid');
               matchHelp.innerHTML = '<i class="fas fa-check text-success"></i> Las contraseñas coinciden';
               matchHelp.className = 'text-success';
           } else {
               confirmPassword.classList.remove('is-valid');
               confirmPassword.classList.add('is-invalid');
               matchHelp.innerHTML = '<i class="fas fa-times text-danger"></i> Las contraseñas no coinciden';
               matchHelp.className = 'text-danger';
           }
       } else {
           confirmPassword.classList.remove('is-valid', 'is-invalid');
           matchHelp.innerHTML = '';
           matchHelp.className = '';
       }
       
       toggleSubmitButton();
   }
   
   function toggleSubmitButton() {
       const newPassword = document.getElementById('new_password');
       const confirmPassword = document.getElementById('confirm_password');
       const currentPassword = document.getElementById('current_password');
       const submitBtn = document.getElementById('submitBtn');
       
       const isNewPasswordValid = newPassword.value.length >= 6;
       const isPasswordMatch = confirmPassword.value === newPassword.value && confirmPassword.value !== '';
       const hasCurrentPassword = currentPassword.value.length > 0;
       
       if (isNewPasswordValid && isPasswordMatch && hasCurrentPassword) {
           submitBtn.disabled = false;
       } else {
           submitBtn.disabled = true;
       }
   }
   
   // Validar en tiempo real
   document.getElementById('current_password').addEventListener('input', toggleSubmitButton);
   
   // Prevenir envío si las validaciones no pasan
   document.getElementById('passwordForm').addEventListener('submit', function(e) {
       const newPassword = document.getElementById('new_password').value;
       const confirmPassword = document.getElementById('confirm_password').value;
       
       if (newPassword.length < 6) {
           e.preventDefault();
           alert('La nueva contraseña debe tener al menos 6 caracteres');
           return false;
       }
       
       if (newPassword !== confirmPassword) {
           e.preventDefault();
           alert('Las contraseñas no coinciden');
           return false;
       }
   });
</script>

</body>
</html>