<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Datos del hero
$bgClass = "bg-productos";
$heroTitle = "Nuestros productos";
$heroSubtitle = "Nuestra Selección reúne cafés de especialidad de fincas únicas...";
$heroButtonText = "";
$heroButtonLink = "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>La Cafetera - Productos</title>
    <!-- CSS con ruta ABSOLUTA (esto siempre funciona) -->
    <link rel="stylesheet" href="/lacafetera/assets/css/style.css">
</head>

<body>

<?php include __DIR__ . '/templates/header.php'; ?>

<main>

    <?php include __DIR__ . '/templates/hero.php'; ?>
    <?php include __DIR__ . '/templates/search.php'; ?>

    <?php
    /* -----------------------------------
        PAGINACIÓN
    ----------------------------------- */
    $por_pagina = 12;

    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina < 1) $pagina = 1;

    $offset = ($pagina - 1) * $por_pagina;

    /* -----------------------------------
        CONSULTA DE PRODUCTOS
    ----------------------------------- */
    $stmt = $conn->prepare("
        SELECT id, nombre_cafe, presentacion, imagen, puntuacion_sca
        FROM productos
        WHERE disponible = 1
        LIMIT :offset, :limite
    ");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* -----------------------------------
        CONTAR TOTAL
    ----------------------------------- */
    $total_query = $conn->query("SELECT COUNT(*) AS total FROM productos WHERE disponible = 1");
    $total_productos = $total_query->fetch()['total'];
    $total_paginas = ceil($total_productos / $por_pagina);
    ?>

    <h2 class="filter-title" style="text-align:center; margin-top:3rem;">
        Selecciona tus productos
    </h2>

    <section class="product-grid">

    <?php foreach ($productos as $p): ?>
        <div class="product-card">

            <button class="fav-btn">
                <img src="../assets/img/icon-heart.png" alt="Fav">
            </button>

            <div class="product-image">
                <img src="/lacafetera/assets/img/<?= htmlspecialchars($p['imagen']) ?>" 
                     alt="<?= htmlspecialchars($p['nombre_cafe']) ?>">
            </div>

            <div class="product-rating">★ ★ ★ ★ ☆</div>

            <h3 class="product-name"><?= htmlspecialchars($p['nombre_cafe']) ?></h3>

            <p class="product-weight"><?= htmlspecialchars($p['presentacion']) ?></p>

            <p class="product-price"><?= number_format($p['puntuacion_sca'], 1) ?>€</p>

            <button class="product-btn">Ver detalles</button>

        </div>
    <?php endforeach; ?>

    </section>

    <!-- PAGINACIÓN -->
    <div class="pagination">

        <?php if ($pagina > 1): ?>
            <a class="page-btn" href="?pagina=<?= $pagina - 1 ?>">← Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a class="page-btn <?= ($i == $pagina ? 'active' : '') ?>" href="?pagina=<?= $i ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($pagina < $total_paginas): ?>
            <a class="page-btn" href="?pagina=<?= $pagina + 1 ?>">Siguiente →</a>
        <?php endif; ?>

    </div>

</main>

<?php include __DIR__ . "/templates/footer.php"; ?>

</body>
</html>
