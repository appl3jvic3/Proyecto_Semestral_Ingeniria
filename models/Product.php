<?php
class Product
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll($filters = [])
    {
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR product_id_comercial LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['low_stock'])) {
            $sql .= " AND stock <= reorder_point";
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function findByComercialId($comercialId)
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE product_id_comercial = ?", [$comercialId]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO products (product_id_comercial, name, model, category, supplier, price, stock, reorder_point, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $data['product_id_comercial'],
            $data['name'],
            $data['model'] ?? null,
            $data['category'] ?? null,
            $data['supplier'] ?? null,
            $data['price'],
            $data['stock'] ?? 0,
            $data['reorder_point'] ?? 10,
            $data['created_by'] ?? $_SESSION['user']['id'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }

    public function updateStock($id, $newStock, $userId, $reason, $observations = null)
    {
        // Obtener stock actual
        $product = $this->findById($id);
        if (!$product) return false;

        $oldStock = $product['stock'];

        // Actualizar stock
        $this->db->query("UPDATE products SET stock = ? WHERE id = ?", [$newStock, $id]);

        // Registrar en log de auditoría
        $sql = "INSERT INTO inventory_logs (product_id, user_id, old_stock, new_stock, reason, observations) 
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, [$id, $userId, $oldStock, $newStock, $reason, $observations]);
    }

    public function delete($id)
    {
        // Borrado lógico
        return $this->db->query("UPDATE products SET status = 'inactivo' WHERE id = ?", [$id]);
    }

    public function getLowStockProducts()
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE stock <= reorder_point AND status = 'activo' ORDER BY stock ASC");
        return $stmt->fetchAll();
    }

    public function getCategories()
    {
        $stmt = $this->db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
