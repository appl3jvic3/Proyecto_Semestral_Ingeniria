<?php
class PromotionController
{
    private $promotionModel;
    private $productModel;

    public function __construct()
    {
        if (!isLoggedIn() || !hasAnyRole(['admin', 'marketing'])) {
            setFlash('error', 'Acceso denegado');
            redirect('index.php?controller=dashboard&action=index');
        }
        $db = Database::getInstance();
        $this->promotionModel = new Promotion($db);
        $this->productModel = new Product($db);
    }

    public function index()
    {
        $action = $_GET['subaction'] ?? 'list';
        $data = ['action' => $action];

        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
            return;
        }

        if ($action === 'edit' && isset($_GET['id'])) {
            $promotion = $this->promotionModel->findById($_GET['id']);
            if (!$promotion) {
                setFlash('error', 'Promoción no encontrada');
                redirect('index.php?controller=promotion&action=index');
            }
            $data['promotion'] = $promotion;
            $data['product_ids'] = !empty($promotion['product_ids']) ? explode(',', $promotion['product_ids']) : [];
            $data['all_products'] = $this->productModel->findAll(['status' => 'activo']);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processUpdate($_GET['id']);
                return;
            }
            view('promotions/index', $data, 'edit');
            return;
        }

        if ($action === 'delete' && isset($_GET['id'])) {
            $this->processDelete($_GET['id']);
            return;
        }

        // Listado
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

        $data['promotions'] = $this->promotionModel->findAll($filters);
        $data['all_products'] = $this->productModel->findAll(['status' => 'activo']);
        view('promotions/index', $data, 'list');
    }

    private function processCreate()
    {
        $name = $_POST['name'] ?? '';
        $discount = $_POST['discount_percent'] ?? 0;
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $description = $_POST['description'] ?? '';
        $productIds = $_POST['product_ids'] ?? [];

        $errors = [];
        if (empty($name)) $errors[] = 'Nombre de la promoción es obligatorio';
        if ($discount <= 0 || $discount > 100) $errors[] = 'El descuento debe ser entre 1 y 100%';
        if (empty($startDate)) $errors[] = 'Fecha de inicio es obligatoria';
        if (empty($endDate)) $errors[] = 'Fecha de fin es obligatoria';
        if ($startDate > $endDate) $errors[] = 'La fecha de inicio no puede ser mayor a la fecha de fin';
        if (empty($productIds)) $errors[] = 'Debe seleccionar al menos un producto';

        if (!empty($errors)) {
            foreach ($errors as $error) setFlash('error', $error);
            $data = ['action' => 'create', 'all_products' => $this->productModel->findAll(['status' => 'activo'])];
            view('promotions/index', $data, 'create');
            return;
        }

        $data = [
            'name' => $name,
            'discount_percent' => $discount,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $description,
            'product_ids' => $productIds,
            'created_by' => $_SESSION['user']['id']
        ];

        if ($this->promotionModel->create($data)) {
            setFlash('success', 'Promoción creada correctamente');
            redirect('index.php?controller=promotion&action=index');
        } else {
            setFlash('error', 'Error al crear promoción');
            $data = ['action' => 'create', 'all_products' => $this->productModel->findAll(['status' => 'activo'])];
            view('promotions/index', $data, 'create');
        }
    }

    private function processUpdate($id)
    {
        $name = $_POST['name'] ?? '';
        $discount = $_POST['discount_percent'] ?? 0;
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $description = $_POST['description'] ?? '';
        $productIds = $_POST['product_ids'] ?? [];

        if (empty($name) || $discount <= 0 || $discount > 100 || empty($startDate) || empty($endDate) || $startDate > $endDate) {
            setFlash('error', 'Datos inválidos');
            redirect('index.php?controller=promotion&action=index&subaction=edit&id=' . $id);
            return;
        }

        $data = [
            'name' => $name,
            'discount_percent' => $discount,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $description,
            'product_ids' => $productIds
        ];

        if ($this->promotionModel->update($id, $data)) {
            setFlash('success', 'Promoción actualizada correctamente');
        } else {
            setFlash('error', 'Error al actualizar promoción');
        }
        redirect('index.php?controller=promotion&action=index');
    }

    private function processDelete($id)
    {
        if ($this->promotionModel->delete($id)) {
            setFlash('success', 'Promoción eliminada correctamente');
        } else {
            setFlash('error', 'Error al eliminar promoción');
        }
        redirect('index.php?controller=promotion&action=index');
    }
}
