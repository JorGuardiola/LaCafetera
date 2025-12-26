<?php
// frontend/profile.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// 1. SEGURIDAD
if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . '/frontend/login.php'); exit; }
$user_id = $_SESSION['user_id'];
$mensaje = '';
$tab_activa = $_GET['tab'] ?? 'datos';

// 2. LÓGICA POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTUALIZAR DATOS USUARIO (Incluye Teléfono)
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $telefono = trim($_POST['telefono']);
        
        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, telefono = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nombre, $apellido, $telefono, $user_id])) {
            $mensaje = "Datos actualizados.";
        }
    }

    // AÑADIR DIRECCIÓN (SIMPLIFICADO)
    if (isset($_POST['action']) && $_POST['action'] === 'add_address') {
        $dir = trim($_POST['direccion']);
        $ciudad = trim($_POST['ciudad']);
        $prov = trim($_POST['provincia']);
        $cp = trim($_POST['codigo_postal']);
        $pais = trim($_POST['pais']);

        // Insertamos solo en los campos originales
        $sql = "INSERT INTO direcciones (id_usuario, direccion, ciudad, provincia, pais, codigo_postal) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $dir, $ciudad, $prov, $pais, $cp]);
        $mensaje = "Dirección añadida.";
        $tab_activa = 'direcciones';
    }

    // BORRAR DIRECCIÓN
    if (isset($_POST['action']) && $_POST['action'] === 'delete_address') {
        $id_dir = (int)$_POST['id_direccion'];
        $pdo->prepare("DELETE FROM direcciones WHERE id_direccion = ? AND id_usuario = ?")->execute([$id_dir, $user_id]);
        $mensaje = "Dirección eliminada.";
        $tab_activa = 'direcciones';
    }

    // MARCAR PREDETERMINADA
    if (isset($_POST['action']) && $_POST['action'] === 'set_default') {
        $id_dir = (int)$_POST['id_direccion'];
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE direcciones SET predeterminada = 0 WHERE id_usuario = ?")->execute([$user_id]);
        $pdo->prepare("UPDATE direcciones SET predeterminada = 1 WHERE id_direccion = ? AND id_usuario = ?")->execute([$id_dir, $user_id]);
        $pdo->commit();
        $tab_activa = 'direcciones';
    }
}

// 3. CONSULTAS
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM direcciones WHERE id_usuario = ? ORDER BY predeterminada DESC, id_direccion DESC");
$stmt->execute([$user_id]);
$mis_direcciones = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_orden DESC");
$stmt->execute([$user_id]);
$mis_pedidos = $stmt->fetchAll();
?>

<?php include __DIR__ . '/templates/header.php'; ?>

