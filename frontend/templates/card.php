<?php
// Este template recibe un array $p con:
// id, nombre_cafe, presentacion, imagen, puntuacion_sca, precio
?>

<div class="product-card">

    <button class="fav-btn">
        <img src="/lacafetera/assets/img/icon-heart.png" alt="Fav">
    </button>

    <div class="product-image">
        <img src="/lacafetera/assets/img/imgsproducts/<?= htmlspecialchars($p['imagen']) ?>"
             alt="<?= htmlspecialchars($p['nombre_cafe']) ?>">
    </div>

    <div class="product-rating">★ ★ ★ ★ ☆</div>

    <h3 class="product-name"><?= htmlspecialchars($p['nombre_cafe']) ?></h3>

    <p class="product-weight"><?= htmlspecialchars($p['presentacion']) ?></p>

    <p class="product-price">
        <?= isset($p['precio']) ? number_format($p['precio'], 2) : '0.00' ?> €
    </p>

    <button class="product-btn">Ver detalles</button>

</div>