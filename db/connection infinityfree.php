<?php
// db/connection_infinityfree.php (VERSIÓN PRODUCCIÓN)

// 1. Configuración de Base de Datos INFINITYFREE
$host = 'sql210.infinityfree.com';
$db   = 'if0_40576183_cafeteria_db';
$user = 'if0_40576183';
$pass = 'j3j6nendJd';
$charset = 'utf8mb4';

// 2. Definición automática de BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$server = $_SERVER['HTTP_HOST'];
// Detecta la carpeta del proyecto
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_path = str_replace(['/frontend', '/backend', '/db'], '', $path);

define('BASE_URL', $protocol . "://" . $server . $base_path);

// 3. Conexión PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('.');
}
?>