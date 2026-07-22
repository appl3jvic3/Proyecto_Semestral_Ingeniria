<?php if ($action === 'list'): ?>
    <div class="table-container">
        <h2>Gestión de Usuarios</h2>
        <div class="actions">
            <a href="?controller=user&action=index&subaction=create" class="btn btn-primary">Agregar Usuario</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users ?? [])): ?>
                    <tr>
                        <td colspan="6">No hay usuarios registrados</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= sanitize($u['name']) ?></td>
                            <td><?= sanitize($u['email']) ?></td>
                            <td><?= sanitize($u['role']) ?></td>
                            <td><?= sanitize($u['status']) ?></td>
                            <td>
                                <a href="?controller=user&action=index&subaction=edit&id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="?controller=user&action=index&subaction=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm btn-delete">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($action === 'create'): ?>
    <div class="form-container">
        <h2>Crear Usuario</h2>
        <form method="POST">
            <div class="form-group">
                <label class="required">Nombre Completo</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label class="required">Correo Electrónico</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label class="required">Contraseña</label>
                <input type="password" name="password" id="password" required minlength="8">
            </div>
            <div class="form-group">
                <label class="required">Rol</label>
                <select name="role" required>
                    <option value="cliente">Cliente</option>
                    <option value="inventario">Encargado de Inventario</option>
                    <option value="marketing">Encargado de Marketing</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="phone">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="?controller=user&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php elseif ($action === 'edit'): ?>
    <div class="form-container">
        <h2>Editar Usuario</h2>
        <form method="POST">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" value="<?= sanitize($user['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="role">
                    <option value="cliente" <?= (isset($user['role']) && $user['role'] === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                    <option value="inventario" <?= (isset($user['role']) && $user['role'] === 'inventario') ? 'selected' : '' ?>>Encargado de Inventario</option>
                    <option value="marketing" <?= (isset($user['role']) && $user['role'] === 'marketing') ? 'selected' : '' ?>>Encargado de Marketing</option>
                    <option value="admin" <?= (isset($user['role']) && $user['role'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="status">
                    <option value="activo" <?= (isset($user['status']) && $user['status'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= (isset($user['status']) && $user['status'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" name="password" minlength="8">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Actualizar</button>
                <a href="?controller=user&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php endif; ?>