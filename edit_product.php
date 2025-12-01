<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$product_id = validate_input($_GET['id'] ?? 0, 'int');
$errors = [];

if (!$product_id) {
    redirect('index.php', 'ID de producto no válido', 'error');
}

// Obtener producto actual
$result = db_execute('get_product_by_id', [$product_id]);
$product = pg_fetch_assoc($result);

if (!$product) {
    redirect('index.php', 'Producto no encontrado', 'error');
}

$form_data = [
    'name' => $product['name'],
    'description' => $product['description'],
    'price' => $product['price'],
    'stock' => $product['stock'],
    'category' => $product['category'],
    'current_image' => $product['image_path']
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
    $image_path = $form_data['current_image'];
    $delete_current_image = isset($_POST['delete_image']);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Eliminar imagen anterior si existe
        if ($image_path) {
            delete_old_image($image_path);
        }
        
        $image_path = save_uploaded_image($_FILES['image']);
        if (!$image_path) {
            $errors[] = 'La imagen no es válida. Solo se permiten JPG, PNG y GIF (máx. 5MB)';
        }
    } elseif ($delete_current_image && $image_path) {
        delete_old_image($image_path);
        $image_path = null;
    }
    
    // Si no hay errores, actualizar en la base de datos
    if (empty($errors)) {
        $result = db_execute('update_product', [
            $form_data['name'],
            $form_data['description'],
            $form_data['price'],
            $form_data['stock'],
            $form_data['category'],
            $image_path,
            $product_id
        ]);
        
        if ($result) {
            redirect('product.php?id=' . $product_id, 'Producto actualizado correctamente');
        } else {
            $errors[] = 'Error al actualizar el producto en la base de datos';
        }
    }
}

$data = [
    'form_data' => $form_data,
    'errors' => $errors,
    'is_edit' => true,
    'product_id' => $product_id
];

render('product_form', $data);
?>