<?php
require_once __DIR__ . '/../../../db/connection.php';

$id     = $_GET['id'] ?? '';
$user   = $_GET['usuario'] ?? '';
$estado = $_GET['estado'] ?? '';
$fecha  = $_GET['fecha'] ?? '';
$email = $_GET['email'] ?? '';

// Usamos id_orden y fecha_orden según tu captura
$sql = "SELECT o.id_orden, o.total, o.estado, o.fecha_orden, u.nombre, u.apellido, u.email 
        FROM pedidos o
        INNER JOIN usuarios u ON o.id_usuario = u.id_usuario 
        WHERE 1=1";

$params = [];

if ($id) {
    $sql .= " AND o.id_orden = ?";
    $params[] = $id;
}
if ($user) {
    $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ?)";
    $busqueda = "%$user%";
    $params[] = $busqueda;
    $params[] = $busqueda;
    $params[] = $busqueda;
}
if ($estado) {
    $sql .= " AND o.estado = ?";
    $params[] = $estado;
}
if ($fecha) {
    $sql .= " AND DATE(o.fecha_orden) = ?";
    $params[] = $fecha;
}
if (!empty($email)) {
    $sql .= " AND u.email LIKE ?";
    $params[] = "%$email%";
}

$sql .= " ORDER BY o.fecha_orden DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$pedidos) {
    echo '<tr><td colspan="6" style="text-align:center;">No se encontraron pedidos.</td></tr>';
    exit;
}

foreach ($pedidos as $p): ?>
    <tr>
        <td data-label="ID">#<?= $p['id_orden'] ?></td>
        <td data-label="Nombre"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></td>
        <td data-label="Email"><?= htmlspecialchars($p['email']) ?></td>
        <td data-label="Total"><?= number_format($p['total'], 2) ?>€</td>
        <td data-label="Estado">
            <form method="POST" action="admin.php?tab=pedidos">
                <input type="hidden" name="action" value="update_order_status">
                <input type="hidden" name="id_orden" value="<?= $p['id_orden'] ?>">
                <select name="nuevo_estado" onchange="this.form.submit()" class="selector1" style="padding: 2px;">
                    <?php 
                    $estados = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];
                    foreach ($estados as $e): ?>
                        <option value="<?= $e ?>" <?= $p['estado'] == $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </td>
        <td data-label="Fecha pedido"><?= date('d/m/Y H:i', strtotime($p['fecha_orden'])) ?></td>
        <td data-label="Acciones">
            <a href="success.php?orden=<?= $p['id_orden'] ?>" class="modificar-btn" style="text-decoration:none;">Ver Detalle</a>
        </td>
    </tr>
<?php endforeach; ?>