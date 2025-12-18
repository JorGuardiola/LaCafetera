<?php
// frontend/ajaxcart.php
session_start();
require_once __DIR__ . '/../db/connection.php'; 

// Verificamos que sea una petición POST válida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sku'], $_POST['cantidad'])) {
    $sku = $_POST['sku'];
    $cantidad = (int)$_POST['cantidad'];

    // 1. Actualizar sesión (Evitamos cantidades negativas)
    if ($cantidad >= 1) {
        $_SESSION['carrito'][$sku] = $cantidad;
    } else {
        // Opcional: Si manda 0 o menos, podrías borrarlo, pero por seguridad lo dejamos en 1 o ignoramos
    }

    // 2. Recalcular todo consultando la BD (Seguridad)
    $total_productos = 0;
    $total_items_header = 0;
    $subtotal_item_actual = 0;

    if (!empty($_SESSION['carrito'])) {
        $skus = array_keys($_SESSION['carrito']);
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

    // 3. LÓGICA DE ENVÍO 
    $es_gratis = ($total_productos > 50); 
    $gastos_envio = $es_gratis ? 0 : 5;
    $total_pagar = $total_productos + $gastos_envio;

    // 4. Generar el HTML de la etiqueta de envío para JS
    if ($es_gratis) {
        $texto_envio_html = '<span style="color:#27AE60;">Gratis</span>';
    } else {
        $texto_envio_html = number_format($gastos_envio, 2) . '€';
    }

    // 5. Respuesta JSON
    echo json_encode([
        'success' => true,
        'nuevoSubtotalItem' => number_format($subtotal_item_actual, 2) . '€',
        'nuevoTotalCarrito' => number_format($total_productos, 2) . '€',
        'nuevoTotalPagar'   => number_format($total_pagar, 2) . '€',
        'textoEnvioHTML'    => $texto_envio_html, // Importante para la actualización visual
        'totalItemsHeader'  => $total_items_header
    ]);
    exit;
}