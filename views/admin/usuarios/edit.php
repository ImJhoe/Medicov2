<?php
require_once 'models/User.php';
require_once 'models/Role.php';
require_once 'models/Sucursal.php';
require_once 'models/Especialidad.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: index.php?action=dashboard');
    exit;
}

$userModel = new User();
$roleModel = new Role();
$sucursalModel = new Sucursal();
$especialidadModel = new Especialidad();

$userId = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Obtener usuario a editar
$user = $userModel->getUserById($userId);
if (!$user) {
    header('Location: index.php?action=admin/usuarios');
    exit;
}

// Obtener especialidades del médico si aplica
$medicoEspecialidades = [];
if ($user['id_rol'] == 3) {
    $especialidadesData = $userModel->getMedicoEspecialidades($userId);
    $medicoEspecialidades = array_column($especialidadesData, 'id_especialidad');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'cedula' => trim($_POST['cedula']) ?: null,
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?: null,
            'genero' => $_POST['genero'] ?: null,
            'telefono' => trim($_POST['telefono']) ?: null,
            'direccion' => trim($_POST['direccion']) ?: null,
            'id_rol' => $_POST['id_rol'],
            'id_sucursal' => $_POST['id_sucursal'] ?: null,
            'especialidades' => $_POST['especialidades'] ?? []
        ];

        // Validaciones básicas
        if (empty($data['username']) || empty($data['email']) ||
                empty($data['nombre']) || empty($data['apellido']) || empty($data['id_rol'])) {
            throw new Exception("Por favor complete todos los campos obligatorios");
        }

        if (!empty($data['password']) && strlen($data['password']) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email no tiene un formato válido");
        }

        // Validaciones específicas por rol
        if (in_array($data['id_rol'], [2, 3]) && empty($data['id_sucursal'])) {
            throw new Exception("Debe seleccionar una sucursal para este rol");
        }

        if ($data['id_rol'] == 3 && empty($data['especialidades'])) {
            throw new Exception("Debe seleccionar al menos una especialidad para los médicos");
        }

        $userModel->updateUser($userId, $data);
        $success = "Usuario actualizado exitosamente";

        // Recargar datos del usuario
        $user = $userModel->getUserById($userId);
        if ($user['id_rol'] == 3) {
            $especialidadesData = $userModel->getMedicoEspecialidades($userId);
            $medicoEspecialidades = array_column($especialidadesData, 'id_especialidad');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener datos para formulario
$roles = $roleModel->getAllRoles();
$sucursales = $sucursalModel->getAllSucursales();
$especialidades = $especialidadModel->getAllEspecialidades();

include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>

<script src="js/bloquear.js"></script>
<style>
/* Reset y base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f8f9fa;
    color: #333;
    line-height: 1.6;
}

/* Container principal */
.user-edit-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header */
.edit-header {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #000;
    margin-bottom: 8px;
}

.header-title i {
    margin-right: 10px;
    color: #666;
}

.header-subtitle {
    color: #666;
    font-size: 1rem;
}

.header-subtitle strong {
    color: #000;
}

/* Botones */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-back {
    background: #fff;
    color: #333;
    border: 2px solid #333;
}

.btn-back:hover {
    background: #333;
    color: #fff;
}

.btn-save {
    background: #000;
    color: #fff;
    border: 2px solid #000;
}

.btn-save:hover {
    background: #333;
    border-color: #333;
}

.btn-cancel {
    background: #fff;
    color: #333;
    border: 2px solid #ccc;
}

.btn-cancel:hover {
    background: #f5f5f5;
    border-color: #999;
}

.btn-consult {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
    padding: 6px 12px;
    font-size: 0.8rem;
    margin-left: 10px;
}

.btn-consult:hover:not(:disabled) {
    background: #e9ecef;
    border-color: #adb5bd;
}

.btn-consult:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.alert-error {
    background: #fff;
    border: 1px solid #000;
    color: #000;
}

.alert-success {
    background: #f8f9fa;
    border: 1px solid #333;
    color: #333;
}

.alert-warning {
    background: #fff;
    border: 1px solid #666;
    color: #666;
}

.alert-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    margin-left: auto;
    color: inherit;
}

/* Layout principal - Información arriba, formulario abajo */
.form-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Tarjetas */
.info-card, .form-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    padding: 15px 20px;
    font-weight: 600;
    color: #000;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 20px;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    padding: 15px 20px;
}

/* Avatar y perfil */
.user-avatar {
    text-align: center;
    margin-bottom: 20px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fafafa;
}

.avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #333;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 15px;
}

.user-avatar h3 {
    margin: 10px 0 5px 0;
    color: #000;
}

.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    background: #333;
    color: #fff;
}

/* Detalles del usuario */
.user-details {
    list-style: none;
    margin: 20px 0;
}

