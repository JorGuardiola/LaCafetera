<?php

// 1. VARIABLES PARA EL HERO (ESENCIALES)
$bgClass = 'bg-nosotros'; 
$heroTitle = 'La Cafetera 1994: Más que café, una tradición.';
$heroSubTitle = 'La Cafetera 1994 no es solo una tienda de café; es el resultado de una promesa de calidad que hicimos hace más de dos décadas. Todo comenzó en 1994, cuando nuestro fundador, KALDI], se propuso recuperar la auténtica experiencia del café. Desde entonces, nuestro objetivo no ha cambiado: traer el café más honesto y perfectamente tostado directamente de la finca a tu taza.';

// 2. Incluir el encabezado
include __DIR__ . '/templates/header.php';
?>

<div class="header-hero-simple <?php echo $bgClass; ?>">

    <div class="hero-texto-centrado contenedor">
        
        <?php if (isset($heroTitle)): ?>
            <h1><?php echo $heroTitle; ?></h1>
        <?php endif; ?>
        
        <?php if (isset($heroSubTitle)): ?>
            <p><?php echo $heroSubTitle; ?></p>
        <?php endif; ?>
        
        <a href="products.php" class="boton-negro">
        Explora Nuestros Tuestes Únicos
    </a>

    </div>

</div> 

<main class="pagina-nosotros">

    <section class="seccion contenido-principal contenedor">
        
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