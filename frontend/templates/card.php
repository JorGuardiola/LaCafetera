<?php
// frontend/templates/card.php
// Este template recibe un array $p con:
// id, nombre_cafe, presentacion, imagen, puntuacion_sca, precio
?>

<div class="product-card">
    <div class="product-content">
            <button class="fav-btn">
            <img src="<?= BASE_URL ?>/assets/img/icon-heart.png" alt="Fav">
            </button>

            <div class="product-image">
                <a href="product.php?id=<?= (int)$p['id'] ?>">
                    <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= htmlspecialchars($p['imagen']) ?>"
                        alt="<?= htmlspecialchars($p['nombre_cafe']) ?>">
                </a>
            </div>

            <div class="product-rating">★ ★ ★ ★ ☆</div>

            <h3 class="product-name">
                <a href="product.php?id=<?= (int)$p['id'] ?>" style="text-decoration: none; color: inherit;">
                    <?= htmlspecialchars($p['nombre_cafe']) ?>
                </a>
            </h3>
            <p class="product-weight"><?= htmlspecialchars($p['presentacion']) ?></p>

            
    </div>

    <div class="product-price">
            <p class="product-price">
                <?= isset($p['precio']) ? number_format($p['precio'], 2) : '0.00' ?> €
            </p>
    </div>

    <a href="<?= BASE_URL ?>/frontend/product.php?id=<?= (int)$p['id'] ?>" class="product-action">
        <button class="boton1-btn">Ver detalles</button>
    </a>
</div>
