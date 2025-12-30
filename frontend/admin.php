<?php
// frontend/admin.php
session_start();
$mensaje = '';
if (!empty($_SESSION['admin_flash'])) {
    $mensaje = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']); // 🔥 CLAVE
}

require_once __DIR__ . '/../db/connection.php';

/* =========================
   1. SEGURIDAD
========================= */

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/frontend/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['user_id']]);
$rol = $stmt->fetchColumn();

if ($rol !== 'admin') {
    header('Location: ' . BASE_URL . '/frontend/index.php');
    exit;
}

/* =========================
   2. ESTADO UI
========================= */

$mensaje = '';
$tab_activa = $_GET['tab'] ?? 'usuarios';


/* =========================
   3. ACCIONES POST
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    switch ($_POST['action']) {

        /* =========================
           CREAR PRODUCTO + VARIANTE
        ========================= */
        case 'create_product':

            try {
                $pdo->beginTransaction();

                // Validación mínima
                if (empty($_POST['nombre_cafe']) || empty($_POST['sku'])) {
                    throw new Exception('Nombre del producto y SKU son obligatorios');
                }

                // 1. Crear producto
                $stmt = $pdo->prepare("
                    INSERT INTO productos (nombre_cafe, descripcion, imagen)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['nombre_cafe'],
                    $_POST['descripcion'] ?? null,
                    $_POST['imagen'] ?? null
                ]);

                $producto_id = $pdo->lastInsertId();
                if (!$producto_id) {
                    throw new Exception('No se pudo crear el producto');
                }

                // 2. Crear primera variante
                $stmt = $pdo->prepare("
                    INSERT INTO producto_variantes
                    (producto_id, sku, precio, stock, molienda, tueste, envase)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $producto_id,
                    $_POST['sku'],
                    $_POST['precio'],
                    $_POST['stock'],
                    $_POST['molienda'] ?? null,
                    $_POST['tueste'] ?? null,
                    $_POST['envase'] ?? null
                ]);

                $pdo->commit();

                $_SESSION['admin_flash'] = 'Producto creado correctamente';
                header('Location: admin.php?tab=productos');
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = 'Error al crear el producto';
            }

        break;


        /* =========================
           ACTUALIZAR PRODUCTO + VARIANTE
        ========================= */
        case 'update_product':

            try {
                $pdo->beginTransaction();

                if (empty($_POST['producto_id'])) {
                    throw new Exception('ID de producto no válido');
                }

                // 1. Actualizar producto
                $stmt = $pdo->prepare("
                    UPDATE productos
                    SET nombre_cafe = ?, descripcion = ?, imagen = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['nombre_cafe'],
                    $_POST['descripcion'] ?? null,
                    $_POST['imagen'] ?? null,
                    $_POST['producto_id']
                ]);

                // 2. Actualizar variante
                $stmt = $pdo->prepare("
                    UPDATE producto_variantes
                    SET sku = ?, precio = ?, stock = ?, molienda = ?, tueste = ?, envase = ?
                    WHERE producto_id = ?
                ");
                $stmt->execute([
                    $_POST['sku'],
                    $_POST['precio'],
                    $_POST['stock'],
                    $_POST['molienda'] ?? null,
                    $_POST['tueste'] ?? null,
                    $_POST['envase'] ?? null,
                    $_POST['producto_id']
                ]);

                $pdo->commit();

                $_SESSION['admin_flash'] = 'Producto actualizado correctamente';
                header('Location: admin.php?tab=productos');
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = 'Error al actualizar el producto';
            }

        break;


        /* =========================
           ELIMINAR VARIANTE
        ========================= */
        case 'delete_product_variant':

            if (!empty($_POST['sku'])) {
                $stmt = $pdo->prepare("
                    DELETE FROM producto_variantes
                    WHERE sku = ?
                ");
                $stmt->execute([$_POST['sku']]);

                $_SESSION['admin_flash'] = 'Variante eliminada';
                header('Location: admin.php?tab=productos');
                exit;
            }

        break;


        /* =========================
           ACTUALIZAR ESTADO PEDIDO
        ========================= */
        case 'update_order_status':

            if (!empty($_POST['id_orden']) && !empty($_POST['estado'])) {
                $stmt = $pdo->prepare("
                    UPDATE pedidos
                    SET estado = ?
                    WHERE id_orden = ?
                ");
                $stmt->execute([
                    $_POST['estado'],
                    $_POST['id_orden']
                ]);

                $_SESSION['admin_flash'] = 'Estado del pedido actualizado';
                header('Location: admin.php?tab=pedidos');
                exit;
            }

        break;

        /* =========================
           USUARIOS: CREAR
        ========================= */
        case 'create_user':
            try {
                if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['password'])) {
                    throw new Exception('Nombre, email y contraseña son obligatorios');
                }

                // Email único
                $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
                $stmt->execute([$_POST['email']]);
                if ($stmt->fetchColumn()) {
                    throw new Exception('El email ya existe');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono, rol)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['apellido'] ?? '',
                    $_POST['email'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['telefono'] ?? '',
                    $_POST['rol'] ?? 'cliente'
                ]);

                $_SESSION['admin_flash'] = 'Usuario creado correctamente';
                header('Location: admin.php?tab=usuarios');
                exit;

            } catch (Exception $e) {
                $_SESSION['admin_flash'] = 'Error al crear usuario: ' . $e->getMessage();
                header('Location: admin.php?tab=usuarios');
                exit;
            }
        break;

        /* =========================
           USUARIOS: MODIFICAR
           - Si password viene vacío, NO se cambia
        ========================= */
        case 'update_user':
            try {
                if (empty($_POST['id_usuario'])) throw new Exception('Usuario no válido');

                // Email único (excepto este usuario)
                $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ? LIMIT 1");
                $stmt->execute([$_POST['email'], $_POST['id_usuario']]);
                if ($stmt->fetchColumn()) {
                    throw new Exception('El email ya está en uso por otro usuario');
                }

                // Update base
                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET nombre = ?, apellido = ?, email = ?, telefono = ?, rol = ?
                    WHERE id_usuario = ?
                ");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['apellido'] ?? '',
                    $_POST['email'],
                    $_POST['telefono'] ?? '',
                    $_POST['rol'] ?? 'cliente',
                    (int)$_POST['id_usuario']
                ]);

                // Update password opcional
                if (!empty($_POST['password'])) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?");
                    $stmt->execute([
                        password_hash($_POST['password'], PASSWORD_DEFAULT),
                        (int)$_POST['id_usuario']
                    ]);
                }

                $_SESSION['admin_flash'] = 'Usuario actualizado correctamente';
                header('Location: admin.php?tab=usuarios');
                exit;

            } catch (Exception $e) {
                $_SESSION['admin_flash'] = 'Error al actualizar usuario: ' . $e->getMessage();
                header('Location: admin.php?tab=usuarios');
                exit;
            }
        break;

        /* =========================
           USUARIOS: CAMBIAR ROL (inline)
        ========================= */
        case 'change_user_role':
            if (!empty($_POST['id_usuario']) && isset($_POST['rol'])) {

                // Evitar que te quites el rol admin a ti mismo sin querer (opcional)
                // if ((int)$_POST['id_usuario'] === (int)$_SESSION['user_id'] && $_POST['rol'] !== 'admin') { ... }

                $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?");
                $stmt->execute([$_POST['rol'], (int)$_POST['id_usuario']]);

                $_SESSION['admin_flash'] = 'Rol actualizado';
                header('Location: admin.php?tab=usuarios');
                exit;
            }
        break;

        /* =========================
           USUARIOS: ELIMINAR
        ========================= */
        case 'delete_user':
            if (!empty($_POST['id_usuario'])) {

                // No permitir auto-eliminación
                if ((int)$_POST['id_usuario'] === (int)$_SESSION['user_id']) {
                    $_SESSION['admin_flash'] = 'No puedes eliminar tu propio usuario';
                    header('Location: admin.php?tab=usuarios');
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
                $stmt->execute([(int)$_POST['id_usuario']]);

                $_SESSION['admin_flash'] = 'Usuario eliminado';
                header('Location: admin.php?tab=usuarios');
                exit;
            }
        break;







    }
}










