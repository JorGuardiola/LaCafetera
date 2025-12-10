<?php
// frontend/cart.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ==========================================
// 1. LÓGICA DE BACKEND (Añadir/Eliminar)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // A) AÑADIR AL CARRITO
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        
        $product_id = $_POST['product_id'] ?? null;
        $envase = $_POST['envase'] ?? null;
        $molienda = $_POST['molienda'] ?? null;
        $tueste = $_POST['tueste'] ?? null;
        $cantidad = (int)($_POST['cantidad'] ?? 1);

        if ($product_id && $envase && $molienda && $tueste) {
            // Buscamos el SKU en la base de datos basándonos en la selección
            // Esto es seguro y robusto
            $stmt = $pdo->prepare("SELECT sku FROM producto_variantes WHERE producto_id = ? AND envase = ? AND molienda = ? AND tueste = ? LIMIT 1");
            $stmt->execute([$product_id, $envase, $molienda, $tueste]);
            $variante = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($variante) {
                $sku = $variante['sku'];
                if (isset($_SESSION['carrito'][$sku])) {
                    $_SESSION['carrito'][$sku] += $cantidad;
                } else {
                    $_SESSION['carrito'][$sku] = $cantidad;
                }
            }
        }
    }

    // B) ACTUALIZAR CANTIDAD (Desde el Carrito con JS)
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $sku_update = $_POST['sku'];
        $nueva_cantidad = (int)$_POST['cantidad'];
        
        if (isset($_SESSION['carrito'][$sku_update]) && $nueva_cantidad >= 1) {
            $_SESSION['carrito'][$sku_update] = $nueva_cantidad;
        }
    }

    // C) ELIMINAR
    if (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $sku_remove = $_POST['sku_remove'];
        if (isset($_SESSION['carrito'][$sku_remove])) {
            unset($_SESSION['carrito'][$sku_remove]);
        }
    }

    // Redirigir para evitar reenvío de formulario
    header('Location:' . BASE_URL . '/frontend/cart.php');
    exit;
}

// ==========================================
// 2. OBTENER DATOS PARA MOSTRAR
// ==========================================
$items_carrito = [];
$total_carrito = 0;

if (!empty($_SESSION['carrito'])) {
    $skus = array_keys($_SESSION['carrito']);
    if(count($skus) > 0) {
        $placeholders = implode(',', array_fill(0, count($skus), '?'));
        
        $sql = "
            SELECT pv.sku, pv.precio, pv.molienda, pv.envase, pv.tueste, p.nombre_cafe, p.imagen
            FROM producto_variantes pv
            JOIN productos p ON pv.producto_id = p.id
            WHERE pv.sku IN ($placeholders)
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($skus);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados as $row) {
            $sku = $row['sku'];
            if (isset($_SESSION['carrito'][$sku])) {
                $cantidad = $_SESSION['carrito'][$sku];
                $subtotal = $row['precio'] * $cantidad;
                
                $row['cantidad'] = $cantidad;
                $row['subtotal'] = $subtotal;
                
                $items_carrito[] = $row;
                $total_carrito += $subtotal;
            }
        }
    }
}
?>


<?php include __DIR__ . '/templates/header.php'; ?>

<div class="container" style="margin-top: 4rem; margin-bottom: 4rem;">
    
    <h1 style="font-size: 3.5rem; margin-bottom: 3rem;">Mi carrito</h1>

    <?php if (empty($items_carrito)): ?>
        <div class="center-text" style="padding: 4rem; background: #f9f9f9; border-radius: 8px;">
            <p>Tu carrito está vacío.</p>
            <a href="products.php" class="btn-checkout" style="display:inline-block; width:auto; padding: 10px 30px; margin-top:1rem;">Ir a la tienda</a>
        </div>
    <?php else: ?>

    <div class="cart-layout">
        
        <div class="cart-list">
            <?php foreach ($items_carrito as $item): ?>
            <div class="cart-item-row">
                
                <div class="cart-img">
                    <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= htmlspecialchars($item['imagen']) ?>" alt="Café">
                </div>

                <div class="cart-info">
                    <div class="cart-product-name"><?= htmlspecialchars($item['nombre_cafe']) ?></div>
                    <div class="cart-sku">SKU: <?= htmlspecialchars($item['sku']) ?></div>
                    <span class="variant-tag">Envase: <?= htmlspecialchars($item['envase']) ?></span>
                    <span class="variant-tag"><?= ucfirst($item['molienda']) ?></span>
                </div>

                <div class="mobile-hide" style="font-weight:bold;">
                    <?= number_format($item['precio'], 2) ?>€
                </div>

                <div class="quantity-selector-widget">
                    <button type="button" class="qty-btn" 
                            onclick="updateCart('<?= $item['sku'] ?>', <?= $item['cantidad'] - 1 ?>)"
                            <?= $item['cantidad'] <= 1 ? 'disabled style="opacity:0.3"' : '' ?>>
                        -
                    </button>

                    <input type="text" value="<?= $item['cantidad'] ?>" readonly>

                    <button type="button" class="qty-btn" 
                            onclick="updateCart('<?= $item['sku'] ?>', <?= $item['cantidad'] + 1 ?>)">
                        +
                    </button>
                </div>

                <form action="cart.php" method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="sku_remove" value="<?= $item['sku'] ?>">
                    <button type="submit" style="background:none; border:none; cursor:pointer;" class="btn-delete">
                        <i class="fa-regular fa-trash-can" style="font-size:1.2rem; color:#00BFA5;"></i>
                    </button>
                </form>

            </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary-box">
            <h2>Resumen del pedido</h2>
            
            <div class="summary-row">
                <span>Subtotal</span>
                <span><?= number_format($total_carrito, 2) ?>€</span>
            </div>
            
            <div class="summary-row">
                <span>Gastos de envío</span>
                <span>0€</span>
            </div>

            <hr style="border:0; border-top:1px solid #eee; margin: 15px 0;">

            <div class="summary-row total">
                <span>Total</span>
                <span><?= number_format($total_carrito, 2) ?>€</span>
            </div>
            <div class="iva-text">IVA incluido</div>

            <button class="btn-checkout" onclick="window.location.href='<?= BASE_URL ?>/frontend/checkout.php'">Tramitar pedido</button>
        </div>

    </div>
    <?php endif; ?>

</div>

<form id="formUpdateCart" action="cart.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="sku" id="sku_update">
    <input type="hidden" name="cantidad" id="qty_update">
</form>

<script>
// Función para enviar el formulario oculto al hacer clic en + o -
function updateCart(sku, qty) {
    if (qty < 1) return; // Seguridad extra
    document.getElementById('sku_update').value = sku;
    document.getElementById('qty_update').value = qty;
    document.getElementById('formUpdateCart').submit();
}
</script>

<?php include __DIR__ . "/templates/footer.php"; ?>

