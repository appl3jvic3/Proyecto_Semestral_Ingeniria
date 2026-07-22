<div class="register-container login-container">
    <h2>Registro de Usuario</h2>
    <form method="POST" data-validate>
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
            <small>Mínimo 8 caracteres</small>
        </div>
        <div class="form-group">
            <label class="required">Confirmar Contraseña</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <div class="form-group">
            <label>Teléfono (Opcional)</label>
            <input type="tel" name="phone">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Registrarse</button>
        </div>
        <div class="form-links">
            <a href="index.php?controller=auth&action=login">¿Ya tienes cuenta? Inicia Sesión</a>
        </div>
    </form>
</div>