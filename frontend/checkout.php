<?php
// frontend/checkout.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// 1. SI EL CARRITO ESTÁ VACÍO, REDIRIGIR
if (empty($_SESSION['carrito'])) {
    header('Location: ' . BASE_URL . '/frontend/products.php');
    exit;
}

// 2. CALCULAR TOTALES (Igual que en cart.php)
$total_carrito = 0;
$items_checkout = [];

// Obtener detalles de productos
$skus = array_keys($_SESSION['carrito']);
if(count($skus) > 0) {
    $placeholders = implode(',', array_fill(0, count($skus), '?'));
    $sql = "SELECT pv.sku, pv.precio, p.nombre_cafe, p.imagen, pv.envase 
            FROM producto_variantes pv
            JOIN productos p ON pv.producto_id = p.id
            WHERE pv.sku IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($skus);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $row) {
        $sku = $row['sku'];
        $cantidad = $_SESSION['carrito'][$sku];
        $subtotal = $row['precio'] * $cantidad;
        $total_carrito += $subtotal;
        $row['cantidad'] = $cantidad; // Guardamos cantidad para procesar luego
        $items_checkout[] = $row;
    }
}

// Gastos de envío (Simulado)
$gastos_envio = ($total_carrito > 50) ? 0 : 5.00;
$total_pagar = $total_carrito + $gastos_envio;

// 3. RECUPERAR DATOS DE USUARIO SI ESTÁ LOGUEADO
$user_id = $_SESSION['user_id'] ?? null;
$user_data = []; // Para rellenar campos si está logueado
$direcciones_guardadas = [];

if ($user_id) {
    // 1. Obtener datos personales (Nombre, Apellido, Teléfono)
    $stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmtUser->execute([$user_id]);
    $user_data = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener direcciones del usuario para el desplegable
    $stmtAddr = $pdo->prepare("SELECT * FROM direcciones WHERE id_usuario = ?");
    $stmtAddr->execute([$user_id]);
    $direcciones_guardadas = $stmtAddr->fetchAll(PDO::FETCH_ASSOC);
}


