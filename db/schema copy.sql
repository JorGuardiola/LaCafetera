-- Crear base de datos
CREATE DATABASE IF NOT EXISTS proyecto;
USE proyecto;

-- Crear tablas principales

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente','admin') DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cafe VARCHAR(150) NOT NULL,
    pais_origen VARCHAR(100),
    region VARCHAR(150),
    finca VARCHAR(150),
    altitud_msnm INT,
    variedad VARCHAR(150),
    proceso VARCHAR(150),
    perfil_tueste VARCHAR(100),
    puntuacion_sca DECIMAL(4,1),
    notas_sabor TEXT,
    metodos_recomendados TEXT,
    presentacion VARCHAR(255),
    precio_eur DECIMAL(10,2) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    disponible TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente','pagado','preparando','enviado','entregado','cancelado') DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS pedido_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Tabla intermedia para múltiples categorías por producto
CREATE TABLE IF NOT EXISTS producto_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    categoria_id INT NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Insertar categorías por país
INSERT INTO categorias (nombre, descripcion) VALUES
('Brasil','Cafés originarios de Brasil'),
('Burundi','Cafés originarios de Burundi'),
('Colombia','Cafés originarios de Colombia'),
('Etiopía','Cafés originarios de Etiopía'),
('Guatemala','Cafés originarios de Guatemala'),
('Honduras','Cafés originarios de Honduras'),
('Kenia','Cafés originarios de Kenia'),
('Nicaragua','Cafés originarios de Nicaragua'),
('Perú','Cafés originarios de Perú');

-- Categorías por proceso
INSERT INTO categorias (nombre, descripcion) VALUES
('Natural','Café procesado mediante secado natural'),
('Lavado','Café procesado mediante lavado'),
('Honey','Café procesado Honey'),
('Hydro Honey','Café procesado Hydro Honey'),
('Natural Anaeróbico','Café procesado Natural Anaeróbico');

-- Categorías por tueste
INSERT INTO categorias (nombre, descripcion) VALUES
('Ligero','Tueste Ligero'),
('Medio','Tueste Medio'),
('Ligero-Medio','Tueste Ligero-Medio'),
('Medio-Ligero','Tueste Medio-Ligero');

-- Insertar todos los productos (imagen placeholder)
INSERT INTO productos (nombre_cafe, pais_origen, region, finca, altitud_msnm, variedad, proceso, perfil_tueste, puntuacion_sca, notas_sabor, metodos_recomendados, presentacion, precio_eur, descripcion, imagen) VALUES
('Brasil Sarutaia','Brasil','Minas Gerais','Fazenda Sarutaia',1100,'Yellow Bourbon','Natural','Medio',84,'Chocolate, nuez, caramelo','Espresso, moka','Tostado en grano y molido bajo pedido',12.9,
 'Café brasileño suave y dulce, con cuerpo redondo y acidez baja. Ideal para quienes buscan un perfil clásico y estable.','placeholder.jpg'),

('Brasil Vila Boa','Brasil','Cerrado Mineiro','Vila Boa',1200,'Catuai','Honey','Medio',85,'Cacao, miel, almendra','Espresso, V60','Tostado en grano y molido bajo pedido',13.5,
 'Café dulce y equilibrado, con textura melosa y un postgusto prolongado gracias al proceso Honey.','placeholder.jpg'),

('Burundi Kawavumera','Burundi','Kayanza','Kawavumera Cooperative',1800,'Red Bourbon','Lavado','Ligero-Medio',87,'Frutos rojos, té negro, cítrico','V60, Kalita','Tostado en grano y molido bajo pedido',15.9,
 'Café vibrante, complejo y brillante con notas intensas a frutos rojos y un final limpio.','placeholder.jpg'),

('Colombia Agualinda','Colombia','Antioquia','Finca Agualinda',1900,'Caturra','Lavado','Ligero',86,'Panela, mandarina, floral','V60, Aeropress','Tostado en grano y molido bajo pedido',14.9,
 'Café fresco y floral con acidez cítrica balanceada y dulzor alto, muy típico del perfil colombiano.','placeholder.jpg'),

