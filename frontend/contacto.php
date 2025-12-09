<?php

// 1. VARIABLES PARA LA PLANTILLA HERO.PHP
// Definimos la clase CSS para la imagen de fondo de esta página.
$bgClass = 'bg-contacto'; 

// 2. Incluir el encabezado
include __DIR__ . '/templates/header.php';
?>

    <main class="pagina-contacto">
        
        <?php 
        // 3. Incluir la sección HERO con la imagen de fondo dinámica
    
        include __DIR__ . '/templates/hero.php'; 
        ?>

        <section class="contenedor seccion contenido-principal">
            
            <div class="texto-contacto">
                
                <h2>Contáctanos</h2>
                
                <p>¿Tienes alguna pregunta sobre nuestros tuestes, necesitas ayuda con un pedido, o quieres hablar sobre la historia detrás de nuestros granos?</p>
                
                <p>Estamos aquí para ayudarte a encontrar tu café perfecto.</p>

                
                <h3>Formas de Contacto</h3>
                
                <div class="info-contacto">
                    
                    <p><strong>Teléfono:</strong> Llámanos al <a href="tel:931486237">931486237</a> (De Lunes a Viernes, de 9:00h a 17:00h).</p>
                    
                    <p><strong>Correo Electrónico:</strong> Envíanos un email a <a href="mailto:lacaffetera1994@gmail.com">lacaffetera1994@gmail.com</a> y te responderemos en un plazo de 24 o 48 horas laborables.</p>
                    <p><strong>Redes Sociales:</strong> Síguenos y envíanos un mensaje directo en <a href="https://www.instagram.com/lacafetera1994/">Instagram</a>. ¡Nos encanta conectar con nuestra comunidad cafetera!</p>             
                </div>
                         
                
                
            </div>
            
        </section>

    </main>

<?php
// 4. Incluir el pie de página
require_once __DIR__ . '/templates/footer.php';
?>