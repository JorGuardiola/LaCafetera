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

// --- LÓGICA DE ENVÍO DINÁMICA ---
// Si supera 50€, envío es 0€, si no 5€
$gastos_envio = ($total_carrito > 50) ? 0.00 : 5.00;
$total_pagar = $total_carrito + $gastos_envio;
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
<div class="cart-item-row" id="row-<?= $item['sku'] ?>">
    
    <div class="cart-img">
        <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= htmlspecialchars($item['imagen']) ?>" alt="Café">
    </div>

    <div class="cart-info">
        <div class="cart-product-name"><?= htmlspecialchars($item['nombre_cafe']) ?></div>
        <div class="cart-sku">SKU: <?= htmlspecialchars($item['sku']) ?></div>
        <span class="variant-tag">Envase: <?= htmlspecialchars($item['envase']) ?></span>
        <span class="variant-tag">Molienda: <?= ucfirst($item['molienda']) ?></span>
        <span class="variant-tag">Tueste: <?= ucfirst($item['tueste']) ?></span>
    </div>

    <div class="mobile-hide cart-total-line">
        <span id="subtotal-<?= $item['sku'] ?>">
            <?= number_format($item['precio'] * $item['cantidad'], 2) ?>
        </span>€
    </div>

    <div class="quantity-selector-widget">
        <button type="button" class="qty-btn" id="btn-minus-<?= $item['sku'] ?>"
                onclick="updateCartAjax('<?= $item['sku'] ?>', <?= $item['cantidad'] - 1 ?>)"
                <?= $item['cantidad'] <= 1 ? 'disabled style="opacity:0.3"' : '' ?>>
            -
        </button>

        <input type="text" id="input-qty-<?= $item['sku'] ?>" value="<?= $item['cantidad'] ?>" readonly>

        <button type="button" class="qty-btn" id="btn-plus-<?= $item['sku'] ?>"
                onclick="updateCartAjax('<?= $item['sku'] ?>', <?= $item['cantidad'] + 1 ?>)">
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
    <span id="summary-subtotal"><?= number_format($total_carrito, 2) ?>€</span>
</div>
            
            <div class="summary-row">
                <span>Gastos de envío</span>
                <?php if ($total_carrito > 50): ?>
                    <span style="color:#27AE60;">
                        <?= number_format($gastos_envio, 2) ?>€ (Gratis)
                    </span>
                <?php else: ?>
                    <span><?= number_format($gastos_envio, 2) ?>€</span>
                <?php endif; ?>
            </div>

            <hr style="border:0; border-top:1px solid #eee; margin: 15px 0;">

            <div class="summary-row total">
                <span>Total</span>
                <span><?= number_format($total_pagar, 2) ?>€</span>
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
function updateCartAjax(sku, qty) {
    if (qty < 1) return;

    const formData = new FormData();
    formData.append('sku', sku);
    formData.append('cantidad', qty);

    fetch('ajaxcart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualiza el número visual
            document.getElementById('input-qty-' + sku).value = qty;
            
            // Actualiza el precio de la fila
            document.getElementById('subtotal-' + sku).innerText = data.nuevoSubtotalItem.replace('€', '');

            // Actualiza el resumen de la derecha
            document.getElementById('summary-subtotal').innerText = data.nuevoTotalCarrito;
            document.getElementById('summary-total').innerText = data.nuevoTotalCarrito;

            // Actualiza el icono del carrito arriba (header)
            const headerCount = document.getElementById('headerCartCount');
            if (headerCount) headerCount.innerText = data.totalItemsHeader;

            // Actualiza los botones para que el siguiente clic funcione
            const btnMinus = document.getElementById('btn-minus-' + sku);
            const btnPlus = document.getElementById('btn-plus-' + sku);
            
            btnMinus.setAttribute('onclick', `updateCartAjax('${sku}', ${qty - 1})`);
            btnPlus.setAttribute('onclick', `updateCartAjax('${sku}', ${qty + 1})`);
            
            // Desactivar el menos si es 1
            btnMinus.disabled = (qty <= 1);
            btnMinus.style.opacity = (qty <= 1) ? '0.3' : '1';
        }
    });
}
</script>

<?php include __DIR__ . "/templates/footer.php"; ?>

