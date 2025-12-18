<?php
session_start();
require_once __DIR__ . '/../db/connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sku'], $_POST['cantidad'])) {
    $sku = $_POST['sku'];
    $cantidad = (int)$_POST['cantidad'];

    // Actualizar sesión
    if ($cantidad >= 1) {
        $_SESSION['carrito'][$sku] = $cantidad;
    }

    // Recalcular todo
    $total_productos = 0;
    $total_items_header = 0;
    $subtotal_item_actual = 0;

    if (!empty($_SESSION['carrito'])) {
        $skus = array_keys($_SESSION['carrito']);
        // Verificar que hay productos antes de consultar
        if (count($skus) > 0) {
            $placeholders = implode(',', array_fill(0, count($skus), '?'));
            $stmt = $pdo->prepare("SELECT sku, precio FROM producto_variantes WHERE sku IN ($placeholders)");
            $stmt->execute($skus);
            $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($precios as $p) {
                if (isset($_SESSION['carrito'][$p['sku']])) {
                    $cant = $_SESSION['carrito'][$p['sku']];
                    $subtotal = $p['precio'] * $cant;
                    $total_productos += $subtotal;
                    $total_items_header += $cant;
                    
                    if ($p['sku'] === $sku) {
                        $subtotal_item_actual = $subtotal;
                    }
                }
            }
        }
    }

    // LÓGICA DE ENVÍO (Debe coincidir con cart.php)
    $gastos_envio = ($total_productos > 50) ? 0 : 5;
    $total_pagar = $total_productos + $gastos_envio;

    echo json_encode([
        'success' => true,
        'nuevoSubtotalItem' => number_format($subtotal_item_actual, 2) . '€',
        'nuevoTotalCarrito' => number_format($total_productos, 2) . '€', // Esto es el Subtotal visual
        'nuevoTotalPagar'   => number_format($total_pagar, 2) . '€',     // Esto es el Total con envío
        'totalItemsHeader'  => $total_items_header
    ]);
    exit;
}