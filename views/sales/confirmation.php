<div class="confirmation-container">
    <div class="success-icon">✓</div>
    <h2>¡Compra Realizada con Éxito!</h2>
    <p class="order-number">Número de Orden: #<?= $sale['id'] ?? '' ?></p>

    <div class="order-details">
        <h3>Detalles de la Orden</h3>
        <p><strong>Fecha:</strong> <?= isset($sale['created_at']) ? date('d/m/Y H:i', strtotime($sale['created_at'])) : '' ?></p>
        <p><strong>Total:</strong> $<?= number_format($sale['total'] ?? 0, 2) ?></p>
        <p><strong>Estado:</strong> <span class="badge badge-success"><?= $sale['status'] ?? 'confirmada' ?></span></p>

        <?php if (isset($invoice) && $invoice): ?>
            <p><strong>Factura:</strong> <?= $invoice['invoice_number'] ?></p>
        <?php endif; ?>
    </div>

    <div class="order-items">
        <h3>Productos</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details ?? [])): ?>
                        <?php foreach ($details as $item): ?>
                            <tr>
                                <td><?= sanitize($item['product_name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="confirmation-actions">
        <a href="index.php?controller=sale&action=catalog" class="btn btn-primary">Seguir Comprando</a>
        <a href="index.php?controller=dashboard&action=index" class="btn btn-secondary">Ir al Dashboard</a>
    </div>

    <p class="email-note">
        <small>Se ha enviado un correo con la factura a tu dirección de correo.</small>
    </p>
</div>