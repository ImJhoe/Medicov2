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

// Obtener datos actuales del usuario
$usuario = $userModel->getUserById($_SESSION['user_id']);

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?: null,
            'genero' => $_POST['genero'] ?: null,
            'telefono' => trim($_POST['telefono'] ?? '') ?: null,
            'direccion' => trim($_POST['direccion'] ?? '') ?: null,
            'cedula' => trim($_POST['cedula'] ?? '') ?: null
        ];

        // Validaciones básicas
        if (empty($data['username']) || empty($data['email']) ||
                empty($data['nombre']) || empty($data['apellido'])) {
            throw new Exception("Por favor complete todos los campos obligatorios");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email no tiene un formato válido");
        }

        // Validar cédula ecuatoriana si se proporciona
        if (!empty($data['cedula']) && !preg_match('/^[0-9]{10}$/', $data['cedula'])) {
            throw new Exception("La cédula debe tener 10 dígitos");
        }

        // Actualizar datos personales
        $userModel->updateUserProfile($_SESSION['user_id'], $data);

        // Actualizar datos en sesión
        $_SESSION['nombre_completo'] = $data['nombre'] . ' ' . $data['apellido'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['email'] = $data['email'];

        $success = "Datos actualizados exitosamente";

        // Recargar datos del usuario
        $usuario = $userModel->getUserById($_SESSION['user_id']);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Función para calcular edad
function calcularEdad($fechaNacimiento) {
    if (!$fechaNacimiento)
        return null;
    $hoy = new DateTime();
    $nacimiento = new DateTime($fechaNacimiento);
    return $hoy->diff($nacimiento)->y;
}

include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>
<script src="js/bloquear.js"></script>
<style>
    body {
        background-color: #ffffff;
        color: #000000;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .profile-container {
        padding: 20px;
        max-width: 800px;
        margin: auto;
    }

    .profile-header {
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

    .user-card {
        background: #f2f2f2;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .avatar-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: #fff;
    }

    .bg-imperial { background-color: #000000; }
    .bg-medical { background-color: #000000; }
    .bg-tech { background-color: #000000; }
    .bg-primary { background-color: #000000; }

    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: green;
        position: relative;
        top: -25px;
        left: 65px;
    }

    .user-info h2 {
        font-size: 24px;
        margin: 10px 0;
    }

    .username {
        color: #555555;
    }

    .user-badge span {
        background: #000000;
        color: #ffffff;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
    }

    .user-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 15px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-weight: bold;
        font-size: 18px;
    }

    .profile-nav ul {
        list-style: none;
        padding: 0;
    }

    .profile-nav li {
        margin: 10px 0;
    }

    .profile-nav a {
        text-decoration: none;
        color: #000000;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .account-info {
        background: #f2f2f2;
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin: 10px 0;
    }

    .label {
        font-weight: bold;
    }

    .form-container {
        background: #f2f2f2;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .form-header h2 {
        margin-top: 0;
    }

    fieldset {
        border: 1px solid #ccc;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    legend {
        font-weight: bold;
        background: #000000;
        color: #ffffff;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="date"],
    select,
    textarea {
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

    .input-with-icon i {
        position: absolute;
        left: 10px;
        top: 10px;
        color: #888;
    }

    input[type="text"], input[type="email"], input[type="tel"], input[type="date"], select, textarea {
        padding-left: 30px;
    }

    .age-display {
        margin-top: 5px;
        font-size: 14px;
        color: #555;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .widget {
        background: #f2f2f2;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 15px;
    }

    .widget i {
        font-size: 32px;
        color: #000000;
    }

    .widget h3 {
        margin: 10px 0 5px;
    }
</style>

<!-- Contenido sin cambios estructurales (solo se ha modificado el estilo) -->
<div class="profile-container">
    <header class="profile-header">
        <div class="header-content">
            <div class="title-group">
                <h1>Mi Espacio Personal</h1>
                <p>Configuración y detalles de tu cuenta</p>
            </div>
            <div class="header-actions">
                <a href="index.php?action=dashboard" class="btn btn-glass">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button class="btn btn-primary btn-edit">
                    <i class=""></i> Editar Perfil
                </button>
            </div>
        </div>
    </header>

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

    <aside class="profile-sidebar">
        <!-- Información de usuario -->
        <div class="user-card">
            <div class="">
                <div class="">
                    <i class=""></i>
                </div>
                <div class=""></div>
            </div>
            <div class="user-info">
                <h2>Juan Pérez</h2>
                <p class="username">@juanperez</p>
                <div class="user-badge">
                    <span class="badge-imperial">Paciente</span>
                </div>
            </div>
            <div class="user-stats">
                <div class="stat-item">
                    <span class="stat-value">ID-12345</span>
                    <span class="stat-label">Identificación</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">30</span>
                    <span class="stat-label">Años</span>
                </div>
            </div>
        </div>

        <nav class="profile-nav">
            <ul>
                <li class="active"><a href="#"><i class="fas fa-user-edit"></i> Datos Personales</a></li>
                <li><a href="index.php?action=perfil/password"><i class="fas fa-key"></i> Seguridad</a></li>
                <li><a href="index.php?action=perfil/notificaciones"><i class="fas fa-bell"></i> Notificaciones</a></li>
                <li><a href="index.php?action=perfil/seguridad"><i class="fas fa-shield-alt"></i> Privacidad</a></li>
            </ul>
        </nav>

        <div class="account-info">
            <h3><i class="fas fa-info-circle"></i> Detalles de Cuenta</h3>
            <div class="info-item">
                <span class="label">Registro</span>
                <span class="value">01 Ene 2024</span>
            </div>
            <div class="info-item">
                <span class="label">Último acceso</span>
                <span class="value">Nunca</span>
            </div>
            <div class="info-item">
                <span class="label">Estado</span>
                <span class="value active">Activo</span>
            </div>
        </div>
    </aside>

    <main class="profile-content">
        <div class="form-container" id="profileForm">
            <div class="form-header">
                <h2><i class=""></i> Configuración de Perfil</h2>
                <p>Actualiza tu información personal</p>
            </div>

            <form method="POST" id="userForm" class="profile-form">
                <!-- Sección 1 -->
                <fieldset class="form-section">
                    <legend><i class=""></i> Identificación</legend>
                    <div class="form-group">
                        <label>Nombre de Usuario *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-at"></i>
                            <input type="text" name="username" value="juanperez" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="juan@example.com" required>
                        </div>
                    </div>
                </fieldset>

                <!-- Sección 2 -->
                <fieldset class="form-section">
                    <legend><i class=""></i> Información Personal</legend>
                    <div class="form-group">
                        <label>Nombre *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-signature"></i>
                            <input type="text" name="nombre" value="Juan" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Apellido *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-signature"></i>
                            <input type="text" name="apellido" value="Pérez" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cédula</label>
                        <div class="input-with-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="cedula" value="1234567890" pattern="[0-9]{10}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="telefono" value="0987654321">
                        </div>
                    </div>
                </fieldset>

                <!-- Sección 3 -->
                <fieldset class="form-section">
                    <legend><i class=""></i> Detalles Adicionales</legend>
                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <div class="input-with-icon">
                            <i class="fas fa-calendar-day"></i>
                            <input type="date" name="fecha_nacimiento" value="1990-01-01">
                        </div>
                        <div class="age-display">Edad: 34 años</div>
                    </div>
                    <div class="form-group">
                        <label>Género</label>
                        <div class="input-with-icon">
                            <i class="fas fa-venus-mars"></i>
                            <select name="genero">
                                <option value="">Seleccionar...</option>
                                <option value="M" selected>Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label>Dirección</label>
                        <div class="input-with-icon">
                            <i class="fas fa-map-marked-alt"></i>
                            <textarea name="direccion" rows="2">Calle Principal, Ciudad</textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Restablecer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- <div class="profile-widgets">
            <div class="widget">
                <i class="fas fa-shield-alt"></i>
                <h3>Cuenta Protegida</h3>
                <p>Tus datos están encriptados y seguros</p>
            </div>
            <div class="widget">
                <i class="fas fa-history"></i>
                <h3>Actividad Reciente</h3>
                <p>Último acceso: Ninguno</p>
            </div> -->
        </div>
    </main>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validación en tiempo real
        const form = document.getElementById('userForm');
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearValidation);
        });

        // Validación de cédula ecuatoriana
        const cedulaInput = document.querySelector('input[name="cedula"]');
        if (cedulaInput) {
            cedulaInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').substring(0, 10);
                validateCedula(this);
            });
        }

        // Validación del formulario al enviar
        form.addEventListener('submit', function (e) {
            if (!validateForm()) {
                e.preventDefault();
                showAlert('Por favor, corrige los errores antes de continuar.', 'warning');
            } else {
                // Mostrar loading en el botón
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
                submitBtn.disabled = true;
            }
        });
    });

    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();

        // Limpiar clases previas
        field.classList.remove('is-valid', 'is-invalid');

        // Validaciones específicas
        switch (field.name) {
            case 'username':
                if (value.length < 3) {
                    setInvalid(field, 'El nombre de usuario debe tener al menos 3 caracteres');
                } else {
                    setValid(field);
                }
                break;

            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    setInvalid(field, 'Ingresa un email válido');
                } else {
                    setValid(field);
                }
                break;

            case 'nombre':
            case 'apellido':
                if (value.length < 2) {
                    setInvalid(field, 'Debe tener al menos 2 caracteres');
                } else {
                    setValid(field);
                }
                break;

            case 'telefono':
                if (value && !/^[0-9+\-\s()]{7,15}$/.test(value)) {
                    setInvalid(field, 'Formato de teléfono inválido');
                } else if (value) {
                    setValid(field);
                }
                break;

        }
    }

    function clearValidation(e) {
        const field = e.target;
        field.classList.remove('is-valid', 'is-invalid');
        removeFieldError(field);
    }

    function validateCedula(field) {
        const cedula = field.value;

        if (cedula.length === 0) {
            field.classList.remove('is-valid', 'is-invalid');
            removeFieldError(field);
            return;
        }

        if (cedula.length !== 10) {
            setInvalid(field, 'La cédula debe tener 10 dígitos');
            return;
        }

        // Validación del algoritmo de cédula ecuatoriana
        const provincia = parseInt(cedula.substring(0, 2));
        if (provincia < 1 || provincia > 24) {
            setInvalid(field, 'Código de provincia inválido');
            return;
        }

        const tercerDigito = parseInt(cedula.charAt(2));
        if (tercerDigito > 5) {
            setInvalid(field, 'Tercer dígito de cédula inválido');
            return;
        }

        // Algoritmo de validación
        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        let suma = 0;

        for (let i = 0; i < 9; i++) {
            let valor = parseInt(cedula.charAt(i)) * coeficientes[i];
            if (valor > 9)
                valor -= 9;
            suma += valor;
        }

        const digitoVerificador = parseInt(cedula.charAt(9));
        const residuo = suma % 10;
        const resultado = residuo === 0 ? 0 : 10 - residuo;

        if (resultado !== digitoVerificador) {
            setInvalid(field, 'Número de cédula inválido');
        } else {
            setValid(field);
        }
    }

    function setValid(field) {
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');
        removeFieldError(field);
    }

    function setInvalid(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        showFieldError(field, message);
    }

    function showFieldError(field, message) {
        removeFieldError(field);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    function removeFieldError(field) {
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    function validateForm() {
        const requiredFields = ['username', 'email', 'nombre', 'apellido'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (!field.value.trim()) {
                setInvalid(field, 'Este campo es obligatorio');
                isValid = false;
            }
        });

        // Validar email
        const emailField = document.querySelector('[name="email"]');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            setInvalid(emailField, 'Email inválido');
            isValid = false;
        }

        // Validar cédula si está llena
        const cedulaField = document.querySelector('[name="cedula"]');
        if (cedulaField.value && cedulaField.classList.contains('is-invalid')) {
            isValid = false;
        }

        return isValid;
    }

    function resetForm() {
        if (confirm('¿Estás seguro de que quieres restablecer todos los cambios?')) {
            document.getElementById('userForm').reset();

            // Limpiar validaciones
            const inputs = document.querySelectorAll('#userForm input, #userForm select, #userForm textarea');
            inputs.forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                removeFieldError(input);
            });

            showAlert('Formulario restablecido', 'info');
        }
    }

    function showAlert(message, type = 'info') {
        // Crear alerta temporal
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show border-0 shadow-sm`;
        alertDiv.innerHTML = `
           <div class="d-flex align-items-center">
               <i class="fas fa-${getAlertIcon(type)} me-3 fs-5"></i>
               <div>${message}</div>
           </div>
           <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
       `;

        // Insertar al inicio del contenedor
        const container = document.querySelector('.container-fluid .row .col-12');
        container.insertBefore(alertDiv, container.children[1]);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-triangle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    // Funciones para mejorar la experiencia del usuario
    function formatPhone(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 4) {
            value = value.substring(0, 4) + '-' + value.substring(4, 10);
        }
        input.value = value;
    }

    function calculateAge() {
        const birthDate = document.querySelector('[name="fecha_nacimiento"]').value;
        if (birthDate) {
            const today = new Date();
            const birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            const monthDiff = today.getMonth() - birth.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }

            // Mostrar edad calculada
            const ageDisplay = document.querySelector('.age-display');
            if (ageDisplay) {
                ageDisplay.textContent = `Edad: ${age} años`;
            }
        }
    }

    // Eventos adicionales
    document.addEventListener('DOMContentLoaded', function () {
        // Calcular edad al cambiar fecha de nacimiento
        const birthDateInput = document.querySelector('[name="fecha_nacimiento"]');
        if (birthDateInput) {
            birthDateInput.addEventListener('change', calculateAge);
        }

        // Formatear teléfono
        const phoneInput = document.querySelector('[name="telefono"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                formatPhone(this);
            });
        }

        // Animaciones de entrada
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Efecto de escritura para el nombre
        const userName = document.querySelector('.user-profile-card h4');
        if (userName) {
            const text = userName.textContent;
            userName.textContent = '';
            let i = 0;

            function typeWriter() {
                if (i < text.length) {
                    userName.textContent += text.charAt(i);
                    i++;
                    setTimeout(typeWriter, 50);
                }
            }

            setTimeout(typeWriter, 1000);
        }

        // Tooltip para botones
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Función para preview de cambios antes de guardar
    function previewChanges() {
        const form = document.getElementById('userForm');
        const formData = new FormData(form);

        let changes = [];
        const originalData = {
            username: '<?php echo htmlspecialchars($usuario['username']); ?>',
            email: '<?php echo htmlspecialchars($usuario['email']); ?>',
            nombre: '<?php echo htmlspecialchars($usuario['nombre']); ?>',
            apellido: '<?php echo htmlspecialchars($usuario['apellido']); ?>'
        };

        for (let [key, value] of formData.entries()) {
            if (originalData[key] && originalData[key] !== value) {
                changes.push(`${key}: "${originalData[key]}" → "${value}"`);
            }
        }

        if (changes.length > 0) {
            const changesList = changes.join('\n');
            return confirm(`Se realizarán los siguientes cambios:\n\n${changesList}\n\n¿Continuar?`);
        }

        return true;
    }

    // Detectar cambios no guardados
    window.addEventListener('beforeunload', function (e) {
        const form = document.getElementById('userForm');
        const formData = new FormData(form);
        let hasChanges = false;

        // Verificar si hay cambios
        const originalData = {
            username: '<?php echo htmlspecialchars($usuario['username']); ?>',
            email: '<?php echo htmlspecialchars($usuario['email']); ?>',
            nombre: '<?php echo htmlspecialchars($usuario['nombre']); ?>',
            apellido: '<?php echo htmlspecialchars($usuario['apellido']); ?>'
        };

        for (let [key, value] of formData.entries()) {
            if (originalData[key] && originalData[key] !== value.trim()) {
                hasChanges = true;
                break;
            }
        }

        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.';
        }
    });
</script>

</body>
</html>