<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/model.php';

$proximos = sla_upcoming_events(3);
$eventos  = sla_upcoming_events(6);
$esAdmin  = sla_is_logged_in();
$enviado  = $_GET['mensaje'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shanti Lanka Ashram — Muchos caminos, una intención</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500&family=Karla:wght@300;400;500;600&family=Caveat:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=16">
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>


<main>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg" id="heroBg">
      <img src="images/hero-comunidad.jpg" alt="Comunidad del Shanti Lanka Ashram en ceremonia" style="object-position: center 30%;">
      <div class="hero-overlay"></div>
    </div>
    <div class="hero-particles" id="heroParticles" aria-hidden="true"></div>
    <div class="hero-content">
      <p class="eyebrow eyebrow-light">Shanti Lanka Ashram</p>
      <h1>Muchos caminos.<br>Una sola intención: <span class="script">amar.</span></h1>
      <p class="hero-sub">Conectamos personas, prácticas, culturas y saberes que contribuyen al crecimiento humano y al despertar espiritual.</p>
      <div class="hero-actions">
        <a href="#eventos" class="btn btn-gold">Explorar la comunidad</a>
        <a href="#esencia" class="btn btn-ghost-light">Conocer nuestra esencia</a>
      </div>
      <p class="scroll-hint">Desliza con calma <span class="scroll-arrow" aria-hidden="true"></span></p>
    </div>
  </section>

  <!-- NOVEDADES (banner informativo, con carrusel si hay varios encuentros) -->
  <section class="news-banner" aria-label="Novedades">
    <div class="container news-banner-inner">
      <div class="news-banner-label">
        <svg class="news-banner-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8L12 2z"/></svg>
        <span class="news-banner-dot" aria-hidden="true"></span>
        Novedades
      </div>
      <div class="news-banner-carousel" data-news-carousel>
        <ul class="news-banner-list">
          <?php if (!$proximos): ?>
            <li class="news-slide active"><span class="news-chip news-chip-static">Pronto anunciaremos las próximas actividades.</span></li>
          <?php else: foreach ($proximos as $i => $ev): [$d, $m] = sla_date_parts($ev['event_date']); ?>
            <li class="news-slide <?= $i === 0 ? 'active' : '' ?>">
              <a href="evento.php?id=<?= (int) $ev['id'] ?>" class="news-chip">
                <span class="news-chip-tag">Nuevo</span>
                <span class="news-date"><?= e($d . ' ' . strtolower($m)) ?></span>
                <span class="news-title"><?= e($ev['title']) ?></span>
                <span class="news-chip-arrow" aria-hidden="true">→</span>
              </a>
            </li>
          <?php endforeach; endif; ?>
        </ul>
        <?php if (count($proximos) > 1): ?>
          <div class="news-banner-dots" data-news-dots>
            <?php foreach ($proximos as $i => $ev): ?>
              <button type="button" class="news-banner-tick <?= $i === 0 ? 'active' : '' ?>" data-news-dot="<?= $i ?>" aria-label="Ver novedad <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <a href="#eventos" class="news-banner-link">Ver todos los eventos →</a>
    </div>
  </section>

  <!-- MANIFESTO -->
  <section class="manifesto" id="esencia">
    <div class="container narrow">
      <p class="eyebrow">Manifiesto</p>
      <h2>Un puente entre culturas, corazones y experiencias.</h2>
      <p class="lead">Shanti Lanka Ashram nace para acercar los distintos caminos del crecimiento espiritual.</p>
      <p>Creamos conexiones entre quienes buscan alternativas y quienes comparten prácticas, conocimientos, espacios y formas de servicio alrededor del mundo.</p>
    </div>
    <div class="ticker">
      <span>PAZ</span><span class="divider"></span>
      <span>UNIDAD</span><span class="divider"></span>
      <span>AMOR</span><span class="divider"></span>
      <span>SERVICIO</span>
    </div>
  </section>

  <!-- SWAMI KENA -->
  <section class="guide" id="guia">
    <div class="container split guide-split">
      <div class="guide-img">
        <img src="images/swami-kena.jpg" alt="Swami Kenananda, guía espiritual del Shanti Lanka Ashram">
      </div>
      <div class="guide-copy">
        <p class="eyebrow">Guía espiritual</p>
        <h2>Swami Kenananda</h2>
        <p class="lead">Al frente del Shanti Lanka Ashram, sosteniendo el espacio donde se encuentran los distintos caminos.</p>
        <p>Su presencia guía las prácticas de meditación, silencio y servicio que dan forma a esta comunidad, acompañando a quienes llegan buscando un lugar donde caminar junto a otros.</p>
        <a href="#esencia" class="link-arrow">Conocer su enseñanza →</a>
      </div>
    </div>
  </section>

  <!-- INSTALACIONES -->
  <section class="facilities" id="instalaciones">
    <div class="container">
      <p class="eyebrow">Nuestro hogar</p>
      <h2>Las instalaciones del Shanti Lanka Ashram.</h2>
      <p class="lead">Un refugio de madera, palma y arena junto al mar, construido para practicar en comunidad.</p>

      <div class="facilities-grid">
        <figure class="facility-tile large">
          <img src="images/instalaciones-3.jpg" alt="Vista aérea del ashram junto al mar">
          <figcaption>Nuestro hogar junto al mar</figcaption>
        </figure>
        <figure class="facility-tile">
          <img src="images/instalaciones-yoga-1.jpg" alt="Templo de meditación">
          <figcaption>Templo de meditación</figcaption>
        </figure>
        <figure class="facility-tile">
          <img src="images/instalaciones-yoga-2.jpg" alt="Salón de yoga">
          <figcaption>Salón de yoga</figcaption>
        </figure>
        <figure class="facility-tile">
          <img src="images/instalaciones-hospedaje.jpg" alt="Cabañas de hospedaje">
          <figcaption>Cabañas de hospedaje</figcaption>
        </figure>
        <figure class="facility-tile">
          <img src="images/instalaciones-4.jpg" alt="Habitaciones compartidas">
          <figcaption>Habitaciones</figcaption>
        </figure>
        <figure class="facility-tile">
          <img src="images/instalaciones-vista-aerea.jpg" alt="El ashram entre la selva y el mar">
          <figcaption>Entre la selva y el mar</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <!-- UBICACIÓN -->
  <section class="visit-section" id="ubicacion">
    <div class="container split location-split">
      <div class="location-copy">
        <p class="eyebrow">Cómo llegar</p>
        <h2>Visítanos junto al mar, en Oaxaca.</h2>
        <p class="lead location-lead">Lagunas de Chacahua, Oaxaca · México</p>
        <p>Ubicados frente al Pacífico, en la costa oaxaqueña. Escríbenos antes de tu visita para coordinar tu llegada y conocer los horarios de práctica.</p>
        <a href="https://maps.app.goo.gl/TmAgH5whnUUCua7o7" target="_blank" rel="noopener" class="btn btn-dark">Cómo llegar →</a>
      </div>
      <div class="location-map">
        <iframe
          src="https://www.google.com/maps?q=15.979111,-97.653889&z=15&output=embed"
          width="100%" height="100%" style="border:0;"
          allowfullscreen="" loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="Ubicación del Shanti Lanka Ashram">
        </iframe>
      </div>
    </div>
  </section>

  <!-- FOUR VALUES -->
  <section class="values">
    <div class="values-bg">
      <img src="images/instalaciones-3.jpg" alt="Océano al atardecer">
      <div class="values-overlay"></div>
    </div>
    <div class="values-circle">
      <span class="v-word v-top">SERVICIO</span>
      <span class="v-word v-right">UNIDAD</span>
      <span class="v-word v-bottom">AMOR</span>
      <span class="v-word v-left">PAZ</span>
      <span class="v-dot"></span>
      <span class="ring ring-1"></span>
      <span class="ring ring-2"></span>
    </div>
    <div class="container narrow values-copy">
      <p class="eyebrow eyebrow-light">Los cuatro valores</p>
      <div class="value-block">
        <h2>PAZ</h2>
        <p>Es una cualidad que practicamos y vivimos día a día. Se encuentra dentro de cada corazón y la compartimos durante el camino.</p>
      </div>
      <div class="value-block">
        <h2>UNIDAD</h2>
        <p>Tejemos puentes entre culturas y tradiciones para recordar que caminamos juntos hacia un mismo horizonte.</p>
      </div>
      <div class="value-block">
        <h2>AMOR</h2>
        <p>La intención que sostiene cada encuentro, cada práctica y cada servicio que compartimos como comunidad.</p>
      </div>
      <div class="value-block">
        <h2>SERVICIO</h2>
        <p>Ponemos nuestros dones al servicio de las comunidades y de la tierra que nos sostiene.</p>
      </div>
    </div>
  </section>

  <!-- MISSION / VISION -->
  <section class="mission-vision">
    <div class="container">
      <div class="mv-grid">
        <div class="mv-block">
          <p class="eyebrow">Lo que hacemos hoy</p>
          <h2>Misión</h2>
          <p>Ser una fuente de acceso para quienes buscan el crecimiento espiritual desde las prácticas holísticas que convergen en distintos puntos del mundo. Promovemos un intercambio cultural donde la sabiduría y el servicio forman una comunidad holística sólida y accesible.</p>
        </div>
        <div class="mv-block">
          <p class="eyebrow">El horizonte que construimos</p>
          <h2>Visión</h2>
          <p>Ser una organización sin fines de lucro que genera actividades y teje redes entre culturas, corazones y experiencias para contribuir al despertar espiritual de la humanidad, abriendo centros holísticos en distintos puntos del planeta.</p>
        </div>
      </div>
      <button type="button" class="link-arrow as-button" id="abrirEsencia">Leer nuestra esencia completa →</button>
    </div>
  </section>

  <!-- PRACTICES -->
  <section class="practices" id="practicas">
    <div class="container">
      <p class="eyebrow">Prácticas y caminos</p>
      <h2>Distintas puertas, la misma casa.</h2>
      <div class="tile-grid">
        <a class="tile" href="calendario.php">
          <img src="images/practica-9730.jpg" alt="Meditación">
          <span class="tile-title">Meditación</span>
        </a>
        <a class="tile" href="calendario.php">
          <img src="images/practica-0462.jpg" alt="Yoga">
          <span class="tile-title">Yoga</span>
        </a>
        <a class="tile" href="calendario.php">
          <img src="images/practica-2666.jpg" alt="Terapias holísticas">
          <span class="tile-title">Terapias holísticas</span>
        </a>
      </div>
      <a href="calendario.php" class="link-arrow">Ver cuándo se practican →</a>
    </div>
  </section>

  <!-- EVENTS -->
  <section class="events" id="eventos">
    <div class="container">
      <p class="eyebrow">Eventos y encuentros</p>
      <h2>Donde los caminos se encuentran.</h2>

      <div class="event-list">
        <?php if (!$eventos): ?>
          <p class="empty-note">Por ahora no hay eventos programados. Vuelve pronto.</p>
        <?php else: foreach ($eventos as $ev): [$d, $m] = sla_date_parts($ev['event_date']); ?>
          <article class="event-card">
            <?php if ($ev['image']): ?>
              <div class="event-img"><img src="<?= e($ev['image']) ?>" alt="<?= e($ev['title']) ?>"></div>
            <?php endif; ?>
            <p class="eyebrow"><?= e(strtolower($d . ' ' . $m)) ?> · <?= e($ev['kind']) ?></p>
            <h3><?= e($ev['title']) ?></h3>
            <p class="location"><?= e($ev['location']) ?><?= $ev['modality'] ? ' — ' . e($ev['modality']) : '' ?></p>
            <p class="meta">
              <?= e($ev['organizer']) ?>
              <?= $ev['capacity'] ? ' · ' . (int) $ev['capacity'] . ' lugares' : '' ?>
              <?= $ev['price_note'] ? ' · ' . e($ev['price_note']) : '' ?>
            </p>
            <a href="evento.php?id=<?= (int) $ev['id'] ?>" class="link-arrow">Ver e inscribirme →</a>
          </article>
        <?php endforeach; endif; ?>
      </div>

      <div class="directory-cta">
        <a href="calendario.php" class="btn btn-outline">Ver el calendario</a>
        <?php if ($esAdmin): ?>
          <a href="admin/eventos.php?nuevo=1" class="link-arrow">+ Publicar un evento</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- NETWORK -->
  <section class="network" id="proyectos">
    <div class="container narrow">
      <p class="eyebrow eyebrow-light">Red global</p>
      <h2 class="light">Una constelación humana en movimiento.</h2>
    </div>
    <div class="map-graphic">
      <svg viewBox="0 0 800 400" preserveAspectRatio="xMidYMid slice">
        <defs>
          <pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse">
            <path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(243,234,217,0.12)" stroke-width="1"/>
          </pattern>
        </defs>
        <rect width="800" height="400" fill="url(#grid)"/>
        <path id="arc1" class="map-arc" d="M120,300 Q300,120 380,270" fill="none" stroke="rgba(247,183,63,0.65)" stroke-width="1.5"/>
        <path id="arc2" class="map-arc" d="M380,270 Q500,150 620,220" fill="none" stroke="rgba(247,183,63,0.65)" stroke-width="1.5"/>
        <path id="arc3" class="map-arc map-arc-alt" d="M120,300 Q260,340 380,270" fill="none" stroke="rgba(227,95,44,0.45)" stroke-width="1.5"/>

        <circle class="map-spark" r="3" fill="#fff3d6">
          <animateMotion dur="4.5s" repeatCount="indefinite" rotate="auto">
            <mpath href="#arc1"/>
          </animateMotion>
        </circle>
        <circle class="map-spark" r="3" fill="#fff3d6">
          <animateMotion dur="5.5s" begin="1.2s" repeatCount="indefinite" rotate="auto">
            <mpath href="#arc2"/>
          </animateMotion>
        </circle>

        <circle class="map-node" cx="120" cy="300" r="5" fill="#f7b73f"/>
        <circle class="map-node map-node-main" cx="380" cy="270" r="6" fill="#e35f2c"/>
        <circle class="map-node" cx="620" cy="220" r="5" fill="#f7b73f"/>
        <circle class="map-ping" cx="380" cy="270" r="16" fill="none" stroke="#f7b73f" stroke-width="1" opacity="0.4"/>
      </svg>
    </div>
    <div class="container narrow">
      <p class="tagline">La luz no pertenece a un solo lugar.</p>
    </div>
  </section>

  <!-- WISDOM -->
  <section class="wisdom" id="sabiduria">
    <div class="container split">
      <div class="wisdom-img">
        <img src="images/shivabalayogi-maharaj.jpg" alt="Shri Shivabalayogi Maharaj">
      </div>
      <div class="wisdom-copy">
        <p class="eyebrow">Linaje e inspiración</p>
        <h2>Shri Shivabalayogi Maharaj</h2>
        <p class="lead">Uno de los grandes yoguis que inspiran el camino del Shanti Lanka Ashram.</p>
        <ul class="wisdom-list">
          <li>Nació el 24 de enero de 1935 en Adivarapupeta, un pueblo de tejedores en Andhra Pradesh, India, con el nombre de Sathyaraju Allaka.</li>
          <li>Perdió a su padre de niño; lo crió su madre junto a su abuelo, y desde muy chico trabajó tejiendo para ayudar en casa.</li>
          <li>A los catorce años entró en <em>tapas</em>: doce años de meditación ininterrumpida —veintitrés horas diarias los primeros ocho años, doce horas los últimos cuatro— resistiendo calor, lluvias y hasta mordidas de serpiente sin moverse de su lugar.</li>
          <li>Completó su tapas el 7 de agosto de 1961, a los 26 años, ante decenas de miles de personas que llegaron a recibirlo como maestro autorrealizado.</li>
          <li>Su gurú le había dado una sola tarea: no dar discursos, solo iniciar en meditación a quien la buscara. Así viajó tres décadas por India, Sri Lanka, Inglaterra y Estados Unidos, iniciando gratuitamente a cientos de miles de personas, sin pedir a nadie cambiar de fe.</li>
          <li>Murió en 1994. Su ashram y su samadhi siguen en Adivarapupeta, India — y su ejemplo sostiene aquí una idea sencilla: muchos caminos, una sola intención.</li>
        </ul>
        <button type="button" class="link-arrow as-button" id="abrirEsencia2">Conocer su historia →</button>
      </div>
    </div>
  </section>

  <!-- STORIES -->
  <section class="stories">
    <div class="container">
      <p class="eyebrow">Historias de la comunidad</p>
      <h2>Voces de un mismo tejido.</h2>

      <div class="story-grid">
        <article class="story-card">
          <span class="story-mark" aria-hidden="true">“</span>
          <p class="quote">Vine buscando silencio y encontré vecinas con las que ahora construimos una escuela.</p>
          <p class="author">Nadeesha — Matara, Sri Lanka</p>
        </article>
        <article class="story-card">
          <span class="story-mark" aria-hidden="true">“</span>
          <p class="quote">Nadie me pidió creer en algo. Solo me hicieron un lugar en el círculo.</p>
          <p class="author">Marisol — Bogotá, Colombia</p>
        </article>
        <article class="story-card">
          <span class="story-mark" aria-hidden="true">“</span>
          <p class="quote">Contamos de dónde viene cada color de nuestro tejido. Esa es nuestra manera de compartir.</p>
          <p class="author">Aurelio — Cusco, Perú</p>
        </article>
      </div>
    </div>
  </section>

  <!-- CONTACTO -->
  <section class="contact-section" id="contacto">
    <div class="container narrow">
      <p class="eyebrow">Escríbenos</p>
      <h2>¿Tienes una pregunta?</h2>
      <p class="lead">Cuéntanos qué buscas y te respondemos personalmente.</p>

      <div class="contact-box">
        <?php if ($enviado === 'ok'): ?>
          <p class="alert-ok">¡Gracias! Recibimos tu mensaje y te responderemos pronto.</p>
        <?php elseif ($enviado === 'error'): ?>
          <p class="alert-error">Falta tu nombre o el mensaje. Inténtalo de nuevo.</p>
        <?php endif; ?>

        <form method="post" action="contacto.php" class="site-form">
          <?= sla_csrf_field() ?>
          <label>Tu nombre *
            <input type="text" name="name" required>
          </label>
          <div class="form-row">
            <label>Correo
              <input type="email" name="email">
            </label>
            <label>Teléfono / WhatsApp
              <input type="text" name="phone">
            </label>
          </div>
          <label>Tu mensaje *
            <textarea name="body" rows="4" required></textarea>
          </label>
          <button type="submit" class="btn btn-gold">Enviar mensaje</button>
        </form>
      </div>
    </div>
  </section>

  <!-- FINAL CTA -->
  <section class="final-cta" id="participar">
    <div class="container narrow">
      <h2 class="light">No importa desde qué lugar comienza tu camino.<br>Hay una comunidad dispuesta a caminar contigo.</h2>
      <div class="hero-actions center">
        <a href="#eventos" class="btn btn-gold">Encontrar mi camino</a>
        <a href="#contacto" class="btn btn-ghost-light">Compartir mi servicio</a>
      </div>
    </div>
  </section>

</main>

<!-- ============================================================
     VENTANA: NUESTRA ESENCIA (historia y fundador)
     Para cambiar los textos, edita solo los párrafos de abajo.
     ============================================================ -->
<div class="essence-backdrop" id="esenciaBackdrop" aria-hidden="true">
  <div class="essence-modal" role="dialog" aria-modal="true" aria-labelledby="esenciaTitulo">
    <button type="button" class="essence-close" id="cerrarEsencia" aria-label="Cerrar">✕</button>

    <div class="essence-content">
      <p class="eyebrow">Nuestra esencia</p>
      <h2 id="esenciaTitulo">La historia del Shanti Lanka Ashram</h2>

      <section class="essence-block">
        <h3>¿Qué significa nuestro nombre?</h3>
        <p>Nuestro nombre está formado por tres palabras: <strong>Shanti</strong> (paz), <strong>Lanka</strong> (isla) y <strong>Ashram</strong>.</p>
        <p>¿Y qué es un ashram? Un ashram es un lugar sagrado, muy distinto de un hotel. Representa la oportunidad de alejarse de los asuntos del mundo y volver a una forma de vida más sencilla. Es un espacio para nutrir el alma, profundizar la práctica espiritual y despertar al verdadero Ser. ¡Un ashram es un lugar de libertad interior!</p>
        <p>Este Ashram está dedicado a nuestro amado maestro Shri Shivabalayogi Maharaj, a su devoto discípulo Swami Kenananda, y a las profundas enseñanzas de innumerables santos y yoguis venerados a través de diversos linajes y tradiciones.</p>
      </section>

      <section class="essence-block">
        <h3>Cómo nació este lugar</h3>
        <div class="essence-photo-wide">
          <img src="images/construccion-ashram.jpg" alt="Construcción del Shanti Lanka Ashram en Lagunas de Chacahua">
        </div>
        <p><em>[Texto pendiente: aquí va la historia del ashram — cómo empezó, en qué año, quiénes llegaron primero y cómo se fue construyendo el lugar frente al mar en Lagunas de Chacahua.]</em></p>
      </section>

      <section class="essence-block">
        <h3>El fundador</h3>
        <div class="essence-founder">
          <img src="images/swami-kena.jpg" alt="Swami Kenananda, fundador del Shanti Lanka Ashram">
          <div>
            <p><em>[Texto pendiente: aquí va la historia del fundador — su camino, su formación, cómo llegó a Oaxaca y qué lo movió a abrir este espacio.]</em></p>
          </div>
        </div>
      </section>

      <section class="essence-block">
        <h3>Lo que sostenemos hoy</h3>
        <p><em>[Texto pendiente: en qué se ha convertido el ashram hoy, qué se practica, quiénes forman la comunidad y hacia dónde va.]</em></p>
      </section>

      <div class="essence-actions">
        <a href="#contacto" class="btn btn-gold" id="esenciaContacto">Escríbenos</a>
        <a href="calendario.php" class="btn btn-outline">Ver el calendario</a>
      </div>
    </div>
  </div>
</div>

<div class="essence-backdrop" id="shivaBackdrop" aria-hidden="true">
  <div class="essence-modal" role="dialog" aria-modal="true" aria-labelledby="shivaTitulo">
    <button type="button" class="essence-close" id="cerrarShiva" aria-label="Cerrar">✕</button>

    <div class="essence-content">
      <p class="eyebrow">Linaje e inspiración</p>
      <h2 id="shivaTitulo">Shri Shivabalayogi Maharaj</h2>

      <section class="essence-block">
        <p>Shivabalayogi Maharaj (24 de enero de 1935 – 28 de marzo de 1994) nació como Sathyaraju Allaka en Adivarapupeta, un pueblo de familias tejedoras en Andhra Pradesh, India. Perdió a su padre siendo niño; lo crió su madre junto a su abuelo, y desde muy joven trabajó tejiendo para ayudar en casa.</p>
        <p>En 1949, con catorce años, comenzó lo que en la tradición se llama <em>tapas</em>: doce años de meditación ininterrumpida —veintitrés horas al día durante los primeros ocho años, doce horas al día los últimos cuatro— sin moverse de su sitio, resistiendo el calor, las lluvias y hasta mordidas de serpiente. Completó esa disciplina el 7 de agosto de 1961, a los 26 años, cuando decenas de miles de personas se reunieron para recibirlo como maestro autorrealizado.</p>
        <p>Su gurú le había dado una sola indicación: no dar discursos, solo iniciar en meditación a quien la buscara. A eso dedicó el resto de su vida: durante tres décadas viajó por India y después por Sri Lanka, Inglaterra y Estados Unidos, iniciando de forma gratuita a cientos de miles de personas en una técnica de meditación conocida como <em>Jangama Dhyana</em>. Nunca pidió a nadie cambiar de religión ni adoptar una creencia. Su indicación era simple: siéntate, cierra los ojos y medita.</p>
        <p>Esa manera de entender la práctica —abierta a cualquiera, sin barreras de fe ni de origen— es una de las raíces del Shanti Lanka Ashram y de la idea que nos sostiene: muchos caminos, una sola intención. Su ashram principal y su samadhi siguen en Adivarapupeta, India.</p>
      </section>

      <div class="essence-actions">
        <a href="#esencia" class="btn btn-outline" id="shivaEsencia">Conocer el Shanti Lanka Ashram</a>
        <a href="#contacto" class="btn btn-gold">Escríbenos</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . "/partials/footer.php"; ?>

<script src="script.js?v=6"></script>
</body>
</html>
