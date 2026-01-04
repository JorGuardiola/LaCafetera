<?php
// frontend/templates/search-orders-admin.php

require_once __DIR__ . '/../../db/connection.php';


$estados = ['pendiente', 'procesando', 'completado', 'cancelado'];
?>

<h4>Búsqueda de pedidos</h4>

<div class="filter-bar">
  <div class="filters-group" id="orderFilters">

    <input type="text" id="f-id" placeholder="Nº pedido" class="input1">
    <input type="date" id="f-fecha" class="input1">
    <input type="text" id="f-usuario" placeholder="Email usuario" class="input1">

    <select id="f-estado" class="selector1">
      <option value="">Todos los estados</option>
      <?php foreach ($estados as $e): ?>
        <option value="<?= $e ?>"><?= ucfirst($e) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="button" id="clearOrderFilters" class="boton3-btn">
      Limpiar
    </button>

  </div>
</div>

<div id="ordersTableContainer"></div>

<script>
(() => {

  const filters = {
    id:     document.getElementById('f-id'),
    fecha:  document.getElementById('f-fecha'),
    estado: document.getElementById('f-estado'),
    usuario:document.getElementById('f-usuario')
  };

  const container = document.getElementById('ordersTableContainer');
  const clearBtn  = document.getElementById('clearOrderFilters');

  let debounce;

  function buildQuery() {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, el]) => {
      if (el.value.trim() !== '') {
        params.append(key, el.value.trim());
      }
    });
    return params.toString();
  }

  async function loadOrders() {
    const url = "<?= BASE_URL ?>/frontend/templates/ajax/admin-orders-search.php?" + buildQuery();

    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      container.innerHTML = await res.text();
    } catch (err) {
      console.error(err);
      container.innerHTML = '<p>Error cargando pedidos</p>';
    }
  }

  function debounceLoad() {
    clearTimeout(debounce);
    debounce = setTimeout(loadOrders, 300);
  }

  Object.values(filters).forEach(el => {
    el.addEventListener('input', debounceLoad);
    el.addEventListener('change', loadOrders);
  });

  clearBtn.addEventListener('click', () => {
    Object.values(filters).forEach(el => el.value = '');
    loadOrders();
  });

  // carga inicial
  loadOrders();

})();
</script>
