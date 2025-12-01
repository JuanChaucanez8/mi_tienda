<?php
/**
 * Renderizar vista con variables
 */
function render($template, $data = []) {
    extract($data);
    include __DIR__ . '/../includes/header.php';
    include __DIR__ . "/../views/{$template}.php";
    include __DIR__ . '/../includes/footer.php';
    exit;
}

/**
 * Validar y sanitizar input
 */
function validate_input($data, $type = 'string') {
    if ($data === null) {
        return null;
    }
    
    switch ($type) {
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
        case 'email':
            return filter_var($data, FILTER_VALIDATE_EMAIL);
        case 'url':
            return filter_var($data, FILTER_VALIDATE_URL);
        case 'string':
        default:
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return $data;
    }
}

/**
 * Generar token CSRF
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verificar si es una imagen válida
 */
function is_valid_image($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    return true;
}

/**
 * Guardar imagen subida
 */
function save_uploaded_image($file) {
    if (!$file || !is_valid_image($file)) {
        return null;
    }
    
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . $filename;
    }
    
    return null;
}

/**
 * Eliminar imagen antigua
 */
function delete_old_image($image_path) {
    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
        unlink(__DIR__ . '/../' . $image_path);
    }
}

/**
 * Verificar si el usuario está logueado
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtener información del usuario actual
 */
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $result = db_execute('get_user_by_id', [$_SESSION['user_id']]);
    return pg_fetch_assoc($result);
}

/**
 * Verificar si el usuario es administrador
 */
function is_admin() {
    $user = get_current_user();
    return $user && $user['username'] === 'admin';
}

/**
 * Verificar si el usuario puede editar/eliminar un producto
 */
function can_edit_product($product_user_id) {
    if (!is_logged_in()) {
        return false;
    }
    
    return is_admin() || $_SESSION['user_id'] == $product_user_id;
}

/**
 * Redireccionar con mensaje flash
 */
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Obtener mensaje flash
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Paginación
 */
function paginate($total_items, $per_page = 10, $current_page = 1) {
    $total_pages = ceil($total_items / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'per_page' => $per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Hash de contraseña
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validar fortaleza de contraseña
 */
function is_strong_password($password) {
    return strlen($password) >= 8;
}

/**
 * Requiere que el usuario esté logueado
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php', 'Debes iniciar sesión para acceder a esta página', 'error');
    }
}

/**
 * Requiere que el usuario sea administrador
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect('index.php', 'No tienes permisos para acceder a esta página', 'error');
    }
}

/**
 * Formatear nombre de usuario
 */
function format_author_name($user) {
    if (!$user) return 'Usuario desconocido';
    
    if (!empty($user['first_name']) && !empty($user['last_name'])) {
        return htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
    } else {
        return htmlspecialchars($user['username']);
    }
}
?>