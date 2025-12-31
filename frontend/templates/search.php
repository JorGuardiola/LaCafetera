<?php
// frontend/templates/search.php
$origenes = ["Brasil","Burundi","Colombia","Etiopía","Guatemala","Honduras","Kenia","Nicaragua","Perú"];
$procesos = ["Lavado","Natural","Honey"];
?>

<div class="filter-bar">

    <div class="filters-group">
        
        <input type="text" id="ajax-search" placeholder="Buscar café..." class="input1" / 
               >

        <select id="ajax-origin" class="selector1">
            <option value="">Origen</option>
            <?php foreach ($origenes as $o): ?>
                <option value="<?= $o ?>"><?= $o ?></option>
            <?php endforeach; ?>
        </select>

        <select id="ajax-process" class="selector1">
            <option value="">Proceso</option>
            <?php foreach ($procesos as $p): ?>
                <option value="<?= $p ?>"><?= $p ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="actions-group" style="display:flex; flex-grow: 1; gap:10px; justify-content: flex-end;">
        
        <button id="btn-sort-name" class="boton3-btn" data-dir="ASC">
            Nombre <span id="icon-name">A-Z</span>
        </button>

        <button id="btn-sort-price" class="boton3-btn" data-dir="ASC">
            Precio <span id="icon-price">=</span>
        </button>

        <button id="btn-clear-filters" class="boton2-btn">
            <i class="fa-solid fa-trash"></i> Borrar filtros
        </button>
    </div>

</div>