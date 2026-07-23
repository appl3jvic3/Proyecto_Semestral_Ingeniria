<?php
session_start();
require_once __DIR__ . '/config/database.php'; // Carga BASE_URL y DB
require_once __DIR__ . '/functions.php';

spl_autoload_register(function ($className) {
    $paths = [__DIR__ . '/controllers/', __DIR__ . '/models/'];
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';
$controllerClass = ucfirst($controller) . 'Controller';

if (!class_exists($controllerClass)) {
    die('Error: Controlador no encontrado');
}
$instance = new $controllerClass();
if (!method_exists($instance, $action)) {
    die('Error: Acción no encontrada');
}
$instance->$action();
