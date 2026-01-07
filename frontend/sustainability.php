<?php
// frontend/sustainability.php

// 1. Configuración del Hero
$bgClass = 'bg-sostenibilidad'; 
$heroTitle = 'Sostenibilidad: Nuestra Raíz';
$heroSubTitle = 'El buen café no debe costarle el futuro al planeta. En La Cafetera 1994, trabajamos cada día para reducir nuestra huella y multiplicar el impacto positivo.';

// 2. Incluir header
include __DIR__ . '/templates/header.php';
?>

<style>
    .icon-sost {
        font-size: 4rem;
        color: #2E7D32; /* Verde bosque */
        margin-bottom: 2rem;
        display: block;
    }
    /* Pequeño ajuste para que el texto principal respire */
    .intro-sostenibilidad {
        max-width: 800px; 
        margin: 4rem auto; 
        text-align: center;
        padding: 0 2rem;
    }
</style>

<div class="header-hero-simple <?php echo $bgClass; ?>">
    <div class="hero-texto-centrado contenedor">
        <h1><?php echo $heroTitle; ?></h1>
        <p><?php echo $heroSubTitle; ?></p>
    </div>
</div> 

<main>

    <section class="intro-sostenibilidad">
        <h2>Un Compromiso Circular</h2>
        <p>Desde el cultivo hasta tu taza, y de vuelta a la tierra. Nuestro modelo se basa en tres pilares fundamentales que garantizan calidad ética.</p>
    </section>

    <section class="seccion contenedor">
        <div class="hero-ritual-right">
            
            <div class="ritual-puntos-hero">
                
                <div class="ritual-item-hero">
                    <i class="fas fa-hands-helping icon-sost"></i>
                    <h4>Comercio Justo</h4> 
                    <p>No negociamos precios a la baja. Trabajamos directamente con los caficultores, eliminando intermediarios para garantizar que reciben un pago digno superior al mercado.</p>
                </div>
                
                <div class="ritual-item-hero">
                    <i class="fas fa-recycle icon-sost"></i>
                    <h4>Envases Eco-Friendly</h4>
                    <p>Adiós al aluminio. Nuestras bolsas son 100% libres de plásticos complejos. Usamos materiales monomateriales reciclables y tintas al agua.</p>
                </div>
                
                <div class="ritual-item-hero">
                    <i class="fas fa-leaf icon-sost"></i>
                    <h4>Tueste Eficiente</h4>
                    <p>Nuestra tostadora utiliza un sistema de recirculación de aire caliente, reduciendo el consumo de gas y las emisiones en un 40% respecto a métodos tradicionales.</p>
                </div>
                
            </div>
        </div>
    </section>

    <section class="contenedor">
        <div class="llamada-a-la-accion">
            <p>¿Quieres apoyar este movimiento?</p>
            <p style="font-size: 1.6rem; margin-top: -1rem; margin-bottom: 3rem; max-width: 600px;">
                Cada vez que eliges nuestros granos, estás invirtiendo en agricultura regenerativa y comunidades prósperas.
            </p>
            <a href="products.php" class="boton-negro">Ver Cafés Sostenibles</a>
        </div>
    </section>
        
    <section class="contenedor">
        <div class="llamada-a-la-accion">
            <p>¿Qué puedes hacer tú?</p>
            <p style="font-size: 1.6rem; margin-top: -1rem; margin-bottom: 3rem; max-width: 600px;">
                La sostenibilidad es un ciclo. Nosotros ponemos el café ético, tú cierras el círculo.<br>
            <strong>Tip:</strong> Usa los posos de café como abono natural para tus plantas, ¡les encanta el nitrógeno!
            </p>
            <a href="products.php" class="boton-negro">Ver Cafés Sostenibles</a>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>