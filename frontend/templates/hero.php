<div class="hero-container <?= $bgClass ?>">
    <div class="hero-inner">

        <!-- IZQUIERDA: textos dinámicos -->
        <div class="hero-left">
            <h1><?= $heroTitle ?></h1>
            <h2><?= $heroSubtitle ?></h2>

            <?php if (!empty($heroButtonText)) : ?>
                <a href="<?= $heroButtonLink ?>" class="hero-btn">
                    <?= $heroButtonText ?>
                </a>
            <?php endif; ?>
        </div>

        <!-- DERECHA VACÍA (sirve para mantener composición) -->
        <div class="hero-right"></div>

    </div>
</div>



