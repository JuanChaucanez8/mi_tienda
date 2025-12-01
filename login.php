<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Si ya está logueado, redirigir al inicio
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$form_data = [
    'username' => '',
    'password' => '',
    'remember_me' => false
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    }
    
    // Recoger y validar datos
    $form_data['username'] = validate_input($_POST['username'] ?? '');
    $form_data['password'] = $_POST['password'] ?? '';
    $form_data['remember_me'] = isset($_POST['remember_me']);
    
    // Validaciones
    if (empty($form_data['username'])) {
        $errors[] = 'El nombre de usuario o email es obligatorio';
    }
    
    if (empty($form_data['password'])) {
        $errors[] = 'La contraseña es obligatoria';
    }
    
    // Si no hay errores, verificar credenciales
    if (empty($errors)) {
        // Buscar usuario por username o email
        $result = db_execute('get_user_by_username', [$form_data['username']]);
        $user = pg_fetch_assoc($result);
        
        if (!$user) {
            // Intentar buscar por email
            $result = db_execute('get_user_by_email', [$form_data['username']]);
            $user = pg_fetch_assoc($result);
        }
        
        if ($user && verify_password($form_data['password'], $user['password_hash'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Actualizar último login
            db_execute('update_user_login', [$user['id']]);
            
            // Redirigir a la página solicitada o al inicio
            $redirect_url = $_GET['redirect'] ?? 'index.php';
            redirect($redirect_url, '¡Bienvenido de nuevo, ' . htmlspecialchars($user['username']) . '!');
        } else {
            $errors[] = 'Credenciales inválidas. Por favor, verifica tu usuario y contraseña.';
        }
    }
}

$data = [
    'form_data' => $form_data,
    'errors' => $errors,
    'redirect' => $_GET['redirect'] ?? ''
];

render('login', $data);
?>