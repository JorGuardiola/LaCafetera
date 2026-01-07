<?php
// frontend/sustainability.php

// 1. VARIABLES PARA EL HERO
$bgClass = 'bg-sustainability'; 
$heroTitle = 'Sostenibilidad: Nuestra Raíz';
$heroSubTitle = 'El buen café no debe costarle el futuro al planeta. En La Cafetera 1994, trabajamos cada día para reducir nuestra huella y multiplicar el impacto positivo en las comunidades cafeteras.';

// 2. Incluir el encabezado
include __DIR__ . '/templates/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* Asegurar tamaño de letra legible en listas y párrafos */
    main p, main li { font-size: 1.6rem; line-height: 1.8; color: #444; }
    
    /* Iconos de los pilares */
    .icon-pillar {
        font-size: 4rem;
        color: #2E7D32; /* Verde bosque */
        margin-bottom: 2rem;
        display: block;
    }
    
    /* Pequeño ajuste al grid */
    .ritual-puntos-hero { gap: 4rem; }
    
    /* Tarjeta inferior */
    .cta-box {
        background-color: #FAF7F2; 
        padding: 5rem 3rem; 
        border-radius: 12px; 
        text-align: center;
        max-width: 900px;
        margin: 6rem auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
</style>

<div class="header-hero-simple <?php echo $bgClass; ?>">
    <div class="hero-texto-centrado contenedor">
        <?php if (isset($heroTitle)): ?>
            <h1><?php echo $heroTitle; ?></h1>
        <?php endif; ?>
        
        <?php if (isset($heroSubTitle)): ?>
            <p><?php echo $heroSubTitle; ?></p>
        <?php endif; ?>
    </div>
</div> 

<main style="max-width: 1200px; margin: 5rem auto; padding: 0 2rem;">

    <section class="seccion contenedor">
        <h2 class="center-text" style="margin-bottom: 1rem;">Nuestros 3 Compromisos</h2>
        <p class="center-text" style="max-width: 700px; margin: 0 auto 5rem auto;">Un enfoque circular que abarca desde la finca hasta el residuo final.</p>
        
        <div class="ritual-puntos-hero">
            
            <div class="ritual-item-hero">
                <i class="fas fa-hands-helping icon-pillar"></i>
                <h3>Comercio Justo</h3> 
                <p>No negociamos precios, pagamos calidad. Trabajamos directamente con los caficultores, eliminando intermediarios para garantizar que reciben un pago digno superior al precio de mercado.</p>
            </div>
            
            <div class="ritual-item-hero">
                <i class="fas fa-recycle icon-pillar"></i>
                <h3>Envases Eco-Friendly</h3>
                <p>Adiós al aluminio. Nuestras bolsas son 100% libres de plásticos complejos. Usamos materiales monomateriales (PEBD 4) reciclables y tintas al agua biodegradables.</p>
            </div>
            
            <div class="ritual-item-hero">
                <i class="fas fa-leaf icon-pillar"></i>
                <h3>Tueste Eficiente</h3>
                <p>Nuestra tostadora Loring™ utiliza un sistema de recirculación de aire que reduce las emisiones de CO2 y el consumo de gas en un 40% comparado con tostadoras tradicionales.</p>
            </div>
            
        </div>
    </section>

    <section class="cta-box">
        <h3 style="font-family: var(--fuenteHeading); font-size: 2.8rem; margin-bottom: 2rem;">¿Qué puedes hacer tú?</h3>
        <p style="margin-bottom: 3rem;">
            La sostenibilidad es un ciclo. Nosotros ponemos el café ético, tú cierras el círculo. <br>
            <strong>Tip:</strong> Usa los posos de café como abono natural para tus plantas, ¡les encanta el nitrógeno!
        </p>
        <a href="products.php" class="boton-negro">Ver Cafés Sostenibles</a>
    </section>

</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>