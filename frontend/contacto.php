<?php
// ==============================================================================
// 0. INICIO DE SESIÓN PARA EL PATRÓN PRG (Post-Redirect-Get)
// Esto es crucial para que el mensaje de éxito no se quede fijo.
// ¡Debe ser la primera línea!
// ==============================================================================
session_start();


// ==============================================================================
// 1. INCLUSIONES Y CONFIGURACIÓN INICIAL
// ==============================================================================
// Incluye las clases necesarias de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// ¡RUTAS CORREGIDAS! Quitamos el '../' porque 'vendor' está dentro de 'frontend'.
require __DIR__ . '/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/src/SMTP.php';


$bgClass = 'bg-contacto'; 
// Asumiendo que templates está al mismo nivel que contacto.php (dentro de frontend/)



// Define una variable para almacenar mensajes de estado
$mensaje_estado = '';

// *** NUEVO CÓDIGO PRG: MUESTRA EL MENSAJE Y LO LIMPIA ***
if (isset($_SESSION['mensaje_contacto'])) {
    $mensaje_estado = $_SESSION['mensaje_contacto']; // Asignar el mensaje guardado
    unset($_SESSION['mensaje_contacto']);           // ¡Limpiar la sesión inmediatamente!
}
// *******************************************************


// ==============================================================================
// 2. LÓGICA DE PROCESAMIENTO Y ENVÍO DEL FORMULARIO
// ==============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Saneamiento de los datos de entrada
    $datos = $_POST['contacto'];

    $nombre = filter_var($datos['nombre'], FILTER_SANITIZE_STRING);
    $email = filter_var($datos['email'], FILTER_SANITIZE_EMAIL);
    $motivo = filter_var($datos['motivo'], FILTER_SANITIZE_STRING);
    $mensaje = filter_var($datos['mensaje'], FILTER_SANITIZE_STRING);
    $n_pedido = isset($datos['n_pedido']) ? filter_var($datos['n_pedido'], FILTER_SANITIZE_STRING) : '';

    // 2. Validación de los campos requeridos
    $errores = [];

    if (!$nombre) {
        $errores[] = "El Nombre es obligatorio.";
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Debes ingresar un correo electrónico válido.";
    }
    if (!$motivo) {
        $errores[] = "Debes seleccionar un Motivo de Contacto.";
    }
    if (!$mensaje) {
        $errores[] = "El Mensaje no puede ir vacío.";
    }
    if (!isset($datos['aceptar_politica'])) {
        $errores[] = "Debes aceptar la Política de Privacidad.";
    }

    // 3. Procesamiento y Envío del Correo con PHPMailer
    if (empty($errores)) {
        
        $mail = new PHPMailer(true); // 'true' habilita las excepciones
        
        try {
            // --- 3.1. CONFIGURACIÓN DEL SERVIDOR SMTP (Gmail) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';  // El Host SMTP de Gmail
            $mail->SMTPAuth   = true;
            
            // !!! REEMPLAZA ESTOS VALORES CON TUS CREDENCIALES !!!
            $mail->Username   = 'lacaffetera1994@gmail.com'; // Tu dirección de Gmail
            $mail->Password   = 'vryz vqby njse wnez'; // Contraseña de Aplicación
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SMTPS 
            $mail->Port       = 465; // Puerto para SMTPS (Gmail)
            $mail->CharSet    = 'UTF-8';

            // --- 3.2. CONFIGURACIÓN DEL CORREO ---
            
            $mail->setFrom('lacaffetera1994@gmail.com', 'Formulario Web La Cafetera'); 
            $mail->addAddress('lacaffetera1994@gmail.com', 'Soporte La Cafetera'); 
            $mail->addReplyTo($email, $nombre); 

            $mail->isHTML(true); 
            $mail->Subject = "Nueva Consulta Web: " . $motivo;
            
            // Construcción del cuerpo del mensaje (HTML)
            $contenido = "<html>";
            $contenido .= "<p>Has recibido un nuevo mensaje desde el formulario de contacto de La Cafetera.</p>";
            $contenido .= "<h3>Detalles del Contacto</h3>";
            $contenido .= "<ul>";
            $contenido .= "<li><strong>Nombre:</strong> " . $nombre . "</li>";
            $contenido .= "<li><strong>Email:</strong> " . $email . "</li>";
            $contenido .= "<li><strong>Motivo:</strong> " . $motivo . "</li>";
            
            if ($motivo === 'pedido' && $n_pedido) {
                $contenido .= "<li><strong>Número de Pedido:</strong> " . $n_pedido . "</li>";
            }
            
            $contenido .= "</ul>";
            $contenido .= "<h3>Mensaje:</h3>";
            $contenido .= "<p>" . nl2br($mensaje) . "</p>";
            $contenido .= "</html>";
            
            $mail->Body     = $contenido;
            
            // --- 3.3. ENVÍO ---
            $mail->send();
            
            // *** CÓDIGO NUEVO PRG: GUARDAR EN SESIÓN Y REDIRIGIR ***
            $_SESSION['mensaje_contacto'] = "<p class='alerta exito'>¡Gracias! Hemos recibido tu mensaje y te responderemos pronto.</p>";
            header('Location: contacto.php');
            exit;
            // ********************************************************


        } catch (Exception $e) {
            // Error de envío
            $mensaje_estado = "<p class='alerta error'>Error al enviar el mensaje. Por favor, intenta de nuevo o contacta por teléfono.</p>";
        }

    } else {
        // Mostrar errores si la validación falla
        $mensaje_estado = "<div class='alerta error'>Hubo errores en el formulario:";
        $mensaje_estado .= "<ul>";
        foreach ($errores as $error) {
            $mensaje_estado .= "<li>" . $error . "</li>";
        }
        $mensaje_estado .= "</ul></div>";
    }
}

