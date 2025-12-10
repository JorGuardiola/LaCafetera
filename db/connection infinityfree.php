<?php
// db/connection.php

define('BASE_URL', '');    

$host = 'sql210.infinityfree.com';  // Host de InfinityFree
$db = 'if0_40576183_cafeteria_db';  // Tu base de datos real
$user = 'if0_40576183';             // Usuario MySQL
$pass = 'j3j6nendJd';               // Tu contraseña MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());

}
