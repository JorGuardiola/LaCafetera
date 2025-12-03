-- ##################################################
-- # SCRIPT SQL 3 diciembre
-- ##################################################
-- ----------------------------------------------------
-- 0. CREACIÓN Y SELECCIÓN DE LA BASE DE DATOS 
-- ----------------------------------------------------

-- Crea la base de datos si no existe
CREATE DATABASE IF NOT EXISTS cafetera CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Selecciona la base de datos para ejecutar las siguientes instrucciones
USE cafetera;

-- ----------------------------------------------------
-- 1. DROP DE TABLAS (OPCIONAL: Para empezar desde cero)
-- ----------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS orden_items;
DROP TABLE IF EXISTS ordenes;
DROP TABLE IF EXISTS carrito_items;
DROP TABLE IF EXISTS carritos;
DROP TABLE IF EXISTS direcciones;
DROP TABLE IF EXISTS usuarios;
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
    
    FOREIGN KEY (producto_id) REFERENCES productos(id),
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
    
    FOREIGN KEY (id_carrito) REFERENCES carritos(id_carrito),
    FOREIGN KEY (id_variante_sku) REFERENCES producto_variantes(sku),
    UNIQUE KEY uk_carrito_variante (id_carrito, id_variante_sku)
);

-- TABLA ORDENES 
CREATE TABLE ordenes (
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

-- TABLA ORDEN_ITEMS 
CREATE TABLE orden_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_variante_sku VARCHAR(50) NOT NULL,
    precio_unitario DECIMAL(10,2),
    cantidad INT,
    
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden),
    FOREIGN KEY (id_variante_sku) REFERENCES producto_variantes(sku)
);

-- TABLA PAGOS 
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    metodo VARCHAR(50) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    estado VARCHAR(50),
    fecha_pago DATETIME,
    referencia_transaccion VARCHAR(255) UNIQUE,
    
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden)
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

-- Ejemplo de inserción de una variante (250g, Grano) para el producto con ID=1
INSERT INTO producto_variantes (sku, producto_id, stock, precio, molienda, tueste, envase)
VALUES ('SARUT_250_G', 1, 100, 12.90, 'grano', 'medio', '250g');

-- Ejemplo de inserción de una variante (1kg, Molido Espresso) para el producto con ID=4 (Colombia Agualinda)
-- El tueste se ajusta a 'medio'
INSERT INTO producto_variantes (sku, producto_id, stock, precio, molienda, tueste, envase)
VALUES ('AGUAL_1KG_E', 4, 50, 49.90, 'molido espresso', 'medio', '1kg');

-- Ejemplo de inserción de una variante (250g, Molido V60) para el producto con ID=11 (Ethiopia Aramo)
-- El tueste se ajusta a 'medio'
INSERT INTO producto_variantes (sku, producto_id, stock, precio, molienda, tueste, envase)
VALUES ('ARAMO_250_V', 11, 80, 18.50, 'molido goteo', 'medio', '250g');





