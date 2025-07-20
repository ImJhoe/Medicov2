<?php
require_once 'models/User.php';
require_once 'models/Role.php';

// Verificar autenticación y permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: index.php?action=dashboard');
    exit;
}

$userModel = new User();
$roleModel = new Role();

// Procesar eliminación lógica
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    if ($userModel->deleteUser($userId)) {
        $success = "Usuario eliminado correctamente";
    } else {
        $error = "Error al eliminar el usuario";
    }
}

// Parámetros de búsqueda y paginación
$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role_filter'] ?? '';
$limit = 10;

// Obtener usuarios y total para paginación
$users = $userModel->getAllUsers($page, $limit, $search, $roleFilter);
$totalUsers = $userModel->countUsers($search, $roleFilter);
$totalPages = ceil($totalUsers / $limit);

// Obtener roles para filtro
$roles = $roleModel->getAllRoles();

include 'views/includes/header.php';
include 'views/includes/navbar.php';
?>
<script src="js/bloquear.js"></script>
<style> 
    
</style>
<div class="user-management-container">
    <!-- Header -->
    <div class="management-header">
        <div class="header-content">
            <h2 class="header-title">Gestión de Usuarios</h2>
            <p class="header-subtitle">Administrar usuarios del sistema</p>
        </div>
        <div class="header-actions">
            <a href="index.php?action=admin/usuarios/create" class="btn btn-primary">
                Nuevo Usuario
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-elegant">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-elegant">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filter-card">
        <div class="card-body">
            <form method="GET" action="index.php">
                <input type="hidden" name="action" value="admin/usuarios">
                <div class="filter-grid">
                    <div class="filter-item">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control form-control-elegant" name="search" 
                               placeholder="Nombre, email, usuario, cédula..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-item">
                        <label class="form-label">Filtrar por Rol</label>
                        <select class="form-select form-select-elegant" name="role_filter">
                            <option value="">Todos los roles</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id_rol']; ?>" 
                                        <?php echo $roleFilter == $role['id_rol'] ? 'selected' : ''; ?>>
                                    <?php echo $role['nombre_rol']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-action">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-outline-primary btn-elegant">
                            Buscar
                        </button>
                    </div>
                    <div class="filter-action">
                        <label class="form-label">&nbsp;</label>
                        <a href="index.php?action=admin/usuarios" class="btn btn-outline-secondary btn-elegant">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="users-table-card">
        <div class="table-header">
            <h5 class="table-title">
                Lista de Usuarios 
                <span class="badge badge-elegant"><?php echo $totalUsers; ?></span>
            </h5>
        </div>
        <div class="table-container">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="elegant-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Sucursal</th>
                                <th>Fecha Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-avatar">
                                            <div class="avatar-circle">
                                            </div>
                                            <div class="user-info">
                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                <?php if ($user['cedula']): ?>
                                                    <span class="user-detail">CI: <?php echo $user['cedula']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-name">
                                            <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?>
                                            <?php if ($user['telefono']): ?>
                                                <span class="user-contact">
                                                    <?php echo $user['telefono']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge">
                                            <?php echo $user['nombre_rol']; ?>
                                        </span>
                                    </td>
                                    <td class="user-branch">
                                        <?php echo $user['nombre_sucursal'] ?? '<span class="text-muted">Sin asignar</span>'; ?>
                                    </td>
                                    <td class="user-date">
                                        <?php echo date('d/m/Y H:i', strtotime($user['fecha_registro'])); ?>
                                    </td>
                                    <td class="user-actions">
                                        <div class="action-buttons">
                                            <a href="index.php?action=admin/usuarios/edit&id=<?php echo $user['id_usuario']; ?>" 
                                               class="btn-action btn-edit" title="Editar">
                                                Editar
                                            </a>
                                            <button type="button" class="btn-action btn-delete" 
                                                    onclick="confirmDelete(<?php echo $user['id_usuario']; ?>, '<?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?>')"
                                                    title="Eliminar">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h5>No se encontraron usuarios</h5>
                    <p>No hay usuarios que coincidan con los criterios de búsqueda.</p>
                    <a href="index.php?action=admin/usuarios/create" class="btn btn-primary btn-elegant">
                        Crear Primer Usuario
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
            <div class="table-footer">
                <div class="pagination-container">
                    <div class="pagination-info">
                        <small>
                            Mostrando <?php echo (($page - 1) * $limit) + 1; ?> - 
                            <?php echo min($page * $limit, $totalUsers); ?> de <?php echo $totalUsers; ?> usuarios
                        </small>
                    </div>
                    <nav>
                        <ul class="elegant-pagination">
                            <?php if ($page > 1): ?>
                                <li>
                                    <a href="?action=admin/usuarios&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role_filter=<?php echo $roleFilter; ?>">
                                        Anterior
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <a href="?action=admin/usuarios&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role_filter=<?php echo $roleFilter; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li>
                                    <a href="?action=admin/usuarios&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role_filter=<?php echo $roleFilter; ?>">
                                        Siguiente
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="elegant-modal">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title">
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar al usuario <strong id="userName"></strong>?</p>
                <div class="modal-note">
                    <strong>Nota:</strong> Esta acción desactivará el usuario pero no eliminará sus datos permanentemente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="user_id" id="userIdToDelete">
                    <button type="submit" name="delete_user" class="btn btn-danger btn-modal">
                        Eliminar Usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos generales */
