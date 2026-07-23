<?php if ($action === 'list'): ?>
    <div class="products-container">
        <h2>Gestión de Inventario</h2>

        <?php if (isset($low_stock_count) && $low_stock_count > 0): ?>
            <div class="alert alert-warning">
                ⚠️ Hay <?= $low_stock_count ?> producto(s) con stock bajo.
                <a href="?controller=product&action=index&low_stock=1">Ver productos</a>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="?controller=product&action=index&subaction=create" class="btn btn-primary">Agregar Producto</a>
        </div>

        <!-- Filtros de búsqueda -->
        <div class="filters">
            <form method="GET">
                <input type="hidden" name="controller" value="product">
                <input type="hidden" name="action" value="index">
                <input type="text" name="search" placeholder="Buscar producto..." value="<?= $_GET['search'] ?? '' ?>">
                <select name="category">
                    <option value="">Todas las categorías</option>
                    <?php foreach (($categories ?? []) as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status">
                    <option value="">Todos los estados</option>
                    <option value="activo" <?= ($_GET['status'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($_GET['status'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>

        <!-- Tabla de productos -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Punto Reorden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products ?? [])): ?>
                        <tr>
                            <td colspan="8">No hay productos registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr class="<?= $product['stock'] <= $product['reorder_point'] ? 'low-stock-row' : '' ?>">
                                <td><?= sanitize($product['product_id_comercial']) ?></td>
                                <td><?= sanitize($product['name']) ?></td>
                                <td><?= sanitize($product['category']) ?></td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                                <td>
                                    <?= $product['stock'] ?>
                                    <?php if ($product['stock'] <= $product['reorder_point']): ?>
                                        <span class="badge badge-warning">¡Bajo!</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $product['reorder_point'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $product['status'] ?>">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?controller=product&action=index&subaction=edit&id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?controller=product&action=index&subaction=adjust_stock&id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Ajustar Stock</a>
                                    <a href="?controller=product&action=index&subaction=delete&id=<?= $product['id'] ?>" class="btn btn-danger btn-sm btn-delete">Eliminar</a>
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
        <h2>Agregar Producto</h2>
        <form method="POST">
            <div class="form-group">
                <label class="required">ID de Producto (Comercial)</label>
                <input type="text" name="product_id_comercial" required placeholder="Ej: PROD-001">
                <small>Identificador único del producto</small>
            </div>
            <div class="form-group">
                <label class="required">Nombre del Producto</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="model">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <input type="text" name="category">
                </div>
            </div>
            <div class="form-group">
                <label>Proveedor</label>
                <input type="text" name="supplier">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="required">Precio Unitario</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="required">Cantidad Inicial en Stock</label>
                    <input type="number" name="stock" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label class="required">Punto de Reorden</label>
                <input type="number" name="reorder_point" min="0" value="10" required>
                <small>Cantidad mínima para activar alerta de stock bajo</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar Producto</button>
                <a href="?controller=product&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

<?php elseif ($action === 'edit'): ?>
    <div class="form-container">
        <h2>Editar Producto</h2>
        <form method="POST">
            <div class="form-group">
                <label class="required">Nombre del Producto</label>
                <input type="text" name="name" value="<?= sanitize($product['name'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="model" value="<?= sanitize($product['model'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <input type="text" name="category" value="<?= sanitize($product['category'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Proveedor</label>
                <input type="text" name="supplier" value="<?= sanitize($product['supplier'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="required">Precio Unitario</label>
                    <input type="number" name="price" step="0.01" min="0" value="<?= $product['price'] ?? 0 ?>" required>
                </div>
                <div class="form-group">
                    <label class="required">Punto de Reorden</label>
                    <input type="number" name="reorder_point" min="0" value="<?= $product['reorder_point'] ?? 10 ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="status">
                    <option value="activo" <?= (isset($product['status']) && $product['status'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= (isset($product['status']) && $product['status'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Actualizar Producto</button>
                <a href="?controller=product&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

<?php elseif ($action === 'adjust_stock'): ?>
    <div class="form-container">
        <h2>Ajustar Stock</h2>
        <div class="product-info">
            <p><strong>Producto:</strong> <?= sanitize($product['name'] ?? '') ?></p>
            <p><strong>ID:</strong> <?= sanitize($product['product_id_comercial'] ?? '') ?></p>
            <p><strong>Stock Actual:</strong> <?= $product['stock'] ?? 0 ?></p>
            <p><strong>Punto de Reorden:</strong> <?= $product['reorder_point'] ?? 10 ?></p>
        </div>
        <form method="POST">
            <div class="form-group">
                <label class="required">Nueva Cantidad en Stock</label>
                <input type="number" name="new_stock" min="0" value="<?= $product['stock'] ?? 0 ?>" required>
            </div>
            <div class="form-group">
                <label class="required">Motivo del Ajuste</label>
                <select name="reason" required>
                    <option value="">Seleccionar motivo...</option>
                    <option value="compra_proveedor">Compra a proveedor</option>
                    <option value="devolucion_cliente">Devolución de cliente</option>
                    <option value="merma">Merma / Daño</option>
                    <option value="ajuste_inventario">Ajuste manual de inventario</option>
                    <option value="venta">Venta registrada</option>
                </select>
            </div>
            <div class="form-group">
                <label>Observaciones</label>
                <textarea name="observations" rows="3" placeholder="Detalles adicionales sobre el ajuste..."></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Confirmar Ajuste</button>
                <a href="?controller=product&action=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
<?php endif; ?>