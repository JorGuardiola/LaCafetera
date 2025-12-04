-- ##################################################
-- # SCRIPT SQL MODIFICADO: cafetera_db
-- ##################################################

-- ----------------------------------------------------
-- 0. CREACIÓN Y SELECCIÓN DE LA BASE DE DATOS
-- ----------------------------------------------------

-- Crea la base de datos si no existe
CREATE DATABASE IF NOT EXISTS cafeteria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Selecciona la base de datos para ejecutar las siguientes instrucciones
USE cafeteria_db;

-- ----------------------------------------------------
-- 1. DROP DE TABLAS 
-- ----------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS pedido_items; -- Antes orden_items
DROP TABLE IF EXISTS pedidos;      -- Antes ordenes
DROP TABLE IF EXISTS carrito_items;
DROP TABLE IF EXISTS carritos;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS direcciones;
DROP TABLE IF EXISTS producto_variantes;
DROP TABLE IF EXISTS productos;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------
-- 2. DEFINICIÓN DE TABLAS MODIFICADAS
-- ----------------------------------------------------

-- TABLA PRODUCTOS
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cafe VARCHAR(150) NOT NULL,
    pais_origen VARCHAR(100),
    region VARCHAR(150),
    finca VARCHAR(150),
    altitud_msnm INT,
    variedad VARCHAR(150),
    proceso ENUM('Lavado', 'Natural', 'Honey') NOT NULL, 
    puntuacion_sca DECIMAL(4,1),
    notas_sabor TEXT,
    presentacion VARCHAR(255),
    descripcion TEXT,
    imagen VARCHAR(255),
    disponible BOOLEAN DEFAULT TRUE
);

-- TABLA PRODUCTO_VARIANTES
CREATE TABLE producto_variantes (
    sku VARCHAR(50) PRIMARY KEY, 
    producto_id INT NOT NULL,
    stock INT DEFAULT 0,
    precio DECIMAL(10,2) NOT NULL,
    molienda ENUM('grano', 'molido espresso', 'molido moka', 'molido goteo', 'molido francesa') NOT NULL,
    tueste ENUM('medio', 'oscuro') NOT NULL, 
    envase ENUM('250g', '1kg', '2kg') NOT NULL,
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_variante (producto_id, molienda, tueste, envase) 
);

-- TABLA USUARIOS 
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    apellido VARCHAR(150),
    email VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    rol ENUM('cliente','admin') DEFAULT 'cliente',
    fecha_registro DATETIME
);

-- TABLA DIRECCIONES 
CREATE TABLE direcciones (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(150),
    provincia VARCHAR(150),
    pais VARCHAR(150),
    codigo_postal VARCHAR(20),
    predeterminada BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- TABLA CARRITOS 
CREATE TABLE carritos (
    id_carrito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNIQUE NOT NULL,
    fecha_creacion DATETIME,
    fecha_ultima_act DATETIME,
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- TABLA CARRITO_ITEMS 
CREATE TABLE carrito_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT NOT NULL,
    id_variante_sku VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    fecha_agregado DATETIME,
    
    FOREIGN KEY (id_carrito) REFERENCES carritos(id_carrito) ON DELETE CASCADE,
    FOREIGN KEY (id_variante_sku) REFERENCES producto_variantes(sku) ON UPDATE CASCADE,
    UNIQUE KEY uk_carrito_variante (id_carrito, id_variante_sku)
);

-- TABLA PEDIDOS (ANTES ORDENES)
CREATE TABLE pedidos (
    id_orden INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_direccion INT NOT NULL,
    total DECIMAL(10,2),
    estado ENUM('pendiente','pagado','preparando','enviado','entregado','cancelado') DEFAULT 'pendiente',
    fecha_orden DATETIME,
    direccion_envio_snapshot TEXT, 
    ciudad_envio_snapshot VARCHAR(150),
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_direccion) REFERENCES direcciones(id_direccion)
);

-- TABLA PEDIDO_ITEMS (ANTES ORDEN_ITEMS)
CREATE TABLE pedido_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_variante_sku VARCHAR(50) NOT NULL,
    precio_unitario DECIMAL(10,2),
    cantidad INT,
    
    FOREIGN KEY (id_orden) REFERENCES pedidos(id_orden),
    FOREIGN KEY (id_variante_sku) REFERENCES producto_variantes(sku)
);

-- TABLA PAGOS
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    metodo ENUM('tarjeta', 'bizum', 'gpay', 'paypal') NOT NULL, 
    monto DECIMAL(10,2) NOT NULL,
    estado VARCHAR(50),
    fecha_pago DATETIME,
    referencia_transaccion VARCHAR(255) UNIQUE,
    
    FOREIGN KEY (id_orden) REFERENCES pedidos(id_orden)
);

-- ----------------------------------------------------
-- 3. INSERCIÓN DE DATOS DE PRODUCTOS
-- ----------------------------------------------------

INSERT INTO productos (
    nombre_cafe, pais_origen, region, finca, altitud_msnm, variedad, proceso, 
    puntuacion_sca, notas_sabor, presentacion, descripcion, imagen
) VALUES 
('Brasil Sarutaia', 'Brasil', 'Minas Gerais', 'Fazenda Sarutaia', 1100, 'Yellow Bourbon', 'Natural', 84.0, 
    'Chocolate, nuez, caramelo', 'Espresso, moka', 
    'Café brasileño suave y dulce, con cuerpo redondo y acidez baja. Ideal para quienes buscan un perfil clásico y estable.', 
    'brasil_sarutaia.jpg'),
('Brasil Vila Boa', 'Brasil', 'Cerrado Mineiro', 'Vila Boa', 1200, 'Catuai', 'Honey', 85.0, 
    'Cacao, miel, almendra', 'Espresso, V60', 
    'Café dulce y equilibrado, con textura melosa y un postgusto prolongado gracias al proceso Honey.', 
    'brasil_vila_boa.jpg'),
('Burundi Kawavumera', 'Burundi', 'Kayanza', 'Kawavumera Cooperative', 1800, 'Red Bourbon', 'Lavado', 87.0, 
    'Frutos rojos, té negro, cítrico', 'V60, Kalita', 
    'Café vibrante, complejo y brillante con notas intensas a frutos rojos y un final limpio.', 
    'burundi_kawavumera.jpg'),
('Colombia Agualinda', 'Colombia', 'Antioquia', 'Finca Agualinda', 1900, 'Caturra', 'Lavado', 86.0, 
    'Panela, mandarina, floral', 'V60, Aeropress', 
    'Café fresco y floral con acidez cítrica balanceada y dulzor alto, muy típico del perfil colombiano.', 
    'colombia_agualinda.jpg'),
('Colombia Bourbon Sidra', 'Colombia', 'Nariño', 'El Silencio', 2050, 'Bourbon Sidra', 'Natural', 89.0, 
    'Fresa, vino tinto, jazmín', 'V60, Chemex', 
    'Café complejo y aromático con notas florales intensas y un carácter casi vinoso.', 
    'colombia_bourbon_sidra.jpg'),
('Colombia Ceiba Honey', 'Colombia', 'Huila', 'La Ceiba', 1750, 'Caturra', 'Honey', 87.0, 
    'Miel, melocotón, cacao', 'V60, Aeropress', 
    'Café dulce y jugoso con textura sedosa y excelente equilibrio gracias al proceso Honey.', 
    'colombia_ceiba_honey.jpg'),
('Colombia Guayava', 'Colombia', 'Tolima', 'El Vergel', 1500, 'Varietal Blend', 'Natural', 88.0, 
    'Guayaba, mora, flor blanca', 'V60, Kalita', 
    'Café frutal intenso con notas tropicales marcadas y una acidez brillante.', 
    'colombia_guayava.jpg'),
('Colombia Hydro Honey', 'Colombia', 'Huila', 'Las Flores', 1750, 'Bourbon Rosado', 'Honey', 88.0, 
    'Uva, miel, flor de cacao', 'V60, Aeropress', 
    'Café complejo con proceso Hydro Honey, dulce y limpio con notas a uva y miel.', 
    'colombia_hydro_honey.jpg'),
('Colombia Las Garzas Natural', 'Colombia', 'Cauca', 'Las Garzas', 1850, 'Castillo', 'Natural', 86.0, 
    'Frutos rojos, cacao, especias', 'V60, Chemex', 
    'Café afrutado y especiado, con dulzor intenso y gran profundidad.', 
    'colombia_las_garzas.jpg'),
('Colombia Mango Washed', 'Colombia', 'Antioquia', 'El Recreo', 1600, 'Castillo', 'Lavado', 87.0, 
    'Mango, cítrico, miel', 'V60, Aeropress', 
    'Café tropical con notas a mango y miel, brillante y expresivo.', 
    'colombia_mango_washed.jpg'),
('Ethiopia Aramo Natural', 'Etiopía', 'Yirgacheffe', 'Aramo', 2000, 'Heirloom', 'Natural', 88.0, 
    'Arándanos, jazmín, miel', 'V60, Chemex', 
    'Café floral y afrutado, dulce y aromático, ideal para filtrados delicados.', 
    'ethiopia_aramo.jpg'),
('Ethiopia Kochere Beloya Oro', 'Etiopía', 'Kochere', 'Beloya', 1950, 'Heirloom', 'Lavado', 87.0, 
    'Limón, melocotón, té blanco', 'V60, Kalita', 
    'Café limpio, delicado y floral con acidez refrescante y final suave.', 
    'ethiopia_kochere.jpg'),
