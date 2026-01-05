<?php
// frontend/templates/search-orders-admin.php
// Obtenemos los estados posibles para el selector
$estados = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];
?>


<div class="filter-bar">
    <input type="text" id="o-id" placeholder="N° pedido" class="input1">
    <input type="text" id="o-usuario" placeholder="Nombre/Apellido" class="input1">
    <input type="text" id="o-email" placeholder="Email" class="input1"> 
    <select id="o-estado" class="selector1">
        <option value="">Estado</option>
        <?php foreach ($estados as $est): ?>
            <option value="<?= $est ?>"><?= ucfirst($est) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" id="o-fecha" class="input1">
    <div></div> <button type="button" id="clearOrderFilters" class="boton3-btn">Limpiar</button>
</div>

<table class="orders-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Usuario</th>
            <th>Email</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="ordersTableContainer">
        </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const oContainer = document.getElementById('ordersTableContainer');
    const oFilters = ['o-id', 'o-usuario', 'o-estado', 'o-fecha'];

    async function loadOrders() {
        if (!oContainer) return;

        const params = new URLSearchParams();
        oFilters.forEach(id => {
            const el = document.getElementById(id);
            if (el && el.value) {
                params.append(id.replace('o-', ''), el.value);
            }
        });

        try {
            const response = await fetch('templates/ajax/admin-orders-search.php?' + params.toString());
            const text = await response.text();
            oContainer.innerHTML = text;
        } catch (error) {
            console.error('Error al cargar pedidos:', error);
            oContainer.innerHTML = '<tr><td colspan="6">Error al cargar pedidos</td></tr>';
        }
    }

    // Eventos
    oFilters.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', loadOrders);
            el.addEventListener('change', loadOrders);
        }
    });

    document.getElementById('clearOrderFilters').addEventListener('click', () => {
        oFilters.forEach(id => document.getElementById(id).value = '');
        loadOrders();
    });

    loadOrders(); // Carga inicial
});
</script>