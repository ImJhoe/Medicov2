<?php
require_once 'models/Role.php';

// Verificar autenticación y permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: index.php?action=dashboard');
    exit;
}

$roleModel = new Role();
$error = '';
$success = '';
$editingRole = null;

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $nombre = trim($_POST['nombre_rol']);
                $descripcion = trim($_POST['descripcion']);
                $permisos = $_POST['permisos'] ?? [];

                if (empty($nombre)) {
                    throw new Exception("El nombre del rol es obligatorio");
                }

                $roleId = $roleModel->createRole($nombre, $descripcion);

                // Guardar permisos si se especificaron
                if (!empty($permisos)) {
                    $roleModel->saveRolePermissions($roleId, $permisos);
                }

                $success = "Rol '{$nombre}' creado exitosamente";
                break;

            case 'update':
                $roleId = $_POST['role_id'];
                $nombre = trim($_POST['nombre_rol']);
                $descripcion = trim($_POST['descripcion']);
                $permisos = $_POST['permisos'] ?? [];

                if (empty($nombre)) {
                    throw new Exception("El nombre del rol es obligatorio");
                }

                $roleModel->updateRole($roleId, $nombre, $descripcion);
                $roleModel->saveRolePermissions($roleId, $permisos);

                $success = "Rol '{$nombre}' actualizado exitosamente";
                break;

            case 'delete':
                $roleId = $_POST['role_id'];
                $role = $roleModel->getRoleById($roleId);
                $roleModel->deleteRole($roleId);
                $success = "Rol '{$role['nombre_rol']}' eliminado exitosamente";
                break;

            case 'clone':
                $fromRoleId = $_POST['from_role_id'];
                $toRoleId = $_POST['to_role_id'];
                $roleModel->clonePermissions($fromRoleId, $toRoleId);
                $success = "Permisos clonados exitosamente";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener rol para editar si se especifica
if (isset($_GET['edit'])) {
    $editingRole = $roleModel->getRoleById($_GET['edit']);
    if (!$editingRole) {
        header('Location: index.php?action=admin/roles');
        exit;
    }
}

// Obtener datos para la vista
$search = $_GET['search'] ?? '';
$roles = $roleModel->getAllRolesForAdmin($search);
$menuStructure = $roleModel->getMenuStructureWithPermissions($editingRole['id_rol'] ?? null);

include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>
<script src="js/bloquear.js"></script>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
}

