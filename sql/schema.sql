-- Crear tabla products
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    stock INTEGER NOT NULL DEFAULT 0,
    category VARCHAR(100),
    image_path VARCHAR(512),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE
);

-- Crear índices
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category);
CREATE INDEX IF NOT EXISTS idx_products_created_at ON products(created_at);
CREATE INDEX IF NOT EXISTS idx_products_price ON products(price);

-- Insertar datos de prueba
INSERT INTO products (name, description, price, stock, category, image_path) VALUES
('Laptop HP Pavilion', 'Laptop HP Pavilion 15.6 pulgadas, 8GB RAM, 256GB SSD', 899.99, 10, 'Electrónicos', NULL),
('Smartphone Samsung Galaxy', 'Teléfono inteligente Android con 128GB almacenamiento', 699.99, 15, 'Electrónicos', NULL),
('Auriculares Bluetooth', 'Auriculares inalámbricos con cancelación de ruido', 149.99, 25, 'Accesorios', NULL),
('Mouse Inalámbrico', 'Mouse ergonómico con conectividad USB', 29.99, 50, 'Accesorios', NULL),
('Tablet Apple iPad', 'Tablet iPad 10.2 pulgadas, 64GB, Wi-Fi', 429.99, 8, 'Electrónicos', NULL);

-- Crear usuario para la aplicación (si no existe)
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'mi_tienda_user') THEN
        CREATE USER mi_tienda_user WITH PASSWORD 'tienda123';
    END IF;
END
$$;

-- Otorgar permisos
GRANT CONNECT ON DATABASE mi_tienda_db TO mi_tienda_user;
GRANT USAGE ON SCHEMA public TO mi_tienda_user;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO mi_tienda_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO mi_tienda_user;