<?php
$bgClass = 'bg-contacto'; 
include __DIR__ . '/templates/header.php';
?>

<main class="contenedor seccion contenido-centrado">
    
    <h1 class="text-center">Hablemos de Café</h1>
    <p class="text-center descripcion-contacto">
        Si tienes preguntas sobre un pedido, necesitas consejos de preparación o quieres colaborar, estamos aquí para ayudarte.
    </p>

    <div class="contacto-flex">
        
        <form class="formulario" method="POST" action="contacto.php"> 
            
            <fieldset>
                <legend>Información Personal</legend>

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="contacto[nombre]" placeholder="Tu Nombre" required>

                <label for="email">E-mail:</label>
                <input type="email" id="email" name="contacto[email]" placeholder="Tu Email" required>
            </fieldset>

            <fieldset>
                <legend>Motivo y Mensaje</legend>
                
                <label for="motivo">Motivo de Contacto:</label>
                <select id="motivo" name="contacto[motivo]" required>
                    <option value="" disabled selected>-- Seleccione un Motivo --</option>
                    <option value="pedido">Consulta sobre Pedido Existente</option>
                    <option value="producto">Ayuda con Producto/Molienda (Pregunta al Barista)</option>
                    <option value="negocios">Ventas al por Mayor / Negocios</option>
                    <option value="sugerencia">Comentarios y Sugerencias</option>
                </select>

                <div id="campo-pedido" style="display: none;">
                    <label for="n_pedido">Número de Pedido:</label>
                    <input type="text" id="n_pedido" name="contacto[n_pedido]" placeholder="Ej: #12345">
                </div>

                <label for="mensaje">Mensaje:</label>
                <textarea id="mensaje" name="contacto[mensaje]" required></textarea>

                <div class="aviso-legal">
                    <input type="checkbox" id="aceptar_politica" name="contacto[aceptar_politica]" required>
                    <label for="aceptar_politica">Acepto la <a href="politica-privacidad.php">Política de Privacidad</a> y los <a href="terminos-condiciones.php">Términos y Condiciones</a>.</label>
                </div>

            </fieldset>
            
            <input type="submit" value="Enviar mi Consulta y Tomar un Café" class="boton-verde">
        </form>
        <div class="info-adicional">
            <h3>Nuestros Canales</h3>
            <p><strong>Horario de Atención:</strong></p>
            <p>Lunes a Viernes, 9:00 a 18:00 (CET)</p>
            
            <p><strong>Teléfono de Soporte:</strong></p>
            <p><a href="tel:+34123456789">+34 648502176</a></p>

            <p><strong>Correo Electrónico:</strong></p>
            <p>Pedidos y Soporte: <a href="mailto:pedidos@lacaferetera.com">lacaffetera1994@gmail.com</a></p>
            <p>Prensa y Colab.: <a href="mailto:colabora@lacaferetera.com">lacaffetera1994@gmail.com</a></p>
           
        </div>
        </div> </main>

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
// Define una variable para almacenar mensajes de estado
$mensaje_estado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Saneamiento de los datos de entrada
    $datos = $_POST['contacto'];

    // Función para sanear (limpiar) las cadenas
    $nombre    = filter_var($datos['nombre'], FILTER_SANITIZE_STRING);
    $email     = filter_var($datos['email'], FILTER_SANITIZE_EMAIL);
    $motivo    = filter_var($datos['motivo'], FILTER_SANITIZE_STRING);
    $mensaje   = filter_var($datos['mensaje'], FILTER_SANITIZE_STRING);
    $n_pedido  = isset($datos['n_pedido']) ? filter_var($datos['n_pedido'], FILTER_SANITIZE_STRING) : '';

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

    // 3. Procesamiento y Envío del Correo
    if (empty($errores)) {
        
        // --- Configuración del Correo ---
        
        $destinatario = 'pedidos@lacaferetera.com'; // Dirección de correo de destino
        $asunto       = "Nueva Consulta Web: " . $motivo;
        
        // Construcción del cuerpo del mensaje (HTML o texto plano)
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
        $contenido .= "<p>" . $mensaje . "</p>";
        $contenido .= "</html>";
        
        // Headers para el envío de correo
        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= 'From: La Cafetera Web <no-reply@tudominio.com>' . "\r\n"; 
        $headers .= 'Reply-To: ' . $email . "\r\n"; 

        // Enviar el correo usando la función mail()
        if (mail($destinatario, $asunto, $contenido, $headers)) {
            $mensaje_estado = "<p class='alerta exito'>¡Gracias! Hemos recibido tu mensaje y te responderemos pronto.</p>";
            // Opcional: Redireccionar al usuario a una página de éxito para evitar reenvío del formulario
            // header('Location: contacto.php?resultado=1');
        } else {
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

$bgClass = 'bg-contacto'; 

?>

<?php 
    // 3. Mostrar el mensaje de estado (éxito o error)
    echo $mensaje_estado; 
?>

<?php
// 4. Incluir el pie de página (Footer)
require_once __DIR__ . '/templates/footer.php';
?>