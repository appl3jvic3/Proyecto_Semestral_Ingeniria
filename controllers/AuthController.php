<?php
class AuthController
{

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                setFlash('error', 'Por favor complete todos los campos');
                view('auth/login', [], 'login');
                return;
            }

            $db = Database::getInstance();
            $userModel = new User($db);
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                setFlash('success', 'Bienvenido ' . $user['name']);
                redirect('index.php?controller=dashboard&action=index');
            } else {
                setFlash('error', 'Credenciales incorrectas');
                view('auth/login', [], 'login');
            }
        } else {
            if (isLoggedIn()) {
                redirect('index.php?controller=dashboard&action=index');
            }
            view('auth/login', [], 'login');
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $phone = $_POST['phone'] ?? '';

            $errors = [];
            if (empty($name)) $errors[] = 'Nombre completo es obligatorio';
            if (empty($email)) $errors[] = 'Correo electrónico es obligatorio';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido';
            if (empty($password)) $errors[] = 'Contraseña es obligatoria';
            if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres';
            if ($password !== $confirmPassword) $errors[] = 'Las contraseñas no coinciden';

            if (!empty($errors)) {
                foreach ($errors as $error) setFlash('error', $error);
                view('auth/register', [], 'register');
                return;
            }

            $db = Database::getInstance();
            $userModel = new User($db);

            if ($userModel->findByEmail($email)) {
                setFlash('error', 'El correo electrónico ya está registrado');
                view('auth/register', [], 'register');
                return;
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'cliente',
                'status' => 'activo',
                'phone' => $phone
            ];

            if ($userModel->create($data)) {
                setFlash('success', 'Registro exitoso. Ahora puede iniciar sesión.');
                redirect('index.php?controller=auth&action=login');
            } else {
                setFlash('error', 'Error al registrar usuario');
                view('auth/register', [], 'register');
            }
        } else {
            view('auth/register', [], 'register');
        }
    }

    public function logout()
    {
        session_destroy();
        setFlash('success', 'Sesión cerrada correctamente');
        redirect('index.php?controller=auth&action=login');
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            if (empty($email)) {
                setFlash('error', 'Ingrese su correo electrónico');
                view('auth/forgot_password', [], 'forgot');
                return;
            }
            setFlash('success', 'Se ha enviado un enlace de recuperación a su correo');
            view('auth/forgot_password', [], 'forgot');
        } else {
            view('auth/forgot_password', [], 'forgot');
        }
    }
}
