<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login(); // Requiere que el usuario esté logueado

$errors = [];
$form_data = [
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'category' => ''
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de seguridad inválido';
    }
    
    // Recoger y validar datos
    $form_data['name'] = validate_input($_POST['name'] ?? '');
    $form_data['description'] = validate_input($_POST['description'] ?? '');
    $form_data['price'] = validate_input($_POST['price'] ?? '', 'float');
    $form_data['stock'] = validate_input($_POST['stock'] ?? '', 'int');
    $form_data['category'] = validate_input($_POST['category'] ?? '');
    
    // Validaciones
    if (empty($form_data['name'])) {
        $errors[] = 'El nombre del producto es obligatorio';
    }
    
    if (strlen($form_data['name']) > 255) {
        $errors[] = 'El nombre no puede tener más de 255 caracteres';
    }
    
    if ($form_data['price'] === false || $form_data['price'] < 0) {
        $errors[] = 'El precio debe ser un número mayor o igual a 0';
    }
    
    if ($form_data['stock'] === false || $form_data['stock'] < 0) {
        $errors[] = 'El stock debe ser un número entero mayor o igual a 0';
    }
    
    if (strlen($form_data['category']) > 100) {
        $errors[] = 'La categoría no puede tener más de 100 caracteres';
    }
    
    // Procesar imagen
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image_path = save_uploaded_image($_FILES['image']);
        if (!$image_path) {
            $errors[] = 'La imagen no es válida. Solo se permiten JPG, PNG y GIF (máx. 5MB)';
        }
    }
    
    // Si no hay errores, guardar en la base de datos
    if (empty($errors)) {
        $result = db_execute('insert_product', [
            $form_data['name'],
            $form_data['description'],
            $form_data['price'],
            $form_data['stock'],
            $form_data['category'],
            $image_path,
            $_SESSION['user_id'] // Agregar user_id del usuario logueado
        ]);
        
        if ($result) {
            $new_id = pg_fetch_result($result, 0, 0);
            redirect('product.php?id=' . $new_id, 'Producto agregado correctamente');
        } else {
            $errors[] = 'Error al guardar el producto en la base de datos';
        }
    }
}

$data = [
    'form_data' => $form_data,
    'errors' => $errors,
    'is_edit' => false
];

render('product_form', $data);
?>