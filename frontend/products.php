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

    <!-- AQUÍ VA TU SEARCH.PHP COMO TEMPLATE -->
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
    $current_page = $pagina; 
    $total_pages = $total_paginas;

    // ----------------------------------------------------------------------
    // CASO ESPECIAL: SOLO DOS PÁGINAS (Implementando el orden específico)
    // ----------------------------------------------------------------------
    if ($total_pages == 2) {

        if ($current_page == 1) {
            // EN PÁGINA 1: Mostrar [1] [Siguiente ->] [2]

            // 1. Enlace a la página 1 (activo)
            $query['pagina'] = 1;
            echo '<a class="page-btn active" href="?' . http_build_query($query) . '">1</a>';
            
            // 2. Botón Siguiente (que va a 2)
            $query['pagina'] = 2; // El link Siguiente siempre va a la página 2
            echo '<a class="page-btn" href="?' . http_build_query($query) . '">Siguiente →</a>';
            
            // 3. Enlace a la página 2 (inactivo)
            $query['pagina'] = 2;
            echo '<a class="page-btn" href="?' . http_build_query($query) . '">2</a>';
            
        } elseif ($current_page == 2) {
            // EN PÁGINA 2: Mostrar [1] [2] [<- Anterior]

            // 1. Enlace a la página 1 (inactivo)
            $query['pagina'] = 1;
            echo '<a class="page-btn" href="?' . http_build_query($query) . '">1</a>';
            
            // 2. Enlace a la página 2 (activo)
            $query['pagina'] = 2;
            echo '<a class="page-btn active" href="?' . http_build_query($query) . '">2</a>';
            
            // 3. Botón Anterior (que va a 1)
            $query['pagina'] = 1;
            echo '<a class="page-btn" href="?' . http_build_query($query) . '">← Anterior</a>';
        }

    } 
    // ----------------------------------------------------------------------
    // CASO GENERAL: MÁS DE DOS PÁGINAS (Se mantiene la lógica estándar simple)
    // ----------------------------------------------------------------------
    else {

        // Enlace Anterior
        if ($current_page > 1) {
            $query['pagina'] = $current_page - 1;
            echo '<a class="page-btn" href="?' . http_build_query($query) . '">← Anterior</a>';
        }

        // Mostrar páginas adyacentes
        $start_page = max(1, $current_page - 1);
        $end_page = min($total_pages, $current_page + 1);

        for ($i = $start_page; $i <= $end_page; $i++) {
            $query['pagina'] = $i;
            $active_class = ($i == $current_page) ? 'active' : '';
            echo '<a class="page-btn ' . $active_class . '" href="?' . http_build_query($query) . '">' . $i . '</a>';
        }
        
        // Enlace Siguiente
        if ($current_page < $total_pages) {
            $query['pagina'] = $current_page + 1;
            echo '<a class="page-btn" href="?' . http_build_query($query) . '">Siguiente →</a>';
        }
    }
    ?>
</div>

</main>

<?php include __DIR__ . "/templates/footer.php"; ?>

