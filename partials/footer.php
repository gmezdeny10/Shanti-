<?php $sla_home = $sla_home ?? ""; ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a href="index.php" class="logo logo-light">
        <img src="images/logo-shanti-lanka.png" alt="Shanti Lanka Ashram" class="logo-img">
      </a>
      <a href="mailto:Shantilankamexico@gmail.com" class="footer-email">Shantilankamexico@gmail.com</a>
      <a href="https://maps.app.goo.gl/TmAgH5whnUUCua7o7" target="_blank" rel="noopener" class="footer-address">Lagunas de Chacahua, Oaxaca · México</a>
    </div>

    <div class="footer-col">
      <p class="eyebrow eyebrow-light">Explorar</p>
      <a href="<?= $sla_home ?>#practicas">Prácticas</a>
      <a href="<?= $sla_home ?>#eventos">Eventos</a>
      <a href="<?= $sla_home ?>#proyectos">Proyectos</a>
    </div>

    <div class="footer-col">
      <p class="eyebrow eyebrow-light">Organización</p>
      <a href="<?= $sla_home ?>#esencia">Nuestra esencia</a>
      <a href="<?= $sla_home ?>#sabiduria">Shivabalayogi</a>
      <a href="<?= $sla_home ?>#participar">Participar y donar</a>
      <a href="<?= $sla_home ?>#contacto">Transparencia</a>
    </div>

    <div class="footer-col footer-newsletter">
      <p class="eyebrow eyebrow-light">Carta comunitaria</p>
      <p>Historias, encuentros y proyectos, una vez al mes.</p>
      <form class="newsletter-form" id="newsletterForm">
        <input type="email" placeholder="tu@correo.org" required>
        <button type="submit" class="btn btn-gold btn-sm">Recibir</button>
      </form>
    </div>
  </div>
  <div class="container footer-bottom">
    <p>© 2026 Shanti Lanka Ashram. Muchos caminos, una sola intención.</p>
  </div>
</footer>

<a href="https://wa.me/529541533391?text=Hola%2C%20me%20gustar%C3%ADa%20conocer%20m%C3%A1s%20sobre%20Shanti%20Lanka%20Ashram"
   target="_blank" rel="noopener" class="whatsapp-fab" aria-label="Escribir por WhatsApp">
  <svg viewBox="0 0 32 32" aria-hidden="true">
    <path d="M16.02 3C9.4 3 4 8.37 4 15c0 2.32.64 4.49 1.76 6.35L3.2 29l7.85-2.5A12.9 12.9 0 0 0 16.02 27C22.63 27 28 21.63 28 15S22.63 3 16.02 3Zm0 2.2c5.4 0 9.78 4.38 9.78 9.8s-4.38 9.8-9.78 9.8a9.7 9.7 0 0 1-4.95-1.36l-.35-.21-4.66 1.48 1.5-4.53-.23-.37A9.72 9.72 0 0 1 6.24 15c0-5.42 4.38-9.8 9.78-9.8Zm-4.5 5.2c-.2 0-.53.08-.8.38-.28.3-1.06 1.04-1.06 2.53 0 1.49 1.08 2.93 1.23 3.13.15.2 2.1 3.3 5.16 4.5 2.55 1 3.07.8 3.63.75.55-.05 1.79-.73 2.04-1.44.25-.7.25-1.3.18-1.44-.08-.14-.28-.22-.58-.37-.3-.15-1.78-.88-2.06-.98-.28-.1-.48-.15-.68.15-.2.3-.78.98-.95 1.18-.18.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.76-1.66-2.06-.18-.3-.02-.46.13-.6.13-.14.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.68-1.7-.95-2.32-.24-.58-.49-.5-.68-.51h-.58Z"/>
  </svg>
</a>

<div class="lightbox" id="lightbox" aria-hidden="true">
  <button class="lightbox-close" id="lightboxClose" aria-label="Cerrar">✕</button>
  <button class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Anterior">‹</button>
  <figure class="lightbox-figure">
    <img id="lightboxImg" src="" alt="">
    <figcaption id="lightboxCaption"></figcaption>
  </figure>
  <button class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Siguiente">›</button>
</div>

