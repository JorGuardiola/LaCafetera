<?php
// index.php (Archivo raíz para redirigir a frontend/index.php)

// carga la conexión para calcular BASE_URL automáticamente
require_once __DIR__ . '/db/connection.php';

// redirige a frontend/index.php
header('Location: ' . BASE_URL . '/frontend/index.php');
exit;
?>