<?php
// frontend/templates/search-users-admin.php
$roles = $pdo->query("SELECT DISTINCT rol FROM usuarios WHERE rol != ''")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="filter-bar">
    <input type="text" id="f-nombre" placeholder="Nombre" class="input1">
    <input type="text" id="f-apellido" placeholder="Apellido" class="input1">
    <input type="text" id="f-email" placeholder="Email" class="input1">
    <input type="text" id="f-telefono" placeholder="Teléfono" class="input1">
    <select id="f-rol" class="selector1">
        <option value="">Todos los roles</option>
        <?php foreach ($roles as $rol): ?>
            <option value="<?= htmlspecialchars($rol) ?>"><?= htmlspecialchars($rol) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="button" id="clearUserFilters" class="boton3-btn">Limpiar</button>
</div>
<table class="orders-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="usersTableContainer">
        
    </tbody>
</table>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('usersTableContainer');
    const filters = ['f-nombre', 'f-apellido', 'f-email', 'f-telefono', 'f-rol'];

    async function loadUsers() {
        if (!container) return; // Evita el error de "null"

        const params = new URLSearchParams();
        filters.forEach(id => {
            const el = document.getElementById(id);
            if (el.value) params.append(id.replace('f-', ''), el.value);
        });

        try {
            // RUTA SEGÚN TU IMAGEN: templates/ajax/admin-users-search.php
            const response = await fetch('templates/ajax/admin-users-search.php?' + params.toString());
            const text = await response.text();
            container.innerHTML = text;
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = '<tr><td colspan="7">Error al cargar datos</td></tr>';
        }
    }

    filters.forEach(id => document.getElementById(id).addEventListener('input', loadUsers));
    document.getElementById('clearUserFilters').addEventListener('click', () => {
        filters.forEach(id => document.getElementById(id).value = '');
        loadUsers();
    });

    loadUsers(); // Carga inicial
});
</script>