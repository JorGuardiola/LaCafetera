<?php
// frontend/templates/search-admin.php

// Valores únicos para el render inicial (fallback sin JS)
$moliendas = $pdo->query("
    SELECT DISTINCT molienda
    FROM producto_variantes
    WHERE molienda IS NOT NULL AND molienda != ''
    ORDER BY molienda
")->fetchAll(PDO::FETCH_COLUMN);

$tuestes = $pdo->query("
    SELECT DISTINCT tueste
    FROM producto_variantes
    WHERE tueste IS NOT NULL AND tueste != ''
    ORDER BY tueste
")->fetchAll(PDO::FETCH_COLUMN);

$envases = $pdo->query("
    SELECT DISTINCT envase
    FROM producto_variantes
    WHERE envase IS NOT NULL AND envase != ''
    ORDER BY envase
")->fetchAll(PDO::FETCH_COLUMN);
?>
<h4>Busqueda de productos</h4>
<form method="GET"
      id="adminSearchForm"
      class="admin-search-bar"
      style="margin-bottom:1rem;display:flex;gap:1rem;align-items:center";background: #FAF7F2; padding: 1rem; border-radius: 8px;>

    <input
        type="text"
        name="q"
        placeholder="Escriba SKU"
        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
    >

    <select name="molienda" id="filter-molienda">
        <option value="">Elija moliendas</option>
        <?php foreach ($moliendas as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>"
                <?= (($_GET['molienda'] ?? '') === $m) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="tueste" id="filter-tueste">
        <option value="">Elija tuestes</option>
        <?php foreach ($tuestes as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>"
                <?= (($_GET['tueste'] ?? '') === $t) ? 'selected' : '' ?>>
                <?= htmlspecialchars($t) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="envase" id="filter-envase">
        <option value="">Elija envases</option>
        <?php foreach ($envases as $e): ?>
            <option value="<?= htmlspecialchars($e) ?>"
                <?= (($_GET['envase'] ?? '') === $e) ? 'selected' : '' ?>>
                <?= htmlspecialchars($e) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="hidden" name="tab" value="productos">

    <button type="submit"
        style="padding:8px 16px;border-radius:6px;background:#1A1A1A;color:#fff;border:none">
        Filtrar
    </button>

    <a href="admin.php?tab=productos"
       style="padding:8px 14px;border-radius:6px;border:1px solid #ccc;text-decoration:none">
        Limpiar
    </a>
</form>

<script>
(() => {
  const moliendaSelect = document.getElementById('filter-molienda');
  const tuesteSelect   = document.getElementById('filter-tueste');
  const envaseSelect   = document.getElementById('filter-envase');

  // Si esto falla, no existen los IDs (y por eso nunca había peticiones)
  if (!moliendaSelect || !tuesteSelect || !envaseSelect) {
    console.warn('Filtros admin: no se encontraron selects (IDs).');
    return;
  }

  function updateSelect(select, values, placeholder) {
    const current = select.value;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    (values || []).forEach(v => {
      const opt = document.createElement('option');
      opt.value = v;
      opt.textContent = v;
      if (v === current) opt.selected = true;
      select.appendChild(opt);
    });
  }

  async function fetchOptions() {
    const params = new URLSearchParams({
      molienda: moliendaSelect.value,
      tueste: tuesteSelect.value
    });

    const url = "<?= BASE_URL ?>/frontend/templates/ajax/admin-filters.php?" + params.toString();
    // DEBUG útil: deberías ver esto en consola y en Network
    console.log('Fetching:', url);

    const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return await res.json();
  }

  async function refreshFromMolienda() {
    try {
      tuesteSelect.value = '';
      envaseSelect.value = '';
      const data = await fetchOptions();
      updateSelect(tuesteSelect, data.tuestes, 'Todos los tuestes');
      updateSelect(envaseSelect, data.envases, 'Todos los envases');
    } catch (e) {
      console.error('Error filtros (molienda):', e);
    }
  }

  async function refreshFromTueste() {
    try {
      envaseSelect.value = '';
      const data = await fetchOptions();
      updateSelect(envaseSelect, data.envases, 'Todos los envases');
    } catch (e) {
      console.error('Error filtros (tueste):', e);
    }
  }

  moliendaSelect.addEventListener('change', refreshFromMolienda);
  tuesteSelect.addEventListener('change', refreshFromTueste);
})();
</script>
