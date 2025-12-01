<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$errors = [];
$success = false;

// Obtener información actual del usuario
$current_user = get_current_user();

$form_data = [
    'first_name' => $current_user['first_name'] ?? '',
    'last_name' => $current_user['last_name'] ?? '',
    'email' => $current_user['email'] ?? ''
];

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    }
    
    // Recoger y validar datos
    $form_data['first_name'] = validate_input($_POST['first_name'] ?? '');
    $form_data['last_name'] = validate_input($_POST['last_name'] ?? '');
    $form_data['email'] = validate_input($_POST['email'] ?? '', 'email');
    
    // Validaciones
    if (empty($form_data['email'])) {
        $errors[] = 'El email es obligatorio';
    } elseif (!$form_data['email']) {
        $errors[] = 'El email no es válido';
    } else {
        // Verificar si el email ya existe (excluyendo el usuario actual)
        $result = db_execute('get_user_by_email', [$form_data['email']]);
        $existing_user = pg_fetch_assoc($result);
        if ($existing_user && $existing_user['id'] != $current_user['id']) {
            $errors[] = 'El email ya está en uso por otro usuario';
        }
    }
    
    if (strlen($form_data['first_name']) > 100) {
        $errors[] = 'El nombre no puede tener más de 100 caracteres';
    }
    
    if (strlen($form_data['last_name']) > 100) {
        $errors[] = 'El apellido no puede tener más de 100 caracteres';
    }
    
    // Si no hay errores, actualizar perfil
    if (empty($errors)) {
        $result = db_execute('update_user_profile', [
            $form_data['first_name'],
            $form_data['last_name'],
            $current_user['id']
        ]);
        
        if ($result) {
            $success = true;
            // Actualizar datos en sesión si es necesario
            $current_user = get_current_user(); // Refrescar datos
        } else {
            $errors[] = 'Error al actualizar el perfil. Por favor, intenta nuevamente.';
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    }
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($current_password)) {
        $errors[] = 'La contraseña actual es obligatoria';
    }
    
    if (empty($new_password)) {
        $errors[] = 'La nueva contraseña es obligatoria';
    } elseif (!is_strong_password($new_password)) {
        $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'Las nuevas contraseñas no coinciden';
    }
    
    // Verificar contraseña actual
    if (empty($errors)) {
        $result = db_execute('get_user_by_id', [$current_user['id']]);
        $user_with_password = pg_fetch_assoc($result);
        
        if (!verify_password($current_password, $user_with_password['password_hash'])) {
            $errors[] = 'La contraseña actual es incorrecta';
        }
    }
    
    // Si no hay errores, cambiar contraseña
    if (empty($errors)) {
        $new_password_hash = hash_password($new_password);
        $result = db_execute('update_user_password', [$new_password_hash, $current_user['id']]);
        
        if ($result) {
            $success = true;
            $_SESSION['flash_message'] = 'Contraseña actualizada correctamente';
            $_SESSION['flash_type'] = 'success';
            redirect('profile.php');
        } else {
            $errors[] = 'Error al cambiar la contraseña. Por favor, intenta nuevamente.';
        }
    }
}

$data = [
    'current_user' => $current_user,
    'form_data' => $form_data,
    'errors' => $errors,
    'success' => $success
];

render('profile', $data);
?>