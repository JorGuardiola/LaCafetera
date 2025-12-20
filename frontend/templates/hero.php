<div class="hero-container <?= $bgClass ?>">
    <div class="hero-inner">

        <!-- IZQUIERDA: textos dinámicos -->
        <div class="hero-left">
            <h1><?= $heroTitle ?></h1>
            <h4><?= $heroSubtitle ?></h4>

            <?php if (!empty($heroButtonText)) : ?>
                <a href="<?= $heroButtonLink ?>" class="hero-btn">
                    <?= $heroButtonText ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- DERECHA VACÍA (sirve para mantener composición) -->
        <div class="hero-right">
            <?php
            // Solo si la variable está definida (es decir, estamos en login.php)
            if (isset($heroRightContent)) {
                echo $heroRightContent;
            }
            ?>
        </div>
    </div>
</div>



