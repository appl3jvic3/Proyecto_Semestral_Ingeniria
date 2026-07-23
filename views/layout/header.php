<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universe Zero - Sistema Gestor</title>
    <!-- Usamos BASE_URL para garantizar ruta absoluta -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'public/css/style.css'; ?>">
</head>

<body>
    <?php if (isLoggedIn()): ?>
        <nav class="navbar">
            <div class="container">
                <div class="nav-brand">
                    <img src="<?php echo BASE_URL . 'public/img/logo.png'; ?>" alt="Universe Zero" height="40">
                    <span>Universe Zero</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="<?php echo BASE_URL . 'index.php?controller=dashboard&action=index'; ?>">Dashboard</a></li>
                    <?php if (hasAnyRole(['admin', 'inventario'])): ?>
                        <li><a href="<?php echo BASE_URL . 'index.php?controller=product&action=index'; ?>">Inventario</a></li>
                    <?php endif; ?>
                    <?php if (hasAnyRole(['admin', 'marketing'])): ?>
                        <li><a href="<?php echo BASE_URL . 'index.php?controller=promotion&action=index'; ?>">Promociones</a></li>
                    <?php endif; ?>
                    <?php if (hasAnyRole(['admin'])): ?>
                        <li><a href="<?php echo BASE_URL . 'index.php?controller=user&action=index'; ?>">Usuarios</a></li>
                        <li><a href="<?php echo BASE_URL . 'index.php?controller=report&action=index'; ?>">Reportes</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL . 'index.php?controller=sale&action=catalog'; ?>">Tienda</a></li>
                    <li class="nav-user">
                        <span><?php echo sanitize($_SESSION['user']['name']); ?></span>
                        <span class="role-badge"><?php echo sanitize($_SESSION['user']['role']); ?></span>
                        <a href="<?php echo BASE_URL . 'index.php?controller=auth&action=logout'; ?>" class="btn-logout">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
            <?php if ($flash = getFlash('success')): ?>
                <div class="alert alert-success"><?php echo $flash; ?></div>
            <?php endif; ?>
            <?php if ($flash = getFlash('error')): ?>
                <div class="alert alert-error"><?php echo $flash; ?></div>
            <?php endif; ?>
            <?php if ($flash = getFlash('warning')): ?>
                <div class="alert alert-warning"><?php echo $flash; ?></div>
            <?php endif; ?>
            <!-- CONTENIDO DINÁMICO -->