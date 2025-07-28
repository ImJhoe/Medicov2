<?php include 'views/layouts/header.php'; ?>

<main class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-user-plus"></i> Crear Nuevo Doctor</h1>
        <div class="header-actions">
            <a href="index.php?action=doctores" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <div class="content-body">
        <div class="form-container">
            <form method="POST" class="doctor-form">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="apellido">Apellido *</label>
                        <input type="text" id="apellido" name="apellido" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="cedula">Cédula *</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="especialidades">Especialidades *</label>
                    <div class="checkbox-group">
                        <?php foreach ($especialidades as $especialidad): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" 
                                   id="esp_<?= $especialidad['id_especialidad'] ?>" 
                                   name="especialidades[]" 
                                   value="<?= $especialidad['id_especialidad'] ?>">
                            <label for="esp_<?= $especialidad['id_especialidad'] ?>">
                                <?= htmlspecialchars($especialidad['nombre_especialidad']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Crear Doctor
                    </button>
                    <a href="index.php?action=doctores" class="btn btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.form-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 800px;
    margin: 0 auto;
}

.doctor-form .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.doctor-form .form-group {
    margin-bottom: 20px;
}

.doctor-form .form-group.col-md-6 {
    flex: 1;
}

.doctor-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.doctor-form .form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.doctor-form .form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 10px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #f8f9fa;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.checkbox-item label {
    margin: 0;
    cursor: pointer;
    font-weight: normal;
}

.form-footer {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #545b62;
}

/* Responsive */
@media (max-width: 768px) {
    .doctor-form .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .form-container {
        padding: 20px;
        margin: 10px;
    }
    
    .checkbox-group {
        grid-template-columns: 1fr;
    }
}
</style>
