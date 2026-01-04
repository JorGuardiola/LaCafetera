<?php
// frontend/templates/ajax/admin-users-search.php
require_once __DIR__ . '/../../../db/connection.php';

$sql = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

foreach (['nombre', 'apellido', 'email', 'telefono'] as $field) {
    if (!empty($_GET[$field])) {
        $sql .= " AND $field LIKE ?";
        $params[] = "%" . $_GET[$field] . "%";
    }
}

if (!empty($_GET['rol'])) {
    $sql .= " AND rol = ?";
    $params[] = $_GET['rol'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

foreach ($usuarios as $u): ?>
    <tr>
        <td><?= $u['id_usuario'] ?></td>
        <td><?= htmlspecialchars($u['nombre']) ?></td>
        <td><?= htmlspecialchars($u['apellido']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['telefono']) ?></td>
        <td>
            <form action="admin.php?tab=usuarios" method="POST">
                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                <input type="hidden" name="action" value="change_user_role">
                <select name="rol" class="selector1" onchange="this.form.submit()">
                    <option value="cliente" <?= $u['rol']=='cliente'?'selected':'' ?>>Cliente</option>
                    <option value="admin" <?= $u['rol']=='admin'?'selected':'' ?>>Admin</option>
                </select>
            </form>
        </td>

        <td>
            <button class="modificar-btn" onclick="openEditUser(<?= $u['id_usuario'] ?>)">Modificar</button>

            <form action="admin.php?tab=usuarios" method="POST"  onsubmit="return confirm('¿Eliminar este usuario?')">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                <button type="submit" class="eliminar-btn" >
                Eliminar
                </button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>