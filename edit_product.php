<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

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

// Verificar permisos
if (!can_edit_product($product['user_id'])) {
    redirect('index.php', 'No tienes permisos para editar este producto', 'error');
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
    // ... (el resto del código se mantiene igual, pero en el update se pasa user_id)
    
    // Si no hay errores, actualizar en la base de datos
    if (empty($errors)) {
        $result = db_execute('update_product', [
            $form_data['name'],
            $form_data['description'],
            $form_data['price'],
            $form_data['stock'],
            $form_data['category'],
            $image_path,
            $product_id,
            $_SESSION['user_id'] // Verificar permisos
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