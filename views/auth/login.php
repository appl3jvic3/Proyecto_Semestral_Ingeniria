<div class="login-container">
    <h2>Iniciar Sesión</h2>
    <form method="POST">
        <div class="form-group">
            <label class="required">Correo Electrónico</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label class="required">Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </div>
        <div class="form-links">
            <a href="index.php?controller=auth&action=register">Registrarse</a>
            <a href="index.php?controller=auth&action=forgotPassword">Olvidé mi contraseña</a>
        </div>
    </form>
</div>