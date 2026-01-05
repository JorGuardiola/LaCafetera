<?php
// templates/ajax/admin-products-search.php

// 1. Conexión a la base de datos (ajusta la ruta según tu estructura)
require_once __DIR__ . '/../../../db/connection.php';

// 2. Recoger filtros enviados por el fetch de JS
$nombre   = $_GET['nombre'] ?? '';
$molienda = $_GET['molienda'] ?? '';
$tueste   = $_GET['tueste'] ?? '';
$sku      = $_GET['sku'] ?? '';
$envase   = $_GET['envase'] ?? '';

// 3. Construir la consulta con JOIN para traer datos del producto y sus variantes
$sql = "SELECT p.id, p.nombre_cafe, v.sku, v.precio, v.stock, v.molienda, v.tueste, v.envase, p.imagen 
        FROM productos p
        INNER JOIN producto_variantes v ON p.id = v.producto_id
        WHERE 1=1";

$params = [];

if ($nombre) {
    $sql .= " AND p.nombre_cafe LIKE ?";
    $params[] = "%$nombre%";
}
if ($molienda) {
    $sql .= " AND v.molienda = ?";
    $params[] = $molienda;
}
if ($tueste) {
    $sql .= " AND v.tueste = ?";
    $params[] = $tueste;
}
if ($sku) {
    $sql .= " AND v.sku LIKE ?";
    $params[] = "%$sku%";
}
if ($envase) {
    $sql .= " AND v.envase = ?";
    $params[] = $envase;
}

// Ordenar por ID para mantener coherencia
$sql .= " ORDER BY p.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Generar el HTML de las filas
    if (empty($productos)) {
        echo '<tr><td colspan="7">No se encontraron productos con esos filtros.</td></tr>';
        exit;
    }

    foreach ($productos as $prod): ?>
        <tr>
            <td data-label="ID"><?= $prod['id'] ?></td>
            <td data-label="Nombre"><strong><?= htmlspecialchars($prod['nombre_cafe']) ?></strong></td>
            <td data-label="SKU"><code><?= htmlspecialchars($prod['sku']) ?></code></td>
            <td data-label="Precio"><?= number_format($prod['precio'], 2) ?>€</td>
            <td data-label="Stock">
                <span class="stock-badge" style="color: <?= $prod['stock'] < 10 ? 'red' : 'green' ?>;">
                    <?= $prod['stock'] ?> uds.
                </span>
            </td>
            <td data-label="Molienda">
                <span><?= htmlspecialchars($prod['molienda']) ?></span>
            </td>
            <td data-label="Tueste">
                <span><?= htmlspecialchars($prod['tueste']) ?></span>
            </td>
            <td data-label="Envase"><?= htmlspecialchars($prod['envase']) ?></td>
            <td data-label="Imagen">
                <?php if (!empty($prod['imagen'])): ?>
                    <img src="assets/img/<?= htmlspecialchars($prod['imagen']) ?>" alt="Café" style="width: 10rem; height: 10rem; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                    <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 10px; color: #999;">Sin foto</div>
                <?php endif; ?>
            </td>
            <td data-label="Acciones">
                <button type="button" class="modificar-btn" 
                    onclick="openEditProduct({
                    id: <?= $prod['id'] ?>,
                    nombre: '<?= addslashes($prod['nombre_cafe']) ?>',
                    sku: '<?= addslashes($prod['sku']) ?>',
                    precio: <?= $prod['precio'] ?>,
                    stock: <?= $prod['stock'] ?>,
                    molienda: '<?= $prod['molienda'] ?>',
                    tueste: '<?= $prod['tueste'] ?>',
                    envase: '<?= addslashes($prod['envase']) ?>'
                    imagen: '<?= isset($prod['imagen']) ? $prod['imagen'] : '' ?>'
                    })">Modificar
                </button>
                
                
                <form method="POST" action="admin.php?tab=productos" style="display:inline;" 
                    onsubmit="return confirm('¿Estás seguro de eliminar esta variante específica?');">
                    <input type="hidden" name="action" value="delete_variant"> <input type="hidden" name="sku" value="<?= $prod['sku'] ?>"> 
                    <button type="submit" class="eliminar-btn">
                    Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach;

} catch (PDOException $e) {
    echo '<tr><td colspan="7">Error en la base de datos: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
}