.user-management-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
}

/* Header */
.management-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.header-title {
    font-weight: 600;
    color: #000;
    margin: 0;
    font-size: 1.8rem;
}

.header-subtitle {
    color: #666;
    margin: 5px 0 0;
    font-size: 0.9rem;
}

/* Botones */
.btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.btn-primary {
    background-color: #000;
    color: #fff;
    border-color: #000;
}

.btn-primary:hover {
    background-color: #333;
    border-color: #333;
}

.btn-outline-primary {
    background-color: transparent;
    color: #000;
    border-color: #000;
}

.btn-outline-primary:hover {
    background-color: #000;
    color: #fff;
}

.btn-outline-secondary {
    background-color: transparent;
    color: #666;
    border-color: #ddd;
}

.btn-outline-secondary:hover {
    background-color: #f5f5f5;
    border-color: #ccc;
}

/* Alertas */
.alert {
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 20px;
    border-left: 4px solid transparent;
}

.alert-success {
    background-color: #f0f9f0;
    border-left-color: #28a745;
    color: #28a745;
}

.alert-danger {
    background-color: #fdf3f3;
    border-left-color: #dc3545;
    color: #dc3545;
}

.btn-close {
    background: none;
    border: none;
    float: right;
    cursor: pointer;
    opacity: 0.7;
    font-size: 1.2rem;
    line-height: 1;
}

.btn-close:hover {
    opacity: 1;
}

/* Filtros */
.filter-card {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}

.card-body {
    padding: 20px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    align-items: end;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #444;
    font-size: 0.9rem;
}

.form-control, .form-select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: border-color 0.3s;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #999;
    box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}

/* Tabla */
.users-table-card {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}

.table-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    background-color: #f9f9f9;
}

.table-title {
    margin: 0;
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge-elegant {
    background-color: #333;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.elegant-table {
    width: 100%;
    border-collapse: collapse;
}

.elegant-table th {
    background-color: #f5f5f5;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #444;
    border-bottom: 1px solid #e0e0e0;
}

.elegant-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.elegant-table tr:hover td {
    background-color: #f9f9f9;
}

/* Estilos para elementos de usuario */
.user-avatar {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-weight: 600;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-detail {
    font-size: 0.8rem;
    color: #777;
    margin-top: 2px;
}

.user-name {
    display: flex;
    flex-direction: column;
}

.user-contact {
    font-size: 0.8rem;
    color: #666;
    margin-top: 3px;
}

.user-email {
    color: #444;
}

.role-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    background-color: #e0e0e0;
    color: #333;
}

.user-branch {
    color: #444;
}

.user-date {
    color: #666;
    font-size: 0.9rem;
}

/* Acciones */
.user-actions {
    text-align: center;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.btn-action {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.btn-edit {
    background-color: #f0f0f0;
    color: #333;
    border-color: #ddd;
}

.btn-edit:hover {
    background-color: #e0e0e0;
}

.btn-delete {
    background-color: #f8f8f8;
    color: #d32f2f;
    border-color: #e0e0e0;
}

.btn-delete:hover {
    background-color: #f0f0f0;
    color: #b71c1c;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state h5 {
    font-weight: 500;
    color: #444;
    margin: 15px 0 10px;
}

.empty-state p {
    color: #777;
    margin-bottom: 20px;
}

/* Paginación */
.table-footer {
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    background-color: #f9f9f9;
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pagination-info small {
    color: #666;
    font-size: 0.85rem;
}

.elegant-pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 5px;
}

.elegant-pagination li a {
    display: block;
    padding: 6px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
}

.elegant-pagination li.active a {
    background-color: #000;
    color: #fff;
    border-color: #000;
}

.elegant-pagination li a:hover {
    background-color: #f0f0f0;
}

/* Modal */
.elegant-modal {
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    background-color: #f9f9f9;
}

.modal-header-danger {
    background-color: #fdf3f3;
    border-bottom-color: #f5c6cb;
}

.modal-title {
    margin: 0;
    font-weight: 500;
    color: #333;
}

.modal-body {
    padding: 20px;
    color: #444;
}

.modal-note {
    margin-top: 15px;
    padding: 10px;
    background-color: #f5f5f5;
    border-radius: 4px;
    font-size: 0.85rem;
    color: #666;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    background-color: #f9f9f9;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-modal {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
}

.btn-danger {
    background-color: #d32f2f;
    color: white;
    border-color: #d32f2f;
}

.btn-danger:hover {
    background-color: #b71c1c;
    border-color: #b71c1c;
}

/* Responsividad */
@media (max-width: 768px) {
    .management-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .elegant-table {
        display: block;
        overflow-x: auto;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: 15px;
    }
}
</style>