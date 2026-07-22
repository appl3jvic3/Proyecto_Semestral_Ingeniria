<?php
class ProductController
{
    private $productModel;

    public function __construct()
    {
        if (!isLoggedIn() || !hasAnyRole(['admin', 'inventario'])) {
            setFlash('error', 'Acceso denegado');
            redirect('index.php?controller=dashboard&action=index');
        }
        $db = Database::getInstance();
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
            $product = $this->productModel->findById($_GET['id']);
            if (!$product) {
                setFlash('error', 'Producto no encontrado');
                redirect('index.php?controller=product&action=index');
            }
            $data['product'] = $product;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processUpdate($_GET['id']);
                return;
            }
            view('products/index', $data, 'edit');
            return;
        }

        if ($action === 'adjust_stock' && isset($_GET['id'])) {
            $product = $this->productModel->findById($_GET['id']);
            if (!$product) {
                setFlash('error', 'Producto no encontrado');
                redirect('index.php?controller=product&action=index');
            }
            $data['product'] = $product;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processAdjustStock($_GET['id']);
                return;
            }
            view('products/index', $data, 'adjust_stock');
            return;
        }

        if ($action === 'delete' && isset($_GET['id'])) {
            $this->processDelete($_GET['id']);
            return;
        }

        // Listado
        $filters = [];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['low_stock'])) $filters['low_stock'] = true;

        $data['products'] = $this->productModel->findAll($filters);
        $data['categories'] = $this->productModel->getCategories();
        $data['low_stock_count'] = count($this->productModel->getLowStockProducts());
        view('products/index', $data, 'list');
    }

    private function processCreate()
    {
        $productId = $_POST['product_id_comercial'] ?? '';
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $reorderPoint = $_POST['reorder_point'] ?? 10;
        $category = $_POST['category'] ?? '';
        $supplier = $_POST['supplier'] ?? '';
        $model = $_POST['model'] ?? '';

        $errors = [];
        if (empty($productId)) $errors[] = 'ID de producto es obligatorio';
        if (empty($name)) $errors[] = 'Nombre del producto es obligatorio';
        if ($price <= 0) $errors[] = 'El precio debe ser mayor a 0';
        if ($stock < 0) $errors[] = 'El stock no puede ser negativo';
        if ($reorderPoint < 0) $errors[] = 'El punto de reorden no puede ser negativo';

        if (!empty($errors)) {
            foreach ($errors as $error) setFlash('error', $error);
            view('products/index', ['action' => 'create'], 'create');
            return;
        }

        if ($this->productModel->findByComercialId($productId)) {
            setFlash('error', 'El ID de producto ya existe');
            view('products/index', ['action' => 'create'], 'create');
            return;
        }

        $data = [
            'product_id_comercial' => $productId,
            'name' => $name,
            'model' => $model,
            'category' => $category,
            'supplier' => $supplier,
            'price' => $price,
            'stock' => $stock,
            'reorder_point' => $reorderPoint,
            'created_by' => $_SESSION['user']['id']
        ];

        if ($this->productModel->create($data)) {
            setFlash('success', 'Producto creado correctamente');
            redirect('index.php?controller=product&action=index');
        } else {
            setFlash('error', 'Error al crear producto');
            view('products/index', ['action' => 'create'], 'create');
        }
    }

    private function processUpdate($id)
    {
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $category = $_POST['category'] ?? '';
        $supplier = $_POST['supplier'] ?? '';
        $model = $_POST['model'] ?? '';
        $reorderPoint = $_POST['reorder_point'] ?? 10;
        $status = $_POST['status'] ?? 'activo';

        if (empty($name) || $price <= 0) {
            setFlash('error', 'Nombre y precio son obligatorios');
            redirect('index.php?controller=product&action=index&subaction=edit&id=' . $id);
            return;
        }

        $data = [
            'name' => $name,
            'model' => $model,
            'category' => $category,
            'supplier' => $supplier,
            'price' => $price,
            'reorder_point' => $reorderPoint,
            'status' => $status
        ];

        if ($this->productModel->update($id, $data)) {
            setFlash('success', 'Producto actualizado correctamente');
        } else {
            setFlash('error', 'Error al actualizar producto');
        }
        redirect('index.php?controller=product&action=index');
    }

    private function processAdjustStock($id)
    {
        $newStock = $_POST['new_stock'] ?? -1;
        $reason = $_POST['reason'] ?? '';
        $observations = $_POST['observations'] ?? '';

        if ($newStock < 0) {
            setFlash('error', 'La cantidad debe ser un número entero no negativo');
            redirect('index.php?controller=product&action=index&subaction=adjust_stock&id=' . $id);
            return;
        }

        if (empty($reason)) {
            setFlash('error', 'El motivo del ajuste es obligatorio');
            redirect('index.php?controller=product&action=index&subaction=adjust_stock&id=' . $id);
            return;
        }

        if ($this->productModel->updateStock($id, $newStock, $_SESSION['user']['id'], $reason, $observations)) {
            setFlash('success', 'Stock actualizado correctamente');
        } else {
            setFlash('error', 'Error al actualizar stock');
        }
        redirect('index.php?controller=product&action=index');
    }

    private function processDelete($id)
    {
        if ($this->productModel->delete($id)) {
            setFlash('success', 'Producto desactivado correctamente');
        } else {
            setFlash('error', 'Error al desactivar producto');
        }
        redirect('index.php?controller=product&action=index');
    }
}