('Colombia Bourbon Sidra','Colombia','Nariño','El Silencio',2050,'Bourbon Sidra','Natural','Ligero',89,'Fresa, vino tinto, jazmín','V60, Chemex','Tostado en grano y molido bajo pedido',22,
 'Café complejo y aromático con notas florales intensas y un carácter casi vinoso.','placeholder.jpg'),

('Colombia Ceiba Honey','Colombia','Huila','La Ceiba',1750,'Caturra','Honey','Ligero-Medio',87,'Miel, melocotón, cacao','V60, Aeropress','Tostado en grano y molido bajo pedido',15.5,
 'Café dulce y jugoso con textura sedosa y excelente equilibrio gracias al proceso Honey.','placeholder.jpg'),

('Colombia Guayava','Colombia','Tolima','El Vergel',1500,'Varietal Blend','Natural','Ligero',88,'Guayaba, mora, flor blanca','V60, Kalita','Tostado en grano y molido bajo pedido',16.5,
 'Café frutal intenso con notas tropicales marcadas y una acidez brillante.','placeholder.jpg'),

('Colombia Hydro Honey','Colombia','Huila','Las Flores',1750,'Bourbon Rosado','Hydro Honey','Ligero-Medio',88,'Uva, miel, flor de cacao','V60, Aeropress','Tostado en grano y molido bajo pedido',17.9,
 'Café complejo con proceso Hydro Honey, dulce y limpio con notas a uva y miel.','placeholder.jpg'),

('Colombia Las Garzas Natural','Colombia','Cauca','Las Garzas',1850,'Castillo','Natural','Ligero',86,'Frutos rojos, cacao, especias','V60, Chemex','Tostado en grano y molido bajo pedido',15.9,
 'Café afrutado y especiado, con dulzor intenso y gran profundidad.','placeholder.jpg'),

('Colombia Mango Washed','Colombia','Antioquia','El Recreo',1600,'Castillo','Lavado Fermentado','Ligero-Medio',87,'Mango, cítrico, miel','V60, Aeropress','Tostado en grano y molido bajo pedido',16.9,
 'Café tropical con notas a mango y miel, brillante y expresivo.','placeholder.jpg'),

('Ethiopia Aramo Natural','Etiopía','Yirgacheffe','Aramo',2000,'Heirloom','Natural','Ligero',88,'Arándanos, jazmín, miel','V60, Chemex','Tostado en grano y molido bajo pedido',18.5,
 'Café floral y afrutado, dulce y aromático, ideal para filtrados delicados.','placeholder.jpg'),

('Ethiopia Kochere Beloya Oro','Etiopía','Kochere','Beloya',1950,'Heirloom','Lavado','Ligero',87,'Limón, melocotón, té blanco','V60, Kalita','Tostado en grano y molido bajo pedido',17.9,
 'Café limpio, delicado y floral con acidez refrescante y final suave.','placeholder.jpg'),

('Ethiopia Yirga Natural Anaerobico','Etiopía','Yirgacheffe','Worka',2050,'Heirloom','Natural Anaeróbico','Ligero',89,'Fresa fermentada, flor, vino','V60, Chemex','Tostado en grano y molido bajo pedido',21,
 'Café explosivo y aromático con notas vinosas gracias al proceso anaeróbico.','placeholder.jpg'),

('Etiopía Sidamo Shantawene','Etiopía','Sidamo','Shantawene',1900,'Heirloom','Lavado','Ligero',87,'Bergamota, miel, flor blanca','V60, Kalita','Tostado en grano y molido bajo pedido',16.9,
 'Café elegante y floral con acidez refinada y dulzor suave.','placeholder.jpg'),

('Guatemala San Sebastián','Guatemala','Antigua','San Sebastián',1650,'Bourbon','Lavado','Medio',85,'Chocolate, avellana, cítrico','Espresso, Chemex','Tostado en grano y molido bajo pedido',14.5,
 'Café equilibrado y suave con notas clásicas a chocolate y cítrico.','placeholder.jpg'),

