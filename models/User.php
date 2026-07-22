<?php
class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll($filters = [])
    {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = ?", [$email]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (name, email, password, role, status, phone) VALUES (?, ?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'cliente',
            $data['status'] ?? 'activo',
            $data['phone'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $fields[] = "password = ?";
                $params[] = password_hash($value, PASSWORD_DEFAULT);
            } else {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }

    public function delete($id)
    {
        // Borrado lógico - solo cambiar estado a inactivo
        return $this->db->query("UPDATE users SET status = 'inactivo' WHERE id = ?", [$id]);
    }

    public function getStats()
    {
        $result = $this->db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        return $result->fetchAll();
    }
}
