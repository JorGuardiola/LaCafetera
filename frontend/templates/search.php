<?php
// frontend/templates/search.php
$origenes = ["Brasil","Burundi","Colombia","Etiopía","Guatemala","Honduras","Kenia","Nicaragua","Perú"];
$procesos = ["Lavado","Natural","Honey"];
?>

<div class="product-filter-bar" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding: 15px; background:#f9f9f9; border-radius: 8px;">

    <div class="filters-group" style="display:flex; flex-grow: 10; gap:10px; min-width: 300px;">
        
        <input type="text" id="ajax-search" placeholder="Buscar café..." 
               style="flex-grow: 2; width: 0; min-width: 100px; padding:10px; border:1px solid #ddd; border-radius:4px;">

        <select id="ajax-origin" class="filter-btn" style="flex-grow: 1; width: 0; min-width: 80px; padding:10px; border:1px solid #ddd; border-radius:4px; cursor:pointer;">
            <option value="">Origen</option>
            <?php foreach ($origenes as $o): ?>
                <option value="<?= $o ?>"><?= $o ?></option>
            <?php endforeach; ?>
        </select>

        <select id="ajax-process" class="filter-btn" style="flex-grow: 1; width: 0; min-width: 80px; padding:10px; border:1px solid #ddd; border-radius:4px; cursor:pointer;">
            <option value="">Proceso</option>
            <?php foreach ($procesos as $p): ?>
                <option value="<?= $p ?>"><?= $p ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="actions-group" style="display:flex; flex-grow: 1; gap:10px; justify-content: flex-end;">
        
        <button id="btn-sort-name" class="filter-btn" data-dir="ASC" style="padding:10px 15px; cursor:pointer; background:#fff; border:1px solid #ccc; border-radius:4px; white-space:nowrap;">
            Nombre <span id="icon-name">A-Z</span>
        </button>

        <button id="btn-sort-price" class="filter-btn" data-dir="ASC" style="padding:10px 15px; cursor:pointer; background:#fff; border:1px solid #ccc; border-radius:4px; white-space:nowrap;">
            Precio <span id="icon-price">=</span>
        </button>

        <button id="btn-clear-filters" class="boton2-btn">
            <i class="fa-solid fa-trash"></i> Borrar filtros
        </button>
    </div>

</div>