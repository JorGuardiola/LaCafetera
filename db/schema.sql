<?php
// frontend/admin.php
session_start();
require_once __DIR__ . '/../db/connection.php';

/* ======================================================
   1. SEGURIDAD – SOLO ADMIN
====================================================== */
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/frontend/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['user_id']]);
$rol = $stmt->fetchColumn();

if ($rol !== 'admin') {
    header('Location: ' . BASE_URL . '/frontend/profile.php');
    exit;
}

$mensaje = '';
$tab_activa = 'usuarios';

/* ======================================================
   2. ACCIONES POST
====================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Cambiar rol usuario */
    if ($_POST['action'] === 'change_role') {
        $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?")
            ->execute([$_POST['rol'], (int)$_POST['id_usuario']]);
        $mensaje = 'Rol actualizado';
        $tab_activa = 'usuarios';
    }

    /* Eliminar usuario */
    if ($_POST['action'] === 'delete_user') {
        $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?")
            ->execute([(int)$_POST['id_usuario']]);
        $mensaje = 'Usuario eliminado';
        $tab_activa = 'usuarios';
    }

    /* Eliminar producto */
    if ($_POST['action'] === 'delete_product') {
        $pdo->prepare("DELETE FROM productos WHERE id = ?")
            ->execute([(int)$_POST['id']]);
        $mensaje = 'Producto eliminado';
        $tab_activa = 'productos';
    }

    /* Cambiar estado pedido */
    if ($_POST['action'] === 'update_order_status') {
        $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_orden = ?")
            ->execute([$_POST['estado'], (int)$_POST['id_orden']]);
        $mensaje = 'Estado del pedido actualizado';
        $tab_activa = 'pedidos';
    }
}

/* ======================================================
   3. CONSULTAS
====================================================== */

/* Usuarios */
$usuarios = $pdo->query("
    SELECT id_usuario, email, rol, fecha_registro
    FROM usuarios
    ORDER BY fecha_registro DESC
")->fetchAll();

/* Productos (precio desde variantes) */
$productos = $pdo->query("
    SELECT 
        p.id,
        p.nombre_cafe,
        MIN(v.precio) AS precio_min,
        SUM(v.stock) AS stock_total
    FROM productos p
    LEFT JOIN producto_variantes v ON v.producto_id = p.id
    GROUP BY p.id
    ORDER BY p.id DESC
")->fetchAll();

/* Pedidos */
$pedidos = $pdo->query("
    SELECT p.*, u.email
    FROM pedidos p
    JOIN usuarios u ON u.id_usuario = p.id_usuario
    ORDER BY p.fecha_orden DESC
")->fetchAll();
?>

<?php include __DIR__ . '/templates/header.php'; ?>

<div class="container profile-container">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h1>Panel de Administración</h1>
        <?php if ($mensaje): ?>
            <div style="background:#e8f5e9; color:#2e7d32; padding:1rem; border-radius:8px;">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-layout">

        <!-- SIDEBAR -->
        <aside class="profile-sidebar">
            <button class="profile-menu-btn <?= $tab_activa==='usuarios'?'active':'' ?>" onclick="openTab('usuarios')">Usuarios</button>
            <button class="profile-menu-btn <?= $tab_activa==='productos'?'active':'' ?>" onclick="openTab('productos')">Productos</button>
            <button class="profile-menu-btn <?= $tab_activa==='pedidos'?'active':'' ?>" onclick="openTab('pedidos')">Pedidos</button>
            <a href="profile.php" class="profile-menu-btn">Volver a mi cuenta</a>
        </aside>

        <!-- CONTENIDO -->
        <div class="profile-content">

            <!-- ================= USUARIOS ================= -->
            <div id="usuarios" class="profile-content-section <?= $tab_activa==='usuarios'?'active':'' ?>">
                <h2 class="section-title">Gestión de Usuarios</h2>

                <table class="orders-table">
                    <thead>
                        <tr><th>ID</th><th>Email</th><th>Rol</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                    <select name="rol" onchange="this.form.submit()">
                                        <option value="cliente" <?= $u['rol']==='cliente'?'selected':'' ?>>Cliente</option>
                                        <option value="admin" <?= $u['rol']==='admin'?'selected':'' ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php if ($u['rol'] !== 'admin'): ?>
                                <form method="POST" onsubmit="return confirm('¿Eliminar usuario?')">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                                    <button style="color:red;background:none;border:none">Eliminar</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ================= PRODUCTOS ================= -->
            <div id="productos" class="profile-content-section <?= $tab_activa==='productos'?'active':'' ?>">
                <h2 class="section-title">Gestión de Productos</h2>

                <table class="orders-table">
                    <thead>
                        <tr><th>ID</th><th>Producto</th><th>Precio</th><th>Stock</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['nombre_cafe']) ?></td>
                            <td>Desde <?= number_format($p['precio_min'], 2) ?> €</td>
                            <td><?= (int)$p['stock_total'] ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('¿Eliminar producto?')">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button style="color:red;background:none;border:none">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ================= PEDIDOS ================= -->
            <div id="pedidos" class="profile-content-section <?= $tab_activa==='pedidos'?'active':'' ?>">
                <h2 class="section-title">Gestión de Pedidos</h2>

                <table class="orders-table">
                    <thead>
                        <tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $o): ?>
                        <tr>
                            <td>#<?= $o['id_orden'] ?></td>
                            <td><?= htmlspecialchars($o['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($o['fecha_orden'])) ?></td>
                            <td><?= number_format($o['total'], 2) ?> €</td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <input type="hidden" name="id_orden" value="<?= $o['id_orden'] ?>">
                                    <select name="estado" onchange="this.form.submit()">
                                        <?php foreach (['pendiente','pagado','preparando','enviado','entregado','cancelado'] as $estado): ?>
                                            <option value="<?= $estado ?>" <?= $o['estado']===$estado?'selected':'' ?>>
                                                <?= ucfirst($estado) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
function openTab(tabId) {
    document.querySelectorAll('.profile-content-section').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.profile-menu-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