<div class="container profile-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
        <h1>
            Mi cuenta
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <span>Administrador</span>
            <?php endif; ?>
        </h1>
    </div>

    <div class="profile-layout">
        <aside class="profile-sidebar">

            <button class="profile-menu-btn <?= $tab_activa === 'datos' ? 'active' : '' ?>"
                    onclick="openTab('datos')">
                Mis datos
            </button>

            <button class="profile-menu-btn <?= $tab_activa === 'direcciones' ? 'active' : '' ?>"
                    onclick="openTab('direcciones')">
                Mis direcciones
            </button>

            <button class="profile-menu-btn <?= $tab_activa === 'pedidos' ? 'active' : '' ?>"
                    onclick="openTab('pedidos')">
                Mis pedidos
            </button>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>/frontend/admin.php"
                   class="profile-menu-btn admin-btn">
                    Panel de administración
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/frontend/logout.php"
               class="profile-menu-btn btn-logout">
                Cerrar sesión
            </a>

        </aside>

        <div class="profile-content">
            <div id="datos" class="profile-content-section <?= $tab_activa == 'datos' ? 'active' : '' ?>">
                <h2 class="section-title">Datos Personales</h2>
                <form method="POST" style="background:#fff; padding:2rem; border:1px solid #eee; border-radius:8px;">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label>Nombre</label> <input type="text" name="nombre" class="form-input" value="<?= htmlspecialchars($user_data['nombre']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Apellidos</label> <input type="text" name="apellido" class="form-input" value="<?= htmlspecialchars($user_data['apellido']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label> <input type="tel" name="telefono" class="form-input" value="<?= htmlspecialchars($user_data['telefono']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label> <input type="email" class="form-input" value="<?= htmlspecialchars($user_data['email']) ?>" disabled style="background:#f9f9f9; color:#999;">
                    </div>
                    <button type="submit" class="boton-negro" style="border:none; margin-top:1rem;">Guardar</button>
                </form>
            </div>

            <div id="direcciones" class="profile-content-section <?= $tab_activa == 'direcciones' ? 'active' : '' ?>">
                <h2 class="section-title">Mis Direcciones</h2>
                <div class="address-grid">
                    <?php foreach($mis_direcciones as $dir): ?>
                        <div class="address-card <?= $dir['predeterminada'] ? 'default' : '' ?>">
                            <?php if($dir['predeterminada']): ?><span class="badge-default">Predeterminada</span><?php endif; ?>
                            
                            <p><strong><?= htmlspecialchars($dir['direccion']) ?></strong></p>
                            <p><?= htmlspecialchars($dir['codigo_postal'] . ', ' . $dir['ciudad']) ?></p>
                            <p><?= htmlspecialchars($dir['provincia'] . ' (' . $dir['pais'] . ')') ?></p>

                            <div class="address-actions">
                                <?php if(!$dir['predeterminada']): ?>
                                    <form method="POST"><input type="hidden" name="action" value="set_default"><input type="hidden" name="id_direccion" value="<?= $dir['id_direccion'] ?>"><button type="submit" style="background:none; border:none; cursor:pointer; text-decoration:underline;">Predeterminada</button></form>
                                <?php endif; ?>
                                <form method="POST" onsubmit="return confirm('¿Borrar?');"><input type="hidden" name="action" value="delete_address"><input type="hidden" name="id_direccion" value="<?= $dir['id_direccion'] ?>"><button type="submit" style="background:none; border:none; cursor:pointer; color:red;">Eliminar</button></form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 style="margin-top:3rem;">Añadir nueva dirección</h3>
                <form method="POST" style="background:#f9f9f9; padding:2rem; border-radius:8px; margin-top:1rem;">
                    <input type="hidden" name="action" value="add_address">
                    <?php include __DIR__ . '/templates/address_form.php'; ?>
                    <button type="submit" class="boton-negro" style="border:none; margin-top:1.5rem;">Guardar Dirección</button>
                </form>
            </div>

            <div id="pedidos" class="profile-content-section <?= $tab_activa == 'pedidos' ? 'active' : '' ?>">
                <h2 class="section-title">Historial de Pedidos</h2>
                <?php if(empty($mis_pedidos)): ?><p>No hay pedidos.</p><?php else: ?>
                    <table class="orders-table">
                        <thead><tr><th>Nº</th><th>Fecha</th><th>Total</th><th>Estado</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach($mis_pedidos as $p): ?>
                                <tr>
                                    <td>#<?= $p['id_orden'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_orden'])) ?></td>
                                    <td><?= number_format($p['total'], 2) ?>€</td>
                                    <td><?= ucfirst($p['estado']) ?></td>
                                    <td><a href="success.php?orden=<?= $p['id_orden'] ?>">Ver</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function openTab(tabId) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);

        document.querySelectorAll('.profile-content-section')
            .forEach(el => el.classList.remove('active'));

        document.querySelectorAll('.profile-menu-btn')
            .forEach(el => el.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');

        document.querySelectorAll('.profile-menu-btn').forEach(btn => {
            if (btn.getAttribute('onclick')?.includes(tabId)) {
                btn.classList.add('active');
            }
        });
    }
</script>
<?php include __DIR__ . '/templates/footer.php'; ?>