.roles-management-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Header */
.management-header {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-content {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.title-section {
    display: flex;
    align-items: center;
    gap: 15px;
}

.title-text h1 {
    font-size: 24px;
    color: #333;
    margin-bottom: 5px;
}

.subtitle {
    color: #666;
    font-size: 14px;
}

.btn-primary, .btn-secondary {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #333;
    color: white;
}

.btn-primary:hover {
    background: #555;
}

.btn-secondary {
    background: white;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #f5f5f5;
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    position: relative;
}

.success-alert {
    background: #f8f9fa;
    border: 1px solid #28a745;
    color: #155724;
}

.error-alert {
    background: #f8f9fa;
    border: 1px solid #dc3545;
    color: #721c24;
}

.alert-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

/* Main Layout - Cambiado a vertical */
.main-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Roles Sidebar - Ahora como sección superior */
.roles-sidebar {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sidebar-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.sidebar-header h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 15px;
}

.search-container {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.search-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

/* Roles List - Grid layout */
.roles-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
}

.role-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    padding: 15px;
    transition: all 0.3s ease;
}

.role-card:hover {
    border-color: #333;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.role-card.active {
    border-color: #333;
    background: #333;
    color: white;
}

.role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.role-header h4 {
    font-size: 16px;
    margin: 0;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-badge.active {
    background: #28a745;
    color: white;
}

.status-badge.inactive {
    background: #6c757d;
    color: white;
}

.role-actions {
    display: flex;
    gap: 5px;
    justify-content: flex-end;
    margin-top: 10px;
}

.action-btn {
    padding: 6px 8px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    text-decoration: none;
    color: white;
}

.action-btn.edit {
    background: #333;
}

.action-btn.delete {
    background: #dc3545;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

/* Content Area */
.content-area {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Form */
.role-form-container {
    max-width: 100%;
}

.form-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.form-header h2 {
    font-size: 20px;
    color: #333;
    margin-bottom: 5px;
}

.current-role {
    color: #666;
    font-size: 14px;
}

.form-section {
    margin-bottom: 30px;
}

.form-section h3 {
    font-size: 16px;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.input-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.input-group {
    display: flex;
    flex-direction: column;
}

.input-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.input-group input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

/* Permissions Section */
.permissions-section {
    margin-bottom: 30px;
}

.permissions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.permissions-header h3 {
    font-size: 16px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.permissions-controls {
    display: flex;
    gap: 10px;
}

.control-btn {
    padding: 6px 12px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.control-btn:hover {
    background: #f5f5f5;
}

/* Permissions Grid - Stack vertically */
.permissions-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.menu-module {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.module-header {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.module-toggle {
    display: flex;
    align-items: center;
}

.toggle-checkbox {
    margin-right: 12px;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    flex: 1;
}

.module-icon {
    width: 24px;
    text-align: center;
}

.module-info h4 {
    font-size: 16px;
    color: #333;
    margin: 0;
}

.module-counter {
    color: #666;
    font-size: 12px;
}

.submenu-list {
    padding: 0;
}

.submenu-item {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.submenu-item:last-child {
    border-bottom: none;
}

.submenu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    flex-wrap: wrap;
    gap: 10px;
}

.submenu-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}

.submenu-toggle label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.submenu-path {
    font-size: 12px;
    color: #666;
    font-family: monospace;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
}

.crud-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
}

.crud-option {
    display: flex;
    align-items: center;
}

.crud-label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.crud-label.create { color: #28a745; }
.crud-label.read { color: #17a2b8; }
.crud-label.update { color: #ffc107; }
.crud-label.delete { color: #dc3545; }

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

/* Welcome State */
.welcome-state {
    text-align: center;
    padding: 60px 20px;
}

.welcome-icon i {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.welcome-content h2 {
    color: #333;
    margin-bottom: 10px;
}

.welcome-content p {
    color: #666;
    margin-bottom: 30px;
}

.btn-primary.large {
    padding: 15px 30px;
    font-size: 16px;
}

/* Modals */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-container {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-header.danger {
    color: #dc3545;
}

.modal-header.info {
    color: #17a2b8;
}

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.confirmation-content, .clone-content {
    text-align: center;
}

.confirmation-icon i, .clone-icon i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
}

.warning-note {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 10px;
    border-radius: 4px;
    margin-top: 15px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-danger {
    background: #dc3545;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-info {
    background: #17a2b8;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-info:hover {
    background: #138496;
}

/* Clone specific styles */
.clone-selectors {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
    flex-wrap: wrap;
    justify-content: center;
}

.selector-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 150px;
}

.selector-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.clone-arrow {
    font-size: 20px;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .roles-management-container {
        padding: 10px;
    }
    
    .header-content {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .roles-list {
        grid-template-columns: 1fr;
    }
    
    .input-grid {
        grid-template-columns: 1fr;
    }
    
    .permissions-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .permissions-controls {
        justify-content: center;
    }
    
    .crud-controls {
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .clone-selectors {
        flex-direction: column;
    }
    
    .clone-arrow {
        transform: rotate(90deg);
    }
}
</style>

<div class="roles-management-container">
    <!-- Header Principal -->
    <header class="management-header">
        <div class="header-content">
            <div class="title-section">
                <div class="">
                    <i class=""></i>
                </div>
                <div class="title-text">
                    <h1>Sistema de Roles</h1>
                    <span class="subtitle">Control de acceso y permisos</span>
                </div>
            </div>
            <div class="header-actions">
                <?php if ($editingRole): ?>
                    <a href="index.php?action=admin/roles" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Regresar
                    </a>
                <?php else: ?>
                    <button class="btn-primary" onclick="showCreateForm()">
                        <i class="fas fa-plus-circle"></i>
                        Agregar Rol
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mensajes de Estado -->
    <?php if (!empty($success)): ?>
        <div class="alert success-alert">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>Operación exitosa</strong>
                <p><?php echo $success; ?></p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert error-alert">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong>Ha ocurrido un error</strong>
                <p><?php echo $error; ?></p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Layout Principal -->
    <div class="main-layout">
        
        <!-- Panel de Roles -->
        <aside class="roles-sidebar">
            <div class="sidebar-header">
                <h3>Roles Disponibles</h3>
                <div class="search-container">
                    <input type="text" 
                           class="search-input" 
                           placeholder="Filtrar roles..."
                           value="<?php echo htmlspecialchars($search); ?>"
                           onchange="searchRoles(this.value)">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>

            <div class="roles-list">
                <?php if (empty($roles)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay roles registrados</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <div class="role-card <?php echo $editingRole && $editingRole['id_rol'] == $role['id_rol'] ? 'active' : ''; ?>">
                            <div class="role-info">
                                <div class="role-header">
                                    <h4><?php echo htmlspecialchars($role['nombre_rol']); ?></h4>
                                    <?php if ($role['activo'] == 0): ?>
                                        <span class="status-badge inactive">Inactivo</span>
                                    <?php else: ?>
                                        <span class="status-badge active">Activo</span>
                                    <?php endif; ?>
                                </div>

                                <div class="role-stats">
                                    <!-- <span class="user-count">
                                        <i class="fas fa-users"></i>
                                        <?php echo $role['usuarios_activos']; ?> usuario<?php echo $role['usuarios_activos'] != 1 ? 's' : ''; ?>
                                    </span> -->
                                </div>
                            </div>
                            <div class="role-actions">
                                <a href="index.php?action=admin/roles&edit=<?php echo $role['id_rol']; ?>" 
                                   class="action-btn edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($role['usuarios_activos'] == 0 && $role['activo'] == 1): ?>
                                    <button class="action-btn delete" 
                                            onclick="confirmDelete(<?php echo $role['id_rol']; ?>, '<?php echo htmlspecialchars($role['nombre_rol']); ?>')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="sidebar-footer">
              
            </div>
        </aside>

        <!-- Panel Principal de Contenido -->
        <main class="content-area">
            
            <!-- Formulario de Rol -->
            <div id="createForm" class="role-form-container" style="display: <?php echo $editingRole ? 'block' : 'none'; ?>">
                <div class="form-header">
                    <h2>
                        <i class="fas fa-<?php echo $editingRole ? 'edit' : 'plus-circle'; ?>"></i>
                        <?php echo $editingRole ? 'Modificar Rol' : 'Nuevo Rol'; ?>
                    </h2>
                    <?php if ($editingRole): ?>
                        <span class="current-role"><?php echo htmlspecialchars($editingRole['nombre_rol']); ?></span>
                    <?php endif; ?>
                </div>

                <form method="POST" id="roleForm" class="role-form">
                    <input type="hidden" name="action" value="<?php echo $editingRole ? 'update' : 'create'; ?>">
                    <?php if ($editingRole): ?>
                        <input type="hidden" name="role_id" value="<?php echo $editingRole['id_rol']; ?>">
                    <?php endif; ?>

                    <!-- Información Básica -->
                    <section class="form-section">
                        <h3>Información General</h3>
                        <div class="input-grid">
                            <div class="input-group">
                                <label>Nombre del Rol *</label>
                                <input type="text" 
                                       name="nombre_rol" 
                                       value="<?php echo $editingRole ? htmlspecialchars($editingRole['nombre_rol']) : ''; ?>" 
                                       required 
                                       placeholder="Nombre descriptivo del rol">
                            </div>
                            <div class="input-group">
                                <label>Descripción</label>
                                <input type="text" 
                                       name="descripcion" 
                                       value="<?php echo $editingRole ? htmlspecialchars($editingRole['descripcion']) : ''; ?>" 
                                       placeholder="Breve descripción del rol">
                            </div>
                        </div>
                    </section>

                    <!-- Sistema de Permisos -->
                    <section class="permissions-section">
                        <div class="permissions-header">
                            <h3>
                                <i class="fas fa-shield-alt"></i>
                                Configuración de Permisos
                            </h3>
                            <div class="permissions-controls">
                                <button type="button" class="control-btn select-all" onclick="selectAllPermissions()">
                                    <i class="fas fa-check-double"></i>
                                    Seleccionar Todo
                                </button>
                                <button type="button" class="control-btn clear-all" onclick="clearAllPermissions()">
                                    <i class="fas fa-ban"></i>
                                    Limpiar Todo
                                </button>
                            </div>
                        </div>

                        <div class="permissions-grid">
                            <?php foreach ($menuStructure as $menu): ?>
                                <div class="menu-module">
                                    <div class="module-header">
                                        <div class="module-toggle">
                                            <input type="checkbox" 
                                                   class="toggle-checkbox" 
                                                   id="menu_<?php echo $menu['id_menu']; ?>"
                                                   onchange="toggleMenu(<?php echo $menu['id_menu']; ?>)">
                                            <label for="menu_<?php echo $menu['id_menu']; ?>" class="toggle-label">
                                                <div class="module-icon">
                                                    <i class="<?php echo $menu['icono']; ?>"></i>
                                                </div>
                                                <div class="module-info">
                                                    <h4><?php echo $menu['nombre_menu']; ?></h4>
                                                    <span class="module-counter">
                                                        <span id="count_<?php echo $menu['id_menu']; ?>">0</span> de <?php echo count($menu['submenus']); ?>
                                                    </span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="submenu-list">
                                        <?php foreach ($menu['submenus'] as $submenu): ?>
                                            <div class="submenu-item">
                                                <div class="submenu-header">
                                                    <div class="submenu-toggle">
                                                        <input type="checkbox" 
                                                               class="submenu-checkbox" 
                                                               id="submenu_<?php echo $submenu['id_submenu']; ?>"
                                                               data-menu="<?php echo $menu['id_menu']; ?>"
                                                               onchange="toggleSubmenu(<?php echo $submenu['id_submenu']; ?>)"
                                                               <?php echo $submenu['permisos']['tiene_permiso'] ? 'checked' : ''; ?>>
                                                        <label for="submenu_<?php echo $submenu['id_submenu']; ?>">
                                                            <i class="<?php echo $submenu['icono']; ?>"></i>
                                                            <?php echo $submenu['nombre_submenu']; ?>
                                                        </label>
                                                    </div>
                                                    <span class="submenu-path"><?php echo $submenu['uri_submenu']; ?></span>
                                                </div>

                                                <div class="crud-controls" 
                                                     id="crud_<?php echo $submenu['id_submenu']; ?>"
                                                     style="display: <?php echo $submenu['permisos']['tiene_permiso'] ? 'flex' : 'none'; ?>">
                                                    
                                                    <div class="crud-option">
                                                        <input type="checkbox" 
                                                               id="crear_<?php echo $submenu['id_submenu']; ?>"
                                                               name="permisos[<?php echo $submenu['id_submenu']; ?>][crear]" 
                                                               value="1"
                                                               <?php echo $submenu['permisos']['crear'] ? 'checked' : ''; ?>>
                                                        <label for="crear_<?php echo $submenu['id_submenu']; ?>" class="crud-label create">
                                                            <i class="fas fa-plus"></i>
                                                            <span>Crear</span>
                                                        </label>
                                                    </div>

                                                    <div class="crud-option">
                                                        <input type="checkbox" 
                                                               id="leer_<?php echo $submenu['id_submenu']; ?>"
                                                               name="permisos[<?php echo $submenu['id_submenu']; ?>][leer]" 
                                                               value="1"
                                                               <?php echo $submenu['permisos']['leer'] ? 'checked' : ''; ?>>
                                                        <label for="leer_<?php echo $submenu['id_submenu']; ?>" class="crud-label read">
                                                            <i class="fas fa-eye"></i>
                                                            <span>Ver</span>
                                                        </label>
                                                    </div>

                                                    <div class="crud-option">
                                                        <input type="checkbox" 
                                                               id="editar_<?php echo $submenu['id_submenu']; ?>"
                                                               name="permisos[<?php echo $submenu['id_submenu']; ?>][editar]" 
                                                               value="1"
                                                               <?php echo $submenu['permisos']['editar'] ? 'checked' : ''; ?>>
                                                        <label for="editar_<?php echo $submenu['id_submenu']; ?>" class="crud-label update">
                                                            <i class="fas fa-edit"></i>
                                                            <span>Editar</span>
                                                        </label>
                                                    </div>

                                                    <div class="crud-option">
                                                        <input type="checkbox" 
                                                               id="eliminar_<?php echo $submenu['id_submenu']; ?>"
                                                               name="permisos[<?php echo $submenu['id_submenu']; ?>][eliminar]" 
                                                               value="1"
                                                               <?php echo $submenu['permisos']['eliminar'] ? 'checked' : ''; ?>>
                                                        <label for="eliminar_<?php echo $submenu['id_submenu']; ?>" class="crud-label delete">
                                                            <i class="fas fa-trash"></i>
                                                            <span>Eliminar</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Acciones del Formulario -->
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="cancelForm()">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $editingRole ? 'Actualizar Rol' : 'Crear Rol'; ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Estado Inicial -->
            <div id="noFormMessage" class="welcome-state" style="display: <?php echo $editingRole ? 'none' : 'block'; ?>">
                <div class="welcome-content">
                    <div class="welcome-icon">
                        <i class=""></i>
                    </div>
                    <h2>Centro de Control de Roles</h2>
                    <p>Gestiona los roles y permisos del sistema de manera eficiente</p>
                    <div class="welcome-actions">
                        <button class="btn-primary large" onclick="showCreateForm()">
                            <i class="fas fa-plus-circle"></i>
                            Crear Primer Rol
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal-overlay" id="deleteModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header danger">
            <h3>
                <i class="fas fa-exclamation-triangle"></i>
                Confirmar Eliminación
            </h3>
            <button class="modal-close" onclick="closeModal('deleteModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="confirmation-content">
                <div class="confirmation-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p>¿Confirma la eliminación del rol <strong id="roleName"></strong>?</p>
                <div class="warning-note">
                    <i class="fas fa-info-circle"></i>
                    Esta acción desactivará el rol permanentemente
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal('deleteModal')">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="role_id" id="roleIdToDelete">
                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash"></i>
                    Eliminar Definitivamente
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Clonación -->
<div class="modal-overlay" id="cloneModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header info">
            <h3>
                <i class="fas fa-clone"></i>
                Clonar Configuración
            </h3>
            <button class="modal-close" onclick="closeModal('cloneModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="clone">
                <div class="clone-content">
                    <div class="clone-icon">
                        <i class="fas fa-copy"></i>
                    </div>
                    <p>Duplicar permisos entre roles existentes</p>
                    <div class="clone-selectors">
                        <div class="selector-group">
                            <label>Rol Origen:</label>
                            <select name="from_role_id" required>
                                <option value="">Seleccionar rol...</option>
                                <?php foreach ($roles as $role): ?>
                                    <?php if ($role['activo'] == 1): ?>
                                        <option value="<?php echo $role['id_rol']; ?>">
                                            <?php echo htmlspecialchars($role['nombre_rol']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="clone-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="selector-group">
                            <label>Rol Destino:</label>
                            <select name="to_role_id" required>
                                <option value="">Seleccionar rol...</option>
                                <?php foreach ($roles as $role): ?>
                                    <?php if ($role['activo'] == 1): ?>
                                        <option value="<?php echo $role['id_rol']; ?>">
                                            <?php echo htmlspecialchars($role['nombre_rol']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="warning-note">
                        <i class="fas fa-exclamation-triangle"></i>
                        Los permisos del rol destino serán reemplazados completamente
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('cloneModal')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-info">
                    <i class="fas fa-clone"></i>
                    Ejecutar Clonación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Inicializar tooltips y contadores al cargar la página
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        updateAllMenuCounters();

        // Añadir efecto de loading a formularios
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('btn-loading');
                    submitBtn.disabled = true;
                }
            });
        });
    });

    function showCreateForm() {
        document.getElementById('createForm').style.display = 'block';
        document.getElementById('noFormMessage').style.display = 'none';

        // Smooth scroll mejorado
        document.getElementById('createForm').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Focus en el primer input
        setTimeout(() => {
            const firstInput = document.querySelector('#createForm input[name="nombre_rol"]');
            if (firstInput)
                firstInput.focus();
        }, 500);
    }

    function cancelForm() {
        // Confirmar si hay cambios
        const form = document.getElementById('roleForm');
        const formData = new FormData(form);
        let hasChanges = false;

        for (let [key, value] of formData.entries()) {
            if (value && key !== 'action' && key !== 'role_id') {
                hasChanges = true;
                break;
            }
        }

        if (hasChanges) {
            if (confirm('¿Está seguro que desea cancelar? Se perderán los cambios no guardados.')) {
                window.location.href = 'index.php?action=admin/roles';
            }
        } else {
            window.location.href = 'index.php?action=admin/roles';
        }
    }

    function searchRoles(query) {
        // Debounce para búsqueda
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            window.location.href = `index.php?action=admin/roles&search=${encodeURIComponent(query)}`;
        }, 300);
    }

   // Reemplaza estas funciones en tu JavaScript existente

function confirmDelete(roleId, roleName) {
    document.getElementById('roleName').textContent = roleName;
    document.getElementById('roleIdToDelete').value = roleId;
    
    // Mostrar modal nativo (no Bootstrap)
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
    
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden';
}

function showCloneModal() {
    const modal = document.getElementById('cloneModal');
    modal.style.display = 'flex';
    
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    
    // Restaurar scroll del body
    document.body.style.overflow = 'auto';
}

// Agregar event listeners para cerrar modales
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar modal al hacer clic fuera
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
    
    // Event listeners para botones de cerrar
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('.modal-overlay');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
});

    function showCloneModal() {
        const modal = new bootstrap.Modal(document.getElementById('cloneModal'));
        modal.show();
    }

    // Funciones para manejo de permisos mejoradas
    function toggleMenu(menuId) {
        const menuCheckbox = document.getElementById(`menu_${menuId}`);
        const submenuCheckboxes = document.querySelectorAll(`input[data-menu="${menuId}"]`);

        submenuCheckboxes.forEach(checkbox => {
            checkbox.checked = menuCheckbox.checked;
            toggleSubmenu(checkbox.id.replace('submenu_', ''));
        });

        updateMenuCounter(menuId);

        // Efecto visual
        const menuSection = menuCheckbox.closest('.menu-section');
        if (menuCheckbox.checked) {
            menuSection.style.borderLeftColor = '#28a745';
        } else {
            menuSection.style.borderLeftColor = '#667eea';
        }
    }

    function toggleSubmenu(submenuId) {
        const submenuCheckbox = document.getElementById(`submenu_${submenuId}`);
        const crudContainer = document.getElementById(`crud_${submenuId}`);

        if (submenuCheckbox.checked) {
            crudContainer.style.display = 'block';
            // Auto-seleccionar permiso de lectura como mínimo
            document.getElementById(`leer_${submenuId}`).checked = true;

            // Efecto de aparición suave
            crudContainer.style.opacity = '0';
            crudContainer.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                crudContainer.style.transition = 'all 0.3s ease';
                crudContainer.style.opacity = '1';
                crudContainer.style.transform = 'translateY(0)';
            }, 10);
        } else {
            crudContainer.style.display = 'none';
            // Desmarcar todos los permisos CRUD
            ['crear', 'leer', 'editar', 'eliminar'].forEach(perm => {
                document.getElementById(`${perm}_${submenuId}`).checked = false;
            });
        }

        // Actualizar contador del menú padre
        const menuId = submenuCheckbox.getAttribute('data-menu');
        updateMenuCounter(menuId);
    }

    function updateMenuCounter(menuId) {
        const submenuCheckboxes = document.querySelectorAll(`input[data-menu="${menuId}"]`);
        const checkedCount = Array.from(submenuCheckboxes).filter(cb => cb.checked).length;
        const counterElement = document.getElementById(`count_${menuId}`);

        // Animación del contador
        counterElement.style.transform = 'scale(1.2)';
        counterElement.textContent = checkedCount;
        setTimeout(() => {
            counterElement.style.transform = 'scale(1)';
        }, 150);

        // Actualizar estado del checkbox del menú
        const menuCheckbox = document.getElementById(`menu_${menuId}`);
        if (checkedCount === 0) {
            menuCheckbox.checked = false;
            menuCheckbox.indeterminate = false;
        } else if (checkedCount === submenuCheckboxes.length) {
            menuCheckbox.checked = true;
            menuCheckbox.indeterminate = false;
        } else {
            menuCheckbox.checked = false;
            menuCheckbox.indeterminate = true;
        }

        // Cambiar color del badge
        const badge = counterElement.closest('.badge');
        if (checkedCount === 0) {
            badge.className = 'badge bg-secondary rounded-pill';
        } else if (checkedCount === submenuCheckboxes.length) {
            badge.className = 'badge bg-success rounded-pill';
        } else {
            badge.className = 'badge bg-warning rounded-pill';
        }
    }

    function updateAllMenuCounters() {
        const menuCheckboxes = document.querySelectorAll('.menu-checkbox');
        menuCheckboxes.forEach(checkbox => {
            const menuId = checkbox.id.replace('menu_', '');
            updateMenuCounter(menuId);
        });
    }

    function selectAllPermissions() {
        // Mostrar confirmación
        if (!confirm('¿Está seguro que desea seleccionar todos los permisos? Esto otorgará acceso completo al rol.')) {
            return;
        }

        // Animación de selección
        const buttons = document.querySelectorAll('.submenu-checkbox');
        buttons.forEach((checkbox, index) => {
            setTimeout(() => {
                checkbox.checked = true;
                toggleSubmenu(checkbox.id.replace('submenu_', ''));
            }, index * 50);
        });

        // Seleccionar todos los permisos CRUD después de un delay
        setTimeout(() => {
            document.querySelectorAll('.crud-permissions input[type="checkbox"]').forEach(cb => {
                cb.checked = true;
            });
            updateAllMenuCounters();
        }, buttons.length * 50 + 100);
    }

    function clearAllPermissions() {
        if (!confirm('¿Está seguro que desea limpiar todos los permisos?')) {
            return;
        }

        document.querySelectorAll('.submenu-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            toggleSubmenu(checkbox.id.replace('submenu_', ''));
        });

        updateAllMenuCounters();
    }

    // Función para validar formulario antes del envío
    function validateForm() {
        const nombreRol = document.querySelector('input[name="nombre_rol"]').value.trim();

        if (!nombreRol) {
            alert('El nombre del rol es obligatorio');
            return false;
        }

        // Verificar si al menos un permiso está seleccionado
        const permisosSeleccionados = document.querySelectorAll('.submenu-checkbox:checked');
        if (permisosSeleccionados.length === 0) {
            if (!confirm('No ha seleccionado ningún permiso. ¿Desea continuar creando un rol sin permisos?')) {
                return false;
            }
        }

        return true;
    }

    // Añadir validación al formulario
    document.addEventListener('DOMContentLoaded', function () {
        const roleForm = document.getElementById('roleForm');
        if (roleForm) {
            roleForm.addEventListener('submit', function (e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        }
    });
</script>

</body>
</html>