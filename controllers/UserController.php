<?php
class UserController
{
    private $userModel;

    public function __construct()
    {
        if (!isLoggedIn() || !hasRole('admin')) {
            setFlash('error', 'Acceso denegado');
            redirect('index.php?controller=dashboard&action=index');
        }
        $db = Database::getInstance();
        $this->userModel = new User($db);
    }

    public function index()
    {
        $action = $_GET['subaction'] ?? 'list';
        $data = [];

        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCreate();
            return;
        }

        if ($action === 'edit' && isset($_GET['id'])) {
            $user = $this->userModel->findById($_GET['id']);
            if (!$user) {
                setFlash('error', 'Usuario no encontrado');
                redirect('index.php?controller=user&action=index');
            }
            $data['user'] = $user;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->processUpdate($_GET['id']);
                return;
            }
            view('users/index', $data, 'edit');
            return;
        }

        if ($action === 'delete' && isset($_GET['id'])) {
            $this->processDelete($_GET['id']);
            return;
        }

        // Listado
        $filters = [];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['role'])) $filters['role'] = $_GET['role'];

        $data['users'] = $this->userModel->findAll($filters);
        $data['stats'] = $this->userModel->getStats();
        view('users/index', $data, 'list');
    }

    private function processCreate()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'cliente';
        $phone = $_POST['phone'] ?? '';

        $errors = [];
        if (empty($name)) $errors[] = 'Nombre completo es obligatorio';
        if (empty($email)) $errors[] = 'Correo electrónico es obligatorio';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido';
        if (empty($password)) $errors[] = 'Contraseña es obligatoria';
        if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres';

        if (!empty($errors)) {
            foreach ($errors as $error) setFlash('error', $error);
            view('users/index', ['action' => 'create'], 'create');
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            setFlash('error', 'El correo electrónico ya está registrado');
            view('users/index', ['action' => 'create'], 'create');
            return;
        }

        $data = ['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role, 'phone' => $phone];
        if ($this->userModel->create($data)) {
            setFlash('success', 'Usuario creado correctamente');
            redirect('index.php?controller=user&action=index');
        } else {
            setFlash('error', 'Error al crear usuario');
            view('users/index', ['action' => 'create'], 'create');
        }
    }

    private function processUpdate($id)
    {
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? 'cliente';
        $status = $_POST['status'] ?? 'activo';

        if (empty($name)) {
            setFlash('error', 'El nombre es obligatorio');
            redirect('index.php?controller=user&action=index&subaction=edit&id=' . $id);
            return;
        }

        $data = ['name' => $name, 'role' => $role, 'status' => $status];

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                setFlash('error', 'La contraseña debe tener al menos 8 caracteres');
                redirect('index.php?controller=user&action=index&subaction=edit&id=' . $id);
                return;
            }
            $data['password'] = $_POST['password'];
        }

        if ($this->userModel->update($id, $data)) {
            setFlash('success', 'Usuario actualizado correctamente');
        } else {
            setFlash('error', 'Error al actualizar usuario');
        }
        redirect('index.php?controller=user&action=index');
    }

    private function processDelete($id)
    {
        if ($this->userModel->delete($id)) {
            setFlash('success', 'Usuario eliminado correctamente');
        } else {
            setFlash('error', 'Error al eliminar usuario');
        }
        redirect('index.php?controller=user&action=index');
    }
}
