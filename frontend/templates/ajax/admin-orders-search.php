<?php
// frontend/templates/ajax/admin-orders-search.php

require_once __DIR__ . '/../../../db/connection.php';

/* =========================
   FILTROS (coinciden con JS)
========================= */

$where = [];
$params = [];

if (!empty($_GET['id'])) {
    $where[] = "p.id_orden = ?";
    $params[] = (int)$_GET['id'];
}

if (!empty($_GET['fecha'])) {
    $where[] = "DATE(p.fecha_orden) = ?";
    $params[] = $_GET['fecha'];
}

if (!empty($_GET['estado'])) {
    $where[] = "p.estado = ?";
    $params[] = $_GET['estado'];
}

if (!empty($_GET['usuario'])) {
    $where[] = "u.email LIKE ?";
    $params[] = '%' . $_GET['usuario'] . '%';
}

/* =========================
   QUERY
========================= */

$sql = "
    SELECT
        p.id_orden,
        p.fecha_orden,
        p.total,
        p.estado,
        u.email
    FROM pedidos p
    JOIN usuarios u ON u.id_usuario = p.id_usuario
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.fecha_orden DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table class="orders-table">
  <thead>
    <tr>
      <th>ID Pedido</th>
      <th>Usuario</th>
      <th>Fecha</th>
      <th>Total (€)</th>
      <th>Estado</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$pedidos): ?>
      <tr>
        <td colspan="6">No se encontraron pedidos</td>
      </tr>
    <?php else: ?>
      <?php foreach ($pedidos as $o): ?>
        <tr>
          <td><?= (int)$o['id_orden'] ?></td>
          <td><?= htmlspecialchars($o['email']) ?></td>
          <td><?= htmlspecialchars($o['fecha_orden']) ?></td>
          <td><?= number_format($o['total'], 2) ?> €</td>
          <td>
            <form method="POST">
              <input type="hidden" name="action" value="update_order_status">
              <input type="hidden" name="id_orden" value="<?= (int)$o['id_orden'] ?>">
              <select name="estado" onchange="this.form.submit()" class="selector1">
                <?php foreach (['pendiente','procesando','completado','cancelado'] as $e): ?>
                  <option value="<?= $e ?>" <?= $o['estado']===$e?'selected':'' ?>>
                    <?= ucfirst($e) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td>
            <a href="order-details.php?id=<?= (int)$o['id_orden'] ?>" class="modificar-btn">
              Ver
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
