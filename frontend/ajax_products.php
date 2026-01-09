<?php
// frontend/ajax_products.php
require_once __DIR__ . '/../db/connection.php';

// 1. Recoger filtros
$search  = $_GET['q'] ?? '';
$origen  = $_GET['origen'] ?? '';
$proceso = $_GET['proceso'] ?? '';
$orderBy = $_GET['sort'] ?? 'id'; // id, nombre, precio
$orderDir= $_GET['dir'] ?? 'ASC'; // ASC, DESC

// 2. Construir SQL
// Nota: Usamos una subconsulta para el precio base (envase 250g)
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

// Filtro Nombre
if ($search !== '') {
    $sql .= " AND (p.nombre_cafe LIKE :search OR p.descripcion LIKE :search)";
    $params[':search'] = "%" . $search . "%";
}

// Filtro Origen
if ($origen !== '') {
    $sql .= " AND p.pais_origen = :origen";
    $params[':origen'] = $origen;
}

// Filtro Proceso
if ($proceso !== '') {
    $sql .= " AND p.proceso = :proceso";
    $params[':proceso'] = $proceso;
}

// 3. Ordenación
// Validamos para evitar inyección SQL directa
$validSorts = ['nombre_cafe', 'precio', 'id'];
$validDirs  = ['ASC', 'DESC'];

if (!in_array($orderBy, $validSorts)) $orderBy = 'id';
if (!in_array($orderDir, $validDirs)) $orderDir = 'ASC';

// En SQL el alias 'precio' se puede usar en ORDER BY
$sql .= " ORDER BY $orderBy $orderDir";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Devolver HTML (Renderizamos las cards aquí mismo)
if (count($productos) > 0) {
    foreach ($productos as $p) {
        // Incluimos la plantilla de tarjeta. 
        // Nota: card.php espera la variable $p
        include __DIR__ . '/templates/card.php';
    }
} else {
    echo '<div style="width:100%; text-align:center; padding:2rem;">No se encontraron cafés con estos filtros.</div>';
}
?>