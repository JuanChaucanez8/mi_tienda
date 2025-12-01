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
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'password' => '',
    'confirm_password' => ''
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    }
    
    // Recoger y validar datos
    $form_data['username'] = validate_input($_POST['username'] ?? '');
    $form_data['email'] = validate_input($_POST['email'] ?? '', 'email');
    $form_data['first_name'] = validate_input($_POST['first_name'] ?? '');
    $form_data['last_name'] = validate_input($_POST['last_name'] ?? '');
    $form_data['password'] = $_POST['password'] ?? '';
    $form_data['confirm_password'] = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($form_data['username'])) {
        $errors[] = 'El nombre de usuario es obligatorio';
    } elseif (strlen($form_data['username']) < 3 || strlen($form_data['username']) > 50) {
        $errors[] = 'El nombre de usuario debe tener entre 3 y 50 caracteres';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $form_data['username'])) {
        $errors[] = 'El nombre de usuario solo puede contener letras, números y guiones bajos';
    } else {
        // Verificar si el usuario ya existe
        $result = db_execute('get_user_by_username', [$form_data['username']]);
        if (pg_fetch_assoc($result)) {
            $errors[] = 'El nombre de usuario ya está en uso';
        }
    }
    
    if (empty($form_data['email'])) {
        $errors[] = 'El email es obligatorio';
    } elseif (!$form_data['email']) {
        $errors[] = 'El email no es válido';
    } else {
        // Verificar si el email ya existe
        $result = db_execute('get_user_by_email', [$form_data['email']]);
        if (pg_fetch_assoc($result)) {
            $errors[] = 'El email ya está registrado';
        }
    }
    
    if (empty($form_data['password'])) {
        $errors[] = 'La contraseña es obligatoria';
    } elseif (!is_strong_password($form_data['password'])) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    if (strlen($form_data['first_name']) > 100) {
        $errors[] = 'El nombre no puede tener más de 100 caracteres';
    }
    
    if (strlen($form_data['last_name']) > 100) {
        $errors[] = 'El apellido no puede tener más de 100 caracteres';
    }
    
    // Si no hay errores, crear el usuario
    if (empty($errors)) {
        $password_hash = hash_password($form_data['password']);
        
        $result = db_execute('insert_user', [
            $form_data['username'],
            $form_data['email'],
            $password_hash,
            $form_data['first_name'],
            $form_data['last_name']
        ]);
        
        if ($result) {
            $user_id = pg_fetch_result($result, 0, 0);
            
            // Iniciar sesión automáticamente
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $form_data['username'];
            
            // Actualizar último login
            db_execute('update_user_login', [$user_id]);
            
            redirect('index.php', '¡Registro exitoso! Bienvenido a Mi Tienda');
        } else {
            $errors[] = 'Error al crear el usuario. Por favor, intenta nuevamente.';
        }
    }
}

$data = [
    'form_data' => $form_data,
    'errors' => $errors
];

render('register', $data);
?>