('Ethiopia Yirga Natural Anaerobico', 'Etiopía', 'Yirgacheffe', 'Worka', 2050, 'Heirloom', 'Natural', 89.0, 
    'Fresa fermentada, flor, vino', 'V60, Chemex', 
    'Café explosivo y aromático con notas vinosas gracias al proceso anaeróbico.', 
    'ethiopia_yirga_anaerobico.jpg'),
('Etiopía Sidamo Shantawene', 'Etiopía', 'Sidamo', 'Shantawene', 1900, 'Heirloom', 'Lavado', 87.0, 
    'Bergamota, miel, flor blanca', 'V60, Kalita', 
    'Café elegante y floral con acidez refinada y dulzor suave.', 
    'ethiopia_sidamo.jpg'),
('Guatemala San Sebastián', 'Guatemala', 'Antigua', 'San Sebastián', 1650, 'Bourbon', 'Lavado', 85.0, 
    'Chocolate, avellana, cítrico', 'Espresso, Chemex', 
    'Café equilibrado y suave con notas clásicas a chocolate y cítrico.', 
    'guatemala_san_sebastian.jpg'),
('Honduras Los Lirios', 'Honduras', 'Marcala', 'Los Lirios', 1600, 'Catuai', 'Lavado', 84.0, 
    'Caramelo, nuez, manzana', 'V60, Moka', 
    'Café suave con dulzor a caramelo y acidez frutal ligera.', 
    'honduras_los_lirios.jpg'),
('Kenia Gititu AA', 'Kenia', 'Kiambu', 'Gititu', 1900, 'SL28, SL34', 'Lavado', 88.0, 
    'Grosella negra, pomelo, floral', 'V60, Chemex', 
    'Café keniano brillante y jugoso con notas intensas y acidez compleja.', 
    'kenia_gititu.jpg'),
('Nicaragua Jinotega', 'Nicaragua', 'Jinotega', 'Buenos Aires', 1400, 'Caturra', 'Lavado', 84.0, 
    'Chocolate, toffee, cítrico', 'Espresso, V60', 
    'Café suave y cremoso con notas cálidas a toffee y cítrico.', 
    'nicaragua_jinotega.jpg'),
('Perú Gesha Los quispe', 'Perú', 'Cusco', 'Los Quispe', 1900, 'Gesha', 'Lavado', 89.0, 
    'Bergamota, jazmín, miel', 'V60, Chemex', 
    'Café floral y elegante con acidez brillante y dulzor delicado.', 
    'peru_gesha_los_quispe.jpg');

-- ----------------------------------------------------
-- 4. INSERCIÓN DE DATOS DE VARIANTES
-- ----------------------------------------------------
-- PAIS(3) + NOM(3) + MOL(3) + TUE(1) + ENV(3)
-- sku de 13 digitos (3 letras pais)+(3 letras nombre)+(GRA,ESP,MOK,GOT,FRA)+(M,O)+(250,1KG,2KG)
-- Ej: BRA + SAR + GRA + M + 250 => BRASARGRAM250
-- ----------------------------------------------------

-- ----------------------------------------------------
-- VARIANTES BÁSICAS (Necesarias para botón rápido de search.php)
-- SKU: PAIS(3) + NOM(3) + GRA + M + 250
-- al final del SQL escribimos todas las variantes
-- ----------------------------------------------------

INSERT INTO producto_variantes (sku, producto_id, precio, stock, molienda, tueste, envase) VALUES 
-- 1. Brasil Sarutaia
('BRASARGRAM250', 1, 12.90, 100, 'grano', 'medio', '250g'),
-- 2. Brasil Vila Boa
('BRAVILGRAM250', 2, 13.50, 100, 'grano', 'medio', '250g'),
-- 3. Burundi Kawavumera
('BURKAWGRAM250', 3, 18.50, 100, 'grano', 'medio', '250g'),
-- 4. Colombia Agualinda
('COLAGUGRAM250', 4, 15.50, 100, 'grano', 'medio', '250g'),
-- 5. Colombia Bourbon Sidra
('COLBOUGRAM250', 5, 22.00, 100, 'grano', 'medio', '250g'),
-- 6. Colombia Ceiba Honey
('COLCEIGRAM250', 6, 16.50, 100, 'grano', 'medio', '250g'),
-- 7. Colombia Guayava
('COLGUAGRAM250', 7, 18.00, 100, 'grano', 'medio', '250g'),
-- 8. Colombia Hydro Honey
('COLHYDGRAM250', 8, 19.50, 100, 'grano', 'medio', '250g'),
-- 9. Colombia Las Garzas
('COLLASGRAM250', 9, 14.90, 100, 'grano', 'medio', '250g'),
-- 10. Colombia Mango Washed
('COLMANGRAM250', 10, 17.00, 100, 'grano', 'medio', '250g'),
-- 11. Ethiopia Aramo Natural
('ETHARAGRAM250', 11, 21.00, 100, 'grano', 'medio', '250g'),
-- 12. Ethiopia Kochere Beloya
('ETHKOCGRAM250', 12, 19.00, 100, 'grano', 'medio', '250g'),
-- 13. Ethiopia Yirga Natural
('ETHYIRGRAM250', 13, 23.00, 100, 'grano', 'medio', '250g'),
-- 14. Etiopía Sidamo Shantawene
('ETHSIDGRAM250', 14, 18.50, 100, 'grano', 'medio', '250g'),
-- 15. Guatemala San Sebastián
('GUASANGRAM250', 15, 14.50, 100, 'grano', 'medio', '250g'),
-- 16. Honduras Los Lirios
('HONLOSGRAM250', 16, 13.90, 100, 'grano', 'medio', '250g'),
-- 17. Kenia Gititu AA
('KENGITGRAM250', 17, 24.00, 100, 'grano', 'medio', '250g'),
-- 18. Nicaragua Jinotega
('NICJINGRAM250', 18, 12.50, 100, 'grano', 'medio', '250g'),
-- 19. Perú Gesha Los Quispe
('PERGESGRAM250', 19, 28.00, 100, 'grano', 'medio', '250g');

-- ----------------------------------------------------
-- 5. INSERCIÓN DE DATOS DE PRUEBA: USUARIOS
-- ----------------------------------------------------

INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono, rol, fecha_registro)
VALUES
('Admin', 'Global', 'admin@cafetera.com', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '123456789', 'admin', NOW()), 
('Cliente', 'Fiel', 'cliente@prueba.com', '$2y$10$YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY', '987654321', 'cliente', NOW());

-- ----------------------------------------------------
-- 6. INSERCIÓN DE DATOS DE PRUEBA: DIRECCIONES
-- ----------------------------------------------------

-- Asume que el 'id_usuario' del 'Cliente Fiel' es 2 (por el orden de inserción)
INSERT INTO direcciones (id_usuario, direccion, ciudad, provincia, pais, codigo_postal, predeterminada)
VALUES
(2, 'Calle Falsa 123', 'Madrid', 'Madrid', 'España', '28001', TRUE),
(2, 'Avenida Siempreviva 742', 'Barcelona', 'Barcelona', 'España', '08001', FALSE);

-- ----------------------------------------------------
-- 7. INSERCIÓN DE DATOS DE PRUEBA: CARRITO
-- ----------------------------------------------------

-- Crea un carrito para el Cliente Fiel (id_usuario=2)
INSERT INTO carritos (id_usuario, fecha_creacion, fecha_ultima_act)
VALUES (2, NOW(), NOW());

-- ----------------------------------------------------
-- 8. INSERCIÓN DE DATOS DE PRUEBA: CARRITO_ITEMS
-- ----------------------------------------------------

-- Añade 2 unidades del Brasil Sarutaia (sku 'BRASARGRAM250') al carrito del usuario 2 (id_carrito=1)
INSERT INTO carrito_items (id_carrito, id_variante_sku, cantidad, fecha_agregado)
VALUES (1, 'BRASARGRAM250', 2, NOW());

-- -------------------------------------------------------------------
-- VARIANTES ADICIONALES (Resto de combinaciones)
-- SKU: PAIS(3) + NOM(3) + MOL(3) + TUE(1) + ENV(3)
-- -------------------------------------------------------------------

