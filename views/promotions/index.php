<?php if ($action === 'list'): ?>
    <div class="promotions-container">
        <h2>Gestión de Promociones</h2>

        <div class="actions">
            <a href="?controller=promotion&action=index&subaction=create" class="btn btn-primary">Crear Nueva Promoción</a>
        </div>

        <div class="filters">
            <form method="GET">
                <input type="hidden" name="controller" value="promotion">
                <input type="hidden" name="action" value="index">
                <input type="text" name="search" placeholder="Buscar promoción..." value="<?= $_GET['search'] ?? '' ?>">
                <select name="status">
                    <option value="">Todos los estados</option>
                    <option value="vigente" <?= ($_GET['status'] ?? '') === 'vigente' ? 'selected' : '' ?>>Vigente</option>
                    <option value="vencido" <?= ($_GET['status'] ?? '') === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                    <option value="proximo" <?= ($_GET['status'] ?? '') === 'proximo' ? 'selected' : '' ?>>Próximo</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descuento</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions ?? [])): ?>
                        <tr>
                            <td colspan="7">No hay promociones registradas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions as $promo): ?>
                            <tr>
                                <td><?= $promo['id'] ?></td>
                                <td><?= sanitize($promo['name']) ?></td>
                                <td><?= $promo['discount_percent'] ?>%</td>
                                <td><?= date('d/m/Y', strtotime($promo['start_date'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($promo['end_date'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $promo['status'] ?>">
                                        <?= ucfirst($promo['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?controller=promotion&action=index&subaction=edit&id=<?= $promo['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?controller=promotion&action=index&subaction=delete&id=<?= $promo['id'] ?>" class="btn btn-danger btn-sm btn-delete">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'create'): ?>
    <div class="form-container">
        <h2>Crear Promoción</h2>
        <form method="POST">
            <div class="form-group">
                <label class="required">Nombre de la Promoción</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label class="required">Porcentaje de Descuento</label>
                <input type="number" name="discount_percent" min="1" max="100" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Fecha de Inicio</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label class="required">Fecha de Fin</label>
                    <input type="date" name="end_date" required>
                </div>
            </div>

            <div class="form-group">
                <label class="required">Productos Aplicables</label>
                <select name="product_ids[]" multiple required style="height: 150px;">
                    <?php foreach (($all_products ?? []) as $prod): ?>
                        <option value="<?= $prod['id'] ?>">
                            <?= sanitize($prod['name']) ?> (Stock: <?= $prod['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Mantenga presionado Ctrl para seleccionar múltiples productos</small>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="?controller=promotion&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

<?php elseif ($action === 'edit'): ?>
    <div class="form-container">
        <h2>Editar Promoción</h2>
        <form method="POST">
            <div class="form-group">
                <label class="required">Nombre de la Promoción</label>
                <input type="text" name="name" value="<?= sanitize($promotion['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="required">Porcentaje de Descuento</label>
                <input type="number" name="discount_percent" value="<?= $promotion['discount_percent'] ?? 0 ?>" min="1" max="100" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Fecha de Inicio</label>
                    <input type="date" name="start_date" value="<?= $promotion['start_date'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label class="required">Fecha de Fin</label>
                    <input type="date" name="end_date" value="<?= $promotion['end_date'] ?? '' ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="required">Productos Aplicables</label>
                <select name="product_ids[]" multiple required style="height: 150px;">
                    <?php foreach (($all_products ?? []) as $prod): ?>
                        <option value="<?= $prod['id'] ?>" <?= in_array($prod['id'], $product_ids ?? []) ? 'selected' : '' ?>>
                            <?= sanitize($prod['name']) ?> (Stock: <?= $prod['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Mantenga presionado Ctrl para seleccionar múltiples productos</small>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" rows="3"><?= sanitize($promotion['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Actualizar</button>
                <a href="?controller=promotion&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php endif; ?>