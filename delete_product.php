<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

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

// Verificar permisos
if (!can_edit_product($product['user_id'])) {
    redirect('index.php', 'No tienes permisos para eliminar este producto', 'error');
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (código similar pero usando delete_product con user_id)
    
    // Eliminar de la base de datos (soft delete)
    $result = db_execute('delete_product', [$product_id, $_SESSION['user_id']]);
    
    if ($result && pg_affected_rows($result) > 0) {
        // Eliminar imagen si existe
        if ($product['image_path']) {
            delete_old_image($product['image_path']);
        }
        redirect('index.php', 'Producto eliminado correctamente');
    } else {
        redirect('index.php', 'Error al eliminar el producto', 'error');
    }
}

$data = ['product' => $product];
render('delete_confirm', $data);
?>