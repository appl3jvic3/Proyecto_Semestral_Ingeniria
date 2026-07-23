<?php
// Asegurar que BASE_PATH y BASE_URL estén definidas
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/');
}
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    define('BASE_URL', $protocol . $host . $scriptDir . '/');
}

function view($viewName, $data = [], $action = 'list')
{
    $data['action'] = $action;
    extract($data);

    // Incluir header
    require_once __DIR__ . '/views/layout/header.php';

    // Incluir la vista específica
    $viewFile = __DIR__ . '/views/' . $viewName . '.php';
    if (file_exists($viewFile)) {
        require_once $viewFile;
    } else {
        echo "<div class='alert alert-error'>Error: Vista '$viewName' no encontrada</div>";
    }

    // Incluir footer
    require_once __DIR__ . '/views/layout/footer.php';
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function baseUrl($path = '')
{
    return BASE_URL . ltrim($path, '/');
}

function setFlash($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

function getFlash($type)
{
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function hasRole($role)
{
    return isLoggedIn() && $_SESSION['user']['role'] === $role;
}

function hasAnyRole($roles = [])
{
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['user']['role'], $roles);
}

function sanitize($input)
{
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function generateInvoiceNumber()
{
    $db = Database::getInstance();
    $result = $db->query("SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
    $last = $result->fetch(PDO::FETCH_ASSOC);
    $num = $last ? intval(substr($last['invoice_number'], -4)) + 1 : 1;
    return "FAC-" . date('Ymd') . "-" . str_pad($num, 4, '0', STR_PAD_LEFT);
}
