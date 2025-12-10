<?php
// frontend/templates/header.php

// 1. Garantizar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Garantizar que connection.php esté cargado para usar BASE_URL
// Usamos __DIR__ para salir de "templates" (..), salir de "frontend" (..) y entrar a "db"
require_once __DIR__ . '/../../db/connection.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>La Cafetera</title>

  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <!-- Base URL para recursos -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header class="header">
      <!-- LOGO -->
      <div class="img-logo">
        <a href="<?= BASE_URL ?>/frontend/index.php">
          <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Logo La Cafetera">
        </a>
      </div>

      <!-- NAV -->
      <nav class="nav-bar">
        <ul>
          <li><a href="<?= BASE_URL ?>/frontend/nosotros.php">Acerca de nosotros</a></li>
          <li><a href="<?= BASE_URL ?>/frontend/products.php">Productos</a></li>
          <li><a href="<?= BASE_URL ?>/frontend/contacto.php">Contacto</a></li>
        </ul>
      </nav>

    <div class="icons-bar">
        
        <button class="icon-header">
          <img src="<?= BASE_URL ?>/assets/img/buscar.png" alt="Buscar">
        </button>

        <?php if (isset($_SESSION['user_id'])): ?>
            
            <button class="icon-header" onclick="window.location.href='<?= BASE_URL ?>/frontend/profile.php'" title="Mi Perfil">
                <i class="fa-solid fa-user" style="color: #1A1A1A; font-size: 1.8rem;"></i>
            </button>

        <?php else: ?>
            
            <button class="icon-header" onclick="window.location.href='<?= BASE_URL ?>/frontend/login.php'" title="Iniciar Sesión">
                <img src="<?= BASE_URL ?>/assets/img/login.png" alt="Login">
            </button>

        <?php endif; ?>

        <button class="icon-header" onclick="window.location.href='<?= BASE_URL ?>/frontend/cart.php'">
          <img src="<?= BASE_URL ?>/assets/img/carrito.png" alt="Carrito">
        </button>
        
      </div>
</header>