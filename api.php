<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtener acción
$action = validate_input($_GET['action'] ?? '');
$id = validate_input($_GET['id'] ?? 0, 'int');

// Determinar método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Si no se especifica acción en GET, intentar determinar por método HTTP
if (!$action) {
    switch ($method) {
        case 'GET':
            $action = $id ? 'get' : 'list';
            break;
        case 'POST':
            $action = 'create';
            break;
        case 'PUT':
        case 'PATCH':
            $action = 'update';
            break;
        case 'DELETE':
            $action = 'delete';
            break;
        default:
            $action = 'list';
    }
}

// Procesar la acción
try {
    switch ($action) {
        case 'list':
            handle_list();
            break;
        case 'get':
            handle_get($id);
            break;
        case 'create':
            handle_create();
            break;
        case 'update':
            handle_update($id);
            break;
        case 'delete':
            handle_delete($id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
            exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}

/**
 * Listar productos
 */
function handle_list() {
    $search = validate_input($_GET['search'] ?? '');
    $category = validate_input($_GET['category'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(100, max(1, intval($_GET['per_page'] ?? 10)));
    
    // Construir consulta
    if ($search) {
        $search_term = "%{$search}%";
        $result = db_execute('search_products', [$search_term]);
        $total_result = db_execute('search_products', [$search_term]);
    } elseif ($category) {
        $result = db_execute('get_products_by_category', [$category]);
        $total_result = db_execute('get_products_by_category', [$category]);
    } else {
        $pagination = paginate(0, $per_page, $page);
        $result = db_execute('get_products_paginated', [$per_page, $pagination['offset']]);
        $total_result = db_execute('count_products');
    }
    
    // Calcular total
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
    
    // Obtener productos
    $products = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            // Convertir tipos numéricos
            $row['id'] = intval($row['id']);
            $row['price'] = floatval($row['price']);
            $row['stock'] = intval($row['stock']);
            $products[] = $row;
        }
    }
    
    // Construir respuesta
    $response = [
        'success' => true,
        'data' => [
            'products' => $products,
            'pagination' => [
                'total' => $total_products,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total_products / $per_page)
            ]
        ]
    ];
    
    if ($search) {
        $response['data']['search'] = $search;
    }
    
    if ($category) {
        $response['data']['category'] = $category;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * Obtener producto por ID
 */
function handle_get($id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de producto requerido']);
        return;
    }
    
    $result = db_execute('get_product_by_id', [$id]);
    $product = pg_fetch_assoc($result);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado']);
        return;
    }
    
    // Convertir tipos numéricos
    $product['id'] = intval($product['id']);
    $product['price'] = floatval($product['price']);
    $product['stock'] = intval($product['stock']);
    
    echo json_encode([
        'success' => true,
        'data' => $product
    ], JSON_PRETTY_PRINT);
}

/**
 * Crear producto
 */
function handle_create() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    // Obtener y validar JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        return;
    }
    
    // Validar campos requeridos
    $errors = [];
    $name = validate_input($input['name'] ?? '');
    $description = validate_input($input['description'] ?? '');
    $price = validate_input($input['price'] ?? '', 'float');
    $stock = validate_input($input['stock'] ?? '', 'int');
    $category = validate_input($input['category'] ?? '');
    $image_path = validate_input($input['image_path'] ?? '');
    
    if (empty($name)) {
        $errors[] = 'El nombre del producto es obligatorio';
    }
    
    if (strlen($name) > 255) {
        $errors[] = 'El nombre no puede tener más de 255 caracteres';
    }
    
    if ($price === false || $price < 0) {
        $errors[] = 'El precio debe ser un número mayor o igual a 0';
    }
    
    if ($stock === false || $stock < 0) {
        $errors[] = 'El stock debe ser un número entero mayor o igual a 0';
    }
    
    if (strlen($category) > 100) {
        $errors[] = 'La categoría no puede tener más de 100 caracteres';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos', 'details' => $errors]);
        return;
    }
    
    // Insertar en la base de datos
    $result = db_execute('insert_product', [
        $name,
        $description,
        $price,
        $stock,
        $category,
        $image_path
    ]);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el producto']);
        return;
    }
    
    $new_id = pg_fetch_result($result, 0, 0);
    
    // Obtener producto creado
    $result = db_execute('get_product_by_id', [$new_id]);
    $product = pg_fetch_assoc($result);
    
    // Convertir tipos numéricos
    $product['id'] = intval($product['id']);
    $product['price'] = floatval($product['price']);
    $product['stock'] = intval($product['stock']);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Producto creado correctamente',
        'data' => $product
    ], JSON_PRETTY_PRINT);
}

/**
 * Actualizar producto
 */
function handle_update($id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de producto requerido']);
        return;
    }
    
    if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH', 'POST'])) {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    // Verificar que el producto existe
    $result = db_execute('get_product_by_id', [$id]);
    $existing_product = pg_fetch_assoc($result);
    
    if (!$existing_product) {
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado']);
        return;
    }
    
    // Obtener y validar JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        return;
    }
    
    // Preparar datos para actualización
    $name = validate_input($input['name'] ?? $existing_product['name']);
    $description = validate_input($input['description'] ?? $existing_product['description']);
    $price = validate_input($input['price'] ?? $existing_product['price'], 'float');
    $stock = validate_input($input['stock'] ?? $existing_product['stock'], 'int');
    $category = validate_input($input['category'] ?? $existing_product['category']);
    $image_path = validate_input($input['image_path'] ?? $existing_product['image_path']);
    
    // Validaciones
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'El nombre del producto es obligatorio';
    }
    
    if (strlen($name) > 255) {
        $errors[] = 'El nombre no puede tener más de 255 caracteres';
    }
    
    if ($price === false || $price < 0) {
        $errors[] = 'El precio debe ser un número mayor o igual a 0';
    }
    
    if ($stock === false || $stock < 0) {
        $errors[] = 'El stock debe ser un número entero mayor o igual a 0';
    }
    
    if (strlen($category) > 100) {
        $errors[] = 'La categoría no puede tener más de 100 caracteres';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos', 'details' => $errors]);
        return;
    }
    
    // Actualizar en la base de datos
    $result = db_execute('update_product', [
        $name,
        $description,
        $price,
        $stock,
        $category,
        $image_path,
        $id
    ]);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el producto']);
        return;
    }
    
    // Obtener producto actualizado
    $result = db_execute('get_product_by_id', [$id]);
    $product = pg_fetch_assoc($result);
    
    // Convertir tipos numéricos
    $product['id'] = intval($product['id']);
    $product['price'] = floatval($product['price']);
    $product['stock'] = intval($product['stock']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto actualizado correctamente',
        'data' => $product
    ], JSON_PRETTY_PRINT);
}

/**
 * Eliminar producto
 */
function handle_delete($id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de producto requerido']);
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        return;
    }
    
    // Verificar que el producto existe
    $result = db_execute('get_product_by_id', [$id]);
    $product = pg_fetch_assoc($result);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado']);
        return;
    }
    
    // Eliminar imagen si existe
    if ($product['image_path']) {
        delete_old_image($product['image_path']);
    }
    
    // Eliminar de la base de datos
    $result = db_execute('delete_product', [$id]);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el producto']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto eliminado correctamente'
    ], JSON_PRETTY_PRINT);
}
?>