INSERT INTO producto_variantes (sku, producto_id, precio, stock, molienda, tueste, envase) VALUES
('BRASARGRAO250', 1, 12.90, 100, 'grano', 'oscuro', '250g'),
('BRASARESPM250', 1, 12.90, 100, 'molido espresso', 'medio', '250g'),
('BRASARESPO250', 1, 12.90, 100, 'molido espresso', 'oscuro', '250g'),
('BRASARMOKM250', 1, 12.90, 100, 'molido moka', 'medio', '250g'),
('BRASARMOKO250', 1, 12.90, 100, 'molido moka', 'oscuro', '250g'),
('BRASARGOTM250', 1, 12.90, 100, 'molido goteo', 'medio', '250g'),
('BRASARGOTO250', 1, 12.90, 100, 'molido goteo', 'oscuro', '250g'),
('BRASARFRAM250', 1, 12.90, 100, 'molido francesa', 'medio', '250g'),
('BRASARFRAO250', 1, 12.90, 100, 'molido francesa', 'oscuro', '250g'),
('BRASARGRAM1KG', 1, 45.15, 100, 'grano', 'medio', '1kg'),
('BRASARGRAO1KG', 1, 45.15, 100, 'grano', 'oscuro', '1kg'),
('BRASARESPM1KG', 1, 45.15, 100, 'molido espresso', 'medio', '1kg'),
('BRASARESPO1KG', 1, 45.15, 100, 'molido espresso', 'oscuro', '1kg'),
('BRASARMOKM1KG', 1, 45.15, 100, 'molido moka', 'medio', '1kg'),
('BRASARMOKO1KG', 1, 45.15, 100, 'molido moka', 'oscuro', '1kg'),
('BRASARGOTM1KG', 1, 45.15, 100, 'molido goteo', 'medio', '1kg'),
('BRASARGOTO1KG', 1, 45.15, 100, 'molido goteo', 'oscuro', '1kg'),
('BRASARFRAM1KG', 1, 45.15, 100, 'molido francesa', 'medio', '1kg'),
('BRASARFRAO1KG', 1, 45.15, 100, 'molido francesa', 'oscuro', '1kg'),
('BRASARGRAM2KG', 1, 83.85, 100, 'grano', 'medio', '2kg'),
('BRASARGRAO2KG', 1, 83.85, 100, 'grano', 'oscuro', '2kg'),
('BRASARESPM2KG', 1, 83.85, 100, 'molido espresso', 'medio', '2kg'),
('BRASARESPO2KG', 1, 83.85, 100, 'molido espresso', 'oscuro', '2kg'),
('BRASARMOKM2KG', 1, 83.85, 100, 'molido moka', 'medio', '2kg'),
('BRASARMOKO2KG', 1, 83.85, 100, 'molido moka', 'oscuro', '2kg'),
('BRASARGOTM2KG', 1, 83.85, 100, 'molido goteo', 'medio', '2kg'),
('BRASARGOTO2KG', 1, 83.85, 100, 'molido goteo', 'oscuro', '2kg'),
('BRASARFRAM2KG', 1, 83.85, 100, 'molido francesa', 'medio', '2kg'),
('BRASARFRAO2KG', 1, 83.85, 100, 'molido francesa', 'oscuro', '2kg'),

