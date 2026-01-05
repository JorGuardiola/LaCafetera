<?php
// frontend/admin.php
session_start();
require_once __DIR__ . '/../db/connection.php';

/* =========================
   1. SEGURIDAD Y ACCESO
========================= */
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Opcional: Verificar rol admin aquí si no lo hace tu connection.php
$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() !== 'admin') {
    header('Location: index.php');
    exit;
}

$tab_activa = $_GET['tab'] ?? 'usuarios';
$mensaje = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);

/* =========================
   2. PROCESAMIENTO DE ACCIONES (POST)
   Esta es la parte que te faltaba
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            
            case 'create_user':
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono, rol) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['apellido'],
                    $_POST['email'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['telefono'],
                    $_POST['rol']
                ]);
                $_SESSION['admin_flash'] = "Usuario creado con éxito.";
                break;

            case 'update_user':
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, telefono=?, rol=? WHERE id_usuario=?");
                $stmt->execute([
                    $_POST['nombre'], $_POST['apellido'], $_POST['email'], 
                    $_POST['telefono'], $_POST['rol'], $_POST['id_usuario']
                ]);
                
                if (!empty($_POST['password'])) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash=? WHERE id_usuario=?");
                    $stmt->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['id_usuario']]);
                }
                $_SESSION['admin_flash'] = "Usuario actualizado.";
                break;

            /* ========= USUARIOS ========= */
            case 'delete_user':
                try {
                    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
                    $stmt->execute([$_POST['id_usuario']]);
                    $_SESSION['admin_flash'] = 'Usuario eliminado correctamente';
                } catch (PDOException $e) {
                    // El código 23000 es para violaciones de integridad, 
                    // y el 1451 es específicamente para restricciones de llave foránea (parent row)
                    if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                        $_SESSION['admin_flash'] = "No se puede eliminar. Existen pedidos o carritos asociados a este usuario.";
                    } else {
                        $_SESSION['admin_flash'] = "Error inesperado: " . $e->getMessage();
                    }
                }
                header('Location: admin.php?tab=usuarios');
                exit;

            case 'change_user_role':
                $stmt = $pdo->prepare("UPDATE usuarios SET rol=? WHERE id_usuario=?");
                $stmt->execute([$_POST['rol'], $_POST['id_usuario']]);
                $_SESSION['admin_flash'] = "Rol actualizado.";
                break;

            /*productos*/

            case 'create_product':
                try {
                    $pdo->beginTransaction();

                    // 1. Verificamos si el café (nombre_cafe) ya existe en la tabla 'productos'
                    $stmtCheck = $pdo->prepare("SELECT id FROM productos WHERE nombre_cafe = ?");
                    $stmtCheck->execute([$_POST['nombre_cafe']]);
                    $producto_id = $stmtCheck->fetchColumn();

                    // 2. Si no existe, lo creamos primero
                    if (!$producto_id) {
                        $stmtNewProd = $pdo->prepare("INSERT INTO productos (nombre_cafe) VALUES (?)");
                        $stmtNewProd->execute([$_POST['nombre_cafe']]);
                        $producto_id = $pdo->lastInsertId();
                    }

                    // 3. Insertamos la variante con el producto_id obtenido
                    $stmtVar = $pdo->prepare("INSERT INTO producto_variantes (producto_id, sku, precio, stock, molienda, tueste, envase) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtVar->execute([
                        $producto_id,
                        $_POST['sku'],
                        $_POST['precio'],
                        $_POST['stock'],
                        $_POST['molienda'],
                        $_POST['tueste'],
                        $_POST['envase']
                    ]);

                    $pdo->commit();
                    $_SESSION['admin_flash'] = "Nueva variante creada correctamente.";
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    if ($e->getCode() == '23000') {
                        $_SESSION['admin_flash'] = "Error: El SKU ya existe o la variante está duplicada.";
                    } else {
                        $_SESSION['admin_flash'] = "Error: " . $e->getMessage();
                    }
                }
                header("Location: admin.php?tab=productos");
            exit;




            case 'delete_variant':
                try {
                    // Borramos únicamente la fila que coincida con el SKU en la tabla de variantes
                    $stmt = $pdo->prepare("DELETE FROM producto_variantes WHERE sku = ?");
                    $stmt->execute([$_POST['sku']]);
                    
                    $_SESSION['admin_flash'] = "Variante eliminada correctamente.";
                } catch (Exception $e) {
                    $_SESSION['admin_flash'] = "Error al eliminar la variante: " . $e->getMessage();
                }
            break;


            case 'update_product':
                try {
                    $pdo->beginTransaction();
                    
                    // 1. Actualizar nombre del café
                    $stmt1 = $pdo->prepare("UPDATE productos SET nombre_cafe = ? WHERE id = ?");
                    $stmt1->execute([$_POST['nombre_cafe'], $_POST['producto_id']]);
                    
                    // 2. Actualizar la variante
                    $stmt2 = $pdo->prepare("UPDATE producto_variantes SET sku = ?, precio = ?, stock = ?, molienda = ?, tueste = ? WHERE sku = ?");
                    $stmt2->execute([
                        $_POST['sku'], 
                        $_POST['precio'], 
                        $_POST['stock'], 
                        $_POST['molienda'], 
                        $_POST['tueste'], 
                        $_POST['sku_original'] 
                    ]);
                    
                    $pdo->commit();
                    $_SESSION['admin_flash'] = "Variante actualizada correctamente.";
                } catch (PDOException $e) { // Cambiamos a PDOException para capturar el código
                    $pdo->rollBack();
                    
                    // El código 23000 con error 1062 es duplicado en MySQL
                    if ($e->getCode() == '23000') {
                        $_SESSION['admin_flash'] = "Ya existe un producto con esas características (molienda y tueste).";
                    } else {
                        $_SESSION['admin_flash'] = "Error al actualizar: " . $e->getMessage();
                    }
                }
            break;


            // Caso Crear Producto
            case 'create_product':
                $stmt2 = $pdo->prepare("INSERT INTO producto_variantes (producto_id, sku, precio, stock, molienda, tueste, envase) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt2->execute([$nuevo_id, $_POST['sku'], $_POST['precio'], $_POST['stock'], $_POST['molienda'], $_POST['tueste'], $_POST['envase']]);
            break;

            // Caso Modificar Producto
            case 'update_product':
                $stmt2 = $pdo->prepare("UPDATE producto_variantes SET sku = ?, precio = ?, stock = ?, molienda = ?, tueste = ?, envase = ? WHERE sku = ?");
                $stmt2->execute([$_POST['sku'], $_POST['precio'], $_POST['stock'], $_POST['molienda'], $_POST['tueste'], $_POST['envase'], $_POST['sku_original']]);
            break;


            /* ========= PEDIDOS ========= */
            // Dentro del switch ($_POST['action']) en admin.php
            case 'update_order_status':
                try {
                    // Actualizado para usar id_orden
                    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id_orden = ?");
                    $stmt->execute([$_POST['nuevo_estado'], $_POST['id_orden']]);
                    $_SESSION['admin_flash'] = "Estado del pedido #" . $_POST['id_orden'] . " actualizado.";
                } catch (Exception $e) {
                    $_SESSION['admin_flash'] = "Error al actualizar: " . $e->getMessage();
                }
                header("Location: admin.php?tab=pedidos");
            exit;

            



















        }
        
        // Redirigir para limpiar el POST y mantener la pestaña activa
        header("Location: admin.php?tab=" . $tab_activa);
        exit;

    } catch (Exception $e) {
        $_SESSION['admin_flash'] = "Error: " . $e->getMessage();
        header("Location: admin.php?tab=" . $tab_activa);
        exit;
    }
}
?>
<body>
<?php include __DIR__ . '/templates/header.php'; ?>

