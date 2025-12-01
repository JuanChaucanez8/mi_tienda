<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Obtener productos del usuario
$result = db_execute('get_user_products', [$_SESSION['user_id']]);
$products = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Paginación
$total_products = count($products);
$pagination = paginate($total_products, $per_page, $page);
$paginated_products = array_slice($products, $pagination['offset'], $pagination['per_page']);

$data = [
    'products' => $paginated_products,
    'total_products' => $total_products,
    'pagination' => $pagination
];

render('my_products', $data);
?>