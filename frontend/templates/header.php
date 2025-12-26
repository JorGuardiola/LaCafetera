<?php
// frontend/templates/header.php

/* ======================================================
   1. SESIÓN Y CONFIGURACIÓN
====================================================== */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$totalHeader = 0;
if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $cantidad) {
        $totalHeader += (int)$cantidad;
    }
}


// Cargar conexión (para BASE_URL)
require_once __DIR__ . '/../../db/connection.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Cafetera</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>

<header class="header">

    <!-- BOTÓN MENÚ MOBILE -->
    <button class="menu-toggle" aria-label="Abrir menú">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- LOGO -->
    <div class="img-logo">
        <a href="<?= BASE_URL ?>/frontend/index.php">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="La Cafetera">
        </a>
    </div>

    <!-- NAVEGACIÓN -->
    <nav class="nav-bar">
        <ul>
            <li><a href="<?= BASE_URL ?>/frontend/nosotros.php">Acerca de nosotros</a></li>
            <li><a href="<?= BASE_URL ?>/frontend/products.php">Productos</a></li>
            <li><a href="<?= BASE_URL ?>/frontend/contacto.php">Contacto</a></li>
        </ul>
    </nav>

    <!-- ICONOS -->
    <div class="icons-bar">

        <!-- Buscar -->
        <button class="icon-header" aria-label="Buscar">
            <img src="<?= BASE_URL ?>/assets/img/buscar.png" alt="Buscar">
        </button>

        <!-- USUARIO -->
        <div class="user-menu-wrapper">

            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="icon-header user-toggle" aria-label="Usuario">
                    <i class="fa-solid fa-user"></i>
                </button>

                <div class="user-dropdown">
                    <a href="<?= BASE_URL ?>/frontend/profile.php?tab=perfil">Mi perfil</a>
                    <a href="<?= BASE_URL ?>/frontend/profile.php?tab=direcciones">Mis direcciones</a>
                    <a href="<?= BASE_URL ?>/frontend/profile.php?tab=pedidos">Mis pedidos</a>


                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/frontend/admin.php" class="admin-link">
                            Panel de administración
                        </a>
                    <?php endif; ?>

                    <hr>
                    <a href="<?= BASE_URL ?>/frontend/logout.php">Cerrar sesión</a>
                </div>

            <?php else: ?>
                <button class="icon-header"
                        onclick="window.location.href='<?= BASE_URL ?>/frontend/login.php'"
                        title="Iniciar sesión">
                    <img src="<?= BASE_URL ?>/assets/img/login.png" alt="Login">
                </button>
            <?php endif; ?>

        </div>

        <!-- Carrito -->
        <button class="icon-header"
                onclick="window.location.href='<?= BASE_URL ?>/frontend/cart.php'"
                aria-label="Carrito">
            <img src="<?= BASE_URL ?>/assets/img/carrito.png" alt="Carrito">
            <div class="cart-wrapper">
            <span id="headerCartCount" class="cart-badge-number"><?= $totalHeader ?></span>
            </div>
        </button>

        






    </div>

</header>

<!-- JS MENÚ MOBILE -->
<script>
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav-bar');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('active');
        });
    }
</script>

<!-- JS DROPDOWN USUARIO -->
<script>
    const userToggle = document.querySelector('.user-toggle');
    const userDropdown = document.querySelector('.user-dropdown');

    if (userToggle && userDropdown) {
        userToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', () => {
            userDropdown.classList.remove('active');
        });
    }
</script>
