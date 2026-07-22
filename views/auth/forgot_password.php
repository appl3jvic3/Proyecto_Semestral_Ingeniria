<div class="login-container">
    <h2>Recuperar Contraseña</h2>
    <p>Ingresa tu correo electrónico para recibir un enlace de recuperación</p>
    <form method="POST">
        <div class="form-group">
            <label class="required">Correo Electrónico</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enviar Enlace</button>
        </div>
        <div class="form-links">
            <a href="index.php?controller=auth&action=login">Volver al Inicio de Sesión</a>
        </div>
    </form>
</div>