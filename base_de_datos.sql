USE sistema_inventario;

-- 1. Tabla para el módulo de Login y Seguridad
CREATE TABLE usuarios (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre_completo VARCHAR(100) NOT NULL,
usuario VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
rol VARCHAR(20) NOT NULL
);

-- 2. NUEVA TABLA RAÍZ: Categorías del sistema
CREATE TABLE categorias (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre_categoria VARCHAR(50) NOT NULL UNIQUE);

CREATE TABLE productos (
id INT AUTO_INCREMENT PRIMARY KEY,
nombre_producto VARCHAR(100) NOT NULL,
categoria_id INT NOT NULL,
stock INT NOT NULL,
precio DECIMAL(10, 2) NOT NULL,
FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- 4. Inserción de los catálogos base (deben insertarse primero)
INSERT INTO categorias (nombre_categoria) VALUES
('Computadoras'),
('Accesorios'),
('Oficina');

-- 5. Inserción de Productos (Observa cómo ahora vinculamos usando números
enteros)
INSERT INTO productos (nombre_producto, categoria_id, stock, precio) VALUES
('Laptop Dell Inspiron 15', 1, 15, 720.00),
('Mouse Inalámbrico Logitech', 2, 25, 12.00);

SELECT * FROM productos WHERE id = 2;

UPDATE productos SET precio = 12.00 WHERE id = 2;

DELETE FROM productos WHERE id = 3;

SELECT p.id, p.nombre_producto, c.nombre_categoria, p.stock, p.precio FROM productoss p INNER JOIN categorias c ON p.categoria_id = c.id;

SELECT p.id, p.nombre_producto, c.nombre_categoria, p.stock, p.precio FROM productoss p
INNER JOIN categorias c ON p.categoria_id = c.id WHERE c.nombre_categoria = 'Accesorios';


SELECT COUNT(id) AS total_productos_catalogo FROM productos;

SELECT SUM(precio * stock) AS valor_monetario_inventario FROM productos;

SELECT MAX(precio) AS producto_mas_caro FROM productoss;

SELECT c.nombre_categoria, SUM(p.stock) AS existencias_totales
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
SELECT c.nombre_categoria, SUM(p.stock) AS existencias_totales
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
GROUP BY c.nombre_categoria;

-- Creación de la tabla proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(100) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT
);

-- Datos de prueba
INSERT INTO proveedores (nombre_empresa, contacto, telefono, direccion) VALUES
('Tech Data El Salvador', 'Juan Pérez', '2255-8899', 'San Salvador, Col. Escalón'),
('Distribuidora de Papel', 'María Gómez', '2666-4433', 'San Miguel, Centro');