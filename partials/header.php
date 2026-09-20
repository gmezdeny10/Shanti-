<?php $sla_home = $sla_home ?? ""; ?>
<header class="site-header" id="siteHeader">
  <div class="header-inner">
    <a href="index.php" class="logo">
      <img src="images/logo-shanti-lanka.png" alt="Shanti Lanka Ashram" class="logo-img">
    </a>

    <nav class="main-nav" id="mainNav">
      <a href="<?= $sla_home ?>#esencia">Nuestra esencia</a>
      <a href="<?= $sla_home ?>#instalaciones">Instalaciones</a>
      <a href="<?= $sla_home ?>#ubicacion">Ubicación</a>
      <a href="<?= $sla_home ?>#practicas">Prácticas</a>
      <a href="<?= $sla_home ?>#eventos">Eventos</a>
      <div class="nav-more" id="navMore">
        <button type="button" class="nav-more-trigger" id="navMoreTrigger" aria-expanded="false">
          Más <span class="nav-more-caret" aria-hidden="true">▾</span>
        </button>
        <div class="nav-more-panel" id="navMorePanel">
          <a href="<?= $sla_home ?>#sabiduria">Shivabalayogi</a>
          <a href="<?= $sla_home ?>#proyectos">Proyectos</a>
          <a href="<?= $sla_home ?>#participar">Participar</a>
        </div>
      </div>
    </nav>

    <div class="header-actions">
      <?php if (function_exists('sla_is_logged_in') && sla_is_logged_in()): ?>
        <a href="admin/index.php" class="header-access">Panel</a>
      <?php else: ?>
        <a href="admin/login.php" class="header-access">Acceder</a>
      <?php endif; ?>
      <a href="calendario.php" class="btn btn-outline btn-sm">Ver el calendario</a>
      <a href="<?= $sla_home ?>#participar" class="btn btn-accent btn-sm">Únirme a la red</a>
    </div>

    <button class="hamburger" id="hamburger" aria-label="Abrir menú" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<div class="mobile-backdrop" id="mobileBackdrop"></div>
<div class="mobile-panel" id="mobilePanel" aria-hidden="true">
  <div class="mobile-panel-head">
    <span class="mobile-panel-title">Shanti Lanka Ashram</span>
    <button type="button" class="mobile-panel-close" id="mobilePanelClose" aria-label="Cerrar menú">✕</button>
  </div>
  <nav class="mobile-panel-nav" id="mobileNav">
    <a href="<?= $sla_home ?>#esencia">Nuestra esencia</a>
    <a href="<?= $sla_home ?>#instalaciones">Instalaciones</a>
    <a href="<?= $sla_home ?>#ubicacion">Ubicación</a>
    <a href="<?= $sla_home ?>#practicas">Prácticas</a>
    <a href="<?= $sla_home ?>#eventos">Eventos</a>
    <a href="<?= $sla_home ?>#sabiduria">Shivabalayogi</a>
    <a href="<?= $sla_home ?>#proyectos">Proyectos</a>
    <a href="<?= $sla_home ?>#participar">Participar</a>
  </nav>
  <div class="mobile-panel-actions">
    <a href="calendario.php" class="btn btn-outline">Ver el calendario</a>
    <a href="<?= $sla_home ?>#participar" class="btn btn-accent">Únirme a la red</a>
    <?php if (function_exists('sla_is_logged_in') && sla_is_logged_in()): ?>
      <a href="admin/index.php" class="mobile-access">Panel de administración</a>
    <?php else: ?>
      <a href="admin/login.php" class="mobile-access">Acceder</a>
    <?php endif; ?>
  </div>
</div>
