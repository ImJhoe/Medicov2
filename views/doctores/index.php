<?php include 'views/layouts/header.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-user-md"></i> Gestión de Doctores</h1>
        <div class="header-actions">
            <a href="index.php?action=doctores/crear" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Nuevo Doctor
            </a>
        </div>
    </div>

    <div class="content-body">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Especialidades</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doctores as $doctor): ?>
                    <tr>
                        <td><?= htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']) ?></td>
                        <td><?= htmlspecialchars($doctor['cedula']) ?></td>
                        <td><?= htmlspecialchars($doctor['email']) ?></td>
                        <td><?= htmlspecialchars($doctor['telefono']) ?></td>
                        <td><?= htmlspecialchars($doctor['especialidades']) ?></td>
                        <td>
                            <a href="index.php?action=doctores/horarios&id=<?= $doctor['id_usuario'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-alt"></i>
                                Horarios
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
