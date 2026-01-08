<?php
// frontend/404.php

// 1. Enviar cabecera HTTP correcta (Importante para SEO)
http_response_code(404);

// 2. Configuración del Hero , reutilizamos bg-default con filtro
$bgClass = "bg-default"; 
$heroInlineStyle = "filter: grayscale(100%); -webkit-filter: grayscale(100%);";

$heroTitle = "Error 404: Café no encontrado";
$heroSubtitle = "Parece que esta taza está vacía. La página que buscas no existe o ha sido movida de sitio.";
$heroButtonText = "Volver al inicio";
$heroButtonLink = "index.php";

// 3. Incluir Header
include __DIR__ . '/templates/header.php';
?>

<main>
    
    <?php include __DIR__ . '/templates/hero.php'; ?>

    <div class="container center-text" style="margin: 6rem auto;">
        <h3>¿Te has perdido?</h3>
        <p style="margin-bottom: 2rem; color: #666;">
            No te preocupes, mientras encuentras el camino puedes echar un vistazo a nuestros mejores granos.
        </p>
        <a href="products.php" class="boton-negro">
            Ir al Catálogo
        </a>
    </div>

</main>

<?php 
// 4. Incluir Footer
include __DIR__ . '/templates/footer.php'; 
?>