-- Burundi Kawavumera
('BURKAWGRAO250', 2, 18.50, 100, 'grano', 'oscuro', '250g'),
('BURKAWESPM250', 2, 18.50, 100, 'molido espresso', 'medio', '250g'),
('BURKAWESPO250', 2, 18.50, 100, 'molido espresso', 'oscuro', '250g'),
('BURKAWMOKM250', 2, 18.50, 100, 'molido moka', 'medio', '250g'),
('BURKAWMOKO250', 2, 18.50, 100, 'molido moka', 'oscuro', '250g'),
('BURKAWGOTM250', 2, 18.50, 100, 'molido goteo', 'medio', '250g'),
('BURKAWGOTO250', 2, 18.50, 100, 'molido goteo', 'oscuro', '250g'),
('BURKAWFRAM250', 2, 18.50, 100, 'molido francesa', 'medio', '250g'),
('BURKAWFRAO250', 2, 18.50, 100, 'molido francesa', 'oscuro', '250g'),
('BURKAWGRAM1KG', 2, 64.75, 100, 'grano', 'medio', '1kg'),
('BURKAWGRAO1KG', 2, 64.75, 100, 'grano', 'oscuro', '1kg'),
('BURKAWESPM1KG', 2, 64.75, 100, 'molido espresso', 'medio', '1kg'),
('BURKAWESPO1KG', 2, 64.75, 100, 'molido espresso', 'oscuro', '1kg'),
('BURKAWMOKM1KG', 2, 64.75, 100, 'molido moka', 'medio', '1kg'),
('BURKAWMOKO1KG', 2, 64.75, 100, 'molido moka', 'oscuro', '1kg'),
('BURKAWGOTM1KG', 2, 64.75, 100, 'molido goteo', 'medio', '1kg'),
('BURKAWGOTO1KG', 2, 64.75, 100, 'molido goteo', 'oscuro', '1kg'),
('BURKAWFRAM1KG', 2, 64.75, 100, 'molido francesa', 'medio', '1kg'),
('BURKAWFRAO1KG', 2, 64.75, 100, 'molido francesa', 'oscuro', '1kg'),
('BURKAWGRAM2KG', 2, 120.25, 100, 'grano', 'medio', '2kg'),
('BURKAWGRAO2KG', 2, 120.25, 100, 'grano', 'oscuro', '2kg'),
('BURKAWESPM2KG', 2, 120.25, 100, 'molido espresso', 'medio', '2kg'),
('BURKAWESPO2KG', 2, 120.25, 100, 'molido espresso', 'oscuro', '2kg'),
('BURKAWMOKM2KG', 2, 120.25, 100, 'molido moka', 'medio', '2kg'),
('BURKAWMOKO2KG', 2, 120.25, 100, 'molido moka', 'oscuro', '2kg'),
('BURKAWGOTM2KG', 2, 120.25, 100, 'molido goteo', 'medio', '2kg'),
('BURKAWGOTO2KG', 2, 120.25, 100, 'molido goteo', 'oscuro', '2kg'),
('BURKAWFRAM2KG', 2, 120.25, 100, 'molido francesa', 'medio', '2kg'),
('BURKAWFRAO2KG', 2, 120.25, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Agualinda
('COLAGUGRAO250', 3, 15.50, 100, 'grano', 'oscuro', '250g'),
('COLAGUESPM250', 3, 15.50, 100, 'molido espresso', 'medio', '250g'),
('COLAGUESPO250', 3, 15.50, 100, 'molido espresso', 'oscuro', '250g'),
('COLAGUMOKM250', 3, 15.50, 100, 'molido moka', 'medio', '250g'),
('COLAGUMOKO250', 3, 15.50, 100, 'molido moka', 'oscuro', '250g'),
('COLAGUGOTM250', 3, 15.50, 100, 'molido goteo', 'medio', '250g'),
('COLAGUGOTO250', 3, 15.50, 100, 'molido goteo', 'oscuro', '250g'),
('COLAGUFRAM250', 3, 15.50, 100, 'molido francesa', 'medio', '250g'),
('COLAGUFRAO250', 3, 15.50, 100, 'molido francesa', 'oscuro', '250g'),
('COLAGUGRAM1KG', 3, 54.25, 100, 'grano', 'medio', '1kg'),
('COLAGUGRAO1KG', 3, 54.25, 100, 'grano', 'oscuro', '1kg'),
('COLAGUESPM1KG', 3, 54.25, 100, 'molido espresso', 'medio', '1kg'),
('COLAGUESPO1KG', 3, 54.25, 100, 'molido espresso', 'oscuro', '1kg'),
('COLAGUMOKM1KG', 3, 54.25, 100, 'molido moka', 'medio', '1kg'),
('COLAGUMOKO1KG', 3, 54.25, 100, 'molido moka', 'oscuro', '1kg'),
('COLAGUGOTM1KG', 3, 54.25, 100, 'molido goteo', 'medio', '1kg'),
('COLAGUGOTO1KG', 3, 54.25, 100, 'molido goteo', 'oscuro', '1kg'),
('COLAGUFRAM1KG', 3, 54.25, 100, 'molido francesa', 'medio', '1kg'),
('COLAGUFRAO1KG', 3, 54.25, 100, 'molido francesa', 'oscuro', '1kg'),
('COLAGUGRAM2KG', 3, 100.75, 100, 'grano', 'medio', '2kg'),
('COLAGUGRAO2KG', 3, 100.75, 100, 'grano', 'oscuro', '2kg'),
('COLAGUESPM2KG', 3, 100.75, 100, 'molido espresso', 'medio', '2kg'),
('COLAGUESPO2KG', 3, 100.75, 100, 'molido espresso', 'oscuro', '2kg'),
('COLAGUMOKM2KG', 3, 100.75, 100, 'molido moka', 'medio', '2kg'),
('COLAGUMOKO2KG', 3, 100.75, 100, 'molido moka', 'oscuro', '2kg'),
('COLAGUGOTM2KG', 3, 100.75, 100, 'molido goteo', 'medio', '2kg'),
('COLAGUGOTO2KG', 3, 100.75, 100, 'molido goteo', 'oscuro', '2kg'),
('COLAGUFRAM2KG', 3, 100.75, 100, 'molido francesa', 'medio', '2kg'),
('COLAGUFRAO2KG', 3, 100.75, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Bourbon Sidra
('COLBOUGRAO250', 4, 22.00, 100, 'grano', 'oscuro', '250g'),
('COLBOUESPM250', 4, 22.00, 100, 'molido espresso', 'medio', '250g'),
('COLBOUESPO250', 4, 22.00, 100, 'molido espresso', 'oscuro', '250g'),
('COLBOUMOKM250', 4, 22.00, 100, 'molido moka', 'medio', '250g'),
('COLBOUMOKO250', 4, 22.00, 100, 'molido moka', 'oscuro', '250g'),
('COLBOUGOTM250', 4, 22.00, 100, 'molido goteo', 'medio', '250g'),
('COLBOUGOTO250', 4, 22.00, 100, 'molido goteo', 'oscuro', '250g'),
('COLBOUFRAM250', 4, 22.00, 100, 'molido francesa', 'medio', '250g'),
('COLBOUFRAO250', 4, 22.00, 100, 'molido francesa', 'oscuro', '250g'),
('COLBOUGRAM1KG', 4, 77.00, 100, 'grano', 'medio', '1kg'),
('COLBOUGRAO1KG', 4, 77.00, 100, 'grano', 'oscuro', '1kg'),
('COLBOUESPM1KG', 4, 77.00, 100, 'molido espresso', 'medio', '1kg'),
('COLBOUESPO1KG', 4, 77.00, 100, 'molido espresso', 'oscuro', '1kg'),
('COLBOUMOKM1KG', 4, 77.00, 100, 'molido moka', 'medio', '1kg'),
('COLBOUMOKO1KG', 4, 77.00, 100, 'molido moka', 'oscuro', '1kg'),
('COLBOUGOTM1KG', 4, 77.00, 100, 'molido goteo', 'medio', '1kg'),
('COLBOUGOTO1KG', 4, 77.00, 100, 'molido goteo', 'oscuro', '1kg'),
('COLBOUFRAM1KG', 4, 77.00, 100, 'molido francesa', 'medio', '1kg'),
('COLBOUFRAO1KG', 4, 77.00, 100, 'molido francesa', 'oscuro', '1kg'),
('COLBOUGRAM2KG', 4, 143.00, 100, 'grano', 'medio', '2kg'),
('COLBOUGRAO2KG', 4, 143.00, 100, 'grano', 'oscuro', '2kg'),
('COLBOUESPM2KG', 4, 143.00, 100, 'molido espresso', 'medio', '2kg'),
('COLBOUESPO2KG', 4, 143.00, 100, 'molido espresso', 'oscuro', '2kg'),
('COLBOUMOKM2KG', 4, 143.00, 100, 'molido moka', 'medio', '2kg'),
('COLBOUMOKO2KG', 4, 143.00, 100, 'molido moka', 'oscuro', '2kg'),
('COLBOUGOTM2KG', 4, 143.00, 100, 'molido goteo', 'medio', '2kg'),
('COLBOUGOTO2KG', 4, 143.00, 100, 'molido goteo', 'oscuro', '2kg'),
('COLBOUFRAM2KG', 4, 143.00, 100, 'molido francesa', 'medio', '2kg'),
('COLBOUFRAO2KG', 4, 143.00, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Ceiba Honey
('COLCEIGRAO250', 5, 16.50, 100, 'grano', 'oscuro', '250g'),
('COLCEIESPM250', 5, 16.50, 100, 'molido espresso', 'medio', '250g'),
('COLCEIESPO250', 5, 16.50, 100, 'molido espresso', 'oscuro', '250g'),
('COLCEIMOKM250', 5, 16.50, 100, 'molido moka', 'medio', '250g'),
('COLCEIMOKO250', 5, 16.50, 100, 'molido moka', 'oscuro', '250g'),
('COLCEIGOTM250', 5, 16.50, 100, 'molido goteo', 'medio', '250g'),
('COLCEIGOTO250', 5, 16.50, 100, 'molido goteo', 'oscuro', '250g'),
('COLCEIFRAM250', 5, 16.50, 100, 'molido francesa', 'medio', '250g'),
('COLCEIFRAO250', 5, 16.50, 100, 'molido francesa', 'oscuro', '250g'),
('COLCEIGRAM1KG', 5, 57.75, 100, 'grano', 'medio', '1kg'),
('COLCEIGRAO1KG', 5, 57.75, 100, 'grano', 'oscuro', '1kg'),
('COLCEIESPM1KG', 5, 57.75, 100, 'molido espresso', 'medio', '1kg'),
('COLCEIESPO1KG', 5, 57.75, 100, 'molido espresso', 'oscuro', '1kg'),
('COLCEIMOKM1KG', 5, 57.75, 100, 'molido moka', 'medio', '1kg'),
('COLCEIMOKO1KG', 5, 57.75, 100, 'molido moka', 'oscuro', '1kg'),
('COLCEIGOTM1KG', 5, 57.75, 100, 'molido goteo', 'medio', '1kg'),
('COLCEIGOTO1KG', 5, 57.75, 100, 'molido goteo', 'oscuro', '1kg'),
('COLCEIFRAM1KG', 5, 57.75, 100, 'molido francesa', 'medio', '1kg'),
('COLCEIFRAO1KG', 5, 57.75, 100, 'molido francesa', 'oscuro', '1kg'),
('COLCEIGRAM2KG', 5, 107.25, 100, 'grano', 'medio', '2kg'),
('COLCEIGRAO2KG', 5, 107.25, 100, 'grano', 'oscuro', '2kg'),
('COLCEIESPM2KG', 5, 107.25, 100, 'molido espresso', 'medio', '2kg'),
('COLCEIESPO2KG', 5, 107.25, 100, 'molido espresso', 'oscuro', '2kg'),
('COLCEIMOKM2KG', 5, 107.25, 100, 'molido moka', 'medio', '2kg'),
('COLCEIMOKO2KG', 5, 107.25, 100, 'molido moka', 'oscuro', '2kg'),
('COLCEIGOTM2KG', 5, 107.25, 100, 'molido goteo', 'medio', '2kg'),
('COLCEIGOTO2KG', 5, 107.25, 100, 'molido goteo', 'oscuro', '2kg'),
('COLCEIFRAM2KG', 5, 107.25, 100, 'molido francesa', 'medio', '2kg'),
('COLCEIFRAO2KG', 5, 107.25, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Guayava
('COLGUAGRAO250', 6, 18.00, 100, 'grano', 'oscuro', '250g'),
('COLGUAESPM250', 6, 18.00, 100, 'molido espresso', 'medio', '250g'),
('COLGUAESPO250', 6, 18.00, 100, 'molido espresso', 'oscuro', '250g'),
('COLGUAMOKM250', 6, 18.00, 100, 'molido moka', 'medio', '250g'),
('COLGUAMOKO250', 6, 18.00, 100, 'molido moka', 'oscuro', '250g'),
('COLGUAGOTM250', 6, 18.00, 100, 'molido goteo', 'medio', '250g'),
('COLGUAGOTO250', 6, 18.00, 100, 'molido goteo', 'oscuro', '250g'),
('COLGUAFRAM250', 6, 18.00, 100, 'molido francesa', 'medio', '250g'),
('COLGUAFRAO250', 6, 18.00, 100, 'molido francesa', 'oscuro', '250g'),
('COLGUAGRAM1KG', 6, 63.00, 100, 'grano', 'medio', '1kg'),
('COLGUAGRAO1KG', 6, 63.00, 100, 'grano', 'oscuro', '1kg'),
('COLGUAESPM1KG', 6, 63.00, 100, 'molido espresso', 'medio', '1kg'),
('COLGUAESPO1KG', 6, 63.00, 100, 'molido espresso', 'oscuro', '1kg'),
('COLGUAMOKM1KG', 6, 63.00, 100, 'molido moka', 'medio', '1kg'),
('COLGUAMOKO1KG', 6, 63.00, 100, 'molido moka', 'oscuro', '1kg'),
('COLGUAGOTM1KG', 6, 63.00, 100, 'molido goteo', 'medio', '1kg'),
('COLGUAGOTO1KG', 6, 63.00, 100, 'molido goteo', 'oscuro', '1kg'),
('COLGUAFRAM1KG', 6, 63.00, 100, 'molido francesa', 'medio', '1kg'),
('COLGUAFRAO1KG', 6, 63.00, 100, 'molido francesa', 'oscuro', '1kg'),
('COLGUAGRAM2KG', 6, 117.00, 100, 'grano', 'medio', '2kg'),
('COLGUAGRAO2KG', 6, 117.00, 100, 'grano', 'oscuro', '2kg'),
('COLGUAESPM2KG', 6, 117.00, 100, 'molido espresso', 'medio', '2kg'),
('COLGUAESPO2KG', 6, 117.00, 100, 'molido espresso', 'oscuro', '2kg'),
('COLGUAMOKM2KG', 6, 117.00, 100, 'molido moka', 'medio', '2kg'),
('COLGUAMOKO2KG', 6, 117.00, 100, 'molido moka', 'oscuro', '2kg'),
('COLGUAGOTM2KG', 6, 117.00, 100, 'molido goteo', 'medio', '2kg'),
('COLGUAGOTO2KG', 6, 117.00, 100, 'molido goteo', 'oscuro', '2kg'),
('COLGUAFRAM2KG', 6, 117.00, 100, 'molido francesa', 'medio', '2kg'),
('COLGUAFRAO2KG', 6, 117.00, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Hydro Honey
('COLHYDGRAO250', 7, 19.50, 100, 'grano', 'oscuro', '250g'),
('COLHYDESPM250', 7, 19.50, 100, 'molido espresso', 'medio', '250g'),
('COLHYDESPO250', 7, 19.50, 100, 'molido espresso', 'oscuro', '250g'),
('COLHYDMOKM250', 7, 19.50, 100, 'molido moka', 'medio', '250g'),
('COLHYDMOKO250', 7, 19.50, 100, 'molido moka', 'oscuro', '250g'),
('COLHYDGOTM250', 7, 19.50, 100, 'molido goteo', 'medio', '250g'),
('COLHYDGOTO250', 7, 19.50, 100, 'molido goteo', 'oscuro', '250g'),
('COLHYDFRAM250', 7, 19.50, 100, 'molido francesa', 'medio', '250g'),
('COLHYDFRAO250', 7, 19.50, 100, 'molido francesa', 'oscuro', '250g'),
('COLHYDGRAM1KG', 7, 68.25, 100, 'grano', 'medio', '1kg'),
('COLHYDGRAO1KG', 7, 68.25, 100, 'grano', 'oscuro', '1kg'),
('COLHYDESPM1KG', 7, 68.25, 100, 'molido espresso', 'medio', '1kg'),
('COLHYDESPO1KG', 7, 68.25, 100, 'molido espresso', 'oscuro', '1kg'),
('COLHYDMOKM1KG', 7, 68.25, 100, 'molido moka', 'medio', '1kg'),
('COLHYDMOKO1KG', 7, 68.25, 100, 'molido moka', 'oscuro', '1kg'),
('COLHYDGOTM1KG', 7, 68.25, 100, 'molido goteo', 'medio', '1kg'),
('COLHYDGOTO1KG', 7, 68.25, 100, 'molido goteo', 'oscuro', '1kg'),
('COLHYDFRAM1KG', 7, 68.25, 100, 'molido francesa', 'medio', '1kg'),
('COLHYDFRAO1KG', 7, 68.25, 100, 'molido francesa', 'oscuro', '1kg'),
('COLHYDGRAM2KG', 7, 126.75, 100, 'grano', 'medio', '2kg'),
('COLHYDGRAO2KG', 7, 126.75, 100, 'grano', 'oscuro', '2kg'),
('COLHYDESPM2KG', 7, 126.75, 100, 'molido espresso', 'medio', '2kg'),
('COLHYDESPO2KG', 7, 126.75, 100, 'molido espresso', 'oscuro', '2kg'),
('COLHYDMOKM2KG', 7, 126.75, 100, 'molido moka', 'medio', '2kg'),
('COLHYDMOKO2KG', 7, 126.75, 100, 'molido moka', 'oscuro', '2kg'),
('COLHYDGOTM2KG', 7, 126.75, 100, 'molido goteo', 'medio', '2kg'),
('COLHYDGOTO2KG', 7, 126.75, 100, 'molido goteo', 'oscuro', '2kg'),
('COLHYDFRAM2KG', 7, 126.75, 100, 'molido francesa', 'medio', '2kg'),
('COLHYDFRAO2KG', 7, 126.75, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Las Garzas
('COLLASGRAO250', 8, 14.90, 100, 'grano', 'oscuro', '250g'),
('COLLASESPM250', 8, 14.90, 100, 'molido espresso', 'medio', '250g'),
('COLLASESPO250', 8, 14.90, 100, 'molido espresso', 'oscuro', '250g'),
('COLLASMOKM250', 8, 14.90, 100, 'molido moka', 'medio', '250g'),
('COLLASMOKO250', 8, 14.90, 100, 'molido moka', 'oscuro', '250g'),
('COLLASGOTM250', 8, 14.90, 100, 'molido goteo', 'medio', '250g'),
('COLLASGOTO250', 8, 14.90, 100, 'molido goteo', 'oscuro', '250g'),
('COLLASFRAM250', 8, 14.90, 100, 'molido francesa', 'medio', '250g'),
('COLLASFRAO250', 8, 14.90, 100, 'molido francesa', 'oscuro', '250g'),
('COLLASGRAM1KG', 8, 52.15, 100, 'grano', 'medio', '1kg'),
('COLLASGRAO1KG', 8, 52.15, 100, 'grano', 'oscuro', '1kg'),
('COLLASESPM1KG', 8, 52.15, 100, 'molido espresso', 'medio', '1kg'),
('COLLASESPO1KG', 8, 52.15, 100, 'molido espresso', 'oscuro', '1kg'),
('COLLASMOKM1KG', 8, 52.15, 100, 'molido moka', 'medio', '1kg'),
('COLLASMOKO1KG', 8, 52.15, 100, 'molido moka', 'oscuro', '1kg'),
('COLLASGOTM1KG', 8, 52.15, 100, 'molido goteo', 'medio', '1kg'),
('COLLASGOTO1KG', 8, 52.15, 100, 'molido goteo', 'oscuro', '1kg'),
('COLLASFRAM1KG', 8, 52.15, 100, 'molido francesa', 'medio', '1kg'),
('COLLASFRAO1KG', 8, 52.15, 100, 'molido francesa', 'oscuro', '1kg'),
('COLLASGRAM2KG', 8, 96.85, 100, 'grano', 'medio', '2kg'),
('COLLASGRAO2KG', 8, 96.85, 100, 'grano', 'oscuro', '2kg'),
('COLLASESPM2KG', 8, 96.85, 100, 'molido espresso', 'medio', '2kg'),
('COLLASESPO2KG', 8, 96.85, 100, 'molido espresso', 'oscuro', '2kg'),
('COLLASMOKM2KG', 8, 96.85, 100, 'molido moka', 'medio', '2kg'),
('COLLASMOKO2KG', 8, 96.85, 100, 'molido moka', 'oscuro', '2kg'),
('COLLASGOTM2KG', 8, 96.85, 100, 'molido goteo', 'medio', '2kg'),
('COLLASGOTO2KG', 8, 96.85, 100, 'molido goteo', 'oscuro', '2kg'),
('COLLASFRAM2KG', 8, 96.85, 100, 'molido francesa', 'medio', '2kg'),
('COLLASFRAO2KG', 8, 96.85, 100, 'molido francesa', 'oscuro', '2kg'),

-- Colombia Mango Washed
('COLMANGRAO250', 9, 17.00, 100, 'grano', 'oscuro', '250g'),
('COLMANESPM250', 9, 17.00, 100, 'molido espresso', 'medio', '250g'),
('COLMANESPO250', 9, 17.00, 100, 'molido espresso', 'oscuro', '250g'),
('COLMANMOKM250', 9, 17.00, 100, 'molido moka', 'medio', '250g'),
('COLMANMOKO250', 9, 17.00, 100, 'molido moka', 'oscuro', '250g'),
('COLMANGOTM250', 9, 17.00, 100, 'molido goteo', 'medio', '250g'),
('COLMANGOTO250', 9, 17.00, 100, 'molido goteo', 'oscuro', '250g'),
('COLMANFRAM250', 9, 17.00, 100, 'molido francesa', 'medio', '250g'),
('COLMANFRAO250', 9, 17.00, 100, 'molido francesa', 'oscuro', '250g'),
('COLMANGRAM1KG', 9, 59.50, 100, 'grano', 'medio', '1kg'),
('COLMANGRAO1KG', 9, 59.50, 100, 'grano', 'oscuro', '1kg'),
('COLMANESPM1KG', 9, 59.50, 100, 'molido espresso', 'medio', '1kg'),
('COLMANESPO1KG', 9, 59.50, 100, 'molido espresso', 'oscuro', '1kg'),
('COLMANMOKM1KG', 9, 59.50, 100, 'molido moka', 'medio', '1kg'),
('COLMANMOKO1KG', 9, 59.50, 100, 'molido moka', 'oscuro', '1kg'),
('COLMANGOTM1KG', 9, 59.50, 100, 'molido goteo', 'medio', '1kg'),
('COLMANGOTO1KG', 9, 59.50, 100, 'molido goteo', 'oscuro', '1kg'),
('COLMANFRAM1KG', 9, 59.50, 100, 'molido francesa', 'medio', '1kg'),
('COLMANFRAO1KG', 9, 59.50, 100, 'molido francesa', 'oscuro', '1kg'),
('COLMANGRAM2KG', 9, 110.50, 100, 'grano', 'medio', '2kg'),
('COLMANGRAO2KG', 9, 110.50, 100, 'grano', 'oscuro', '2kg'),
('COLMANESPM2KG', 9, 110.50, 100, 'molido espresso', 'medio', '2kg'),
('COLMANESPO2KG', 9, 110.50, 100, 'molido espresso', 'oscuro', '2kg'),
('COLMANMOKM2KG', 9, 110.50, 100, 'molido moka', 'medio', '2kg'),
('COLMANMOKO2KG', 9, 110.50, 100, 'molido moka', 'oscuro', '2kg'),
('COLMANGOTM2KG', 9, 110.50, 100, 'molido goteo', 'medio', '2kg'),
('COLMANGOTO2KG', 9, 110.50, 100, 'molido goteo', 'oscuro', '2kg'),
('COLMANFRAM2KG', 9, 110.50, 100, 'molido francesa', 'medio', '2kg'),
('COLMANFRAO2KG', 9, 110.50, 100, 'molido francesa', 'oscuro', '2kg'),

-- Ethiopia Aramo
('ETHARAGRAO250', 10, 21.00, 100, 'grano', 'oscuro', '250g'),
('ETHARAESPM250', 10, 21.00, 100, 'molido espresso', 'medio', '250g'),
('ETHARAESPO250', 10, 21.00, 100, 'molido espresso', 'oscuro', '250g'),
('ETHARAMOKM250', 10, 21.00, 100, 'molido moka', 'medio', '250g'),
('ETHARAMOKO250', 10, 21.00, 100, 'molido moka', 'oscuro', '250g'),
('ETHARAGOTM250', 10, 21.00, 100, 'molido goteo', 'medio', '250g'),
('ETHARAGOTO250', 10, 21.00, 100, 'molido goteo', 'oscuro', '250g'),
('ETHARAFRAM250', 10, 21.00, 100, 'molido francesa', 'medio', '250g'),
('ETHARAFRAO250', 10, 21.00, 100, 'molido francesa', 'oscuro', '250g'),
('ETHARAGRAM1KG', 10, 73.50, 100, 'grano', 'medio', '1kg'),
('ETHARAGRAO1KG', 10, 73.50, 100, 'grano', 'oscuro', '1kg'),
('ETHARAESPM1KG', 10, 73.50, 100, 'molido espresso', 'medio', '1kg'),
('ETHARAESPO1KG', 10, 73.50, 100, 'molido espresso', 'oscuro', '1kg'),
('ETHARAMOKM1KG', 10, 73.50, 100, 'molido moka', 'medio', '1kg'),
('ETHARAMOKO1KG', 10, 73.50, 100, 'molido moka', 'oscuro', '1kg'),
('ETHARAGOTM1KG', 10, 73.50, 100, 'molido goteo', 'medio', '1kg'),
('ETHARAGOTO1KG', 10, 73.50, 100, 'molido goteo', 'oscuro', '1kg'),
('ETHARAFRAM1KG', 10, 73.50, 100, 'molido francesa', 'medio', '1kg'),
('ETHARAFRAO1KG', 10, 73.50, 100, 'molido francesa', 'oscuro', '1kg'),
('ETHARAGRAM2KG', 10, 136.50, 100, 'grano', 'medio', '2kg'),
('ETHARAGRAO2KG', 10, 136.50, 100, 'grano', 'oscuro', '2kg'),
('ETHARAESPM2KG', 10, 136.50, 100, 'molido espresso', 'medio', '2kg'),
('ETHARAESPO2KG', 10, 136.50, 100, 'molido espresso', 'oscuro', '2kg'),
('ETHARAMOKM2KG', 10, 136.50, 100, 'molido moka', 'medio', '2kg'),
('ETHARAMOKO2KG', 10, 136.50, 100, 'molido moka', 'oscuro', '2kg'),
('ETHARAGOTM2KG', 10, 136.50, 100, 'molido goteo', 'medio', '2kg'),
('ETHARAGOTO2KG', 10, 136.50, 100, 'molido goteo', 'oscuro', '2kg'),
('ETHARAFRAM2KG', 10, 136.50, 100, 'molido francesa', 'medio', '2kg'),
('ETHARAFRAO2KG', 10, 136.50, 100, 'molido francesa', 'oscuro', '2kg'),

-- Ethiopia Kochere
('ETHKOCGRAO250', 11, 19.00, 100, 'grano', 'oscuro', '250g'),
('ETHKOCESPM250', 11, 19.00, 100, 'molido espresso', 'medio', '250g'),
('ETHKOCESPO250', 11, 19.00, 100, 'molido espresso', 'oscuro', '250g'),
('ETHKOCMOKM250', 11, 19.00, 100, 'molido moka', 'medio', '250g'),
('ETHKOCMOKO250', 11, 19.00, 100, 'molido moka', 'oscuro', '250g'),
('ETHKOCGOTM250', 11, 19.00, 100, 'molido goteo', 'medio', '250g'),
('ETHKOCGOTO250', 11, 19.00, 100, 'molido goteo', 'oscuro', '250g'),
('ETHKOCFRAM250', 11, 19.00, 100, 'molido francesa', 'medio', '250g'),
('ETHKOCFRAO250', 11, 19.00, 100, 'molido francesa', 'oscuro', '250g'),
('ETHKOCGRAM1KG', 11, 66.50, 100, 'grano', 'medio', '1kg'),
('ETHKOCGRAO1KG', 11, 66.50, 100, 'grano', 'oscuro', '1kg'),
('ETHKOCESPM1KG', 11, 66.50, 100, 'molido espresso', 'medio', '1kg'),
('ETHKOCESPO1KG', 11, 66.50, 100, 'molido espresso', 'oscuro', '1kg'),
('ETHKOCMOKM1KG', 11, 66.50, 100, 'molido moka', 'medio', '1kg'),
('ETHKOCMOKO1KG', 11, 66.50, 100, 'molido moka', 'oscuro', '1kg'),
('ETHKOCGOTM1KG', 11, 66.50, 100, 'molido goteo', 'medio', '1kg'),
('ETHKOCGOTO1KG', 11, 66.50, 100, 'molido goteo', 'oscuro', '1kg'),
('ETHKOCFRAM1KG', 11, 66.50, 100, 'molido francesa', 'medio', '1kg'),
('ETHKOCFRAO1KG', 11, 66.50, 100, 'molido francesa', 'oscuro', '1kg'),
('ETHKOCGRAM2KG', 11, 123.50, 100, 'grano', 'medio', '2kg'),
('ETHKOCGRAO2KG', 11, 123.50, 100, 'grano', 'oscuro', '2kg'),
('ETHKOCESPM2KG', 11, 123.50, 100, 'molido espresso', 'medio', '2kg'),
('ETHKOCESPO2KG', 11, 123.50, 100, 'molido espresso', 'oscuro', '2kg'),
('ETHKOCMOKM2KG', 11, 123.50, 100, 'molido moka', 'medio', '2kg'),
('ETHKOCMOKO2KG', 11, 123.50, 100, 'molido moka', 'oscuro', '2kg'),
('ETHKOCGOTM2KG', 11, 123.50, 100, 'molido goteo', 'medio', '2kg'),
('ETHKOCGOTO2KG', 11, 123.50, 100, 'molido goteo', 'oscuro', '2kg'),
('ETHKOCFRAM2KG', 11, 123.50, 100, 'molido francesa', 'medio', '2kg'),
('ETHKOCFRAO2KG', 11, 123.50, 100, 'molido francesa', 'oscuro', '2kg'),

-- Ethiopia Yirga
('ETHYIRGRAO250', 12, 23.00, 100, 'grano', 'oscuro', '250g'),
('ETHYIRESPM250', 12, 23.00, 100, 'molido espresso', 'medio', '250g'),
('ETHYIRESPO250', 12, 23.00, 100, 'molido espresso', 'oscuro', '250g'),
('ETHYIRMOKM250', 12, 23.00, 100, 'molido moka', 'medio', '250g'),
('ETHYIRMOKO250', 12, 23.00, 100, 'molido moka', 'oscuro', '250g'),
('ETHYIRGOTM250', 12, 23.00, 100, 'molido goteo', 'medio', '250g'),
('ETHYIRGOTO250', 12, 23.00, 100, 'molido goteo', 'oscuro', '250g'),
('ETHYIRFRAM250', 12, 23.00, 100, 'molido francesa', 'medio', '250g'),
('ETHYIRFRAO250', 12, 23.00, 100, 'molido francesa', 'oscuro', '250g'),
('ETHYIRGRAM1KG', 12, 80.50, 100, 'grano', 'medio', '1kg'),
('ETHYIRGRAO1KG', 12, 80.50, 100, 'grano', 'oscuro', '1kg'),
('ETHYIRESPM1KG', 12, 80.50, 100, 'molido espresso', 'medio', '1kg'),
('ETHYIRESPO1KG', 12, 80.50, 100, 'molido espresso', 'oscuro', '1kg'),
('ETHYIRMOKM1KG', 12, 80.50, 100, 'molido moka', 'medio', '1kg'),
('ETHYIRMOKO1KG', 12, 80.50, 100, 'molido moka', 'oscuro', '1kg'),
('ETHYIRGOTM1KG', 12, 80.50, 100, 'molido goteo', 'medio', '1kg'),
('ETHYIRGOTO1KG', 12, 80.50, 100, 'molido goteo', 'oscuro', '1kg'),
('ETHYIRFRAM1KG', 12, 80.50, 100, 'molido francesa', 'medio', '1kg'),
('ETHYIRFRAO1KG', 12, 80.50, 100, 'molido francesa', 'oscuro', '1kg'),
('ETHYIRGRAM2KG', 12, 149.50, 100, 'grano', 'medio', '2kg'),
('ETHYIRGRAO2KG', 12, 149.50, 100, 'grano', 'oscuro', '2kg'),
('ETHYIRESPM2KG', 12, 149.50, 100, 'molido espresso', 'medio', '2kg'),
('ETHYIRESPO2KG', 12, 149.50, 100, 'molido espresso', 'oscuro', '2kg'),
('ETHYIRMOKM2KG', 12, 149.50, 100, 'molido moka', 'medio', '2kg'),
('ETHYIRMOKO2KG', 12, 149.50, 100, 'molido moka', 'oscuro', '2kg'),
('ETHYIRGOTM2KG', 12, 149.50, 100, 'molido goteo', 'medio', '2kg'),
('ETHYIRGOTO2KG', 12, 149.50, 100, 'molido goteo', 'oscuro', '2kg'),
('ETHYIRFRAM2KG', 12, 149.50, 100, 'molido francesa', 'medio', '2kg'),
('ETHYIRFRAO2KG', 12, 149.50, 100, 'molido francesa', 'oscuro', '2kg'),

-- Etiopía Sidamo Shantawene
('ETHSIDGRAO250', 13, 18.50, 100, 'grano', 'oscuro', '250g'),
('ETHSIDESPM250', 13, 18.50, 100, 'molido espresso', 'medio', '250g'),
('ETHSIDESPO250', 13, 18.50, 100, 'molido espresso', 'oscuro', '250g'),
('ETHSIDMOKM250', 13, 18.50, 100, 'molido moka', 'medio', '250g'),
('ETHSIDMOKO250', 13, 18.50, 100, 'molido moka', 'oscuro', '250g'),
('ETHSIDGOTM250', 13, 18.50, 100, 'molido goteo', 'medio', '250g'),
('ETHSIDGOTO250', 13, 18.50, 100, 'molido goteo', 'oscuro', '250g'),
('ETHSIDFRAM250', 13, 18.50, 100, 'molido francesa', 'medio', '250g'),
('ETHSIDFRAO250', 13, 18.50, 100, 'molido francesa', 'oscuro', '250g'),
('ETHSIDGRAM1KG', 13, 64.75, 100, 'grano', 'medio', '1kg'),
('ETHSIDGRAO1KG', 13, 64.75, 100, 'grano', 'oscuro', '1kg'),
('ETHSIDESPM1KG', 13, 64.75, 100, 'molido espresso', 'medio', '1kg'),
('ETHSIDESPO1KG', 13, 64.75, 100, 'molido espresso', 'oscuro', '1kg'),
('ETHSIDMOKM1KG', 13, 64.75, 100, 'molido moka', 'medio', '1kg'),
('ETHSIDMOKO1KG', 13, 64.75, 100, 'molido moka', 'oscuro', '1kg'),
('ETHSIDGOTM1KG', 13, 64.75, 100, 'molido goteo', 'medio', '1kg'),
('ETHSIDGOTO1KG', 13, 64.75, 100, 'molido goteo', 'oscuro', '1kg'),
('ETHSIDFRAM1KG', 13, 64.75, 100, 'molido francesa', 'medio', '1kg'),
('ETHSIDFRAO1KG', 13, 64.75, 100, 'molido francesa', 'oscuro', '1kg'),
('ETHSIDGRAM2KG', 13, 120.25, 100, 'grano', 'medio', '2kg'),
('ETHSIDGRAO2KG', 13, 120.25, 100, 'grano', 'oscuro', '2kg'),
('ETHSIDESPM2KG', 13, 120.25, 100, 'molido espresso', 'medio', '2kg'),
('ETHSIDESPO2KG', 13, 120.25, 100, 'molido espresso', 'oscuro', '2kg'),
('ETHSIDMOKM2KG', 13, 120.25, 100, 'molido moka', 'medio', '2kg'),
('ETHSIDMOKO2KG', 13, 120.25, 100, 'molido moka', 'oscuro', '2kg'),
('ETHSIDGOTM2KG', 13, 120.25, 100, 'molido goteo', 'medio', '2kg'),
('ETHSIDGOTO2KG', 13, 120.25, 100, 'molido goteo', 'oscuro', '2kg'),
('ETHSIDFRAM2KG', 13, 120.25, 100, 'molido francesa', 'medio', '2kg'),
('ETHSIDFRAO2KG', 13, 120.25, 100, 'molido francesa', 'oscuro', '2kg'),

-- Guatemala San Sebastián
('GUASANGRAO250', 14, 14.50, 100, 'grano', 'oscuro', '250g'),
('GUASANESPM250', 14, 14.50, 100, 'molido espresso', 'medio', '250g'),
('GUASANESPO250', 14, 14.50, 100, 'molido espresso', 'oscuro', '250g'),
('GUASANMOKM250', 14, 14.50, 100, 'molido moka', 'medio', '250g'),
('GUASANMOKO250', 14, 14.50, 100, 'molido moka', 'oscuro', '250g'),
('GUASANGOTM250', 14, 14.50, 100, 'molido goteo', 'medio', '250g'),
('GUASANGOTO250', 14, 14.50, 100, 'molido goteo', 'oscuro', '250g'),
('GUASANFRAM250', 14, 14.50, 100, 'molido francesa', 'medio', '250g'),
('GUASANFRAO250', 14, 14.50, 100, 'molido francesa', 'oscuro', '250g'),
('GUASANGRAM1KG', 14, 50.75, 100, 'grano', 'medio', '1kg'),
('GUASANGRAO1KG', 14, 50.75, 100, 'grano', 'oscuro', '1kg'),
('GUASANESPM1KG', 14, 50.75, 100, 'molido espresso', 'medio', '1kg'),
('GUASANESPO1KG', 14, 50.75, 100, 'molido espresso', 'oscuro', '1kg'),
('GUASANMOKM1KG', 14, 50.75, 100, 'molido moka', 'medio', '1kg'),
('GUASANMOKO1KG', 14, 50.75, 100, 'molido moka', 'oscuro', '1kg'),
('GUASANGOTM1KG', 14, 50.75, 100, 'molido goteo', 'medio', '1kg'),
('GUASANGOTO1KG', 14, 50.75, 100, 'molido goteo', 'oscuro', '1kg'),
('GUASANFRAM1KG', 14, 50.75, 100, 'molido francesa', 'medio', '1kg'),
('GUASANFRAO1KG', 14, 50.75, 100, 'molido francesa', 'oscuro', '1kg'),
('GUASANGRAM2KG', 14, 94.25, 100, 'grano', 'medio', '2kg'),
('GUASANGRAO2KG', 14, 94.25, 100, 'grano', 'oscuro', '2kg'),
('GUASANESPM2KG', 14, 94.25, 100, 'molido espresso', 'medio', '2kg'),
('GUASANESPO2KG', 14, 94.25, 100, 'molido espresso', 'oscuro', '2kg'),
('GUASANMOKM2KG', 14, 94.25, 100, 'molido moka', 'medio', '2kg'),
('GUASANMOKO2KG', 14, 94.25, 100, 'molido moka', 'oscuro', '2kg'),
('GUASANGOTM2KG', 14, 94.25, 100, 'molido goteo', 'medio', '2kg'),
('GUASANGOTO2KG', 14, 94.25, 100, 'molido goteo', 'oscuro', '2kg'),
('GUASANFRAM2KG', 14, 94.25, 100, 'molido francesa', 'medio', '2kg'),
('GUASANFRAO2KG', 14, 94.25, 100, 'molido francesa', 'oscuro', '2kg'),

-- Honduras Los Lirios
('HONLOSGRAO250', 15, 13.90, 100, 'grano', 'oscuro', '250g'),
('HONLOSESPM250', 15, 13.90, 100, 'molido espresso', 'medio', '250g'),
('HONLOSESPO250', 15, 13.90, 100, 'molido espresso', 'oscuro', '250g'),
('HONLOSMOKM250', 15, 13.90, 100, 'molido moka', 'medio', '250g'),
('HONLOSMOKO250', 15, 13.90, 100, 'molido moka', 'oscuro', '250g'),
('HONLOSGOTM250', 15, 13.90, 100, 'molido goteo', 'medio', '250g'),
('HONLOSGOTO250', 15, 13.90, 100, 'molido goteo', 'oscuro', '250g'),
('HONLOSFRAM250', 15, 13.90, 100, 'molido francesa', 'medio', '250g'),
('HONLOSFRAO250', 15, 13.90, 100, 'molido francesa', 'oscuro', '250g'),
('HONLOSGRAM1KG', 15, 48.65, 100, 'grano', 'medio', '1kg'),
('HONLOSGRAO1KG', 15, 48.65, 100, 'grano', 'oscuro', '1kg'),
('HONLOSESPM1KG', 15, 48.65, 100, 'molido espresso', 'medio', '1kg'),
('HONLOSESPO1KG', 15, 48.65, 100, 'molido espresso', 'oscuro', '1kg'),
('HONLOSMOKM1KG', 15, 48.65, 100, 'molido moka', 'medio', '1kg'),
('HONLOSMOKO1KG', 15, 48.65, 100, 'molido moka', 'oscuro', '1kg'),
('HONLOSGOTM1KG', 15, 48.65, 100, 'molido goteo', 'medio', '1kg'),
('HONLOSGOTO1KG', 15, 48.65, 100, 'molido goteo', 'oscuro', '1kg'),
('HONLOSFRAM1KG', 15, 48.65, 100, 'molido francesa', 'medio', '1kg'),
('HONLOSFRAO1KG', 15, 48.65, 100, 'molido francesa', 'oscuro', '1kg'),
('HONLOSGRAM2KG', 15, 90.35, 100, 'grano', 'medio', '2kg'),
('HONLOSGRAO2KG', 15, 90.35, 100, 'grano', 'oscuro', '2kg'),
('HONLOSESPM2KG', 15, 90.35, 100, 'molido espresso', 'medio', '2kg'),
('HONLOSESPO2KG', 15, 90.35, 100, 'molido espresso', 'oscuro', '2kg'),
('HONLOSMOKM2KG', 15, 90.35, 100, 'molido moka', 'medio', '2kg'),
('HONLOSMOKO2KG', 15, 90.35, 100, 'molido moka', 'oscuro', '2kg'),
('HONLOSGOTM2KG', 15, 90.35, 100, 'molido goteo', 'medio', '2kg'),
('HONLOSGOTO2KG', 15, 90.35, 100, 'molido goteo', 'oscuro', '2kg'),
('HONLOSFRAM2KG', 15, 90.35, 100, 'molido francesa', 'medio', '2kg'),
('HONLOSFRAO2KG', 15, 90.35, 100, 'molido francesa', 'oscuro', '2kg'),

-- Kenia Gititu AA
('KENGITGRAO250', 16, 24.00, 100, 'grano', 'oscuro', '250g'),
('KENGITESPM250', 16, 24.00, 100, 'molido espresso', 'medio', '250g'),
('KENGITESPO250', 16, 24.00, 100, 'molido espresso', 'oscuro', '250g'),
('KENGITMOKM250', 16, 24.00, 100, 'molido moka', 'medio', '250g'),
('KENGITMOKO250', 16, 24.00, 100, 'molido moka', 'oscuro', '250g'),
('KENGITGOTM250', 16, 24.00, 100, 'molido goteo', 'medio', '250g'),
('KENGITGOTO250', 16, 24.00, 100, 'molido goteo', 'oscuro', '250g'),
('KENGITFRAM250', 16, 24.00, 100, 'molido francesa', 'medio', '250g'),
('KENGITFRAO250', 16, 24.00, 100, 'molido francesa', 'oscuro', '250g'),
('KENGITGRAM1KG', 16, 84.00, 100, 'grano', 'medio', '1kg'),
('KENGITGRAO1KG', 16, 84.00, 100, 'grano', 'oscuro', '1kg'),
('KENGITESPM1KG', 16, 84.00, 100, 'molido espresso', 'medio', '1kg'),
('KENGITESPO1KG', 16, 84.00, 100, 'molido espresso', 'oscuro', '1kg'),
('KENGITMOKM1KG', 16, 84.00, 100, 'molido moka', 'medio', '1kg'),
('KENGITMOKO1KG', 16, 84.00, 100, 'molido moka', 'oscuro', '1kg'),
('KENGITGOTM1KG', 16, 84.00, 100, 'molido goteo', 'medio', '1kg'),
('KENGITGOTO1KG', 16, 84.00, 100, 'molido goteo', 'oscuro', '1kg'),
('KENGITFRAM1KG', 16, 84.00, 100, 'molido francesa', 'medio', '1kg'),
('KENGITFRAO1KG', 16, 84.00, 100, 'molido francesa', 'oscuro', '1kg'),
('KENGITGRAM2KG', 16, 156.00, 100, 'grano', 'medio', '2kg'),
('KENGITGRAO2KG', 16, 156.00, 100, 'grano', 'oscuro', '2kg'),
('KENGITESPM2KG', 16, 156.00, 100, 'molido espresso', 'medio', '2kg'),
('KENGITESPO2KG', 16, 156.00, 100, 'molido espresso', 'oscuro', '2kg'),
('KENGITMOKM2KG', 16, 156.00, 100, 'molido moka', 'medio', '2kg'),
('KENGITMOKO2KG', 16, 156.00, 100, 'molido moka', 'oscuro', '2kg'),
('KENGITGOTM2KG', 16, 156.00, 100, 'molido goteo', 'medio', '2kg'),
('KENGITGOTO2KG', 16, 156.00, 100, 'molido goteo', 'oscuro', '2kg'),
('KENGITFRAM2KG', 16, 156.00, 100, 'molido francesa', 'medio', '2kg'),
('KENGITFRAO2KG', 16, 156.00, 100, 'molido francesa', 'oscuro', '2kg'),

-- Nicaragua Jinotega
('NICJINGRAO250', 17, 12.50, 100, 'grano', 'oscuro', '250g'),
('NICJINESPM250', 17, 12.50, 100, 'molido espresso', 'medio', '250g'),
('NICJINESPO250', 17, 12.50, 100, 'molido espresso', 'oscuro', '250g'),
('NICJINMOKM250', 17, 12.50, 100, 'molido moka', 'medio', '250g'),
('NICJINMOKO250', 17, 12.50, 100, 'molido moka', 'oscuro', '250g'),
('NICJINGOTM250', 17, 12.50, 100, 'molido goteo', 'medio', '250g'),
('NICJINGOTO250', 17, 12.50, 100, 'molido goteo', 'oscuro', '250g'),
('NICJINFRAM250', 17, 12.50, 100, 'molido francesa', 'medio', '250g'),
('NICJINFRAO250', 17, 12.50, 100, 'molido francesa', 'oscuro', '250g'),
('NICJINGRAM1KG', 17, 43.75, 100, 'grano', 'medio', '1kg'),
('NICJINGRAO1KG', 17, 43.75, 100, 'grano', 'oscuro', '1kg'),
('NICJINESPM1KG', 17, 43.75, 100, 'molido espresso', 'medio', '1kg'),
('NICJINESPO1KG', 17, 43.75, 100, 'molido espresso', 'oscuro', '1kg'),
('NICJINMOKM1KG', 17, 43.75, 100, 'molido moka', 'medio', '1kg'),
('NICJINMOKO1KG', 17, 43.75, 100, 'molido moka', 'oscuro', '1kg'),
('NICJINGOTM1KG', 17, 43.75, 100, 'molido goteo', 'medio', '1kg'),
('NICJINGOTO1KG', 17, 43.75, 100, 'molido goteo', 'oscuro', '1kg'),
('NICJINFRAM1KG', 17, 43.75, 100, 'molido francesa', 'medio', '1kg'),
('NICJINFRAO1KG', 17, 43.75, 100, 'molido francesa', 'oscuro', '1kg'),
('NICJINGRAM2KG', 17, 81.25, 100, 'grano', 'medio', '2kg'),
('NICJINGRAO2KG', 17, 81.25, 100, 'grano', 'oscuro', '2kg'),
('NICJINESPM2KG', 17, 81.25, 100, 'molido espresso', 'medio', '2kg'),
('NICJINESPO2KG', 17, 81.25, 100, 'molido espresso', 'oscuro', '2kg'),
('NICJINMOKM2KG', 17, 81.25, 100, 'molido moka', 'medio', '2kg'),
('NICJINMOKO2KG', 17, 81.25, 100, 'molido moka', 'oscuro', '2kg'),
('NICJINGOTM2KG', 17, 81.25, 100, 'molido goteo', 'medio', '2kg'),
('NICJINGOTO2KG', 17, 81.25, 100, 'molido goteo', 'oscuro', '2kg'),
('NICJINFRAM2KG', 17, 81.25, 100, 'molido francesa', 'medio', '2kg'),
('NICJINFRAO2KG', 17, 81.25, 100, 'molido francesa', 'oscuro', '2kg'),

-- Perú Gesha Los Quispe
('PERGESGRAO250', 18, 28.00, 100, 'grano', 'oscuro', '250g'),
('PERGESESPM250', 18, 28.00, 100, 'molido espresso', 'medio', '250g'),
('PERGESESPO250', 18, 28.00, 100, 'molido espresso', 'oscuro', '250g'),
('PERGESMOKM250', 18, 28.00, 100, 'molido moka', 'medio', '250g'),
('PERGESMOKO250', 18, 28.00, 100, 'molido moka', 'oscuro', '250g'),
('PERGESGOTM250', 18, 28.00, 100, 'molido goteo', 'medio', '250g'),
('PERGESGOTO250', 18, 28.00, 100, 'molido goteo', 'oscuro', '250g'),
('PERGESFRAM250', 18, 28.00, 100, 'molido francesa', 'medio', '250g'),
('PERGESFRAO250', 18, 28.00, 100, 'molido francesa', 'oscuro', '250g'),
('PERGESGRAM1KG', 18, 98.00, 100, 'grano', 'medio', '1kg'),
('PERGESGRAO1KG', 18, 98.00, 100, 'grano', 'oscuro', '1kg'),
('PERGESESPM1KG', 18, 98.00, 100, 'molido espresso', 'medio', '1kg'),
('PERGESESPO1KG', 18, 98.00, 100, 'molido espresso', 'oscuro', '1kg'),
('PERGESMOKM1KG', 18, 98.00, 100, 'molido moka', 'medio', '1kg'),
('PERGESMOKO1KG', 18, 98.00, 100, 'molido moka', 'oscuro', '1kg'),
('PERGESGOTM1KG', 18, 98.00, 100, 'molido goteo', 'medio', '1kg'),
('PERGESGOTO1KG', 18, 98.00, 100, 'molido goteo', 'oscuro', '1kg'),
('PERGESFRAM1KG', 18, 98.00, 100, 'molido francesa', 'medio', '1kg'),
('PERGESFRAO1KG', 18, 98.00, 100, 'molido francesa', 'oscuro', '1kg'),
('PERGESGRAM2KG', 18, 182.00, 100, 'grano', 'medio', '2kg'),
('PERGESGRAO2KG', 18, 182.00, 100, 'grano', 'oscuro', '2kg'),
('PERGESESPM2KG', 18, 182.00, 100, 'molido espresso', 'medio', '2kg'),
('PERGESESPO2KG', 18, 182.00, 100, 'molido espresso', 'oscuro', '2kg'),
('PERGESMOKM2KG', 18, 182.00, 100, 'molido moka', 'medio', '2kg'),
('PERGESMOKO2KG', 18, 182.00, 100, 'molido moka', 'oscuro', '2kg'),
('PERGESGOTM2KG', 18, 182.00, 100, 'molido goteo', 'medio', '2kg'),
('PERGESGOTO2KG', 18, 182.00, 100, 'molido goteo', 'oscuro', '2kg'),
('PERGESFRAM2KG', 18, 182.00, 100, 'molido francesa', 'medio', '2kg'),
('PERGESFRAO2KG', 18, 182.00, 100, 'molido francesa', 'oscuro', '2kg');




