<?php
// templates/ajax/admin-products-search.php

// 1. Conexión a la base de datos (ajusta la ruta según tu estructura)
require_once __DIR__ . '/../../../db/connection.php';

// 2. Recoger filtros enviados por el fetch de JS
$nombre   = $_GET['nombre'] ?? '';
$molienda = $_GET['molienda'] ?? '';
$tueste   = $_GET['tueste'] ?? '';
$sku      = $_GET['sku'] ?? '';

// 3. Construir la consulta con JOIN para traer datos del producto y sus variantes
$sql = "SELECT p.id, p.nombre_cafe, v.sku, v.precio, v.stock, v.molienda, v.tueste 
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
            <td><?= $prod['id'] ?></td>
            <td><strong><?= htmlspecialchars($prod['nombre_cafe']) ?></strong></td>
            <td><code><?= htmlspecialchars($prod['sku']) ?></code></td>
            <td><?= number_format($prod['precio'], 2) ?>€</td>
            <td>
                <span class="stock-badge" style="color: <?= $prod['stock'] < 10 ? 'red' : 'green' ?>;">
                    <?= $prod['stock'] ?> uds.
                </span>
            </td>
            <td>
                <small><?= htmlspecialchars($prod['molienda']) ?> / <?= htmlspecialchars($prod['tueste']) ?></small>
            </td>
            <td>
                <button type="button" class="modificar-btn" 
                        onclick="openEditProduct(<?= $prod['id'] ?>)">
                    Modificar
                </button>
                
                <form method="POST" action="admin.php?tab=productos" style="display:inline;" 
                      onsubmit="return confirm('¿Estás seguro de eliminar este producto? Esto eliminará todas sus variantes.');">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>">
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