<main>

    <div class="container profile-container">
        <h1>Panel de Administración</h1>
        
        

        <div class="profile-layout-admin">
            <aside class="profile-sidebar">
                <button class="profile-menu-btn" data-tab="usuarios" onclick="openTab('usuarios')">Usuarios</button>
                <button class="profile-menu-btn" data-tab="productos" onclick="openTab('productos')">Productos</button>
                <button class="profile-menu-btn" data-tab="pedidos" onclick="openTab('pedidos')">Pedidos</button>
            </aside>
            <div class="profile-content">
                <div id="usuarios" class="profile-content-section <?= $tab_activa==='usuarios'?'active':'' ?>">
                    <div>
                        <h2>Gestión de Usuarios</h2>
                    </div>
                    <div class="filter-bar">
                        <button class="boton2-btn" onclick="openUserForm()" >+ Crear usuario</button>
                    </div>
                    <?php include __DIR__ . '/templates/search-users-admin.php'; ?>
                </div>
            
                <div id="productos" class="profile-content-section">
                    <h2>Gestión de Productos</h2>
                    <div class="filter-bar">
                        <button type="button" class="boton2-btn" onclick="openProductForm()">
                        + Crear producto
                        </button>
                    </div>
                    <?php include __DIR__ . '/templates/search-products-admin.php'; ?>
                    
                </div>
            
                <div id="pedidos" class="profile-content-section">
                    <h2>Gestión de Pedidos</h2>
                    
                    <?php include __DIR__ . '/templates/search-orders-admin.php'; ?>
                    
                </div>
            </div>








            
        </div>
    </div>
