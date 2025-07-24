<?php
require_once 'models/User.php';

// Redirigir si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?action=dashboard');
    exit;
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = "Por favor complete todos los campos";
    } else {
        $userModel = new User();
        $user = $userModel->login($username, $password);
        
        if ($user) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre_completo'] = $user['nombre'] . ' ' . $user['apellido'];
            $_SESSION['role_id'] = $user['id_rol'];
            $_SESSION['role_name'] = $user['nombre_rol'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['requiere_cambio_contrasena'] = $user['requiere_cambio_contrasena'];
            
            // Verificar si requiere cambio de contraseña
            if ($user['requiere_cambio_contrasena'] == 1) {
                header('Location: index.php?action=auth/change-password');
            } else {
                header('Location: index.php?action=dashboard');
            }
            exit;
        } else {
            $error = "Credenciales incorrectas";
        }
    }
}

include 'views/includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="js/bloquear.js"></script>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <style>
        /* Variables de colores */
        :root {
            --pure-white: #ffffff;
            --soft-white: #fafafa;
            --light-grey: #e8e8e8;
            --medium-grey: #666666;
            --dark-grey: #333333;
            --pure-black: #000000;
            --shadow-light: rgba(0, 0, 0, 0.08);
            --shadow-medium: rgba(0, 0, 0, 0.15);
            --error-bg: #fee;
            --error-text: #c33;
        }

        /* Reset y configuración general */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Contenedor principal */
        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--pure-white);
            border: 1px solid var(--light-grey);
            box-shadow: 
                0 4px 25px var(--shadow-light),
                0 8px 50px var(--shadow-medium);
            animation: slideUp 0.6s ease-out;
        }

        /* Header del login */
        .login-header {
            background: var(--pure-black);
            color: var(--pure-white);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--pure-white), transparent);
        }

        .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .login-title {
            font-size: 1.8rem;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            opacity: 0.8;
            font-weight: 300;
        }

        /* Cuerpo del formulario */
        .login-body {
            padding: 40px 30px;
        }

        /* Mensaje de error */
        .error-message {
            background: var(--error-bg);
            color: var(--error-text);
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid var(--error-text);
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .error-message i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        /* Grupos de input */
        .input-group {
            margin-bottom: 25px;
        }

        .input-label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-grey);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
            background: var(--soft-white);
            border: 2px solid var(--light-grey);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .input-wrapper:focus-within {
            border-color: var(--pure-black);
            background: var(--pure-white);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            color: var(--medium-grey);
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--pure-black);
        }

        .form-input {
            width: 100%;
            padding: 18px 50px;
            border: none;
            background: transparent;
            font-size: 1rem;
            color: var(--dark-grey);
            outline: none;
            height: 56px;
        }

        .form-input::placeholder {
            color: var(--medium-grey);
            opacity: 0.7;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            background: transparent;
            border: none;
            color: var(--medium-grey);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
            transition: color 0.3s ease;
            z-index: 2;
            height: 30px;
            width: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: var(--pure-black);
        }

        /* Botón de login */
        .login-button {
            width: 100%;
            background: var(--pure-black);
            color: var(--pure-white);
            border: none;
            padding: 18px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            background: var(--dark-grey);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px var(--shadow-medium);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .button-icon {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .login-button:hover .button-icon {
            transform: translateX(3px);
        }

        /* Footer */
        .login-footer {
            padding: 20px 30px;
            background: var(--soft-white);
            border-top: 1px solid var(--light-grey);
            text-align: center;
        }

        .footer-text {
            color: var(--medium-grey);
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .footer-links a {
            color: var(--medium-grey);
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--pure-black);
        }

        /* Animaciones */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-header,
            .login-body {
                padding: 30px 25px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <i class="fa fa-user-md" aria-hidden="true" style="font-size: 100px;"></i>
            <h1 class="login-title">ACCESO</h1>
            <p class="login-subtitle">Sistema de Gestión</p>
        </div>

        <!-- Cuerpo del formulario -->
        <div class="login-body">
            <!-- Mensaje de error (PHP) -->
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="username" class="input-label">Usuario o Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Ingrese su usuario"
                            required
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            class="form-input"
                            autocomplete="username"
                        >
                    </div>
                </div>
                <div> <label for="password" class="input-label">Contraseña</label></div>
                <div class="input-group">
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Ingrese su contraseña"
                            required
                            class="form-input"
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    <span>Iniciar Sesión</span>
                    <i class="fas fa-arrow-right button-icon"></i>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p class="footer-text">© 2024 Sistema de Gestión</p>
            <div class="footer-links">
                <a href="#" onclick="return false;">
                    <i class="fas fa-question-circle"></i>
                    Ayuda
                </a>
                <a href="#" onclick="return false;">
                    <i class="fas fa-shield-alt"></i>
                    Soporte
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Animación sutil en los inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>

</body>
</html>