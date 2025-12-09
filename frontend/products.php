<?php
// frontend/products.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Datos del hero
$bgClass = "bg-productos";
$heroTitle = "Nuestros productos";
$heroSubtitle = "Nuestra Selección reúne cafés de especialidad de fincas únicas...";
$heroButtonText = "";
$heroButtonLink = "";

// -----------------------------------------------------
// 1. CAPTURAR FILTROS DESDE GET
// -----------------------------------------------------
$f_origen  = $_GET['origen']  ?? '';
$f_proceso = $_GET['proceso'] ?? '';
$f_altitud = $_GET['altitud'] ?? '';

// -----------------------------------------------------
// 2. PAGINACIÓN
// -----------------------------------------------------
$por_pagina = 12;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$offset = ($pagina - 1) * $por_pagina;

// -----------------------------------------------------
// 3. CONSTRUIR SQL BASE + FILTROS
// -----------------------------------------------------

$sql = "
    SELECT 
        p.id, 
        p.nombre_cafe, 
        p.presentacion, 
        p.imagen, 
        p.puntuacion_sca,
        (SELECT precio FROM producto_variantes pv 
         WHERE pv.producto_id = p.id AND pv.envase = '250g' 
         LIMIT 1) AS precio
    FROM productos p
    WHERE p.disponible = 1
";

$params = [];

// FILTRO ORIGEN
if ($f_origen !== '') {
    $sql .= " AND p.pais_origen = :origen";
    $params[':origen'] = $f_origen;
}

// FILTRO PROCESO
if ($f_proceso !== '') {
    $sql .= " AND p.proceso = :proceso";
    $params[':proceso'] = $f_proceso;
}

// FILTRO ALTITUD (RANGOS)
if ($f_altitud !== '') {
    if ($f_altitud === "1000-1500") {
        $sql .= " AND p.altitud_msnm BETWEEN 1000 AND 1500";
    }
    elseif ($f_altitud === "1500-1800") {
        $sql .= " AND p.altitud_msnm BETWEEN 1500 AND 1800";
    }
    elseif ($f_altitud === "1800-9999") {
        $sql .= " AND p.altitud_msnm >= 1800";
    }
}

// ORDEN + PAGINACIÓN
$sql .= " ORDER BY p.id ASC LIMIT :limite OFFSET :offset";

$stmt = $pdo->prepare($sql);

// Valores numéricos (limit/offset)
$stmt->bindValue(':limite', (int)$por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

// Bind de filtros dinámicos
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------------------------------
// 4. CONTAR TOTAL PARA PAGINACIÓN
// -----------------------------------------------------

$count_sql = "SELECT COUNT(*) FROM productos p WHERE p.disponible = 1";
$count_params = [];

if ($f_origen !== '') {
    $count_sql .= " AND p.pais_origen = :origen";
    $count_params[':origen'] = $f_origen;
}

if ($f_proceso !== '') {
    $count_sql .= " AND p.proceso = :proceso";
    $count_params[':proceso'] = $f_proceso;
}

if ($f_altitud !== '') {
    if ($f_altitud === "1000-1500") {
        $count_sql .= " AND p.altitud_msnm BETWEEN 1000 AND 1500";
    }
    elseif ($f_altitud === "1500-1800") {
        $count_sql .= " AND p.altitud_msnm BETWEEN 1500 AND 1800";
    }
    elseif ($f_altitud === "1800-9999") {
        $count_sql .= " AND p.altitud_msnm >= 1800";
    }
}

$count_stmt = $pdo->prepare($count_sql);
foreach ($count_params as $k => $v) {
    $count_stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$count_stmt->execute();

$total_productos = $count_stmt->fetchColumn();
$total_paginas = ceil($total_productos / $por_pagina);

?>

<?php include __DIR__ . '/templates/header.php'; ?>

<main>

    <?php include __DIR__ . '/templates/hero.php'; ?>

    <!-- ⭐ AQUÍ VA TU SEARCH.PHP COMO TEMPLATE -->
    <?php include __DIR__ . '/templates/search.php'; ?>

    <section class="product-grid">
        <?php foreach ($productos as $p): ?>
            <?php include __DIR__ . '/templates/card.php'; ?>
        <?php endforeach; ?>
    </section>

    <!-- PAGINACIÓN -->
    <div class="pagination" style="text-align:center; margin:3rem 0;">
        <?php
        // Construir parámetros GET para no perder filtros
        $query = $_GET;
        ?>

        <?php if ($pagina > 1): ?>
            <?php $query['pagina'] = $pagina - 1; ?>
            <a class="page-btn" href="?<?= http_build_query($query) ?>">← Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <?php $query['pagina'] = $i; ?>
            <a class="page-btn <?= ($i == $pagina ? 'active' : '') ?>" href="?<?= http_build_query($query) ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($pagina < $total_paginas): ?>
            <?php $query['pagina'] = $pagina + 1; ?>
            <a class="page-btn" href="?<?= http_build_query($query) ?>">Siguiente →</a>
        <?php endif; ?>
    </div>

</main>

<?php include __DIR__ . "/templates/footer.php"; ?>