</main>
</body>
<script>

/**
 * Definimos la función de forma global para que el 'onclick' 
 * de los botones pueda encontrarla.
 */
function openTab(tabId) {
    // 1. Buscamos todas las secciones y botones
    const sections = document.querySelectorAll('.profile-content-section');
    const buttons = document.querySelectorAll('.profile-menu-btn');

    // 2. Ocultamos TODAS las secciones (evita que se mezclen)
    sections.forEach(section => {
        section.style.display = 'none'; 
        section.classList.remove('active');
    });

    // 3. Quitamos el estado activo de todos los botones
    buttons.forEach(btn => btn.classList.remove('active'));

    // 4. Buscamos la sección que queremos mostrar
    const targetSection = document.getElementById(tabId);
    
    if (targetSection) {
        // Mostramos la sección y añadimos la clase
        targetSection.style.display = 'block';
        targetSection.classList.add('active');

        // 5. Marcamos el botón como activo usando su atributo data-tab
        const targetBtn = document.querySelector(`.profile-menu-btn[data-tab="${tabId}"]`);
        if (targetBtn) {
            targetBtn.classList.add('active');
        }

        // 6. Actualizamos la URL para que al recargar (F5) se quede en la misma pestaña
        const newUrl = window.location.pathname + '?tab=' + tabId;
        window.history.pushState({path: newUrl}, '', newUrl);
    } else {
        console.warn("No se encontró la sección con ID: " + tabId);
    }
}

// Al cargar el documento, activamos la pestaña que diga la URL o 'usuarios' por defecto
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const initialTab = params.get('tab') || 'usuarios';
    openTab(initialTab);
});



// Función para abrir el formulario de producto vacío (Creación)
function openProductForm() {
    // 1. Cambiamos el título del modal
    document.getElementById('modalProductTitle').innerText = "Nuevo Producto";
    
    // 2. Cambiamos la acción a 'create_product' para que el PHP sepa qué hacer
    document.getElementById('formProductAction').value = "create_product";
    
    // 3. Reseteamos todos los campos (vacía nombre, sku, precio, etc.)
    document.getElementById('productForm').reset();
    
    // 4. Vaciamos los IDs ocultos
    document.getElementById('formProductId').value = "";
    document.getElementById('pInputSkuOriginal').value = "";
    
    // 5. Mostramos el modal
    document.getElementById('productModal').style.display = "block";
}

