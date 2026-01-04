<?php
// frontend/templates/search-products-admin.php

// 1. Obtenemos datos para los selectores usando la conexión $pdo existente
try {
    $moliendas = $pdo->query("SELECT DISTINCT molienda FROM producto_variantes WHERE molienda IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    $tuestes   = $pdo->query("SELECT DISTINCT tueste FROM producto_variantes WHERE tueste IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Si falla, inicializamos como arrays vacíos para que no rompa el HTML
    $moliendas = [];
    $tuestes = [];
}
?>

<div class="filter-bar" style="display:grid; grid-template-columns: repeat(4, 1fr) auto; gap:10px; margin-bottom:20px;">
    <input type="text" id="p-nombre" placeholder="Nombre del café" class="input1">
    
    <select id="p-molienda" class="selector1">
        <option value="">Todas las moliendas</option>
        <?php foreach ($moliendas as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
        <?php endforeach; ?>
    </select>

    <select id="p-tueste" class="selector1">
        <option value="">Todos los tuestes</option>
        <?php foreach ($tuestes as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" id="p-sku" placeholder="SKU" class="input1">
    
    <button type="button" id="clearProductFilters" class="boton3-btn">Limpiar</button>
</div>

<table class="orders-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>SKU</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Molienda/Tueste</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="productsTableContainer">
        </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pContainer = document.getElementById('productsTableContainer');
    // IDs de los inputs de filtro
    const pFilters = ['p-nombre', 'p-molienda', 'p-tueste', 'p-sku'];

    async function loadProducts() {
        if (!pContainer) return;

        const params = new URLSearchParams();
        pFilters.forEach(id => {
            const el = document.getElementById(id);
            if (el && el.value) {
                // Quitamos el prefijo 'p-' para enviarlo al servidor
                params.append(id.replace('p-', ''), el.value);
            }
        });

        try {
            // Llamamos al archivo AJAX de productos
            const response = await fetch('templates/ajax/admin-products-search.php?' + params.toString());
            const text = await response.text();
            pContainer.innerHTML = text;
        } catch (error) {
            console.error('Error al cargar productos:', error);
            pContainer.innerHTML = '<tr><td colspan="7">Error al cargar productos</td></tr>';
        }
    }

    // Escuchar cambios en los filtros
    pFilters.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', loadProducts);
            el.addEventListener('change', loadProducts); // Para los selectores
        }
    });

    // Botón de limpiar
    const btnClear = document.getElementById('clearProductFilters');
    if (btnClear) {
        btnClear.addEventListener('click', () => {
            pFilters.forEach(id => document.getElementById(id).value = '');
            loadProducts();
        });
    }

    // Carga inicial al entrar en la pestaña
    loadProducts();
});
</script>