// ==============================================================================
// Contenido del hero
$bgClass = "bg-contacto";
$heroTitle = "Hablemos de Café";
$heroSubtitle = "Si tienes preguntas sobre un pedido, necesitas consejos de preparación o quieres colaborar, estamos aquí para ayudarte";
$heroButtonText = ""; // vacío → no aparece botón
$heroButtonLink = "";
?>






<?php 
include __DIR__ . '/templates/header.php';
include __DIR__ . '/templates/hero.php';
?>

<main>
    
    <?php 
        echo $mensaje_estado; 
    ?>

    <div class="contacto-flex">
        
        <form class="formulario" method="POST" action="contacto.php"> 
            
            <fieldset>
                <legend>Información Personal</legend>

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="contacto[nombre]" class="input1" placeholder="Tu Nombre" required>

                <label for="email">E-mail:</label>
                <input type="email" id="email" name="contacto[email]" class="input1" placeholder="Tu Email" required>
            </fieldset>

            <fieldset>
                <legend>Motivo y Mensaje</legend>
                
                <label for="motivo">Motivo de Contacto:</label>
                <select id="motivo" name="contacto[motivo]" class="selector1" required>
                    <option value="" disabled selected>Seleccione un Motivo --</option>
                    <option value="pedido">Consulta sobre Pedido Existente</option>
                    <option value="producto">Ayuda con Producto/Molienda (Pregunta al Barista)</option>
                    <option value="negocios">Ventas al por Mayor / Negocios</option>
                    <option value="sugerencia">Comentarios y Sugerencias</option>
                </select>

                <div id="campo-pedido" >
                    <label for="n_pedido">Número de Pedido:</label>
                    <input type="text" id="n_pedido" name="contacto[n_pedido]" placeholder="Numero de pedido" class="input1"Ej: #12345">
                </div>

                <label for="mensaje">Mensaje:</label>
                <textarea id="mensaje" name="contacto[mensaje]" class="textarea1" required></textarea>

                <div class="aviso-legal">
                    <input type="checkbox" id="aceptar_politica" name="contacto[aceptar_politica]" class="checkbox1" required>
                    <label for="aceptar_politica">Acepto la <a href="politica-privacidad.php">Política de Privacidad</a> y los <a href="terminos-condiciones.php">Términos y Condiciones</a>.</label>
                </div>

            </fieldset>
            
            <input type="submit" value="Enviar mi Consulta y Tomar un Café" class="boton4-btn">
        </form>
        
        <div class="info-adicional">
            <h3>Nuestros Canales</h3>
            <p><strong>Horario de Atención:</strong></p>
            <p>Lunes a Viernes, 9:00 a 18:00</p>
            
            <p><strong>Teléfono de Soporte:</strong></p>
            <p><a href="tel:+34123456789">+34 648502176</a></p>

            <p><strong>Correo Electrónico:</strong></p>
            <p>Pedidos y Soporte: <a href="mailto:lacaffetera1994@gmail.com?subject=Consulta%20sobre%20Pedidos%20y%20Soporte" target="_blank">lacaffetera1994@gmail.com</a></p>
            <p>Prensa y Colaboración: <a href="mailto:lacaffetera1994@gmail.com?subject=Consulta%20de%20Prensa%20o%20Colaboracion" target="_blank">lacaffetera1994@gmail.com</a></p>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const motivoSelect = document.getElementById('motivo');
        const campoPedido = document.getElementById('campo-pedido');
        const nPedidoInput = document.getElementById('n_pedido');

        motivoSelect.addEventListener('change', function() {
            if (this.value === 'pedido') {
                campoPedido.style.display = 'block';
                nPedidoInput.setAttribute('required', 'required');
            } else {
                campoPedido.style.display = 'none';
                nPedidoInput.removeAttribute('required');
            }
        });
    });
</script>

<?php
// 4. Incluir el pie de página (Footer)
require_once __DIR__ . '/templates/footer.php';
?>