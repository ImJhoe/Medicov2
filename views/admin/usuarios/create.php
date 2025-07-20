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

$selectedRole = $_GET['role'] ?? '';
$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            // 'password' => trim($_POST['password']),  ← COMENTADA O ELIMINADA
            'cedula' => trim($_POST['cedula'] ?? '') ?: null, // ← Agregar ?? ''
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?: null,
            'genero' => $_POST['genero'] ?: null,
            'telefono' => trim($_POST['telefono'] ?? '') ?: null, // ← Agregar ?? ''
            'direccion' => trim($_POST['direccion'] ?? '') ?: null, // ← Agregar ?? ''
            'id_rol' => $_POST['id_rol'],
            'id_sucursal' => $_POST['id_sucursal'] ?: null,
            'especialidades' => $_POST['especialidades'] ?? []
        ];

        // Validaciones básicas
        if (empty($data['username']) || empty($data['email']) ||
                empty($data['nombre']) || empty($data['apellido']) || empty($data['id_rol'])) {
            throw new Exception("Por favor complete todos los campos obligatorios");
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

        $userId = $userModel->createUser($data);
        $success = "Usuario creado exitosamente";

        // Limpiar formulario
        $_POST = [];
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
<style>
    .form-container {
        background-color: #ffffff;
        color: #000000;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .form-title {
        font-size: 24px;
        margin-bottom: 10px;
        text-align: center;
    }

    .form-subtitle {
        font-size: 14px;
        color: #555;
        margin-bottom: 20px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-control-elegant,
    .form-select-elegant {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        color: #000;
    }

    .form-hint {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }

    .form-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }

    .btn-elegant {
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .btn-secondary {
        background-color: #ccc;
        color: #000;
        border: none;
    }

    .btn-primary {
        background-color: #000;
        color: #fff;
        border: none;
    }

    .alert {
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .alert-success {
        background-color: #e9ffe9;
        color: #0a0;
    }

    .alert-danger {
        background-color: #ffe9e9;
        color: #a00;
    }
</style>

<script src="js/bloquear.js"></script>

<div class="user-form-container form-container">
    <!-- Header -->
    <h2 class="form-title">Nuevo Usuario</h2>
    <p class="form-subtitle">Crear un nuevo usuario en el sistema</p>
    <a href="index.php?action=admin/usuarios" class="btn btn-outline-secondary btn-elegant">
        Volver
    </a>

    <!-- Mensajes -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form method="POST" id="userForm">
        <!-- Paso 1: Seleccionar Rol -->
        <div class="step-card">
            <h5>Paso 1: Seleccionar Rol</h5>
            <div class="form-group">
                <label class="form-label">Tipo de Usuario *</label>
                <select class="form-select-elegant" name="id_rol" id="roleSelect" required onchange="updateForm()">
                    <option value="">Seleccione un rol...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id_rol']; ?>" 
                                <?php echo (($_POST['id_rol'] ?? $selectedRole) == $role['id_rol']) ? 'selected' : ''; ?>>
                            <?php echo $role['nombre_rol']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">
                    Seleccione el rol para mostrar los campos correspondientes
                </div>
            </div>
            <div id="roleInfo">
                <small id="roleDescription"></small>
            </div>
        </div>

        <!-- Paso 2: Datos del Usuario -->
        <div id="userFormContainer">
            <h5>Paso 2: Datos del Usuario</h5>

            <div class="form-group">
                <label class="form-label">Cédula</label>
                <input type="text" class="form-control-elegant" name="cedula" id="cedulaInput"
                       value="<?php echo $_POST['cedula'] ?? ''; ?>" 
                       placeholder="Ingrese número de cédula" 
                       maxlength="10" 
                       oninput="validarCedulaInput()">
                <div id="cedulaResult" class="consult-result"></div>
                <div class="form-hint">10 dígitos - Al consultar se completarán nombres y apellidos automáticamente</div>
            </div>

            <div class="form-group">
                <label class="form-label">Nombre de Usuario *</label>
                <input type="text" class="form-control-elegant" name="username" 
                       value="<?php echo $_POST['username'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control-elegant" name="email" 
                       value="<?php echo $_POST['email'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" class="form-control-elegant" name="nombre" 
                       value="<?php echo $_POST['nombre'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Apellido *</label>
                <input type="text" class="form-control-elegant" name="apellido" 
                       value="<?php echo $_POST['apellido'] ?? ''; ?>" required>
            </div>

            <div class="info-card">
                <div>
                    Contraseña automática:
                    <p>Se generará una contraseña temporal y se enviará por email al usuario.</p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control-elegant" name="fecha_nacimiento" 
                       value="<?php echo $_POST['fecha_nacimiento'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Género</label>
                <select class="form-select-elegant" name="genero">
                    <option value="">Seleccionar...</option>
                    <option value="M" <?php echo (($_POST['genero'] ?? '') == 'M') ? 'selected' : ''; ?>>Masculino</option>
                    <option value="F" <?php echo (($_POST['genero'] ?? '') == 'F') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="O" <?php echo (($_POST['genero'] ?? '') == 'O') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control-elegant" name="telefono" 
                       value="<?php echo $_POST['telefono'] ?? ''; ?>">
            </div>

            <div class="form-group" id="sucursalField">
                <label class="form-label">Sucursal *</label>
                <select class="form-select-elegant" name="id_sucursal" id="sucursalSelect">
                    <option value="">Seleccionar sucursal...</option>
                    <?php foreach ($sucursales as $sucursal): ?>
                        <option value="<?php echo $sucursal['id_sucursal']; ?>"
                                <?php echo (($_POST['id_sucursal'] ?? '') == $sucursal['id_sucursal']) ? 'selected' : ''; ?>>
                            <?php echo $sucursal['nombre_sucursal']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Dirección</label>
                <textarea class="form-control-elegant" name="direccion" rows="2"><?php echo $_POST['direccion'] ?? ''; ?></textarea>
            </div>

            <div class="form-group" id="especialidadesField">
                <label class="form-label">Especialidades *</label>
                <div>
                    <?php foreach ($especialidades as $especialidad): ?>
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="especialidades[]" 
                                   value="<?php echo $especialidad['id_especialidad']; ?>"
                                   id="esp_<?php echo $especialidad['id_especialidad']; ?>"
                                   <?php echo in_array($especialidad['id_especialidad'], $_POST['especialidades'] ?? []) ? 'checked' : ''; ?>>
                            <label for="esp_<?php echo $especialidad['id_especialidad']; ?>">
                                <?php echo $especialidad['nombre_especialidad']; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-hint">Seleccione una o más especialidades</div>
            </div>

            <div class="form-actions">
                <a href="index.php?action=admin/usuarios" class="btn btn-secondary btn-elegant">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-elegant">
                    Crear Usuario
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
        const formContainer = document.getElementById('userFormContainer');
        const roleInfo = document.getElementById('roleInfo');
        const roleDescription = document.getElementById('roleDescription');
        const sucursalField = document.getElementById('sucursalField');
        const especialidadesField = document.getElementById('especialidadesField');
        const sucursalSelect = document.getElementById('sucursalSelect');
        const sucursalRequired = document.getElementById('sucursalRequired');

        if (selectedRole) {
            // Mostrar formulario
            formContainer.style.display = 'block';

            // Mostrar información del rol
            roleInfo.style.display = 'block';
            roleDescription.textContent = roleDescriptions[selectedRole] || 'Rol seleccionado';

            // Resetear campos
            sucursalField.style.display = 'none';
            especialidadesField.style.display = 'none';
            sucursalSelect.required = false;
            sucursalRequired.style.display = 'none';

            // Configurar campos según el rol
            if (selectedRole === '2' || selectedRole === '3') {
                // Recepcionista o Médico requieren sucursal
                sucursalField.style.display = 'block';
                sucursalSelect.required = true;
                sucursalRequired.style.display = 'inline';
            }

            if (selectedRole === '3') {
                // Médico requiere especialidades
                especialidadesField.style.display = 'block';
            }
        } else {
            formContainer.style.display = 'none';
            roleInfo.style.display = 'none';
        }
    }

    // Ejecutar al cargar la página si hay un rol seleccionado
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
