<?php
class Promotion
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtiene todas las promociones con filtros opcionales
     * @param array $filters ['status' => 'vigente', 'search' => 'nombre']
     * @return array Lista de promociones con campo 'product_ids' (string CSV)
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT p.*, 
                       COALESCE(GROUP_CONCAT(pp.product_id), '') as product_ids 
                FROM promotions p 
                LEFT JOIN promotion_products pp ON p.id = pp.promotion_id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND p.name LIKE ?";
            $params[] = "%{$filters['search']}%";
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una promoción por su ID, incluyendo los productos asociados
     * @param int $id
     * @return array|false Datos de la promoción o false si no existe
     */
    public function findById($id)
    {
        $sql = "SELECT p.*, 
                       COALESCE(GROUP_CONCAT(pp.product_id), '') as product_ids 
                FROM promotions p 
                LEFT JOIN promotion_products pp ON p.id = pp.promotion_id 
                WHERE p.id = ? 
                GROUP BY p.id";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    /**
     * Obtiene todas las promociones activas (vigentes en la fecha actual)
     * con sus productos asociados.
     * @return array Lista de promociones vigentes
     */
    public function getActivePromotions()
    {
        $now = date('Y-m-d');
        $sql = "SELECT p.*, 
                       COALESCE(GROUP_CONCAT(pp.product_id), '') as product_ids 
                FROM promotions p 
                LEFT JOIN promotion_products pp ON p.id = pp.promotion_id 
                WHERE p.status = 'vigente' 
                  AND p.start_date <= ? 
                  AND p.end_date >= ? 
                GROUP BY p.id 
                ORDER BY p.discount_percent DESC";
        $stmt = $this->db->query($sql, [$now, $now]);
        return $stmt->fetchAll();
    }

    /**
     * Crea una nueva promoción y asigna los productos seleccionados
     * @param array $data ['name', 'discount_percent', 'start_date', 'end_date', 'description', 'product_ids']
     * @return int|false ID de la promoción creada o false en caso de error
     */
    public function create($data)
    {
        $this->db->query("START TRANSACTION");

        try {
            // Calcular estado automáticamente según fechas
            $now = date('Y-m-d');
            $status = 'proximo';
            if ($data['start_date'] <= $now && $data['end_date'] >= $now) {
                $status = 'vigente';
            } elseif ($data['end_date'] < $now) {
                $status = 'vencido';
            }

            $sql = "INSERT INTO promotions 
                    (name, discount_percent, start_date, end_date, status, description, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, [
                $data['name'],
                $data['discount_percent'],
                $data['start_date'],
                $data['end_date'],
                $status,
                $data['description'] ?? null,
                $data['created_by'] ?? $_SESSION['user']['id'] ?? null
            ]);

            $promotionId = $this->db->lastInsertId();

            // Asignar productos a la promoción
            if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
                foreach ($data['product_ids'] as $productId) {
                    $this->db->query(
                        "INSERT INTO promotion_products (promotion_id, product_id) VALUES (?, ?)",
                        [$promotionId, $productId]
                    );
                }
            }

            $this->db->query("COMMIT");
            return $promotionId;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return false;
        }
    }

    /**
     * Actualiza una promoción existente y sus productos asociados
     * @param int $id ID de la promoción
     * @param array $data Campos a actualizar (mismos que en create)
     * @return bool True si se actualizó correctamente, false en caso de error
     */
    public function update($id, $data)
    {
        $this->db->query("START TRANSACTION");

        try {
            // Calcular estado automáticamente
            $now = date('Y-m-d');
            $status = 'proximo';
            if ($data['start_date'] <= $now && $data['end_date'] >= $now) {
                $status = 'vigente';
            } elseif ($data['end_date'] < $now) {
                $status = 'vencido';
            }

            $sql = "UPDATE promotions SET 
                    name = ?, 
                    discount_percent = ?, 
                    start_date = ?, 
                    end_date = ?, 
                    status = ?, 
                    description = ? 
                    WHERE id = ?";
            $this->db->query($sql, [
                $data['name'],
                $data['discount_percent'],
                $data['start_date'],
                $data['end_date'],
                $status,
                $data['description'] ?? null,
                $id
            ]);

            // Reemplazar productos asociados
            $this->db->query("DELETE FROM promotion_products WHERE promotion_id = ?", [$id]);

            if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
                foreach ($data['product_ids'] as $productId) {
                    $this->db->query(
                        "INSERT INTO promotion_products (promotion_id, product_id) VALUES (?, ?)",
                        [$id, $productId]
                    );
                }
            }

            $this->db->query("COMMIT");
            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            return false;
        }
    }

    /**
     * Elimina una promoción (borrado físico, con cascada a promotion_products)
     * @param int $id
     * @return bool True si se eliminó correctamente
     */
    public function delete($id)
    {
        return $this->db->query("DELETE FROM promotions WHERE id = ?", [$id]);
    }
}
