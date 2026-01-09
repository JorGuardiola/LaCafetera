<?php
// frontend/index.php
require_once __DIR__ . '/../db/connection.php';

/* Definición de las tarjetas en PHP para duplicarlas fácilmente ANTIGUO
$card_html = [
  1 => '<div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta1.jpg\');"><span class="tag-more">SOSTENIBILIDAD</span><h3>Sostenibilidad en cada grano, respeto en cada sorbo</h3></div>',
  2 => '<div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta2.jpg\');"><span class="tag-more">INNOVACION</span><h3>Innovación constante para una experiencia excepcional</h3></div>',
  3 => '<div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta3.jpg\');"><span class="tag-more">EXPERIENCIA</span><h3>Un mundo de sabores fresco y tostado para disfrutar</h3></div>',
  4 => '<div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta4.jpg\');"><span class="tag-more">CERCANIA</span><h3>Conectando con cada taza, cerca de ti</h3></div>',
  5 => '<div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta5.jpg\');"><span class="tag-more">RESPETO</span><h3>Café ético, sabor extraordinario</h3></div>'
];*/

// Definición de las tarjetas con ENLACES a las páginas internas NUEVO
$card_html = [
  // 1. SOSTENIBILIDAD -> sustainability.php
  1 => '<a href="sustainability.php" style="display:contents">
          <div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta1.jpg\');">
            <span class="tag-more">SOSTENIBILIDAD</span>
            <h3>Sostenibilidad en cada grano, respeto en cada sorbo</h3>
          </div>
        </a>',

  // 2. INNOVACIÓN -> process.php (Elaboración)
  2 => '<a href="process.php" style="display:contents">
          <div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta2.jpg\');">
            <span class="tag-more">INNOVACION</span>
            <h3>Innovación constante para una experiencia excepcional</h3>
          </div>
        </a>',

  // 3. EXPERIENCIA -> nosotros.php
  3 => '<a href="nosotros.php" style="display:contents">
          <div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta3.jpg\');">
            <span class="tag-more">EXPERIENCIA</span>
            <h3>Un mundo de sabores fresco y tostado para disfrutar</h3>
          </div>
        </a>',

  // 4. CERCANÍA -> contacto.php
  4 => '<a href="contacto.php" style="display:contents">
          <div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta4.jpg\');">
            <span class="tag-more">CERCANIA</span>
            <h3>Conectando con cada taza, cerca de ti</h3>
          </div>
        </a>',

  // 5. RESPETO -> information.php (Información/Ayuda)
  5 => '<a href="information.php" style="display:contents">
          <div class="tag-card" style="--bg: url(\'' . BASE_URL . '/assets/img/tarjeta5.jpg\');">
            <span class="tag-more">RESPETO</span>
            <h3>Café ético, sabor extraordinario</h3>
          </div>
        </a>'
];
?>
<?php include __DIR__ . '/templates/header.php'; ?>
<?php include __DIR__ . '/templates/chatbot_component.php'; ?>


 <?php
$bgClass = "bg-default";
$heroTitle = "Llevamos el café de especialidad del campo a la taza";
$heroSubtitle = "";
$heroButtonText = ""; // vacío → no aparece botón
$heroButtonLink = "";
?> 

<main>
    <?php include __DIR__ . '/templates/hero.php'; ?>

<!-- CARRUSEL -->

  <section class="tag-carousel">
    <div class="carousel-header">
      <h2>Nuestros valores</h2>
    </div>

    <div class="carousel-track">
      <?php
      // 1. Mostrar el contenido original
      for ($i = 1; $i <= 5; $i++) {
          echo $card_html[$i];
      }
      // 2. Duplicar el contenido para un bucle sin fin (smooth loop)
      for ($i = 1; $i <= 5; $i++) {
          echo $card_html[$i];
      }
      ?>
   </div>
</section>

<!-- PRODUCTOS DESTACADOS -->

<section class="container">
    <h2 class="center-text">Cafés destacados</h2>



<?php
$sql_destacados = "
    SELECT 
        p.id,
        p.nombre_cafe,
        p.presentacion,
        p.imagen,
        p.puntuacion_sca,
        (SELECT precio FROM producto_variantes pv 
         WHERE pv.producto_id = p.id 
         AND pv.envase = '250g'
         LIMIT 1) AS precio
    FROM productos p
        WHERE p.disponible = 1
        ORDER BY p.id ASC
    LIMIT 4;
";
$stmt = $pdo->prepare($sql_destacados);
$stmt->execute();
?>

<div class="product-grid">
<?php while ($p = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <?php include __DIR__ . '/templates/card.php'; ?>
<?php endwhile; ?>
</div>
</section>

</main>

<!-- script pausar CARRUSEL -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector('.carousel-track');
    const cards = document.querySelectorAll('.tag-card');

    cards.forEach(card => {
        // Al entrar en UNA tarjeta -> Pausar 
        card.addEventListener('mouseenter', () => {
            track.style.animationPlayState = 'paused';
        });
        // Al salir de esa tarjeta -> Reanudar 
        card.addEventListener('mouseleave', () => {
            track.style.animationPlayState = 'running';
        });
    });
});
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>






