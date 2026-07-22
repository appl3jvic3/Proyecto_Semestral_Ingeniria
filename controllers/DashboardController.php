<?php
class DashboardController
{

    public function __construct()
    {
        if (!isLoggedIn()) {
            redirect('index.php?controller=auth&action=login');
        }
    }

    public function index()
    {
        $db = Database::getInstance();
        $userModel = new User($db);
        $productModel = new Product($db);
        $saleModel = new Sale($db);

        $totalUsers = count($userModel->findAll());
        $totalProducts = count($productModel->findAll(['status' => 'activo']));
        $lowStock = count($productModel->getLowStockProducts());
        $monthlyStats = $saleModel->getMonthlyStats();

        $monthlySales = 0;
        $monthlyRevenue = 0;
        if (!empty($monthlyStats[0])) {
            $monthlySales = $monthlyStats[0]['total_sales'];
            $monthlyRevenue = $monthlyStats[0]['total_revenue'];
        }

        $recentActivity = $this->getRecentActivity();

        view('dashboard', [
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'low_stock' => $lowStock,
            'monthly_sales' => $monthlySales,
            'monthly_revenue' => $monthlyRevenue,
            'recent_activity' => $recentActivity
        ], 'dashboard');
    }

    private function getRecentActivity()
    {
        $db = Database::getInstance();
        $sql = "SELECT 'venta' as type, id, created_at 
                FROM sales 
                WHERE status != 'cancelada' 
                UNION 
                SELECT 'usuario' as type, id, created_at 
                FROM users 
                ORDER BY created_at DESC LIMIT 10";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }
}