// =======================================================
// 4. PROCESAR FORMULARIO (POST)
// =======================================================
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $pdo->beginTransaction();

        $telefono = trim($_POST['telefono']); // Capturamos teléfono aquí

        // CAMINO 2: EL CLIENTE NO ESTÁ REGISTRADO y pasa a chekout como invitado
        // A. GESTIÓN DE USUARIO (Si no está logueado)
        if (!$user_id) {
            $email = trim($_POST['email']);
            $pass = $_POST['password'];
            $pass2 = $_POST['re_password'];
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellidos']);

            // Validaciones básicas
            if ($pass !== $pass2) throw new Exception("Las contraseñas no coinciden.");
            
            // Verificar si email existe
            $stmtCheck = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmtCheck->execute([$email]);
            if ($stmtCheck->fetch()) throw new Exception("El email ya está registrado. Por favor inicia sesión.");

            // Crear Usuario
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            // AQUÍ GUARDAMOS EL TELÉFONO EN LA TABLA USUARIO
            $stmtUser = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, telefono, fecha_registro, rol) VALUES (?, ?, ?, ?, ?, NOW(), 'cliente')");
            $stmtUser->execute([$nombre, $apellido, $email, $hash, $telefono]);
            $user_id = $pdo->lastInsertId();

            // Auto-login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['logged_in'] = true;
        }
        // CAMINO 1: EL CLIENTE YA ESTABA REGISTRADO

        else {
            // Actualizamos el teléfono en su ficha si lo ha cambiado o no lo tenía
            $stmtUpdate = $pdo->prepare("UPDATE usuarios SET telefono = ? WHERE id_usuario = ?");
            $stmtUpdate->execute([$telefono, $user_id]);
        }

        // B. GESTIÓN DE DIRECCIÓN
        $id_direccion = null;
        $seleccion_dir = $_POST['direccion_guardada'] ?? 'nueva';

        if ($seleccion_dir === 'nueva' || !$user_id) {
            // Recogemos datos del template address_form.php
            $direccion = trim($_POST['direccion']);
            $ciudad = trim($_POST['ciudad']);
            $provincia = trim($_POST['provincia']); 
            $cp = trim($_POST['codigo_postal']);
            $pais = trim($_POST['pais']);

            $stmtDir = $pdo->prepare("INSERT INTO direcciones (id_usuario, direccion, ciudad, provincia, pais, codigo_postal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtDir->execute([$user_id, $direccion, $ciudad, $provincia, $pais, $cp]);
            $id_direccion = $pdo->lastInsertId();
        } else {
            $id_direccion = (int)$seleccion_dir;
        }

        // C. CREAR PEDIDO
        // Snapshot de dirección para el historial (texto simple)
        $snapshot = $_POST['direccion'] . ", " . $_POST['ciudad'] . ", " . $_POST['codigo_postal'];
        
        $sqlPedido = "INSERT INTO pedidos (id_usuario, id_direccion, total, estado, fecha_orden, direccion_envio_snapshot) 
                      VALUES (?, ?, ?, 'pagado', NOW(), ?)";
        $stmtPed = $pdo->prepare($sqlPedido);
        $stmtPed->execute([$user_id, $id_direccion, $total_pagar, $snapshot]);
        $id_orden = $pdo->lastInsertId();

        // D. CREAR ITEMS DEL PEDIDO & PAGO
        // Insertar items
        $stmtItem = $pdo->prepare("INSERT INTO pedido_items (id_orden, id_variante_sku, precio_unitario, cantidad) VALUES (?, ?, ?, ?)");
        
        foreach ($items_checkout as $item) {
            $stmtItem->execute([$id_orden, $item['sku'], $item['precio'], $item['cantidad']]);
            
            // Opcional: Restar stock aquí
            $stmtStock = $pdo->prepare("UPDATE producto_variantes SET stock = stock - ? WHERE sku = ?");
            $stmtStock->execute([$item['cantidad'], $item['sku']]);
        }

        // Registrar Pago (Tabla pagos del schema)
        $metodo_pago = $_POST['metodo_pago'] ?? 'tarjeta';
        $ref_pago = 'TXN-' . strtoupper(uniqid()); // Referencia simulada
        
        $stmtPago = $pdo->prepare("INSERT INTO pagos (id_orden, metodo, monto, estado, fecha_pago, referencia_transaccion) VALUES (?, ?, ?, 'completado', NOW(), ?)");
        $stmtPago->execute([$id_orden, $metodo_pago, $total_pagar, $ref_pago]);

        // E. FINALIZAR
        $pdo->commit();
        unset($_SESSION['carrito']); // Vaciar carrito
        
        // Redirigir a página de éxito
        header("Location: " . BASE_URL . "/frontend/success.php?orden=" . $id_orden);        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $errores[] = $e->getMessage();
    }
}
?>

<?php include __DIR__ . '/templates/header.php'; ?>

