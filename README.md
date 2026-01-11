# ☕ La Cafetera - eCommerce de Café de Especialidad

Proyecto de eCommerce completo desarrollado en PHP nativo (Vanilla), MySQL, HTML5 y CSS3.
Incluye gestión de usuarios, catálogo de productos con variantes, carrito de compras persistente, simulador de pasarela de pago y panel de administración.

## 🚀 Funcionalidades Principales

* **Cliente:**
    * Registro y Login de usuarios.
    * Catálogo con filtros (origen, tueste, molienda).
    * Carrito de compras (guardado en sesión).
    * Checkout con simulación de pago (Tarjeta, Bizum, PayPal, GPay).
    * Perfil de usuario con historial de pedidos.
    * Buscador de productos en tiempo real (AJAX).
* **Administración:**
    * Gestión de Productos (CRUD).
    * Gestión de Usuarios.
    * Visualización de Pedidos.

## 🛠️ Requisitos del Sistema

* Servidor Web (Apache/Nginx).
* PHP 7.4 o superior.
* MySQL 5.7 o MariaDB.
* (Recomendado: XAMPP, MAMP o Laragon para entorno local).

## 📦 Instalación Paso a Paso

### 1. Preparar la Base de Datos
1.  Abre tu gestor de base de datos (ej. phpMyAdmin).
2.  Crea una base de datos vacía llamada `cafeteria_db` (opcional, el script la crea si tienes permisos).
3.  Importa el archivo `/db/schema.sql`.
    * Este script creará todas las tablas e insertará los productos de ejemplo y un usuario administrador.

### 2. Configurar la Conexión
1.  Abre el archivo `/db/connection.php`.
2.  Verifica que las credenciales coincidan con tu servidor local:
    ```php
    $host = 'localhost';
    $db   = 'cafeteria_db';
    $user = 'root'; // Usuario por defecto en XAMPP
    $pass = '';     // Contraseña vacía por defecto en XAMPP
    ```

### 3. Ejecutar el Proyecto
1.  Mueve la carpeta del proyecto a tu directorio público (`htdocs` en XAMPP o `www`).
2.  Accede desde el navegador: `http://localhost/lacafetera/frontend/index.php`

## 🔑 Usuarios de Prueba

**Administrador:**
* Email: `admin@cafetera.com`
* Contraseña: *Para acceder, regístrate como usuario nuevo y cambia tu rol manualmente a 'admin' en la tabla `usuarios` de la base de datos, o usa el hash de prueba.*
* *(Nota: El script SQL incluye un admin por defecto, pero si no conoces su contraseña, crea uno nuevo).*

**Cliente:**
* Puedes registrar un nuevo cliente desde el formulario de registro.

## 📂 Estructura del Proyecto

* `/assets`: Estilos CSS, Imágenes, JS.
* `/backend`: Lógica de administración y autenticación interna.
* `/db`: Conexión y esquemas SQL.
* `/frontend`: Páginas públicas (Tienda, Carrito, Perfil).
    * `/templates`: Componentes reutilizables (Header, Footer, Cards).

## ⚠️ Notas Técnicas

* **Pagos:** La pasarela de pago es una simulación. No se realizan cargos reales.
* **URLs:** El proyecto utiliza rutas absolutas dinámicas definidas en `connection.php`.
