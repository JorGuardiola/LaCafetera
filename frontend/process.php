<?php
// frontend/process.php "Elaboración" en el footer

// 1. Configuración del Hero
$bgClass = 'bg-elaboracion'; 
$heroTitle = 'El Arte del Proceso';
$heroSubTitle = 'El café de especialidad no es casualidad. Es el resultado de un control obsesivo en cada etapa, desde la recolección manual de la cereza hasta la curva exacta de tueste.';

// 2. Incluir header
include __DIR__ . '/templates/header.php';
?>

<style>
    /* Iconos personalizados por color */
    .icon-process {
        font-size: 4rem;
        color: #D35400; 
        margin-bottom: 2rem;
        display: block;
    }
    
    /* Texto de introducción centrado */
    .intro-process {
        max-width: 800px; 
        margin: 5rem auto; 
        text-align: center;
        padding: 0 2rem;
    }
    
    /* Ajuste para que las tarjetas de los pasos tengan la misma altura */
    .ritual-item-hero {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
</style>

<div class="header-hero-simple <?php echo $bgClass; ?>">
    <div class="hero-texto-centrado contenedor">
        <h1><?php echo $heroTitle; ?></h1>
        <p><?php echo $heroSubTitle; ?></p>
    </div>
</div> 

<main>

    <section class="intro-process">
        <h2>De la Cereza al Grano</h2>
        <p>No utilizamos maquinaria industrial. Cada lote es tratado individualmente para respetar su ADN y resaltar las notas propias de su origen.</p>
    </section>

    <section class="seccion contenedor">
        <div class="hero-ritual-right">
            <div class="ritual-puntos-hero">
                
                <div class="ritual-item-hero">
                    <i class="fas fa-seedling icon-process"></i>
                    <h4>1. Selección Manual</h4> 
                    <p>Solo recolectamos las cerezas en su punto óptimo de maduración (sangre de toro). Una recolección selectiva garantiza el dulzor natural en tu taza.</p>
                </div>
                
                <div class="ritual-item-hero">
                    <i class="fas fa-sun icon-process"></i>
                    <h4>2. Beneficio y Secado</h4>
                    <p>Ya sea lavado, natural o honey, secamos los granos en camas africanas elevadas, moviéndolos cada hora para asegurar una humedad uniforme del 11%.</p>
                </div>
                
                <div class="ritual-item-hero">
                    <i class="fas fa-fire-alt icon-process"></i>
                    <h4>3. Tueste de Precisión</h4>
                    <p>Diseñamos una curva de tueste única para cada lote. Tostamos en pequeñas cantidades para desarrollar los aromas sin quemar el grano.</p>
                </div>
                
            </div>
        </div>
    </section>

        <section class="contenedor">
        <div class="llamada-a-la-accion">
            <p>Control de Calidad (Cata SCA)</p>
            <p style="font-size: 1.6rem; margin-top: -1rem; margin-bottom: 3rem; max-width: 600px;">
                Antes de envasar, nuestro equipo de Q-Graders cata cada lote siguiendo los protocolos de la Specialty Coffee Association.<br> 
                Solo los cafés que superan los 80 puntos llegan a nuestra tienda.
            </p>
            <a href="products.php" class="boton-negro">Probar el Resultado</a>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>