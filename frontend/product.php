<?php
// frontend/product.php
require_once __DIR__ . '/../db/connection.php';

// 1. Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location:' . BASE_URL . '/frontend/products.php');
    exit;
}

$id = (int)$_GET['id'];

// 2. Obtener Info General
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND disponible = 1");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}

// 3. Obtener Variantes (Para que JS calcule precios y stock)
$stmtVar = $pdo->prepare("SELECT sku, precio, stock, molienda, tueste, envase FROM producto_variantes WHERE producto_id = ?");
$stmtVar->execute([$id]);
$variantes = $stmtVar->fetchAll(PDO::FETCH_ASSOC);
$variantesJson = json_encode($variantes);
// 4. Calcular total de items en el carrito 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$totalItems = 0;
if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $cantidad_sku) {
        $totalItems += (int)$cantidad_sku;
    }
}
?>





<?php include __DIR__ . '/templates/header.php'; ?>

<main class="product-detail-layout">
    <div class="detail-left">
        <div class="detail-image-container">
            <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= htmlspecialchars($producto['imagen']) ?>" 
                 alt="<?= htmlspecialchars($producto['nombre_cafe']) ?>">
        </div>
        <h1 class="big-product-title"><?= htmlspecialchars($producto['nombre_cafe']) ?></h1>
    </div>

    <div class="detail-right-card">
        
        <div class="card-header">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <div class="reviews-placeholder">
                        <i class="fa-solid fa-star star-icon"></i>
                        <i class="fa-solid fa-star star-icon"></i>
                        <i class="fa-solid fa-star star-icon"></i>
                        <i class="fa-solid fa-star star-icon"></i>
                        <i class="fa-solid fa-star-half-stroke star-icon-half"></i>
                        <a href="#" class="reviews-link">45 valoraciones</a>
                    </div>
                    <h2 class="card-title"><?= htmlspecialchars($producto['nombre_cafe']) ?></h2>
                    <span class="card-origin"><?= htmlspecialchars($producto['region']) ?></span>
                </div>
                <i class="fa-regular fa-heart" style="font-size:1.5rem; cursor:pointer; color:#1A1A1A;"></i>
            </div>
        </div>

        <div class="card-price" id="displayPrice">-- €</div>

        <p class="card-description">
            <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
        </p>

        <form action="cart.php" method="POST" id="addToCartForm">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" value="<?= $producto['id'] ?>">

            <div class="selector-group">
                <label class="selector-label">Elija envase:</label>
                <div class="option-buttons">
                    <input type="radio" name="envase" id="size-250" value="250g" class="option-radio" checked>
                    <label for="size-250" class="option-label">250 G</label>
                    
                    <input type="radio" name="envase" id="size-1kg" value="1kg" class="option-radio">
                    <label for="size-1kg" class="option-label">1 KG</label>
                    
                    <input type="radio" name="envase" id="size-2kg" value="2kg" class="option-radio">
                    <label for="size-2kg" class="option-label">2 KG</label>
                </div>
            </div>

            <div class="selector-group">
                <label class="selector-label">Elija molienda:</label>
                <div class="option-buttons" style="flex-wrap: wrap; gap: 5px;">
                    <input type="radio" name="molienda" id="mol-grano" value="grano" class="option-radio" checked>
                    <label for="mol-grano" class="option-label">Grano</label>

                    <input type="radio" name="molienda" id="mol-espresso" value="molido espresso" class="option-radio">
                    <label for="mol-espresso" class="option-label">Molido-Espresso</label>

                    <input type="radio" name="molienda" id="mol-moka" value="molido moka" class="option-radio">
                    <label for="mol-moka" class="option-label">Molido-Moka</label>

                    <input type="radio" name="molienda" id="mol-goteo" value="molido goteo" class="option-radio">
                    <label for="mol-goteo" class="option-label">Molido-Goteo</label>

                    <input type="radio" name="molienda" id="mol-francesa" value="molido francesa" class="option-radio">
                    <label for="mol-francesa" class="option-label">Molido-Francesa</label>
                </div>
            </div>

            <div class="selector-group">
                <label class="selector-label">Elija tueste:</label>
                <div class="option-buttons">
                    <input type="radio" name="tueste" id="tueste-medio" value="medio" class="option-radio" checked>
                    <label for="tueste-medio" class="option-label">Medio</label>
                    
                    <input type="radio" name="tueste" id="tueste-oscuro" value="oscuro" class="option-radio">
                    <label for="tueste-oscuro" class="option-label">Oscuro</label>
                </div>
            </div>

            <div class="stock-info">
                <div class="stock-dot"></div>
                <span id="stockText">En Stock. Entrega gratuita estimada el lunes.</span>
            </div>

            <div class="card-actions-row">
                <div class="quantity-selector-widget">
                    <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                    <input type="text" name="cantidad" id="inputQty" value="1" readonly>
                    <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                </div>

                <button type="submit" class="btn-add-to-cart-dark" id="btnSubmit">
                    Añadir a la cesta
                </button>
            </div>
        </form>

    </div>
</main>
<div id="cartModal" class="cart-modal">
    <div class="modal-content">
        <button class="close-modal" onclick="closeModal()">&times;</button>
        <div class="modal-body">
            <div class="success-header">
                <i class="fa-solid fa-circle-check"></i>
                <span>Añadido a la cesta</span>
            </div>
            
            <div class="product-preview">
                <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= htmlspecialchars($producto['imagen']) ?>" alt="">
                <div class="product-info">
                    <h4 id="modalProductName"><?= htmlspecialchars($producto['nombre_cafe']) ?></h4>
                    <p id="modalVariantInfo"></p>
                    <p class="modal-price" id="modalProductPrice"></p>
                </div>
            </div>

            <div class="modal-actions">
    <a href="cart.php" class="btn-primary">
        Ver cesta (<span id="cartCount"><?= $totalItems ?></span>)
    </a>
    <a href="products.php" class="btn-secondary">Seguir comprando</a>
