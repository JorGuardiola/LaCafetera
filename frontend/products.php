<?php
// frontend/products.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Datos del hero
$bgClass = "bg-productos";
$heroTitle = "Nuestros productos";
$heroSubtitle = "Nuestra Selección reúne cafés de especialidad de fincas únicas...";
$heroButtonText = "";
$heroButtonLink = "";

// Carga inicial simple (solo los primeros 12 para que no salga vacío al entrar)
// Usamos una subconsulta para sacar el precio de '250g' y mostrarlo correctamente en la tarjeta inicial
$sqlInicial = "SELECT p.*, 
               (SELECT precio FROM producto_variantes pv WHERE pv.producto_id = p.id AND pv.envase = '250g' LIMIT 1) as precio
               FROM productos p WHERE p.disponible = 1 ORDER BY p.id ASC LIMIT 12";
$stmt = $pdo->query($sqlInicial);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/templates/header.php'; ?>

<?php include __DIR__ . '/templates/hero.php'; ?>

<main> 
    <div class="profile-content">
        <?php include __DIR__ . '/templates/search.php'; ?>

        <section class="product-grid" id="results-container">
            <?php if ($productos): ?>
                <?php foreach ($productos as $p): ?>
                    <?php include __DIR__ . '/templates/card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; width:100%;">No hay productos disponibles.</p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include __DIR__ . "/templates/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // ------------------------------------------------------
    // 1. VARIABLES DE ESTADO
    // ------------------------------------------------------
    let currentSort = 'id';   // Por defecto ordenamos por ID
    let currentDir = 'ASC';   // Por defecto Ascendente

    // ------------------------------------------------------
    // 2. REFERENCIAS AL DOM (Elementos de la pantalla)
    // ------------------------------------------------------
    // Inputs y Selects (Deben coincidir con los ID de search.php)
    const inputSearch = document.getElementById('ajax-search');
    const selectOrigin = document.getElementById('ajax-origin');
    const selectProcess = document.getElementById('ajax-process');
    
    // Contenedor donde se pintarán los resultados
    const container = document.getElementById('results-container');
    
    // Botones de acción
    const btnName = document.getElementById('btn-sort-name');
    const btnPrice = document.getElementById('btn-sort-price');
    const btnClear = document.getElementById('btn-clear-filters');

    // ------------------------------------------------------
    // 3. FUNCIÓN PRINCIPAL DE CARGA (AJAX)
    // ------------------------------------------------------
    function loadProducts() {
        // Recogemos los valores actuales (si existen)
        const q = inputSearch ? inputSearch.value : '';
        const origin = selectOrigin ? selectOrigin.value : '';
        const process = selectProcess ? selectProcess.value : '';

        // Añadimos opacidad para indicar "cargando"
        if(container) container.style.opacity = '0.5';

        // Construimos la URL con todos los parámetros
        const url = `ajax_products.php?q=${encodeURIComponent(q)}&origen=${encodeURIComponent(origin)}&proceso=${encodeURIComponent(process)}&sort=${currentSort}&dir=${currentDir}`;

        // Hacemos la petición
        fetch(url)
            .then(response => response.text())
            .then(html => {
                if(container) {
                    container.innerHTML = html;   // Pintamos las nuevas cards
                    container.style.opacity = '1'; // Quitamos opacidad
                }
            })
            .catch(error => console.error('Error cargando productos:', error));
    }

    // ------------------------------------------------------
    // 4. EVENTOS (Interactividad)
    // ------------------------------------------------------

    // A) BUSCADOR DE TEXTO (con retardo para no saturar)
    let timeout;
    if(inputSearch){
        inputSearch.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(loadProducts, 300); // Espera 300ms antes de buscar
        });
    }

    // B) SELECTORES (Origen y Proceso)
    if(selectOrigin) selectOrigin.addEventListener('change', loadProducts);
    if(selectProcess) selectProcess.addEventListener('change', loadProducts);

    // C) BOTÓN ORDENAR POR NOMBRE
    if(btnName){
        btnName.addEventListener('click', () => {
            // Si ya estábamos ordenando por nombre, invertimos la dirección
            if (currentSort === 'nombre_cafe') {
                currentDir = (currentDir === 'ASC') ? 'DESC' : 'ASC';
            } else {
                // Si veníamos de otro filtro, empezamos por A-Z
                currentSort = 'nombre_cafe';
                currentDir = 'ASC';
            }
            
            // Actualizamos visualmente el texto del botón
            const span = btnName.querySelector('span');
            if(span) span.innerText = (currentDir === 'ASC') ? 'A-Z' : 'Z-A';
            
            // Reseteamos el texto del otro botón (Precio) para no confundir
            if(btnPrice) btnPrice.querySelector('span').innerText = '='; 

            loadProducts();
        });
    }

    // D) BOTÓN ORDENAR POR PRECIO (CORREGIDO)
    if(btnPrice){
        btnPrice.addEventListener('click', () => {
            // Si ya estábamos ordenando por precio, invertimos
            if (currentSort === 'precio') {
                currentDir = (currentDir === 'ASC') ? 'DESC' : 'ASC';
            } else {
                // Si es la primera vez que clicamos precio, empezamos por barato (Low)
                currentSort = 'precio'; 
                currentDir = 'ASC';
            }
            
            // Actualizamos visualmente el texto
            const span = btnPrice.querySelector('span');
            if(span) span.innerText = (currentDir === 'ASC') ? '⬇' : '⬆';
            
            // Reseteamos el texto del otro botón (Nombre)
            if(btnName) btnName.querySelector('span').innerText = 'A-Z';

            loadProducts();
        });
    }

    // E) BOTÓN BORRAR FILTROS
    if(btnClear){
        btnClear.addEventListener('click', () => {
            // 1. Limpiar inputs visualmente
            if(inputSearch) inputSearch.value = '';
            if(selectOrigin) selectOrigin.value = '';
            if(selectProcess) selectProcess.value = '';
            
            // 2. Resetear variables lógicas a su estado inicial
            currentSort = 'id';
            currentDir = 'ASC';

            // 3. Resetear textos de los botones de orden
            if(btnName && btnName.querySelector('span')) 
                btnName.querySelector('span').innerText = 'A-Z';
            
            if(btnPrice && btnPrice.querySelector('span')) 
                btnPrice.querySelector('span').innerText = '=';

            // 4. Recargar productos limpios
            loadProducts();
        });
    }
});
</script>