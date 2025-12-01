<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$product_id = validate_input($_GET['id'] ?? 0, 'int');

if (!$product_id) {
    redirect('index.php', 'ID de producto no válido', 'error');
}

$result = db_execute('get_product_by_id', [$product_id]);
$product = pg_fetch_assoc($result);

if (!$product) {
    redirect('index.php', 'Producto no encontrado', 'error');
}

$data = ['product' => $product];
render('product', $data);
?>