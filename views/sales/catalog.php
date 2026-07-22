<div class="catalog">
    <h2>Catálogo de Productos</h2>

    <div class="filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="controller" value="sale">
            <input type="hidden" name="action" value="catalog">

            <div class="filter-group">
                <input type="text" name="search" placeholder="Buscar producto..." value="<?= $_GET['search'] ?? '' ?>">
            </div>
            <div class="filter-group">
                <select name="category">
                    <option value="">Todas las categorías</option>
                    <?php foreach (($categories ?? []) as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="sort">
                    <option value="name" <?= ($_GET['sort'] ?? '') === 'name' ? 'selected' : '' ?>>Ordenar por Nombre</option>
                    <option value="price" <?= ($_GET['sort'] ?? '') === 'price' ? 'selected' : '' ?>>Ordenar por Precio</option>
                    <option value="stock" <?= ($_GET['sort'] ?? '') === 'stock' ? 'selected' : '' ?>>Ordenar por Stock</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="products-grid">
        <?php if (empty($products ?? [])): ?>
            <p>No se encontraron productos</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product['has_discount']): ?>
                        <div class="discount-badge">-<?= $product['discount_percent'] ?>%</div>
                    <?php endif; ?>
                    <div class="product-image">
                        <img src="<?= baseUrl('public/img/product-placeholder.png') ?>" alt="<?= $product['name'] ?>">
                    </div>
                    <div class="product-info">
                        <h3><?= sanitize($product['name']) ?></h3>
                        <p class="product-category"><?= sanitize($product['category']) ?></p>
                        <?php if ($product['has_discount']): ?>
                            <p class="product-price">
                                <span class="original-price">$<?= number_format($product['price'], 2) ?></span>
                                <span class="discounted-price">$<?= number_format($product['discounted_price'], 2) ?></span>
                            </p>
                        <?php else: ?>
                            <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
                        <?php endif; ?>
                        <p class="product-stock <?= $product['stock'] <= $product['reorder_point'] ? 'low-stock' : '' ?>">
                            Stock: <?= $product['stock'] ?>
                        </p>
                        <form method="POST" action="index.php?controller=sale&action=cart" style="display:inline;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <div class="add-to-cart">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                <button type="submit" class="btn btn-primary" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    Agregar al Carrito
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="cart-summary">
        <a href="index.php?controller=sale&action=cart" class="btn btn-success">
            Ver Carrito (<?= $cart_count ?? 0 ?>)
        </a>
    </div>
</div>