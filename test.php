<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Prueba de layout</title>
    <link rel="stylesheet" href="<?php echo BASE_URL . 'public/css/style.css'; ?>">
</head>

<body>
    <?php include __DIR__ . '/views/layout/header.php'; ?>
    <h1 style="padding: 20px; background: yellow;">Contenido de prueba</h1>
    <?php include __DIR__ . '/views/layout/footer.php'; ?>
</body>

</html>