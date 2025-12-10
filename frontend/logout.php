<?php
// frontend/logout.php

// 1. Iniciamos sesión para poder destruirla
session_start();

// 2. Incluimos la conexión para tener disponible BASE_URL
require_once __DIR__ . '/../db/connection.php';

// 3. Destruimos la sesión
session_destroy();

// 4. Redirigimos usando la constante correctamente
header('Location: ' . BASE_URL . '/frontend/index.php');
exit;
?>