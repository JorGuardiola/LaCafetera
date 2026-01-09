<?php
// frontend/information.php

// 1. Configuración del Hero
$bgClass = 'bg-info'; 
$heroTitle = 'Información y Ayuda';
$heroSubTitle = 'Queremos que tu experiencia sea tan perfecta como nuestro café. Aquí tienes todo lo que necesitas saber sobre envíos, devoluciones y métodos de compra.';

// 2. Incluir header
include __DIR__ . '/templates/header.php';
?>

<style>
    /* Iconos de confianza: Azul Petróleo (Primary-500 aprox) */
    .icon-info {
        font-size: 4rem;
        color: #22364A; /* Tu color corporativo oscuro */
        margin-bottom: 2rem;
        display: block;
    }
    
    /* Estilos para las Preguntas Frecuentes (Acordeón simple o lista) */
    .faq-section {
        max-width: 800px;
        margin: 6rem auto;
        padding: 0 2rem;
    }
    
    .faq-item {
        border-bottom: 1px solid #ddd;
        padding: 2rem 0;
    }
    
    .faq-item h3 {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        color: #1A1A1A;
    }
    
    .faq-item p {
        color: #555;
        font-size: 1.5rem;
        margin: 0;
    }
</style>

<div class="header-hero-simple <?php echo $bgClass; ?>">
    <div class="hero-texto-centrado contenedor">
        <h1><?php echo $heroTitle; ?></h1>
        <p><?php echo $heroSubTitle; ?></p>
    </div>
</div> 

<main>

    <section class="seccion contenedor">
        <h2 class="center-text" style="margin-bottom: 4rem;">Compra con Tranquilidad</h2>
        
        <div class="hero-ritual-right">
            <div class="ritual-puntos-hero">
                
                <div class="ritual-item-hero">
                    <i class="fas fa-shipping-fast icon-info"></i>
                    <h4>Envíos 24/48h</h4> 
                    <p>Sabemos que la cafeína urge. Preparamos tu pedido el mismo día (si es antes de las 14:00) para que lo recibas volando. Envío GRATIS en pedidos superiores a 50€.</p>
                </div>
                
                <div class="ritual-item-hero">
                    <i class="fas fa-box-open icon-info"></i>
                    <h4>Garantía de Frescura</h4>
                    <p>Si el café no te llega en condiciones óptimas o ha habido un error, te lo reponemos sin coste. Tienes 14 días para devoluciones de accesorios y equipamiento.</p>
                </div>
                
                <div class="ritual-item-hero">
                    <i class="fas fa-lock icon-info"></i>
                    <h4>Pago 100% Seguro</h4>
                    <p>Tu seguridad es lo primero. Procesamos los pagos mediante pasarela encriptada (SSL). Aceptamos tarjeta, PayPal y Bizum.</p>
                </div>
                
            </div>
        </div>
    </section>

    <section class="faq-section">
        <h2 class="center-text" style="margin-bottom: 3rem;">Preguntas Frecuentes (FAQ)</h2>

        <div class="faq-item">
            <h3>¿El café viene molido o en grano?</h3>
            <p>¡Tú eliges! En la ficha de cada producto puedes seleccionar "Grano entero" o el tipo de molienda específica para tu cafetera (Italiana, Espresso, Filtro, etc.).</p>
        </div>

        <div class="faq-item">
            <h3>¿Cómo debo conservar el café?</h3>
            <p>Nuestras bolsas tienen válvula desgasificadora y cierre zip. Guárdalo en un lugar fresco y seco, lejos de la luz directa. ¡Nunca en la nevera!</p>
        </div>

        <div class="faq-item">
            <h3>¿Hacéis envíos a Canarias o Baleares?</h3>
            <p>Sí, llegamos a toda España. Los envíos a Baleares tardan 48/72h. Para Canarias, Ceuta y Melilla, contáctanos antes para gestionar aduanas.</p>
        </div>
        
        <div class="faq-item">
            <h3>¿Puedo modificar mi pedido una vez hecho?</h3>
            <p>Si no ha salido de nuestro almacén, sí. Escríbenos urgentemente a través del formulario de contacto o llámanos.</p>
        </div>

    </section>
    
    <div style="text-align: center; margin-bottom: 6rem;">
        <p style="font-size: 1.6rem; margin-bottom: 2rem;">¿Tienes más dudas?</p>
        <a href="contacto.php" class="boton-negro">Ir a Contacto</a>
    </div>

</main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
