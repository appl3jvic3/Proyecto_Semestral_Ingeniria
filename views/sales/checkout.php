<div class="checkout-container">
    <h2>Finalizar Compra</h2>

    <div class="order-summary">
        <h3>Resumen del Pedido</h3>
        <p>Subtotal: $<?= number_format($subtotal ?? 0, 2) ?></p>
        <p>Envío: $<?= number_format($shipping ?? 0, 2) ?></p>
        <p><strong>Total: $<?= number_format($total ?? 0, 2) ?></strong></p>
    </div>

    <form method="POST" class="checkout-form" data-validate>
        <div class="form-section">
            <h3>Información de Contacto</h3>

            <div class="form-group">
                <label class="required">Correo Electrónico</label>
                <input type="email" name="email" value="<?= isset($user['email']) ? $user['email'] : '' ?>" required>
                <small>Se enviará la factura a este correo</small>
            </div>

            <div class="form-group">
                <label class="required">Nombre Completo</label>
                <input type="text" name="name" value="<?= isset($user['name']) ? $user['name'] : '' ?>" required>
            </div>

            <div class="form-group">
                <label class="required">Teléfono</label>
                <input type="tel" name="phone" value="<?= isset($user['phone']) ? $user['phone'] : '' ?>" required>
            </div>
        </div>

        <div class="form-section">
            <h3>Dirección de Envío</h3>

            <div class="form-group">
                <label class="required">Dirección Completa</label>
                <textarea name="address" rows="3" required></textarea>
            </div>

            <div class="form-group">
                <label>Instrucciones de Entrega (Opcional)</label>
                <textarea name="delivery_notes" rows="2"></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Método de Pago</h3>

            <div class="form-group">
                <label class="required">Seleccionar Método</label>
                <select name="payment_method" required>
                    <option value="">Seleccionar...</option>
                    <option value="visa">Visa</option>
                    <option value="mastercard">Mastercard</option>
                    <option value="paypal">PayPal</option>
                    <option value="stripe">Stripe</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php?controller=sale&action=cart" class="btn btn-secondary">Volver al Carrito</a>
            <button type="submit" class="btn btn-success">Confirmar Compra</button>
        </div>
    </form>
</div>