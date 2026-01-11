<?php

// nosotros.php
// Contenido del hero
$bgClass = "bg-nosotros";
$heroTitle = "La Cafetera 1994: Más que café, una tradición";
$heroSubtitle = "La Cafetera 1994 no es solo una tienda de café; es el resultado de una promesa de calidad que hicimos hace más de dos décadas. ";
$heroButtonText = ""; // vacío → no aparece botón
$heroButtonLink = "";




// 2. Incluir el encabezado
include __DIR__ . '/templates/header.php';

?>

<main>
        
    <?php include __DIR__ . '/templates/hero.php'; ?>   
    <?php include __DIR__ . '/templates/chatbot_component.php'; ?>


    <section>
        
        <div class="hero-ritual-right contenedor"> 
        
        <h3 class="ritual-titulo-centrado">Nuestro Ritual de Calidad</h3>
        
        <div class="ritual-puntos-hero"> 
            
            <div class="ritual-item-hero">
                <h4>1. Origen Ético y Selecto</h4> 
                <p>Seleccionamos personalmente nuestros granos de cultivos de altura en regiones legendarias. Nos aseguramos de trabajar bajo un modelo de comercio directo y justo, garantizando una calidad excepcional para ti y un trato digno para los agricultores.</p>
            </div>
            
            <div class="ritual-item-hero">
                <h4>2. El Arte del Tostado Lento</h4>
                <p>Rechazamos los procesos industriales. Tostamos cada variedad de forma artesanal, en pequeños lotes, utilizando el método tradicional de tostado lento. Este ritual nos permite liberar el perfil de sabor completo y complejo de cada grano.</p>
            </div>
            
            <div class="ritual-item-hero">
                <h4>3. Frescura Inigualable</h4>
                <p>Para garantizar ese aroma irresistible, solo tostamos bajo pedido. Tu café se tuesta y se envía en un plazo de 24 a 72 horas desde el tueste. ¡Abre la bolsa y comprueba la frescura!</p>
            </div>
            
        </div>
        
        </div>
        
    </section>

</main>

<?php
// 3. Incluir el pie de página.
require_once __DIR__ . '/templates/footer.php';
?>