.user-details li {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.user-details li:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 500;
    color: #666;
}

.detail-value {
    color: #000;
    font-weight: 500;
}

/* Formulario */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 5px;
}

.required {
    color: #000;
    font-weight: bold;
}

.form-input, .form-select {
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    background: #fff;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: #333;
}

.form-input.is-valid {
    border-color: #000;
    background: #f8f9fa;
}

.form-input.is-invalid {
    border-color: #666;
    background: #fff;
}

/* Input group */
.input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.input-status {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f8f9fa;
}

/* Resultado de consulta */
.consult-result {
    margin-top: 10px;
    display: none;
}

/* Hints */
.form-hint {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
}

/* Información de rol */
.role-info {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}

.role-info small {
    color: #666;
    font-size: 0.9rem;
}

/* Especialidades */
.especialidades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    background: #fafafa;
}

.checkbox-item input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

.checkbox-item label {
    margin: 0;
    cursor: pointer;
    color: #333;
    font-size: 0.9rem;
}

/* Acciones del formulario */
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

/* Utilidades de texto */
.text-success {
    color: #000 !important;
}

.text-danger {
    color: #666 !important;
}

.text-warning {
    color: #999 !important;
}

.text-muted {
    color: #ccc !important;
}

/* Alerts pequeños */
.alert-sm {
    padding: 8px 12px;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .user-edit-container {
        padding: 10px;
    }
    
    .edit-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .especialidades-grid {
        grid-template-columns: 1fr;
    }
}

/* Estados de hover y focus mejorados */
.form-input:hover, .form-select:hover {
    border-color: #999;
}

.checkbox-item:hover {
    background: #f0f0f0;
    border-color: #ccc;
}

/* Animaciones sutiles */
.btn, .form-input, .form-select, .alert {
    transition: all 0.3s ease;
}

