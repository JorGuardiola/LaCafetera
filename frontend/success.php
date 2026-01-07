<?php
// frontend/success.php
require_once __DIR__ . '/../db/connection.php';

// 1. VALIDACIÓN PRIMERO (Antes de mostrar nada de HTML)
// Si no hay orden, te expulsa al inicio inmediatamente.
if (!isset($_GET['orden']) || empty($_GET['orden'])) {
    header('Location: ' . BASE_URL . '/frontend/index.php');
    exit;
}

$id_orden = (int)$_GET['orden'];
$id_orden = (int)$_GET['orden'];

// 2. Obtener datos completos del pedido (JOIN con usuarios, direcciones y pagos)
$sql = "
    SELECT 
        p.id_orden, p.total, p.fecha_orden, p.estado,
        u.nombre, u.apellido, u.email, u.telefono,
        d.direccion, d.ciudad, d.codigo_postal, d.provincia, d.pais,
        pag.metodo, pag.fecha_pago, pag.monto
    FROM pedidos p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    JOIN direcciones d ON p.id_direccion = d.id_direccion
    LEFT JOIN pagos pag ON p.id_orden = pag.id_orden
    WHERE p.id_orden = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_orden]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<div class='container' style='margin-top:5rem;'>Pedido no encontrado.</div>";
    include __DIR__ . '/templates/footer.php';
    exit;
}

// 2b. Obtener los productos específicos de este pedido
$sql_items = "
    SELECT 
        pi.cantidad, pi.precio_unitario,
        v.molienda, v.envase, v.tueste,
        p.nombre_cafe, p.imagen
    FROM pedido_items pi
    JOIN producto_variantes v ON pi.id_variante_sku = v.sku
    JOIN productos p ON v.producto_id = p.id
    WHERE pi.id_orden = ?
";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$id_orden]);
$items_comprados = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// 3. Cálculos visuales
// Asumimos envío gratis si > 50 (misma lógica que checkout)
$gastos_envio = ($pedido['total'] >= 50) ? 0.00 : 8.00;
$subtotal = $pedido['total'] - $gastos_envio;
$nombre_completo = htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']);
$nombre_solo = htmlspecialchars($pedido['nombre']);


// Formato de fecha (Ej: 27 de febrero 2026)
setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');
$fecha_formateada = strftime('%d de %B %Y', strtotime($pedido['fecha_orden']));
// Fallback si strftime da problemas en algunos servers:
if(!$fecha_formateada) $fecha_formateada = date('d/m/Y', strtotime($pedido['fecha_orden']));

?>

<?php include __DIR__ . '/templates/header.php'; ?>

<main class="container">

    <div class="success-header">
        <div>
            <h1 class="success-title">¡Gracias por tu compra, <?= $nombre_solo ?>!</h1>
        </div>
        <div>
            <p class="success-subtitle">Resumen del pedido nº <?= str_pad($pedido['id_orden'], 5, '0', STR_PAD_LEFT) ?></p>
        </div>
    </div>

    <div class="success-layout">

        <div class="success-card">
            <div class="details-grid">
                
                <div class="details-col-left">
                    
                    <div class="detail-group">
                        <h3>Información de contacto</h3>
                        <p><?= $nombre_completo ?></p>
                        <p><?= htmlspecialchars($pedido['email']) ?></p>
                        <?php if($pedido['telefono']): ?>
                            <p><?= htmlspecialchars($pedido['telefono']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="detail-group">
                        <h3>Dirección de envío</h3>
                        <p><?= $nombre_completo ?></p>
                        <p><?= htmlspecialchars($pedido['direccion']) ?></p>
                        <p><?= htmlspecialchars($pedido['codigo_postal'] . ' ' . $pedido['ciudad']) ?></p>
                        <p><?= htmlspecialchars($pedido['provincia']) ?></p>
                        <p><?= htmlspecialchars($pedido['pais']) ?></p>
                    </div>

                    <div class="detail-group">
                        <h3>Método de envío</h3>
                        <p>Estándar (24h - 72h)</p>
                    </div>

                </div>

                <div class="details-col-right">
                    
                    <div class="detail-group">
                        <h3>Pago</h3>
                        <p style="text-transform: capitalize;"><?= htmlspecialchars($pedido['metodo'] ?? 'Tarjeta') ?></p>
                        <p><?= number_format($pedido['total'], 2) ?>€</p>
                        <p><?= $fecha_formateada ?></p>
                    </div>

                    <div class="detail-group">
                        <h3>Dirección de facturación</h3>
                        <p>Igual que la dirección de envío</p>
                        <p><?= $nombre_completo ?></p>
                        <p><?= htmlspecialchars($pedido['direccion']) ?></p>
                        <p><?= htmlspecialchars($pedido['codigo_postal'] . ' ' . $pedido['ciudad']) ?></p>
                        <p><?= htmlspecialchars($pedido['pais']) ?></p>
                    </div>

                </div>
            </div>
            
            <div class="details-grid">
                 <a href="<?= BASE_URL ?>/frontend/products.php" class="boton1-btn">Volver a la tienda</a>
            </div>
        </div>

        <div class="success-card">
            <h3 class="summary-title">Resumen del pedido</h3>

<div class="purchased-items" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
        <?php foreach ($items_comprados as $item): ?>
            <div class="item-row" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center;">
                <div class="item-img" style="position: relative;">
                        <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= $item['imagen'] ?>"
                         alt="<?= htmlspecialchars($item['nombre_cafe']) ?>" 
                         style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                    <span style="position: absolute; top: -8px; right: -8px; background: #333; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;">
                        <?= $item['cantidad'] ?>
                    </span>
                </div>
                
                <div class="item-info" style="flex: 1;">
                    <p style="margin: 0; font-weight: 600; font-size: 0.95rem; color: #333;">
                        <?= htmlspecialchars($item['nombre_cafe']) ?>
                    </p>
                    <p style="margin: 0; font-size: 0.8rem; color: #777; text-transform: capitalize;">
                        <?= htmlspecialchars($item['envase']) ?> | <?= htmlspecialchars($item['molienda']) ?>
                    </p>
                </div>
                
                <div class="item-price" style="font-weight: 600; color: #333;">
                    <?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?>€
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <hr class="divider" style="border: 0; border-top: 1px solid #eee; margin-bottom: 1.5rem;">

            <div class="summary-line">
                <span>Subtotal</span>
                <span><?= number_format($subtotal, 2) ?>€</span>
            </div>

            <div class="summary-line">
                <span>Gastos de envío</span>
                <?php if($gastos_envio == 0): ?>
                    <span>0€</span>
                <?php else: ?>
                    <span><?= number_format($gastos_envio, 2) ?>€</span>
                <?php endif; ?>
            </div>

            <hr class="divider">

            <div class="total-line">
                <div>
                    Total
                    <span class="iva-note">IVA incluido</span>
                </div>
                <span><?= number_format($pedido['total'], 2) ?>€</span>
            </div>
        </div>

    </div>

</main>

<?php include __DIR__ . '/templates/footer.php'; ?>
