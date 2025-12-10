<?php
// backend/auth_admin.php
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontend/login.php');
    exit;
}

// 2. Conectar y verificar ROL
require_once __DIR__ . '/../db/connection.php';

$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['rol'] !== 'admin') {
    die("<h1>Acceso Denegado</h1><p>No tienes permisos de administrador. <a href='../frontend/index.php'>Volver</a></p>");
}
?>