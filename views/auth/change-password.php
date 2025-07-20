<?php
require_once 'models/User.php';

// Verificar que esté logueado y requiera cambio
if (!isset($_SESSION['user_id']) || $_SESSION['requiere_cambio_contrasena'] != 1) {
    header('Location: index.php?action=dashboard');
    exit;
}

$error = '';
$success = '';

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Por favor complete todos los campos";
    } elseif (strlen($newPassword) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Las contraseñas no coinciden";
    } else {
        $userModel = new User();
        if ($userModel->changePassword($_SESSION['user_id'], $newPassword)) {
            $_SESSION['requiere_cambio_contrasena'] = 0;
            $success = "Contraseña cambiada exitosamente";
            // Redirigir después de 2 segundos
            header("refresh:2;url=index.php?action=dashboard");
        } else {
            $error = "Error al cambiar la contraseña";
        }
    }
}

include 'views/includes/header.php';
?>

<script src="js/bloquear.js"></script>
<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
        font-family: Arial, sans-serif;
    }

    .password-change-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }

    .password-change-card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
        overflow: hidden;
    }

    .password-change-header {
        background-color: #000000;
        color: #ffffff;
        padding: 20px;
        text-align: center;
    }

    .password-change-header h4 {
        margin: 0 0 10px;
        font-size: 20px;
    }

    .password-change-header p {
        margin: 0;
        font-size: 14px;
        opacity: 0.8;
    }

    .password-change-body {
        padding: 20px;
        background-color: #fafafa;
    }

    .password-change-alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .password-change-alert.error {
        background-color: #ffe9e9;
        color: #a00;
    }

    .password-change-alert.success {
        background-color: #e9ffe9;
        color: #0a0;
    }

    .password-change-alert.info {
        background-color: #eef6ff;
        color: #003366;
        border-left: 4px solid #000000;
        padding: 15px;
        margin-bottom: 20px;
    }

    .password-change-form .form-group {
        margin-bottom: 20px;
    }

    .password-change-form label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #000000;
    }

    .password-change-form input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-hint {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }

    .submit-button {
        width: 100%;
        padding: 12px;
        background-color: #000000;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .submit-button:hover {
        background-color: #333333;
    }

    .password-change-footer {
        background-color: #f0f0f0;
        padding: 15px;
        text-align: center;
        border-top: 1px solid #ddd;
    }

    .logout-link {
        color: #000000;
        text-decoration: none;
        font-size: 14px;
        display: inline-block;
        transition: color 0.3s ease;
    }

    .logout-link:hover {
        color: #555;
    }
</style>
<div class="password-change-container">
    <div class="password-change-card">
        <div class="password-change-header">
            <h4>Cambio de Contraseña Requerido</h4>
            <p>Por seguridad, debe cambiar su contraseña</p>
        </div>

        <div class="password-change-body">
            <?php if (!empty($error)): ?>
                <div class="password-change-alert error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="password-change-alert success">
                    <?php echo $success; ?>
                    <br><small>Redirigiendo al dashboard...</small>
                </div>
            <?php else: ?>

                <div class="password-change-alert info">
                    <strong>Hola, <?php echo $_SESSION['nombre_completo']; ?></strong><br>
                    Por motivos de seguridad, debe establecer una nueva contraseña antes de continuar.
                </div>

                <form method="POST" class="password-change-form">
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password"
                               placeholder="Mínimo 6 caracteres" required minlength="6">
                        <div class="form-hint">La contraseña debe tener al menos 6 caracteres</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repita su contraseña" required minlength="6">
                    </div>

                    <button type="submit" class="submit-button">Cambiar Contraseña</button>
                </form>

            <?php endif; ?>
        </div>

        <div class="password-change-footer">
            <a href="index.php?action=logout" class="logout-link">Cerrar Sesión</a>
        </div>
    </div>
</div>