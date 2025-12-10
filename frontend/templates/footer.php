<?php
// frontend/templates/footer.php
?>
<footer class="footer">
  <div class="footer-container">

    <!-- Columna 1 Logo + Descripción -->
    <div class="footer-col footer-brand">
      <img src="<?= BASE_URL ?>/assets/img/logo-white.png" alt="La Cafetera" class="footer-logo">
      <p class="footer-desc">
        Desde 1994 seleccionando los mejores cafés del mundo para llevarlos a tu taza.
      </p>
    </div>

    <!-- Columna 2 -->
    <div class="footer-col">
      <h4>Cafetera</h4>
      <ul>
        <li><a href="<?= BASE_URL ?>/frontend/nosotros.php">Nosotros</a></li>
        <li><a href="#">Elaboración</a></li>
        <li><a href="#">Sostenibilidad</a></li>
        <li><a href="<?= BASE_URL ?>/frontend/contacto.php">Contacto</a></li>
      </ul>
    </div>

    <!-- Columna 3 -->
    <div class="footer-col">
      <h4>Productos</h4>
      <ul>
        <li><a href="<?= BASE_URL ?>/frontend/products.php">Productos</a></li>
        <li><a href="#">Packs</a></li>
        <li><a href="#">Recetas</a></li>
        <li><a href="#">Información</a></li>
      </ul>
    </div>

    <!-- Columna 4 Iconos + Botón -->
    <div class="footer-col footer-right">
      <div class="footer-social">
        <a href="https://www.instagram.com/lacafetera1994/" target="_blank"><img src="../assets/img/instagram.svg" alt="Instagram"></a>
        <a href="#"><img src="<?= BASE_URL ?>/assets/img/facebook.svg" alt="Facebook"></a>
        <a href="#"><img src="<?= BASE_URL ?>/assets/img/whatsapp.svg" alt="WhatsApp"></a>
      </div>
        <a href="<?= BASE_URL ?>/frontend/contacto.php" class="footer-btn">Contacto</a>    </div>

  </div>

  <!-- Línea inferior -->
  <div class="footer-bottom">
    <p>© 2025 La Cafetera. Todos los derechos reservados.</p>
    <div class="footer-links">
      <a href="#">Política de privacidad</a>
      <a href="#">Términos y condiciones</a>
      <a href="#">Cookies</a>
    </div>
  </div>
</footer>

</body>
</html>