('Honduras Los Lirios','Honduras','Marcala','Los Lirios',1600,'Catuai','Lavado','Medio-Ligero',84,'Caramelo, nuez, manzana','V60, Moka','Tostado en grano y molido bajo pedido',13.9,
 'Café suave con dulzor a caramelo y acidez frutal ligera.','placeholder.jpg'),

('Kenia Gititu AA','Kenia','Kiambu','Gititu',1900,'SL28, SL34','Lavado','Ligero',88,'Grosella negra, pomelo, floral','V60, Chemex','Tostado en grano y molido bajo pedido',19,
 'Café keniano brillante y jugoso con notas intensas y acidez compleja.','placeholder.jpg'),

('Nicaragua Jinotega','Nicaragua','Jinotega','Buenos Aires',1400,'Caturra','Lavado','Medio',84,'Chocolate, toffee, cítrico','Espresso, V60','Tostado en grano y molido bajo pedido',12.9,
 'Café suave y cremoso con notas cálidas a toffee y cítrico.','placeholder.jpg'),

('Perú Gesha Los quispe','Perú','Cusco','Los Quispe',1900,'Gesha','Lavado','Ligero',89,'Bergamota, jazmín, miel','V60, Chemex','Tostado en grano y molido bajo pedido',28,
 'Café floral y elegante con acidez brillante y dulzor delicado.','placeholder.jpg');

-- Asignar categorías múltiples por producto (país, proceso, tueste)
-- Esto se hace insertando en la tabla intermedia

-- Categorías por país
INSERT INTO producto_categorias (producto_id, categoria_id)
SELECT p.id, c.id
FROM productos p
JOIN categorias c ON c.nombre = p.pais_origen;

-- Categorías por proceso
INSERT INTO producto_categorias (producto_id, categoria_id)
SELECT p.id, c.id
FROM productos p
JOIN categorias c ON c.nombre = p.proceso;

-- Categorías por tueste
INSERT INTO producto_categorias (producto_id, categoria_id)
SELECT p.id, c.id
FROM productos p
JOIN categorias c ON c.nombre = p.perfil_tueste;

-- Modificación de tu tabla productos
ALTER TABLE productos
CHANGE COLUMN precio_eur precio_base_250g DECIMAL(10,2) NOT NULL;

-- Crear tabla VARIANTES
CREATE TABLE IF NOT EXISTS variantes (
    sku VARCHAR(50) PRIMARY KEY,
    producto_id INT NOT NULL,
    envase ENUM('250g','1kg','2kg') NOT NULL,
    formato ENUM('grano','molido') NOT NULL,
    tueste VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Modificar carrito para usar VARIANTES (no productos)
ALTER TABLE carrito
DROP FOREIGN KEY carrito_ibfk_1;

ALTER TABLE carrito
CHANGE COLUMN producto_id sku VARCHAR(50) NOT NULL;

ALTER TABLE carrito
ADD FOREIGN KEY (sku) REFERENCES variantes(sku);

-- Modificar pedido_detalles igual (de producto a variante) 
ALTER TABLE pedido_detalles
DROP FOREIGN KEY pedido_detalles_ibfk_2;

ALTER TABLE pedido_detalles
CHANGE COLUMN producto_id sku VARCHAR(50) NOT NULL;

ALTER TABLE pedido_detalles
ADD FOREIGN KEY (sku) REFERENCES variantes(sku);

-- Insertar variantes automáticamente para todos tus productos
INSERT INTO variantes (sku, producto_id, envase, formato, tueste, precio, stock)
SELECT 
    CONCAT('P', id, '-250'),
    id,
    '250g',
    'grano',
    perfil_tueste,
    precio_base_250g,
    50
FROM productos;

INSERT INTO variantes (sku, producto_id, envase, formato, tueste, precio, stock)
SELECT 
    CONCAT('P', id, '-1KG'),
    id,
    '1kg',
    'grano',
    perfil_tueste,
    precio_base_250g * 4,
    50
FROM productos;

INSERT INTO variantes (sku, producto_id, envase, formato, tueste, precio, stock)
SELECT 
    CONCAT('P', id, '-2KG'),
    id,
    '2kg',
    'grano',
    perfil_tueste,
    precio_base_250g * 8,
    50
FROM productos;