// Función para abrir el formulario con datos (Edición)
// Se llama desde el botón "Editar" que pusimos en el AJAX
function openEditProduct(id) {
    // Aquí podrías hacer un fetch para obtener los datos del producto
    // o redirigir a una página de edición dedicada:
    window.location.href = 'edit-product.php?id=' + id;
}

function closeProductModal() {
    document.getElementById('productModal').style.display = "none";
}








function openUserForm() {
    document.getElementById('modalTitle').innerText = "Nuevo Usuario";
    document.getElementById('formAction').value = "create_user";
    document.getElementById('userForm').reset();
    document.getElementById('formUserId').value = "";
    document.getElementById('inputPassword').required = true;
    document.getElementById('passHelp').style.display = "none";
    document.getElementById('userModal').style.display = "block";
}

function openEditUser(id) {
    const row = document.querySelector(`button[onclick="openEditUser(${id})"]`).closest('tr');
    const cells = row.querySelectorAll('td');

    document.getElementById('modalTitle').innerText = "Modificar Usuario";
    document.getElementById('formAction').value = "update_user";
    document.getElementById('formUserId').value = id;
    
    document.getElementById('inputNombre').value = cells[1].innerText.trim();
    document.getElementById('inputApellido').value = cells[2].innerText.trim();
    document.getElementById('inputEmail').value = cells[3].innerText.trim();
    document.getElementById('inputTelefono').value = cells[4].innerText.trim();
    
    // Buscar el valor del select dentro de la fila
    const currentRol = cells[5].querySelector('select').value;
    document.getElementById('inputRol').value = currentRol;

    document.getElementById('inputPassword').required = false;
    document.getElementById('passHelp').style.display = "block";
    document.getElementById('userModal').style.display = "block";
}

function closeUserModal() {
    document.getElementById('userModal').style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target == modal) closeUserModal();
}

/* funciones modal productos */
function openProductForm() {
    document.getElementById('modalProductTitle').innerText = "Nuevo Producto";
    document.getElementById('formProductAction').value = "create_product";
    document.getElementById('productForm').reset();
    document.getElementById('formProductId').value = "";

    document.getElementById('productModal').style.display = "block";
}

function openEditProduct(data) {
    document.getElementById('modalProductTitle').innerText = "Modificar Variante";
    document.getElementById('formProductAction').value = "update_product";
    
    document.getElementById('formProductId').value = data.id;
    document.getElementById('pInputNombre').value = data.nombre;
    document.getElementById('pInputSku').value = data.sku;
    document.getElementById('pInputSkuOriginal').value = data.sku; // Guardamos el SKU actual
    document.getElementById('pInputPrecio').value = data.precio;   // Aquí se carga el precio
    document.getElementById('pInputStock').value = data.stock;
    document.getElementById('pInputMolienda').value = data.molienda;
    document.getElementById('pInputTueste').value = data.tueste;
    document.getElementById('pInputEnvase').value = data.envase; // Cargar envase

    document.getElementById('productModal').style.display = "block";
}

function closeProductModal() {
    document.getElementById('productModal').style.display = "none";
}

// Cerrar al hacer clic fuera (extender el window.onclick que ya tienes)
window.onclick = function(event) {
    const userModal = document.getElementById('userModal');
    const productModal = document.getElementById('productModal');
    if (event.target == userModal) closeUserModal();
    if (event.target == productModal) closeProductModal();
}




/* Funciones de búsqueda y filtrado de pedidos */
// Función global para pedidos
async function loadOrders() {
    const oContainer = document.getElementById('ordersTableContainer');
    if (!oContainer) return;

    const oFilters = ['o-id', 'o-usuario', 'o-email', 'o-estado', 'o-fecha'];
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
        oContainer.innerHTML = '<tr><td colspan="7">Error de conexión</td></tr>';
    }
}