/* =========================
   4. CONSULTAS
========================= */

/* USUARIOS */
$usuarios = $pdo->query("
    SELECT id_usuario, nombre, apellido, email, telefono, rol
    FROM usuarios
    ORDER BY id_usuario DESC
")->fetchAll(PDO::FETCH_ASSOC);



/* FILTROS PRODUCTOS */
$filtros = [];
$params = [];

if (!empty($_GET['q'])) {
    $filtros[] = "(p.nombre_cafe LIKE ? OR v.sku LIKE ?)";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}
if (!empty($_GET['molienda'])) {
    $filtros[] = "v.molienda LIKE ?";
    $params[] = '%' . $_GET['molienda'] . '%';
}
if (!empty($_GET['tueste'])) {
    $filtros[] = "v.tueste LIKE ?";
    $params[] = '%' . $_GET['tueste'] . '%';
}
if (!empty($_GET['envase'])) {
    $filtros[] = "v.envase LIKE ?";
    $params[] = '%' . $_GET['envase'] . '%';
}

$where = $filtros ? 'WHERE ' . implode(' AND ', $filtros) : '';

/* PRODUCTOS */
$stmt = $pdo->prepare("
    SELECT
        p.id AS producto_id,
        p.nombre_cafe AS nombre_producto,
        v.sku,
        v.precio,
        v.stock,
        v.molienda,
        v.tueste,
        v.envase
    FROM productos p
    JOIN producto_variantes v ON v.producto_id = p.id
    $where
    ORDER BY p.id DESC
");
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* PEDIDOS */
$pedidos = $pdo->query("
    SELECT p.*, u.email
    FROM pedidos p
    JOIN usuarios u ON u.id_usuario = p.id_usuario
    ORDER BY p.fecha_orden DESC
")->fetchAll();

?>

<?php include __DIR__ . '/templates/header.php'; ?>

<main>
<div class="container profile-container">

    <h1>Panel de Administración</h1>

    <?php if ($mensaje): ?>
        <div style="margin:1.5rem 0;padding:1rem;border-radius:8px;
                    background:#e8f5e9;color:#2e7d32">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="profile-layout">

        <aside class="profile-sidebar">
            <button class="profile-menu-btn <?= $tab_activa=='usuarios'?'active':'' ?>" onclick="openTab('usuarios')">Usuarios</button>
            <button class="profile-menu-btn <?= $tab_activa=='productos'?'active':'' ?>" onclick="openTab('productos')">Productos</button>
            <button class="profile-menu-btn <?= $tab_activa=='pedidos'?'active':'' ?>" onclick="openTab('pedidos')">Pedidos</button>
            <a href="profile.php" class="profile-menu-btn">Volver a mi cuenta</a>
        </aside>

        <div class="profile-content">

            <!-- USUARIOS -->
            <div id="usuarios" class="profile-content-section <?= $tab_activa=='usuarios'?'active':'' ?>">
                <h2 class="section-title">Gestión de Usuarios</h2>

                <button type="button" onclick="openUserForm()"
                    style="padding:10px 16px;border-radius:8px;background:#1A1A1A;color:#fff;border:none;margin-bottom:1rem">
                    + Crear usuario
                </button>

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= (int)$u['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['apellido']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['telefono']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_user_role">
                                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                    <select name="rol" onchange="this.form.submit()">
                                        <option value="cliente" <?= $u['rol']==='cliente'?'selected':'' ?>>Cliente</option>
                                        <option value="admin" <?= $u['rol']==='admin'?'selected':'' ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td style="white-space:nowrap">
                                <button type="button"
                                    onclick='openUserForm(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                    style="color:#1976d2;background:none;border:none;margin-right:10px;cursor:pointer">
                                    Modificar
                                </button>

                                <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar usuario?')">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                    <button type="submit" style="color:red;background:none;border:none;cursor:pointer">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>



            <!-- PRODUCTOS -->
            <div id="productos" class="profile-content-section <?= $tab_activa=='productos'?'active':'' ?>">
                <h2 class="section-title">Gestión de Productos</h2>

                <?php include __DIR__ . '/templates/search-admin.php'; ?>


                <!-- BOTÓN CREAR -->
                <button
                    type="button"
                    onclick="openProductForm()"
                    style="
                        padding:10px 16px;
                        border-radius:8px;
                        background:#1A1A1A;
                        color:#fff;
                        border:none;
                        cursor:pointer;
                        font-weight:600
                        margin-bottom:5rem;
                    ">
                    + Añadir producto
                </button>




                <h4>Resultado de la busqueda</h4>

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>SKU</th>
                            <th>Molienda</th>
                            <th>Tueste</th>
                            <th>Envase</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?= $p['producto_id'] ?></td>
                            <td><?= htmlspecialchars($p['nombre_producto']) ?></td>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td><?= htmlspecialchars($p['molienda']) ?></td>
                            <td><?= htmlspecialchars($p['tueste']) ?></td>
                            <td><?= htmlspecialchars($p['envase']) ?></td>
                            <td><?= number_format($p['precio'], 2) ?> €</td>
                            <td><?= (int)$p['stock'] ?></td>
                            <td style="white-space:nowrap">

                                <!-- MODIFICAR -->
                                <button
                                    type="button"
                                    onclick='openProductForm(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                    style="color:#1976d;background:none;border:none;margin-right:10px;cursor:pointer">
                                    Modificar
                                </button>

                                <!-- ELIMINAR -->
                                <form method="POST"
                                    onsubmit="return confirm('¿Eliminar esta variante?')"
                                    style="display:inline">

                                    <input type="hidden" name="action" value="delete_product_variant">
                                    <input type="hidden" name="sku" value="<?= htmlspecialchars($p['sku']) ?>">

                                    <button
                                        type="submit"
                                        style="color:red;background:none;border:none;cursor:pointer">
                                        Eliminar
                                    </button>
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
</main>

<!-- MODAL VARIANTE -->
<div id="productModal" style="display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.5);align-items:center;justify-content:center">

<form method="POST"
      style="background:#fff;padding:2rem;border-radius:10px;width:420px">

<h3 id="productFormTitle">Añadir producto</h3>

<input type="hidden" name="action" id="productAction" value="create_product">
<input type="hidden" name="producto_id" id="producto_id">


<!-- PRODUCTO BASE -->
<label>Nombre del café</label>
<input type="text" name="nombre_cafe" required>

<label>Origen</label>
<input type="text" name="origen">

<label>Descripción</label>
<textarea name="descripcion"></textarea>

<label>Imagen (ruta o nombre)</label>
<input type="text" name="imagen">

<hr style="margin:1.5rem 0">

<!-- PRIMERA VARIANTE -->
<label>SKU</label>
<input type="text" name="sku" required>

<label>Precio (€)</label>
<input type="number" step="0.01" name="precio" required>

<label>Stock</label>
<input type="number" name="stock" required>

<label>Molienda</label>
<input type="text" name="molienda">

<label>Tueste</label>
<input type="text" name="tueste">

<label>Envase</label>
<input type="text" name="envase">

<div style="margin-top:1rem;text-align:right">
    <button type="button" onclick="closeProductForm()">Cancelar</button>
    <button type="submit"
        style="background:#2e7d32;color:#fff;border:none">
        Guardar producto
    </button>
</div>

</form>
</div>


<!-- MODAL USUARIO -->
<div id="userModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <form method="POST" style="background:#fff;padding:2rem;border-radius:10px;width:420px">
    <h3 id="userFormTitle">Crear usuario</h3>

    <input type="hidden" name="action" id="userAction" value="create_user">
    <input type="hidden" name="id_usuario" id="user_id">

    <label>Nombre</label>
    <input type="text" name="nombre" required>

    <label>Apellido</label>
    <input type="text" name="apellido">

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Teléfono</label>
    <input type="text" name="telefono">

    <label>Contraseña <small id="pwdHint">(obligatoria)</small></label>
    <input type="password" name="password" id="userPassword">

    <label>Rol</label>
    <select name="rol">
      <option value="cliente">Cliente</option>
      <option value="admin">Admin</option>
    </select>

    <div style="margin-top:1rem;text-align:right">
      <button type="button" onclick="closeUserForm()">Cancelar</button>
      <button type="submit" style="background:#2e7d32;color:#fff;border:none">Guardar</button>
    </div>
  </form>
</div>







<?php include __DIR__ . '/templates/footer.php'; ?>


/* =========================
   JS MODAL USUARIO
========================= */
<script>
function openUserForm(data = null) {
  const modal = document.getElementById('userModal');
  const form = modal.querySelector('form');
  const title = document.getElementById('userFormTitle');
  const action = document.getElementById('userAction');
  const id = document.getElementById('user_id');
  const pwd = document.getElementById('userPassword');
  const hint = document.getElementById('pwdHint');

  modal.style.display = 'flex';
  form.reset();

  if (!data) {
    title.textContent = 'Crear usuario';
    action.value = 'create_user';
    id.value = '';
    pwd.required = true;
    hint.textContent = '(obligatoria)';
    return;
  }

  title.textContent = 'Modificar usuario';
  action.value = 'update_user';
  id.value = data.id_usuario;

  form.nombre.value = data.nombre || '';
  form.apellido.value = data.apellido || '';
  form.email.value = data.email || '';
  form.telefono.value = data.telefono || '';
  form.rol.value = data.rol || 'cliente';

  // En editar, contraseña opcional
  pwd.required = false;
  hint.textContent = '(dejar vacío para no cambiar)';
}

function closeUserForm() {
  document.getElementById('userModal').style.display = 'none';
}
</script>




















<script>
function openProductForm(data = null) {
    const modal = document.getElementById('productModal');
    const form  = modal.querySelector('form');

    modal.style.display = 'flex';

    const title   = document.getElementById('productFormTitle');
    const action  = document.getElementById('productAction');
    const prodId  = document.getElementById('producto_id');

    if (!data) {
        // ===== CREAR PRODUCTO =====
        form.reset();
        title.textContent = 'Añadir producto';
        action.value = 'create_product';
        prodId.value = '';
        return;
    }

    // ===== EDITAR PRODUCTO =====
    title.textContent = 'Modificar producto';
    action.value = 'update_product';
    prodId.value = data.producto_id;

    // Producto
    form.nombre_cafe.value = data.nombre_producto || '';
    form.origen.value      = data.pais_origen || '';
    form.descripcion.value = data.descripcion || '';
    form.imagen.value      = data.imagen || '';

    // Variante
    form.sku.value      = data.sku || '';
    form.precio.value   = data.precio || '';
    form.stock.value    = data.stock || '';
    form.molienda.value = data.molienda || '';
    form.tueste.value   = data.tueste || '';
    form.envase.value   = data.envase || '';
}

function closeProductForm() {
    document.getElementById('productModal').style.display = 'none';
}
</script>


<script>
function openTab(tabId) {
    document.querySelectorAll('.profile-content-section').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.profile-menu-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
}
</script>    


/* =========================
   desplegable filtros admin productos
========================= */

<script>
document.addEventListener('change', async (e) => {

  if (e.target.id !== 'filter-molienda' && e.target.id !== 'filter-tueste') {
    return;
  }

  const moliendaSelect = document.getElementById('filter-molienda');
  const tuesteSelect   = document.getElementById('filter-tueste');
  const envaseSelect   = document.getElementById('filter-envase');

  if (!moliendaSelect || !tuesteSelect || !envaseSelect) return;

  function updateSelect(select, values, placeholder) {
    const current = select.value;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    values.forEach(v => {
      const opt = document.createElement('option');
      opt.value = v;
      opt.textContent = v;
      if (v === current) opt.selected = true;
      select.appendChild(opt);
    });
  }

  try {
    const params = new URLSearchParams({
      molienda: moliendaSelect.value,
      tueste: tuesteSelect.value
    });

    const res = await fetch('<?= BASE_URL ?>/frontend/templates/ajax/admin-filters.php?' + params.toString());
    if (!res.ok) throw new Error('HTTP ' + res.status);

    const data = await res.json();

    if (e.target.id === 'filter-molienda') {
      tuesteSelect.value = '';
      envaseSelect.value = '';
      updateSelect(tuesteSelect, data.tuestes || [], 'Todos los tuestes');
    }

    updateSelect(envaseSelect, data.envases || [], 'Todos los envases');

  } catch (err) {
    console.error('Error actualizando filtros:', err);
  }
});
</script>
