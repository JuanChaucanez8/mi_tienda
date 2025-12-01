<?php
session_start();

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'mi_tienda_db');
define('DB_USER', 'mi_tienda_user');
define('DB_PASS', 'tienda123');
define('DB_SSL', false);

/**
 * Conexión a la base de datos PostgreSQL
 */
function get_db_connection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection_string = sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s",
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_USER,
            DB_PASS
        );
        
        if (DB_SSL) {
            $connection_string .= " sslmode=require";
        }
        
        $connection = pg_connect($connection_string);
        
        if (!$connection) {
            error_log("Error de conexión a PostgreSQL: " . pg_last_error());
            http_response_code(500);
            if (php_sapi_name() === 'cli') {
                die("Error de conexión a la base de datos");
            } else {
                die(json_encode(['error' => 'Error de conexión a la base de datos']));
            }
        }
    }
    
    return $connection;
}

/**
 * Ejecutar consulta preparada
 */
function db_execute($query_name, $params = []) {
    $conn = get_db_connection();
    $result = pg_execute($conn, $query_name, $params);
    
    if (!$result) {
        error_log("Error en consulta PostgreSQL: " . pg_last_error($conn));
        return false;
    }
    
    return $result;
}

/**
 * Preparar consulta (si no existe)
 */
function db_prepare($query_name, $query) {
    $conn = get_db_connection();
    $result = pg_prepare($conn, $query_name, $query);
    return $result !== false;
}

// Preparar consultas comunes al iniciar
db_prepare('get_all_products', 'SELECT * FROM products ORDER BY created_at DESC');
db_prepare('get_product_by_id', 'SELECT * FROM products WHERE id = $1');
db_prepare('search_products', 'SELECT * FROM products WHERE name ILIKE $1 OR description ILIKE $1 OR category ILIKE $1 ORDER BY created_at DESC');
db_prepare('get_products_by_category', 'SELECT * FROM products WHERE category = $1 ORDER BY created_at DESC');
db_prepare('insert_product', 'INSERT INTO products (name, description, price, stock, category, image_path, updated_at) VALUES ($1, $2, $3, $4, $5, $6, NOW()) RETURNING id');
db_prepare('update_product', 'UPDATE products SET name = $1, description = $2, price = $3, stock = $4, category = $5, image_path = $6, updated_at = NOW() WHERE id = $7');
db_prepare('delete_product', 'DELETE FROM products WHERE id = $1');
db_prepare('count_products', 'SELECT COUNT(*) as total FROM products');
db_prepare('get_products_paginated', 'SELECT * FROM products ORDER BY created_at DESC LIMIT $1 OFFSET $2');
?>