// Inicialización de eventos
document.addEventListener('DOMContentLoaded', () => {
    const oFilters = ['o-id', 'o-usuario', 'o-estado', 'o-fecha', 'o-email'];
    
    oFilters.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', loadOrders);
            el.addEventListener('change', loadOrders);
        }
    });

    const btnClearOrders = document.getElementById('clearOrderFilters');
    if (btnClearOrders) {
        btnClearOrders.addEventListener('click', () => {
            oFilters.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            loadOrders();
        });
    }

    // Si estamos en la pestaña de pedidos, cargar al inicio
    if (document.getElementById('ordersTableContainer')) {
        loadOrders();
    }
});



</script>

<div id="userModal" class="modal">
    <div class="modal-content">
        <h3 id="modalTitle">Nuevo Usuario</h3>
        <form id="userForm" action="admin.php?tab=usuarios" method="POST">
            <input type="hidden" name="action" id="formAction" value="create_user">
            <input type="hidden" name="id_usuario" id="formUserId">
            
            <div >
                <label class="label1">Nombre:</label><br>
                <input type="text" name="nombre" id="inputNombre" required class="input1">
            </div>
            <div style="margin-bottom:10px;">
                <label class="label1">Apellido:</label><br>
                <input type="text" name="apellido" id="inputApellido" class="input1">
            </div>
            <div style="margin-bottom:10px;">
                <label class="label1">Email:</label><br>
                <input type="email" name="email" id="inputEmail" required class="input1">
            </div>
            <div style="margin-bottom:10px;">
                <label class="label1">Teléfono:</label><br>
                <input type="text" name="telefono" id="inputTelefono" class="input1">
            </div>
            <div style="margin-bottom:10px;">
                <label class="label1">Contraseña:</label><br>
                <input type="password" name="password" id="inputPassword" class="input1">
                <small id="passHelp" style="color: #666; display:none;">Dejar en blanco para no cambiar</small>
            </div>
            <div style="margin-bottom:20px;">
                <label class="label1">Rol:</label><br>
                <select name="rol" id="inputRol" class="selector1">
                    <option value="cliente">Cliente</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div style="text-align:right;">
                <button type="button" onclick="closeUserModal()" class="boton3-btn">Cancelar</button>
                <button type="submit" class="boton2-btn">Guardar</button>
            </div>
        </form>
    </div>
</div>




<div id="productModal" class="modal">
    <div class="modal-content">
        <h3 id="modalProductTitle">Modificar Variante</h3>
        <form id="productForm" action="admin.php?tab=productos" method="POST">
            <input type="hidden" name="action" id="formProductAction" value="update_product">
            <input type="hidden" name="producto_id" id="formProductId">
            <input type="hidden" name="sku_original" id="pInputSkuOriginal">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div>
                    <label class="label1">Nombre del Café:</label>
                    <input type="text" name="nombre_cafe" id="pInputNombre" required class="input1">
                </div>
                <div>
                    <label class="label1">SKU:</label>
                    <input type="text" name="sku" id="pInputSku" required class="input1">
                </div>
                <div>
                    <label class="label1">Precio (€):</label>
                    <input type="number" step="0.01" name="precio" id="pInputPrecio" required class="input1">
                </div>
                <div>
                    <label class="label1">Stock:</label>
                    <input type="number" name="stock" id="pInputStock" required class="input1">
                </div>
                <div>
                    <label class="label1">Molienda:</label>
                    <input type="text" name="molienda" id="pInputMolienda" class="input1">
                </div>
                <div>
                    <label class="label1">Tueste:</label>
                    <input type="text" name="tueste" id="pInputTueste" class="input1">
                </div>
                <div>
                    <label class="label1">Envase:</label>
                    <input type="text" name="envase" id="pInputEnvase" class="input1" placeholder="Ej: 250g, 1kg...">
                </div>
            </div>
            
            <div style="text-align:right; margin-top:20px;">
                <button type="button" onclick="closeProductModal()" class="boton3-btn">Cancelar</button>
                <button type="submit" class="boton2-btn">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>




<?php include __DIR__ . '/templates/footer.php'; ?>