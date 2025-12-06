<?php
// Recoger valores enviados
$f_origen  = $_GET['origen']  ?? '';
$f_proceso = $_GET['proceso'] ?? '';
$f_altitud = $_GET['altitud'] ?? '';

// Valores dinámicos para los selects
$origenes = ["Brasil","Burundi","Colombia","Etiopía","Guatemala","Honduras","Kenia","Nicaragua","Perú"];
$procesos = ["Lavado","Natural","Honey"];
$rangos = [
    "1000-1500" => "1000m - 1500m",
    "1500-1800" => "1500m - 1800m",
    "1800-9999" => "+1800m"
];
?>

<div class="product-filter-bar">

    <h2 class="filter-title">Selecciona tus productos</h2>

    <form method="GET" class="filter-controls">

        <!-- ORIGEN -->
        <select name="origen" class="filter-btn">
            <option value="">ORIGEN</option>
            <?php foreach ($origenes as $o): ?>
                <option value="<?= $o ?>" <?= $f_origen === $o ? 'selected' : '' ?>>
                    <?= $o ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- PROCESO -->
        <select name="proceso" class="filter-btn long">
            <option value="">MÉTODO DE PROCESAMIENTO</option>
            <?php foreach ($procesos as $p): ?>
                <option value="<?= $p ?>" <?= $f_proceso === $p ? 'selected' : '' ?>>
                    <?= $p ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- ALTITUD -->
        <select name="altitud" class="filter-btn">
            <option value="">ALTITUD</option>
            <?php foreach ($rangos as $valor => $label): ?>
                <option value="<?= $valor ?>" <?= $f_altitud === $valor ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="filter-btn">FILTRAR</button>

    </form>
</div>