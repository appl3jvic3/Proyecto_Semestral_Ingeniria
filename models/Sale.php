<?php
class Sale
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create($data)
    {
        $this->db->query("START TRANSACTION");

        try {
            $sql = "INSERT INTO sales (user_id, total, shipping_cost, discount, status) VALUES (?, ?, ?, ?, ?)";
            $this->db->query($sql, [
                $data['user_id'] ?? null,
                $data['total'],
                $data['shipping_cost'] ?? 0,
                $data['discount'] ?? 0,
                'confirmada'
            ]);

            $saleId = $this->db->lastInsertId();

            // Insertar detalles
            foreach ($data['items'] as $item) {
                $sql = "INSERT INTO sale_details (sale_id, product_id, quantity, price, discount) VALUES (?, ?, ?, ?, ?)";
                $this->db->query($sql, [
                    $saleId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['discount'] ?? 0
                ]);

                // Actualizar stock
                $this->db->query("UPDATE products SET stock = stock - ? WHERE id = ?", [$item['quantity'], $item['product_id']]);
            }

            $this->db->query("COMMIT");
            return $saleId;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return false;
        }
    }

    public function findById($id)
    {
        $stmt = $this->db->query("SELECT * FROM sales WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function getDetails($saleId)
    {
        $sql = "SELECT sd.*, p.name as product_name 
                FROM sale_details sd 
                JOIN products p ON sd.product_id = p.id 
                WHERE sd.sale_id = ?";
        $stmt = $this->db->query($sql, [$saleId]);
        return $stmt->fetchAll();
    }

    public function getByUser($userId)
    {
        $stmt = $this->db->query("SELECT * FROM sales WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
        return $stmt->fetchAll();
    }

    public function getMonthlyStats()
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_sales, SUM(total) as total_revenue 
                FROM sales WHERE status != 'cancelada' 
                GROUP BY month ORDER BY month DESC LIMIT 12";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
