<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Parámetros de búsqueda y paginación
$search = validate_input($_GET['search'] ?? '');
$category = validate_input($_GET['category'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;

// Consulta base
if ($search) {
    $search_term = "%{$search}%";
    $result = db_execute('search_products', [$search_term]);
    $total_result = db_execute('search_products', [$search_term]);
} elseif ($category) {
    $result = db_execute('get_products_by_category', [$category]);
    $total_result = db_execute('get_products_by_category', [$category]);
} else {
    $pagination = paginate(0, $per_page, $page); // Placeholder, calcularemos el total después
    $result = db_execute('get_products_paginated', [$per_page, $pagination['offset']]);
    $total_result = db_execute('count_products');
}

// Calcular total de productos
if ($total_result) {
    if ($search || $category) {
        $total_products = pg_num_rows($total_result);
    } else {
        $row = pg_fetch_assoc($total_result);
        $total_products = $row['total'];
    }
} else {
    $total_products = 0;
}

// Recalcular paginación si es necesario
if (!$search && !$category) {
    $pagination = paginate($total_products, $per_page, $page);
}

// Obtener productos
$products = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// Obtener categorías únicas para el filtro
$categories_result = db_execute('get_all_products');
$categories = [];
if ($categories_result) {
    while ($row = pg_fetch_assoc($categories_result)) {
        if ($row['category']) {
            $categories[$row['category']] = $row['category'];
        }
    }
}

$data = [
    'products' => $products,
    'search' => $search,
    'category' => $category,
    'categories' => $categories,
    'total_products' => $total_products,
    'pagination' => $pagination ?? null
];

render('index', $data);
?>