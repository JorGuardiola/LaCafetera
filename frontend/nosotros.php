<?php

// 1. VARIABLES PARA LA PLANTILLA HERO.PHP
$bgClass = 'bg-nosotros'; 

// 2. Incluir el encabezado
include __DIR__ . '/templates/header.php';
?>

    <main class="pagina-nosotros">
        
        <?php 
        // 3. Incluir la sección HERO con la imagen de fondo dinámica
          include __DIR__ . '/templates/hero.php'; 
        ?>

        <section class="contenedor seccion contenido-principal">
            
            <div class="texto-nosotros">
                
                <h2>Acerca de La Cafetera 1994: Más que café, una tradición.</h2>
                
                <p>La Cafetera 1994 no es solo una tienda de café; es el resultado de una promesa de calidad que hicimos hace más de dos décadas. Todo comenzó en 1994, cuando nuestro fundador, [Nombre del Fundador/Familia], se propuso recuperar la auténtica experiencia del café, una que priorizara el aroma y el sabor por encima de la velocidad.</p>

                <p>Desde entonces, nuestro objetivo no ha cambiado: traer el café más honesto y perfectamente tostado directamente de la finca a tu taza.</p>

                
                <h3>Nuestro Ritual de Calidad</h3>
                
                <div class="ritual-puntos">
                    
                    <h4>1. Origen Ético y Selecto</h4>
                    <p>Seleccionamos personalmente nuestros granos de cultivos de altura en regiones legendarias. Nos aseguramos de trabajar bajo un modelo de **comercio directo y justo**, garantizando una calidad excepcional para ti y un trato digno para los agricultores.</p>
                    
                    <h4>2. El Arte del Tostado Lento</h4>
                    <p>Rechazamos los procesos industriales. Tostamos cada variedad de forma artesanal, en **pequeños lotes**, utilizando el método tradicional de **tostado lento**. Este ritual nos permite liberar el perfil de sabor completo y complejo de cada grano.</p>
                    
                    <h4>3. Frescura Inigualable</h4>
                    <p>Para garantizar ese aroma irresistible, solo tostamos bajo pedido. Tu café se tuesta y se envía en un plazo de **24 a 72 horas** desde el tueste. ¡Abre la bolsa y comprueba la frescura!</p>
                
                </div>
                
                
                <h3>Los Rostros Detrás de la Tostadora</h3>

                <div class="equipo-info">
                    <p>Somos un equipo apasionado de amantes del café, pero el corazón de La Cafetera 1994 es nuestro maestro tostador.</p>
                    
                    <p><strong>[Nombre del Tostador, ej: Manuel López]</strong>, Maestro Tostador.</p>
                    <p class="cita"><em>“Llevo [X] años supervisando cada lote. Para mí, el tostado no es ciencia; es un arte que se siente, se huele y se prueba. ¡Espero que disfrutes el resultado!”</em></p>
                    
                    
                </div>
                
                
                <div class="llamada-a-la-accion">
                    <p>Creemos firmemente que la vida es demasiado corta para el café mediocre.</p>
                    <a href="productos.php" class="boton boton-primario">
                        Explora Nuestros Tuestes Únicos
                    </a>
                </div>

            </div>
        </section>

    </main>

<?php
// 4. Incluir el pie de página
require_once __DIR__ . '/templates/footer.php';
?>