.alert {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<div class="user-edit-container">
    <!-- Header -->
    <div class="edit-header">
        <div class="header-content">
            <h1 class="header-title">
                <i class=""></i> Editar Usuario
            </h1>
            <p class="header-subtitle">Modificar datos de: <strong><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></strong></p>
        </div>
        <div class="header-actions">
            <a href="index.php?action=admin/usuarios" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="alert-close">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="alert-close">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" id="userForm" class="edit-form">
        <div class="form-layout">
            <!-- Información Actual - Arriba -->
            <div class="info-card">
                <div class="card-header">
                    <i class=""></i> Información Actual
                </div>
                <div class="card-body">
                    <div class="">
                        <h5><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></h5>
                        <div class="">
                
                        </div>
                        <span class="role-badge role-<?php echo $user['id_rol']; ?>">
                            <?php echo $user['nombre_rol']; ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Cédula 
                            <button type="button" class="btn-consult" id="btnConsultarCedula" disabled onclick="consultarCedula()">
                                <i class="fas fa-search"></i> Consultar
                            </button>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-input" name="cedula" id="cedulaInput"
                                   value="<?php echo htmlspecialchars($user['cedula'] ?? ''); ?>" 
                                   placeholder="Ingrese número de cédula" 
                                   maxlength="10" 
                                   oninput="validarCedulaInput()">
                            <span class="input-status" id="cedulaStatus">
                                <i class="fas fa-question"></i>
                            </span>
                        </div>
                        <div id="cedulaResult" class="consult-result"></div>
                        <div class="form-hint">10 dígitos</div>
                    </div>

                    <ul class="user-details">
                        <li>
                            <span class="detail-label">Usuario:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
                        </li>
                        <li>
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <?php if ($user['cedula']): ?>
                            <li>
                                <span class="detail-label">Cédula:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['cedula']); ?></span>
                            </li>
                        <?php endif; ?>
                        <li>
                            <span class="detail-label">Registro:</span>
                            <span class="detail-value"><?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?></span>
                        </li>
                        <?php if ($user['ultimo_acceso']): ?>
                            <li>
                                <span class="detail-label">Último acceso:</span>
                                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($user['ultimo_acceso'])); ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="form-group">
                        <label class="form-label">Cambiar Rol</label>
                        <select class="form-select" name="id_rol" id="roleSelect" required onchange="updateForm()">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id_rol']; ?>" 
                                        <?php echo ($user['id_rol'] == $role['id_rol']) ? 'selected' : ''; ?>>
                                            <?php echo $role['nombre_rol']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="roleInfo" class="role-info">
                        <small id="roleDescription"></small>
                    </div>
                </div>
            </div>

            <!-- Datos del Usuario - Abajo -->
            <div class="form-card">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Datos del Usuario
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <!-- Fila 1 -->
                        <div class="form-group">
                            <label class="form-label">Nombre de Usuario <span class="required">*</span></label>
                            <input type="text" class="form-input" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" class="form-input" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <!-- Fila 2 -->
                        <div class="form-group">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-input" name="password" 
                                   minlength="6" placeholder="Dejar vacío para mantener actual">
                            <div class="form-hint">Solo completar si desea cambiar la contraseña</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cédula</label>
                            <input type="text" class="form-input" name="cedula" 
                                   value="<?php echo htmlspecialchars($user['cedula'] ?? ''); ?>">
                        </div>

                        <!-- Fila 3 -->
                        <div class="form-group">
                            <label class="form-label">Nombre <span class="required">*</span></label>
                            <input type="text" class="form-input" name="nombre" 
                                   value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido <span class="required">*</span></label>
                            <input type="text" class="form-input" name="apellido" 
                                   value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                        </div>

                        <!-- Fila 4 -->
                        <div class="form-group">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-input" name="fecha_nacimiento" 
                                   value="<?php echo $user['fecha_nacimiento']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Género</label>
                            <select class="form-select" name="genero">
                                <option value="">Seleccionar...</option>
                                <option value="M" <?php echo ($user['genero'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="F" <?php echo ($user['genero'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                                <option value="O" <?php echo ($user['genero'] == 'O') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>

                        <!-- Fila 5 -->
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-input" name="telefono" 
                                   value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>">
                        </div>
                        <div class="form-group" id="sucursalField">
                            <label class="form-label">Sucursal <span class="required" id="sucursalRequired">*</span></label>
                            <select class="form-select" name="id_sucursal" id="sucursalSelect">
                                <option value="">Seleccionar sucursal...</option>
                                <?php foreach ($sucursales as $sucursal): ?>
                                    <option value="<?php echo $sucursal['id_sucursal']; ?>"
                                            <?php echo ($user['id_sucursal'] == $sucursal['id_sucursal']) ? 'selected' : ''; ?>>
                                                <?php echo $sucursal['nombre_sucursal']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Campo de ancho completo -->
                        <div class="form-group full-width">
                            <label class="form-label">Dirección</label>
                            <textarea class="form-input" name="direccion" rows="2"><?php echo htmlspecialchars($user['direccion'] ?? ''); ?></textarea>
                        </div>

                        <!-- Especialidades (si aplica) -->
                        <div class="form-group full-width" id="especialidadesField">
                            <label class="form-label">Especialidades <span class="required">*</span></label>
                            <div class="especialidades-grid">
                                <?php foreach ($especialidades as $especialidad): ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" 
                                               name="especialidades[]" 
                                               value="<?php echo $especialidad['id_especialidad']; ?>"
                                               id="esp_<?php echo $especialidad['id_especialidad']; ?>"
                                               <?php echo in_array($especialidad['id_especialidad'], $medicoEspecialidades) ? 'checked' : ''; ?>>
                                        <label for="esp_<?php echo $especialidad['id_especialidad']; ?>">
                                            <?php echo $especialidad['nombre_especialidad']; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-hint">Seleccione una o más especialidades</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form-actions">
                        <a href="index.php?action=admin/usuarios" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save"></i> Actualizar Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Descripciones de roles
    const roleDescriptions = {
        '1': 'Administrador: Acceso completo al sistema, gestión de usuarios y configuraciones.',
        '2': 'Recepcionista: Gestión de pacientes, citas y pagos. Requiere asignación de sucursal.',
        '3': 'Médico: Atención de pacientes y gestión de consultas. Requiere sucursal y especialidades.',
        '4': 'Paciente: Acceso limitado para gestión personal de citas y datos.'
    };

    function updateForm() {
        const roleSelect = document.getElementById('roleSelect');
        const selectedRole = roleSelect.value;
        const roleDescription = document.getElementById('roleDescription');
        const sucursalField = document.getElementById('sucursalField');
        const especialidadesField = document.getElementById('especialidadesField');
        const sucursalSelect = document.getElementById('sucursalSelect');
        const sucursalRequired = document.getElementById('sucursalRequired');

        // Mostrar descripción del rol
        roleDescription.textContent = roleDescriptions[selectedRole] || 'Rol seleccionado';

        // Resetear campos
        sucursalField.style.display = 'block';
        especialidadesField.style.display = 'none';
        sucursalSelect.required = false;
        sucursalRequired.style.display = 'none';

        // Configurar campos según el rol
        if (selectedRole === '2' || selectedRole === '3') {
            // Recepcionista o Médico requieren sucursal
            sucursalSelect.required = true;
            sucursalRequired.style.display = 'inline';
        }

        if (selectedRole === '3') {
            // Médico requiere especialidades
            especialidadesField.style.display = 'block';
        }

        if (selectedRole === '4') {
            // Paciente no requiere sucursal
            sucursalField.style.display = 'none';
        }
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function () {
        updateForm();
    });

    // Funciones para consulta de cédula
    function validarCedulaInput() {
        const cedulaInput = document.getElementById('cedulaInput');
        const btnConsultar = document.getElementById('btnConsultarCedula');
        const cedulaStatus = document.getElementById('cedulaStatus');
        const cedulaResult = document.getElementById('cedulaResult');
        const cedula = cedulaInput.value.replace(/\D/g, ''); // Solo números

        // Actualizar el input solo con números
        cedulaInput.value = cedula;

        // Resetear resultado anterior
        cedulaResult.style.display = 'none';

        if (cedula.length === 10) {
            if (validarCedulaEcuatoriana(cedula)) {
                cedulaStatus.innerHTML = '<i class="fas fa-check text-success"></i>';
                btnConsultar.disabled = false;
                cedulaInput.classList.remove('is-invalid');
                cedulaInput.classList.add('is-valid');
            } else {
                cedulaStatus.innerHTML = '<i class="fas fa-times text-danger"></i>';
                btnConsultar.disabled = true;
                cedulaInput.classList.remove('is-valid');
                cedulaInput.classList.add('is-invalid');
            }
        } else if (cedula.length > 0) {
            cedulaStatus.innerHTML = '<i class="fas fa-clock text-warning"></i>';
            btnConsultar.disabled = true;
            cedulaInput.classList.remove('is-valid', 'is-invalid');
        } else {
            cedulaStatus.innerHTML = '<i class="fas fa-question text-muted"></i>';
            btnConsultar.disabled = true;
            cedulaInput.classList.remove('is-valid', 'is-invalid');
        }
    }

    function validarCedulaEcuatoriana(cedula) {
        if (cedula.length !== 10)
            return false;

        const digitos = cedula.split('').map(d => parseInt(d));
        let suma = 0;

        for (let i = 0; i < 9; i++) {
            let digito = digitos[i];

            if (i % 2 === 0) {
                digito *= 2;
                if (digito > 9)
                    digito -= 9;
            }

            suma += digito;
        }

        const digitoVerificador = digitos[9];
        const residuo = suma % 10;
        const resultado = residuo === 0 ? 0 : 10 - residuo;

        return digitoVerificador === resultado;
    }

    async function consultarCedula() {
        const cedula = document.getElementById('cedulaInput').value;
        const btnConsultar = document.getElementById('btnConsultarCedula');
        const cedulaResult = document.getElementById('cedulaResult');
        const nombreInput = document.querySelector('input[name="nombre"]');
        const apellidoInput = document.querySelector('input[name="apellido"]');

        if (!cedula || cedula.length !== 10) {
            return;
        }

        // Mostrar loading
        btnConsultar.disabled = true;
        btnConsultar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Consultando...';

        try {
            const response = await fetch('views/api/consultar-cedula.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({cedula: cedula})
            });

            const data = await response.json();

            if (data.success) {
                // Éxito - completar campos
                cedulaResult.innerHTML = `
                <div class="alert alert-success alert-sm mb-0">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Datos encontrados:</strong> ${data.nombres} ${data.apellidos}
                </div>
            `;
                cedulaResult.style.display = 'block';

                // Completar campos automáticamente
                if (data.nombres) {
                    nombreInput.value = data.nombres;
                    nombreInput.classList.add('is-valid');
                }
                if (data.apellidos) {
                    apellidoInput.value = data.apellidos;
                    apellidoInput.classList.add('is-valid');
                }

                // Highlight de los campos completados
                setTimeout(() => {
                    nombreInput.classList.remove('is-valid');
                    apellidoInput.classList.remove('is-valid');
                }, 3000);

            } else {
                // Error
                cedulaResult.innerHTML = `
                <div class="alert alert-warning alert-sm mb-0">
                    <i class="fas fa-exclamation-triangle"></i> 
                    ${data.error || 'No se encontraron datos para esta cédula'}
                </div>
            `;
                cedulaResult.style.display = 'block';
            }

        } catch (error) {
            cedulaResult.innerHTML = `
            <div class="alert alert-danger alert-sm mb-0">
                <i class="fas fa-times-circle"></i> 
                Error de conexión. Verifique su internet e inténtelo nuevamente.
            </div>
        `;
            cedulaResult.style.display = 'block';
        } finally {
            // Restaurar botón
            btnConsultar.disabled = false;
            btnConsultar.innerHTML = '<i class="fas fa-search"></i> Consultar';
        }
    }

// CSS adicional para alerts pequeños
    const style = document.createElement('style');
    style.textContent = `
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    .input-group-text {
        min-width: 40px;
        justify-content: center;
    }
`;
    document.head.appendChild(style);
</script>

</body>
</html>