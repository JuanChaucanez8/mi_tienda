-- Crear tabla users
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE,
    last_login TIMESTAMP WITH TIME ZONE,
    is_active BOOLEAN DEFAULT true
);

-- Modificar tabla products para agregar user_id
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT true;

-- Crear índices para users
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at);

-- Crear índice para products user_id
CREATE INDEX IF NOT EXISTS idx_products_user_id ON products(user_id);
CREATE INDEX IF NOT EXISTS idx_products_is_active ON products(is_active);

-- Insertar usuario administrador por defecto (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES
('admin', 'admin@mitienda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema');

-- Actualizar los productos existentes para asignarlos al usuario admin
UPDATE products SET user_id = (SELECT id FROM users WHERE username = 'admin');

-- Preparar consultas para usuarios
PREPARE get_user_by_id AS SELECT id, username, email, first_name, last_name, created_at, last_login FROM users WHERE id = $1 AND is_active = true;
PREPARE get_user_by_username AS SELECT id, username, email, password_hash, first_name, last_name, created_at, last_login FROM users WHERE username = $1 AND is_active = true;
PREPARE get_user_by_email AS SELECT id, username, email, password_hash, first_name, last_name, created_at, last_login FROM users WHERE email = $1 AND is_active = true;
PREPARE insert_user AS INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES ($1, $2, $3, $4, $5) RETURNING id;
PREPARE update_user_login AS UPDATE users SET last_login = NOW() WHERE id = $1;
PREPARE update_user_profile AS UPDATE users SET first_name = $1, last_name = $2, updated_at = NOW() WHERE id = $3;

-- Modificar consultas de productos para incluir user_id e información de usuario
PREPARE get_all_products AS 
SELECT p.*, u.username as author_username, u.first_name as author_first_name, u.last_name as author_last_name 
FROM products p 
LEFT JOIN users u ON p.user_id = u.id 
WHERE p.is_active = true 
ORDER BY p.created_at DESC;

PREPARE get_product_by_id AS 
SELECT p.*, u.username as author_username, u.first_name as author_first_name, u.last_name as author_last_name 
FROM products p 
LEFT JOIN users u ON p.user_id = u.id 
WHERE p.id = $1 AND p.is_active = true;

PREPARE search_products AS 
SELECT p.*, u.username as author_username, u.first_name as author_first_name, u.last_name as author_last_name 
FROM products p 
LEFT JOIN users u ON p.user_id = u.id 
WHERE (p.name ILIKE $1 OR p.description ILIKE $1 OR p.category ILIKE $1) 
AND p.is_active = true 
ORDER BY p.created_at DESC;

PREPARE get_products_by_category AS 
SELECT p.*, u.username as author_username, u.first_name as author_first_name, u.last_name as author_last_name 
FROM products p 
LEFT JOIN users u ON p.user_id = u.id 
WHERE p.category = $1 AND p.is_active = true 
ORDER BY p.created_at DESC;

PREPARE insert_product AS 
INSERT INTO products (name, description, price, stock, category, image_path, user_id) 
VALUES ($1, $2, $3, $4, $5, $6, $7) 
RETURNING id;

PREPARE update_product AS 
UPDATE products 
SET name = $1, description = $2, price = $3, stock = $4, category = $5, image_path = $6, updated_at = NOW() 
WHERE id = $7 AND (user_id = $8 OR (SELECT username FROM users WHERE id = $8) = 'admin');

PREPARE delete_product AS 
UPDATE products SET is_active = false WHERE id = $1 AND (user_id = $2 OR (SELECT username FROM users WHERE id = $2) = 'admin');

PREPARE count_products AS 
SELECT COUNT(*) as total FROM products WHERE is_active = true;

PREPARE get_products_paginated AS 
SELECT p.*, u.username as author_username, u.first_name as author_first_name, u.last_name as author_last_name 
FROM products p 
LEFT JOIN users u ON p.user_id = u.id 
WHERE p.is_active = true 
ORDER BY p.created_at DESC 
LIMIT $1 OFFSET $2;

PREPARE get_user_products AS 
SELECT p.*, u.username as author_username, u.first_name as author_first_name, u.last_name as author_last_name 
FROM products p 
LEFT JOIN users u ON p.user_id = u.id 
WHERE p.user_id = $1 AND p.is_active = true 
ORDER BY p.created_at DESC;

-- Otorgar permisos
GRANT CONNECT ON DATABASE mi_tienda_db TO mi_tienda_user;
GRANT USAGE ON SCHEMA public TO mi_tienda_user;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO mi_tienda_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO mi_tienda_user;