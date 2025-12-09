<?php
// frontend/templates/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = '/LaCafetera';

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
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<header class="header">
      <!-- LOGO -->
      <div class="img-logo">
        <a href="index.php"> 
          <img src="../assets/img/logo.png" alt="Logo La Cafetera">
        </a>
      </div>

      <!-- NAV -->
      <nav class="nav-bar">
        <ul>
          <li><a href="products.php">Productos</a></li>
          <li><a href="#">Nosotros</a></li>
          <li><a href="#">Recetas</a></li>
          <li><a href="#">Contacto</a></li>
        </ul>
      </nav>

      <!-- ICONOS -->
      <div class="icons-bar">
        <button class="icon-header">
          <img src="../assets/img/buscar.png" alt="Buscar">
        </button>
        <button class="icon-header" onclick="window.location.href='login.php'">
          <img src="../assets/img/login.png" alt="Login">
        </button>
        <button class="icon-header" onclick="window.location.href='cart.php'">
          <img src="../assets/img/carrito.png" alt="Carrito">
        </button>
      </div>
</header>  
