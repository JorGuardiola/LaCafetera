<?php
// db/connection.php (VERSIÓN LOCAL)

// 1. Configuración de Base de Datos LOCAL (XAMPP)
$host = 'localhost';
$db   = 'cafeteria_db';  // <--- que este nombre coincida con phpMyAdmin local
$user = 'root';
$pass = '';              // En XAMPP por defecto está vacía
$charset = 'utf8mb4';

// 2. Definición automática de BASE_URL (Para que carguen CSS e imágenes)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$server = $_SERVER['HTTP_HOST'];

// Detecta la carpeta del proyecto eliminando subcarpetas conocidas
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_path = str_replace(['/frontend', '/backend', '/db'], '', $path);

define('BASE_URL', $protocol . "://" . $server . $base_path);

// 3. Conexión PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Error Conexión Local: ' . $e->getMessage());
}
?>