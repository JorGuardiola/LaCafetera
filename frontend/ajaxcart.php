<?php
session_start();
// Asegúrate de que la ruta al archivo de conexión sea correcta
require_once __DIR__ . '/../db/connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sku'], $_POST['cantidad'])) {
    $sku = $_POST['sku'];
    $cantidad = (int)$_POST['cantidad'];

    if ($cantidad >= 1) {
        $_SESSION['carrito'][$sku] = $cantidad;
    }

    // Recalcular totales para devolver al JS
    $total_carrito = 0;
    $total_items_header = 0;
    $subtotal_item_actual = 0;

    // Obtenemos los precios actualizados de la DB
    if (!empty($_SESSION['carrito'])) {
        $skus = array_keys($_SESSION['carrito']);
        $placeholders = implode(',', array_fill(0, count($skus), '?'));
        $stmt = $pdo->prepare("SELECT sku, precio FROM producto_variantes WHERE sku IN ($placeholders)");
        $stmt->execute($skus);
        $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($precios as $p) {
            $cant = $_SESSION['carrito'][$p['sku']];
            $subtotal = $p['precio'] * $cant;
            $total_carrito += $subtotal;
            $total_items_header += $cant;
            
            if ($p['sku'] === $sku) {
                $subtotal_item_actual = $subtotal;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'nuevoSubtotalItem' => number_format($subtotal_item_actual, 2) . '€',
        'nuevoTotalCarrito' => number_format($total_carrito, 2) . '€',
        'totalItemsHeader' => $total_items_header
    ]);
    exit;
}