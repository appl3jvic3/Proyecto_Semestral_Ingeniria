<?php
class ReportController
{

    public function __construct()
    {
        if (!isLoggedIn() || !hasRole('admin')) {
            setFlash('error', 'Acceso denegado');
            redirect('index.php?controller=dashboard&action=index');
        }
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->generateReport();
            return;
        }

        view('reports/index', ['action' => 'list'], 'list');
    }

    private function generateReport()
    {
        $type = $_POST['report_type'] ?? '';
        $format = $_POST['format'] ?? 'pdf';

        if (empty($type)) {
            setFlash('error', 'Debe seleccionar un tipo de reporte');
            view('reports/index', ['action' => 'list'], 'list');
            return;
        }

        switch ($type) {
            case 'sales':
                $data = $this->getSalesReport();
                break;
            case 'inventory':
                $data = $this->getInventoryReport();
                break;
            case 'users':
                $data = $this->getUsersReport();
                break;
            default:
                setFlash('error', 'Tipo de reporte no válido');
                view('reports/index', ['action' => 'list'], 'list');
                return;
        }

        if (empty($data)) {
            setFlash('warning', 'No hay datos para los filtros seleccionados');
            view('reports/index', ['action' => 'list'], 'list');
            return;
        }

        // Simular generación
        setFlash('success', "Reporte generado correctamente en formato {$format}. Descarga iniciada.");
        $_SESSION['report_data'] = $data;
        $_SESSION['report_type'] = $type;
        view('reports/index', ['action' => 'list', 'report_generated' => true], 'list');
    }

    private function getSalesReport()
    {
        $db = Database::getInstance();
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');

        $sql = "SELECT DATE(s.created_at) as fecha, s.id, u.name as cliente, s.total, s.status 
                FROM sales s 
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE DATE(s.created_at) BETWEEN ? AND ? 
                ORDER BY s.created_at DESC";
        $stmt = $db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function getInventoryReport()
    {
        $db = Database::getInstance();
        $category = $_POST['category'] ?? '';
        $minStock = $_POST['min_stock'] ?? 0;

        $sql = "SELECT p.*, COALESCE(COUNT(sd.id), 0) as veces_vendido 
                FROM products p 
                LEFT JOIN sale_details sd ON p.id = sd.product_id 
                WHERE p.status = 'activo'";
        $params = [];

        if (!empty($category)) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
        }

        if (!empty($minStock)) {
            $sql .= " AND p.stock <= ?";
            $params[] = $minStock;
        }

        $sql .= " GROUP BY p.id ORDER BY p.stock ASC";
        $stmt = $db->query($sql, $params);
        return $stmt->fetchAll();
    }

    private function getUsersReport()
    {
        $db = Database::getInstance();
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');

        $sql = "SELECT u.*, COUNT(s.id) as total_compras, COALESCE(SUM(s.total), 0) as total_gastado 
                FROM users u 
                LEFT JOIN sales s ON u.id = s.user_id AND DATE(s.created_at) BETWEEN ? AND ? 
                WHERE u.role = 'cliente' 
                GROUP BY u.id 
                ORDER BY total_gastado DESC";
        $stmt = $db->query($sql, [$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}