<main class="container">
    
    <h1>Finalizar Compra</h1>

    <?php if (!empty($errores)): ?>
        <div class="alert error">
            <?php foreach($errores as $err) echo "<p>• $err</p>"; ?>
        </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST" class="cart-layout" id="checkoutForm">
        
        <div class="checkout-left">
            
            <?php if (!$user_id): ?>
            <div class="checkout-form-section">
                <div class="checkout-heading">
                    <span>Datos de contacto</span>
                    <a href="<?= BASE_URL ?>/frontend/login.php" class="checkout-login-link">¿Tienes cuenta? Iniciar sesión</a>
                </div>
                
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" required placeholder="ejemplo@email.com">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Repite contraseña</label>
                        <input type="password" name="re_password" required>
                    </div>
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:1rem;">
                    <input type="checkbox" name="newsletter" id="news" style="width:auto;">
                    <label for="news" style="margin:0;">Enviarme novedades y ofertas</label>
                </div>
            </div>
            <?php endif; ?>

            <div class="checkout-form-section">
                <h2 class="checkout-heading">Entrega</h2>

                <?php if (!empty($direcciones_guardadas)): ?>
                    <div class="form-group" style="background:#f9f9f9; padding:1.5rem; border-radius:8px;">
                        <label style="font-weight:bold;">Usar una dirección guardada:</label>
                        <select name="direccion_guardada" id="addressSelector" onchange="fillAddress(this)">
                            <option value="nueva">-- Añadir nueva dirección --</option>
                            <?php foreach($direcciones_guardadas as $dir): ?>
                                <option value="<?= $dir['id_direccion'] ?>" 
                                    data-pais="<?= $dir['pais'] ?>"
                                    data-direccion="<?= $dir['direccion'] ?>"
                                    data-ciudad="<?= $dir['ciudad'] ?>"
                                    data-provincia="<?= $dir['provincia'] ?>"
                                    data-cp="<?= $dir['codigo_postal'] ?>"
                                >
                                    <?= $dir['direccion'] ?>, <?= $dir['ciudad'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="nombre" required 
                               value="<?= $user_id ? htmlspecialchars($user_data['nombre'] ?? '') : '' ?>"
                               <?= $user_id ? 'readonly style="background-color:#e9ecef; color:#555; cursor:not-allowed;"' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" required
                               value="<?= $user_id ? htmlspecialchars($user_data['apellido'] ?? '') : '' ?>"
                               <?= $user_id ? 'readonly style="background-color:#e9ecef; color:#555; cursor:not-allowed;"' : '' ?>>
                    </div>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" required 
                           value="<?= $user_id ? htmlspecialchars($user_data['telefono'] ?? '') : '' ?>">
                </div>

                <div id="newAddressContainer">
                    <?php 
                        // Pasamos un array vacío o datos nulos para que el formulario esté limpio
                        $d = []; 
                        include __DIR__ . '/templates/address_form.php'; 
                    ?>
                </div>

            </div>

            <div class="checkout-form-section">
                <h2 class="checkout-heading">Pago</h2>
                <p style="margin-bottom:1.5rem; font-size:1.4rem; color:#666;">Todas las transacciones son seguras y están encriptadas.</p>
                
                <div class="payment-methods">
                    
                    <label class="payment-option">
                        <input type="radio" name="metodo_pago" value="tarjeta" checked>
                        <div class="payment-label">
                            <span>Tarjeta de crédito</span>
                            <div class="payment-icons">
                                <i class="fa-brands fa-cc-visa"></i>
                                <i class="fa-brands fa-cc-mastercard"></i>
                            </div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="metodo_pago" value="paypal">
                        <div class="payment-label">
                            <span>PayPal</span>
                            <div class="payment-icons"><i class="fa-brands fa-paypal" style="color:#003087;"></i></div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="metodo_pago" value="bizum">
                        <div class="payment-label">
                            <span>Bizum</span>
                            <div class="payment-icons"><i class="fa-solid fa-mobile-screen-button"></i></div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="metodo_pago" value="gpay">
                        <div class="payment-label">
                            <span>Google Pay</span>
                            <div class="payment-icons"><i class="fa-brands fa-google-pay"></i></div>
                        </div>
                    </label>

                </div>
            </div>

        </div> <div class="cart-summary-box" style="height: fit-content; align-self: flex-start; position:sticky; top:2rem;">
            <h2>Resumen del pedido</h2>

            <div style="margin-bottom:2rem; max-height:200px; overflow-y:auto; padding-top: 10px;">
                <?php foreach($items_checkout as $it): ?>
                    <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
                        <div style="position:relative;">
                            <img src="<?= BASE_URL ?>/assets/img/imgsproducts/<?= $it['imagen'] ?>" style="width:50px; border-radius:4px; border:1px solid #eee;">
                            <span style="position:absolute; top:-5px; right:-5px; background:#666; color:white; border-radius:50%; width:18px; height:18px; font-size:1rem; display:flex; justify-content:center; align-items:center;"><?= $it['cantidad'] ?></span>
                        </div>
                        <div style="flex:1; font-size:1.3rem;">
                            <p style="margin:0; font-weight:600;"><?= $it['nombre_cafe'] ?></p>
                            <p style="margin:0; color:#888; font-size:1.1rem;"><?= $it['envase'] ?></p>
                        </div>
                        <div style="font-weight:bold; font-size:1.3rem;">
                            <?= number_format($it['precio'] * $it['cantidad'], 2) ?>€
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <hr style="border:0; border-top:1px solid #eee; margin: 15px 0;">

            <div class="summary-row">
                <span>Subtotal</span>
                <span><?= number_format($total_carrito, 2) ?>€</span>
            </div>
            
            <div class="summary-row">
                <span>Gastos de envío</span>
                <?php if ($gastos_envio == 0): ?>
                    <span style="color:#27AE60;">Gratis</span>
                <?php else: ?>
                    <span><?= number_format($gastos_envio, 2) ?>€</span>
                <?php endif; ?>
            </div>

            <hr style="border:0; border-top:1px solid #eee; margin: 15px 0;">

            <div class="summary-row total">
                <span>Total</span>
                <span><?= number_format($total_pagar, 2) ?>€</span>
            </div>
            <div class="iva-text">IVA incluido</div>

            <button type="submit" class="boton4-btn">Proceder con el pago</button>
        </div>

    </form>

    <div class="checkout-legal-links">
        <button type="button" class="legal-link" onclick="openModal('reembolso')">Política de reembolso</button>
        <button type="button" class="legal-link" onclick="openModal('envio')">Envío</button>
        <button type="button" class="legal-link" onclick="openModal('privacidad')">Política de privacidad</button>
        <button type="button" class="legal-link" onclick="openModal('terminos')">Términos del servicio</button>
        <button type="button" class="legal-link" onclick="openModal('aviso')">Aviso legal</button>
        <button type="button" class="legal-link" onclick="openModal('cancelaciones')">Cancelaciones</button>
        <button type="button" class="legal-link" onclick="openModal('contacto')">Contacto</button>
    </div>

</main>

<div class="modal-overlay" id="legalModal">
    <div class="modal-content">
        <button class="modal-close-btn" onclick="closeModal()">&times;</button>
        <h2 class="modal-title" id="modalTitle">Título</h2>
        <div class="modal-body" id="modalBody">
            </div>
    </div>
</div>

<script>
// --- Script para rellenar campos si elige una dirección guardada ---
function fillAddress(selectObj) {
    var idx = selectObj.selectedIndex;
    
    // Obtenemos los campos del template address_form.php por su ID
    const inpDireccion = document.getElementById('direccion');
    const inpCiudad = document.getElementById('ciudad');
    const inpProvincia = document.getElementById('provincia');
    const inpCp = document.getElementById('codigo_postal');
    const selPais = document.getElementById('pais');

    if (selectObj.value === 'nueva') {
        // Limpiar campos
        inpDireccion.value = '';
        inpCiudad.value = '';
        inpProvincia.value = '';
        inpCp.value = '';
        return;
    }
    
    // Rellenar campos desde los atributos data-
    var selectedOption = selectObj.options[idx];
    
    selPais.value = selectedOption.getAttribute('data-pais');
    inpDireccion.value = selectedOption.getAttribute('data-direccion');
    inpCiudad.value = selectedOption.getAttribute('data-ciudad');
    inpProvincia.value = selectedOption.getAttribute('data-provincia');
    inpCp.value = selectedOption.getAttribute('data-cp');
}
</script>

<script>
// --- Lógica del Modal y Textos Legales ---

const legalTexts = {
    'reembolso': `
        <strong>Política de reembolso</strong>
        <p>Si tienes cualquier incidencia con tu pedido, escríbenos a <lacaffetera1994@gmail.com</strong> y la atenderemos en un plazo de 24-48h laborables. En todos los casos necesitaremos que nos comentes la incidencia lo más detalladamente posible, aportando una foto del contenido de la caja recibido y una foto de la etiqueta del paquete que sea perfectamente legible.</p>
        <p>En <strong>La Cafetera 1994</strong> tostamos nuestros cafés de especialidad cada semana de forma que trabajamos con un producto fresco y recién tostado. De esta forma, aquellos cafés que por alguno de los siguientes motivos sean devueltos, no podemos volver a venderlos.</p>
        <p><strong>Se hará reembolso en caso de que:</strong></p>
        <ul>
            <li>El cliente, por error de la empresa de mensajería, no reciba el paquete.</li>
            <li>Cometimos un error con tu pedido.</li>
            <li>Al recibir el paquete, por problemas con el sellado, pudiese estar abierto.</li>
        </ul>
        <p><strong>No se reembolsará en los siguientes casos:</strong></p>
        <ul>
            <li>Estando el producto en buen estado, el cliente ya no lo quiere.</li>
            <li>Por falta de ausencia en la entrega del paquete pese a los diferentes avisos que la empresa de mensajería utiliza.</li>
            <li>Otros casos donde no fue error ni de la empresa de envíos ni de La Cafetera 1994.</li>
        </ul>
    `,

    'envio': `
        <strong>Envío: Forma, gastos y plazo</strong>
        <p>El cliente podrá seleccionar la forma de envío de entre las posibles para su zona. El Vendedor envía los pedidos a través de Correos Express (o mensajería equivalente). La fecha de entrega depende de la disponibilidad del producto y la zona.</p>
        <p>El coste de mensajería incluye 2 tentativas de entrega; si el cliente no estuviera en la segunda tentativa, su pedido estará esperándole en la oficina más cercana.</p>
        <p><strong>A tener en cuenta:</strong></p>
        <ul>
            <li>Los gastos de envío para península están incluidos en el precio WEB (salvo excepciones indicadas).</li>
            <li>Los plazos de recepción anunciados (24-48h) están condicionados a pedidos realizados antes de las 13:00 horas.</li>
        </ul>
        <p><strong>Envíos en PERIODO DE VACACIONES:</strong></p>
        <p>Todos los pedidos realizados en periodos festivos señalados serán procesados el primer día laborable posterior a la reapertura del tostadero.</p>
        <p><em>Recogida en tienda:</em> El cliente tiene un plazo máximo de 45 días. Pasado ese tiempo, el pedido se retirará sin derecho a reembolso.</p>
    `,

    'privacidad': `
        <strong>Política de privacidad</strong>
        <p>Las presentes condiciones se aplican a todas las transacciones en <strong>www.lacafetera.store</strong>. Para más información, consulte el Aviso Legal.</p>
        <p><strong>1. Identificación:</strong> El Vendedor es La Cafetera 1994 S.L., con domicilio en Avda. de la Molienda, 45, Planta 3, 28005 Madrid (España). Correo: lacaffetera1994@gmail.com.</p>
        <p><strong>2. Información recogida:</strong> Recogemos nombre, info de contacto y dirección para procesar pedidos y facturación.</p>
        <p><strong>3. Cookies:</strong> Utilizamos cookies para análisis estadístico y mejorar la experiencia. Usted puede denegar el uso de cookies en la configuración de su navegador.</p>
        <p><strong>4. Control de su privacidad:</strong> No vendemos, filtramos ni cedemos su información personal a terceros bajo ningún concepto, salvo requerimiento judicial.</p>
    `,

    'terminos': `
        <strong>Términos del servicio y Condiciones de Compra</strong>
        <p><strong>1. Actividad:</strong> Venta a distancia de cafés de especialidad y accesorios.</p>
        <p><strong>2. Contenidos:</strong> Nos esforzamos por ofrecer información veraz. Si existiera un error tipográfico en precios, el cliente tendrá derecho a rescindir su compra sin coste.</p>
        <p><strong>3. Impuestos:</strong> Los precios incluyen IVA aplicable.</p>
        <p><strong>4. Formas de pago:</strong> Tarjeta de crédito, Bizum, Google Pay y PayPal.</p>
        <p><strong>5. Disponibilidad:</strong> Al ser cafés de cosecha y temporada, no garantizamos stock permanente. Si un producto no está disponible tras la compra, se informará al cliente para devolución o cambio.</p>
        <p><strong>6. Obligaciones:</strong> El cliente se compromete a facilitar datos veraces de entrega. La empresa no se hace responsable de retrasos por direcciones incorrectas.</p>
        <p><strong>7. Legislación:</strong> Las compraventas se someten a la legislación de España.</p>
    `,

    'aviso': `
        <strong>Aviso Legal</strong>
        <p><strong>Datos identificativos:</strong><br>
        La Cafetera 1994 S.L.<br>
        Avda. de la Molienda, 45, Planta 3, 28005 Madrid<br>
        CIF: B-12345678<br>
        Email: lacaffetera1994@gmail.com</p>
        <p><strong>Objeto:</strong> Toda persona que acceda a este sitio asume el papel de usuario, comprometiéndose al cumplimiento de las disposiciones legales.</p>
        <p><strong>Responsabilidad:</strong> El sitio web ha sido probado para funcionar correctamente. No obstante, no se garantiza la inexistencia de errores o interrupciones ajenas a la empresa.</p>
    `,

    'cancelaciones': `
        <strong>Política de Cancelaciones y Suscripciones</strong>
        <p>Algunos artículos pueden ofrecerse como suscripción o reserva. Aquí detallamos cómo gestionar estos casos:</p>
        <p><strong>Suscripciones:</strong><br>
        Cuando compras una suscripción, recibirás envíos recurrentes. Puedes cancelar o modificar tu suscripción en cualquier momento desde los enlaces en tus correos de confirmación.</p>
        <p><strong>Reservas (Pre-orders):</strong><br>
        Cuando compras una reserva de un producto fuera de stock, se cobrará al momento o al enviarse (según condiciones). Puedes cancelar una reserva parcialmente pagada si aún no ha sido procesada.</p>
        <p><strong>Prueba antes de comprar:</strong><br>
        Autorizamos el pago antes de enviar el pedido. Tienes un periodo para decidir si te lo quedas. Pasado el tiempo, se cobrará el importe completo si no se ha devuelto.</p>
    `,

    'contacto': `
        <strong>Información de Contacto</strong>
        <p><strong>La Cafetera 1994 S.L.</strong></p>
        <p>Avda. de la Molienda, 45, Planta 3<br>
        28005, Madrid, España</p>
        <p><strong>Email:</strong> lacaffetera1994@gmail.com</p>
        <p><strong>Horario de atención:</strong><br>Lunes a Viernes de 9:00 a 18:00h.</p>
    `
};

function openModal(type) {
    const modal = document.getElementById('legalModal');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');

    // Mapear títulos bonitos
    const titlesMap = {
        'reembolso': 'Política de Reembolso',
        'envio': 'Información de Envío',
        'privacidad': 'Política de Privacidad',
        'terminos': 'Términos del Servicio',
        'aviso': 'Aviso Legal',
        'cancelaciones': 'Política de Cancelaciones',
        'contacto': 'Información de Contacto'
    };

    // Actualizar contenido
    title.textContent = titlesMap[type] || 'Información';
    body.innerHTML = legalTexts[type] || 'Contenido no disponible.';

    // Mostrar modal
    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('legalModal').classList.remove('active');
}

// Cerrar si se hace clic fuera del contenido
document.getElementById('legalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

</script>

<?php include __DIR__ . '/templates/footer.php'; ?>