<?php

/**
 * Controlador de Ventas
 * Maneja el catálogo, carrito de compras, checkout y confirmación de pedidos.
 */
class SaleController
{
    private $productModel;
    private $saleModel;
    private $promotionModel;
    private $cart;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->productModel = new Product($db);
        $this->saleModel = new Sale($db);
        $this->promotionModel = new Promotion($db);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $this->cart = &$_SESSION['cart'];
    }

    /**
     * Muestra el catálogo de productos con filtros y promociones aplicadas.
     */
    public function catalog()
    {
        $filters = [];
        if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['min_price'])) $filters['min_price'] = $_GET['min_price'];
        if (!empty($_GET['max_price'])) $filters['max_price'] = $_GET['max_price'];
        $filters['status'] = 'activo';

        $products = $this->productModel->findAll($filters);
        $categories = $this->productModel->getCategories();
        $promotions = $this->promotionModel->getActivePromotions();

        // Aplicar promociones a productos
        foreach ($products as &$product) {
            $product['has_discount'] = false;
            $product['discount_percent'] = 0;
            $product['discounted_price'] = $product['price'];

            foreach ($promotions as $promotion) {
                // product_ids es un string CSV, o cadena vacía si no hay productos
                $productIds = !empty($promotion['product_ids']) ? explode(',', $promotion['product_ids']) : [];
                if (in_array($product['id'], $productIds)) {
                    $product['has_discount'] = true;
                    $product['discount_percent'] = $promotion['discount_percent'];
                    $product['discounted_price'] = $product['price'] * (1 - $promotion['discount_percent'] / 100);
                    break;
                }
            }
        }

        $cart_count = array_sum(array_column($this->cart, 'quantity'));

        view('sales/catalog', [
            'products' => $products,
            'categories' => $categories,
            'cart_count' => $cart_count
        ], 'catalog');
    }

    /**
     * Muestra el carrito de compras y maneja las acciones (agregar, eliminar, actualizar, vaciar).
     */
    public function cart()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'add') {
                $productId = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 1);
                $this->addToCart($productId, $quantity);
            } elseif ($action === 'remove') {
                $productId = (int)($_POST['product_id'] ?? 0);
                $this->removeFromCart($productId);
            } elseif ($action === 'update') {
                $productId = (int)($_POST['product_id'] ?? 0);
                $quantity = (int)($_POST['quantity'] ?? 1);
                $this->updateCart($productId, $quantity);
            } elseif ($action === 'clear') {
                $this->clearCart();
            }

            redirect('index.php?controller=sale&action=cart');
            return;
        }

        // Obtener detalles completos de los productos en el carrito
        $cartItems = [];
        $subtotal = 0;

        foreach ($this->cart as $productId => $item) {
            $product = $this->productModel->findById($productId);
            if ($product) {
                $item['product'] = $product;
                $item['subtotal'] = $item['quantity'] * $product['price'];
                $cartItems[$productId] = $item;
                $subtotal += $item['subtotal'];
            }
        }

        // Calcular envío (gratis si el subtotal >= 100)
        $shipping = $subtotal > 0 ? ($subtotal >= 100 ? 0 : 10) : 0;
        $total = $subtotal + $shipping;
        $cart_count = array_sum(array_column($this->cart, 'quantity'));

        view('sales/cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'cart_count' => $cart_count
        ], 'cart');
    }

    /**
     * Muestra el formulario de checkout y procesa el pedido.
     */
    public function checkout()
    {
        if (empty($this->cart)) {
            setFlash('error', 'El carrito está vacío');
            redirect('index.php?controller=sale&action=catalog');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCheckout();
            return;
        }

        // Calcular totales
        $subtotal = 0;
        foreach ($this->cart as $productId => $item) {
            $product = $this->productModel->findById($productId);
            if ($product) {
                $subtotal += $item['quantity'] * $product['price'];
            }
        }

        $shipping = $subtotal >= 100 ? 0 : 10;
        $total = $subtotal + $shipping;
        $isLoggedIn = isLoggedIn();
        $user = $_SESSION['user'] ?? null;

        view('sales/checkout', [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'isLoggedIn' => $isLoggedIn,
            'user' => $user
        ], 'checkout');
    }

    /**
     * Muestra la página de confirmación de la compra.
     */
    public function confirmation()
    {
        if (!isset($_SESSION['last_order'])) {
            redirect('index.php?controller=sale&action=catalog');
            return;
        }

        $saleId = $_SESSION['last_order'];
        $db = Database::getInstance();
        $sale = $this->saleModel->findById($saleId);
        $details = $this->saleModel->getDetails($saleId);

        $stmt = $db->query("SELECT * FROM invoices WHERE sale_id = ?", [$saleId]);
        $invoice = $stmt->fetch();

        unset($_SESSION['last_order']);

        view('sales/confirmation', [
            'sale' => $sale,
            'details' => $details,
            'invoice' => $invoice
        ], 'confirmation');
    }
    
    // ===================== MÉTODOS PRIVADOS PARA MANEJO DEL CARRITO =====================

    /**
     * Agrega un producto al carrito.
     */
    private function addToCart($productId, $quantity)
    {
        $product = $this->productModel->findById($productId);
        if (!$product || $product['status'] !== 'activo') {
            setFlash('error', 'Producto no disponible');
            return;
        }

        if ($product['stock'] < $quantity) {
            setFlash('error', "Stock insuficiente. Disponible: {$product['stock']}");
            return;
        }

        if (isset($this->cart[$productId])) {
            $newQuantity = $this->cart[$productId]['quantity'] + $quantity;
            if ($newQuantity > $product['stock']) {
                setFlash('error', "Stock insuficiente. Disponible: {$product['stock']}");
                return;
            }
            $this->cart[$productId]['quantity'] = $newQuantity;
        } else {
            $this->cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
        }
        setFlash('success', "Producto agregado al carrito");
    }

    /**
     * Elimina un producto del carrito.
     */
    private function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            setFlash('success', 'Producto eliminado del carrito');
        }
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    private function updateCart($productId, $quantity)
    {
        $product = $this->productModel->findById($productId);
        if (!$product) return;

        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        if ($quantity > $product['stock']) {
            setFlash('error', "Stock insuficiente. Disponible: {$product['stock']}");
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] = $quantity;
            setFlash('success', 'Carrito actualizado');
        }
    }

    /**
     * Vacía completamente el carrito.
     */
    private function clearCart()
    {
        $this->cart = [];
        setFlash('success', 'Carrito vaciado');
    }

    /**
     * Procesa la compra: valida datos, guarda la venta, genera factura y limpia el carrito.
     */
    private function processCheckout()
    {
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';

        // Validaciones
        $errors = [];
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Correo electrónico válido es obligatorio';
        }
        if (empty($name)) $errors[] = 'Nombre completo es obligatorio';
        if (empty($address)) $errors[] = 'Dirección es obligatoria';
        if (empty($phone)) $errors[] = 'Teléfono es obligatorio';
        if (empty($paymentMethod)) $errors[] = 'Método de pago es obligatorio';

        if (!empty($errors)) {
            foreach ($errors as $error) setFlash('error', $error);
            redirect('index.php?controller=sale&action=checkout');
            return;
        }

        // Construir lista de items
        $items = [];
        $subtotal = 0;
        foreach ($this->cart as $productId => $item) {
            $product = $this->productModel->findById($productId);
            if ($product) {
                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $product['price']
                ];
                $subtotal += $item['quantity'] * $product['price'];
            }
        }

        $shipping = $subtotal >= 100 ? 0 : 10;
        $total = $subtotal + $shipping;

        // Guardar venta
        $saleData = [
            'user_id' => isLoggedIn() ? $_SESSION['user']['id'] : null,
            'total' => $total,
            'shipping_cost' => $shipping,
            'items' => $items
        ];

        $saleId = $this->saleModel->create($saleData);

        if ($saleId) {
            // Generar factura
            $invoiceNumber = generateInvoiceNumber();
            $db = Database::getInstance();
            $db->query("INSERT INTO invoices (sale_id, invoice_number) VALUES (?, ?)", [$saleId, $invoiceNumber]);
            $db->query("UPDATE sales SET status = 'facturada' WHERE id = ?", [$saleId]);

            // Limpiar carrito
            $this->clearCart();

            setFlash('success', "¡Compra realizada con éxito! Número de orden: #{$saleId}");
            $_SESSION['last_order'] = $saleId;

            // Aquí se enviaría el correo con la factura
            redirect('index.php?controller=sale&action=confirmation');
        } else {
            setFlash('error', 'Error al procesar la compra');
            redirect('index.php?controller=sale&action=checkout');
        }
    }
}
