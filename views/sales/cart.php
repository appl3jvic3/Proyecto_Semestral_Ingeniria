<div class="cart-container">
    <h2>Carrito de Compras</h2>

    <?php if (empty($cartItems ?? [])): ?>
        <p>El carrito está vacío</p>
        <a href="index.php?controller=sale&action=catalog" class="btn btn-primary">Seguir Comprando</a>
    <?php else: ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $id => $item): ?>
                        <tr>
                            <td><?= sanitize($item['product']['name']) ?></td>
                            <td>$<?= number_format($item['product']['price'], 2) ?></td>
                            <td>
                                <form method="POST" class="update-cart-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['product']['stock'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                                </form>
                            </td>
                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Subtotal</strong></td>
                        <td><strong>$<?= number_format($subtotal ?? 0, 2) ?></strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>Envío</strong></td>
                        <td><strong>$<?= number_format($shipping ?? 0, 2) ?></strong></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>$<?= number_format($total ?? 0, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="cart-actions">
            <form method="POST">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-danger">Vaciar Carrito</button>
            </form>
            <a href="index.php?controller=sale&action=catalog" class="btn btn-secondary">Seguir Comprando</a>
            <a href="index.php?controller=sale&action=checkout" class="btn btn-success">Proceder al Pago</a>
        </div>

    <?php endif; ?>
</div>