</div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/templates/footer.php"; ?>

<script>
    const variantes = <?= $variantesJson ?>;
    
    // Selectores de grupos de botones (Radio Buttons)
    const radiosEnvase = document.querySelectorAll('input[name="envase"]');
    const radiosMolienda = document.querySelectorAll('input[name="molienda"]');
    const radiosTueste = document.querySelectorAll('input[name="tueste"]'); // Nuevo grupo

    const displayPrice = document.getElementById('displayPrice');
    const btnSubmit = document.getElementById('btnSubmit');
    const stockText = document.getElementById('stockText');
    const stockDot = document.querySelector('.stock-dot');
    const inputQty = document.getElementById('inputQty');

    // Función cantidad
    function updateQty(change) {
        let current = parseInt(inputQty.value);
        let newVal = current + change;
        if(newVal < 1) newVal = 1;
        inputQty.value = newVal;
    }

    // Función principal
    function updateProductState() {
        // 1. Obtener valores de los radio buttons checkeados
        const envaseVal = document.querySelector('input[name="envase"]:checked')?.value;
        const moliendaVal = document.querySelector('input[name="molienda"]:checked')?.value;
        const tuesteVal = document.querySelector('input[name="tueste"]:checked')?.value; // Nuevo valor

        // 2. Buscar variante (comparación segura en minúsculas)
        const found = variantes.find(v => 
            v.envase.toLowerCase() === envaseVal.toLowerCase() && 
            v.molienda.toLowerCase() === moliendaVal.toLowerCase() && 
            v.tueste.toLowerCase() === tuesteVal.toLowerCase()
        );

        // 3. Actualizar UI
        if (found) {
            displayPrice.textContent = parseFloat(found.precio).toFixed(2) + '€';            
            if (parseInt(found.stock) > 0) {
                stockText.textContent = "En Stock. Entrega gratuita estimada en 24h.";
                stockDot.style.backgroundColor = "#27AE60"; // Verde
                btnSubmit.disabled = false;
                btnSubmit.textContent = "Añadir a la cesta";
                btnSubmit.classList.remove('disabled');
            } else {
                stockText.textContent = "Agotado temporalmente.";
                stockDot.style.backgroundColor = "#e74c3c"; // Rojo
                btnSubmit.disabled = true;
                btnSubmit.textContent = "Sin Stock";
                btnSubmit.classList.add('disabled');
            }
        } else {
            displayPrice.textContent = "-- €";
            stockText.textContent = "Combinación no disponible.";
            stockDot.style.backgroundColor = "#ccc"; 
            btnSubmit.disabled = true;
            btnSubmit.textContent = "No disponible";
            btnSubmit.classList.add('disabled');
        }
    }

    // Listeners para todos los grupos
    radiosEnvase.forEach(r => r.addEventListener('change', updateProductState));
    radiosMolienda.forEach(r => r.addEventListener('change', updateProductState));
    radiosTueste.forEach(r => r.addEventListener('change', updateProductState)); // Nuevo listener

    // Inicializar
    updateProductState();
    const cartModal = document.getElementById('cartModal');
const addToCartForm = document.getElementById('addToCartForm');

addToCartForm.addEventListener('submit', function(e) {
    e.preventDefault(); 

    const formData = new FormData(this);
    // Capturamos la cantidad que el usuario tiene en el selector (+ / -)
    const cantidadSeleccionada = parseInt(document.getElementById('inputQty').value) || 1;

    fetch('cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const cartCountElement = document.getElementById('cartCount');
        let totalActual = parseInt(cartCountElement.innerText) || 0;
        cartCountElement.innerText = totalActual + cantidadSeleccionada;
        mostrarResumenModal();
        const headerCount = document.getElementById('headerCartCount');
        if (headerCount) {
        let currentTotal = parseInt(headerCount.innerText) || 0;
        headerCount.innerText = currentTotal + cantidadSeleccionada;
}
    })
    .catch(error => console.error('Error:', error));
});

function mostrarResumenModal() {
    // 1. Capturar variantes
    const envase = document.querySelector('input[name="envase"]:checked').value;
    const molienda = document.querySelector('input[name="molienda"]:checked').value;
    const tueste = document.querySelector('input[name="tueste"]:checked').value;
    
    // 2. Calcular el total (Precio x Cantidad)
    const cantidad = parseInt(document.getElementById('inputQty').value) || 1;
    const precioTexto = document.getElementById('displayPrice').textContent; 
    const precioUnitario = parseFloat(precioTexto.replace('€', '').trim());

    // Calculamos el total de esta línea
    const subtotalFinal = (precioUnitario * cantidad).toFixed(2);

    // 3. Inyectar en el modal
    document.getElementById('modalVariantInfo').textContent = `Envase: ${envase} | Molienda: ${molienda} | Tueste: ${tueste}`;
    
    
    document.getElementById('modalProductPrice').textContent = `${subtotalFinal}€`;
    
    // 4. Abrir modal
    cartModal.style.display = 'block';
}

function closeModal() {
    cartModal.style.display = 'none';
}

// Cerrar si hacen clic fuera del cuadrito blanco
window.onclick = function(event) {
    if (event.target == cartModal) closeModal();
}
</script>

</body>
</html>