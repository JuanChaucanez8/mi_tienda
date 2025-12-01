<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$product_id = validate_input($_GET['id'] ?? 0, 'int');

if (!$product_id) {
    redirect('index.php', 'ID de producto no válido', 'error');
}

// Obtener producto
$result = db_execute('get_product_by_id', [$product_id]);
$product = pg_fetch_assoc($result);

if (!$product) {
    redirect('index.php', 'Producto no encontrado', 'error');
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect('index.php', 'Token de seguridad inválido', 'error');
    }
    
    // Eliminar imagen si existe
    if ($product['image_path']) {
        delete_old_image($product['image_path']);
    }
    
    // Eliminar de la base de datos
    $result = db_execute('delete_product', [$product_id]);
    
    if ($result) {
        redirect('index.php', 'Producto eliminado correctamente');
    } else {
        redirect('index.php', 'Error al eliminar el producto', 'error');
    }
}

$data = ['product' => $product];
render('delete_confirm', $data);
?>