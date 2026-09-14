document.addEventListener('DOMContentLoaded', () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Header scroll state ---------- */
  const header = document.getElementById('siteHeader');
  const newsletterForm = document.getElementById('newsletterForm');

  const onScroll = () => {
    if (window.scrollY > 40) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const input = newsletterForm.querySelector('input');
      input.value = '';
      input.placeholder = '¡Gracias por unirte!';
    });
  }

  /* ---------- Ventana "Nuestra esencia" ---------- */
  const esenciaBackdrop = document.getElementById('esenciaBackdrop');
  const disparadores    = [...document.querySelectorAll('#abrirEsencia, #abrirEsencia2')];
  const cerrarEsencia   = document.getElementById('cerrarEsencia');
  const esenciaContacto = document.getElementById('esenciaContacto');

  if (esenciaBackdrop && disparadores.length) {
    const abrir = () => {
      esenciaBackdrop.classList.add('open');
      esenciaBackdrop.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      if (cerrarEsencia) cerrarEsencia.focus();
    };
    const cerrar = () => {
      esenciaBackdrop.classList.remove('open');
      esenciaBackdrop.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    };

    disparadores.forEach(btn => btn.addEventListener('click', abrir));
    if (cerrarEsencia) cerrarEsencia.addEventListener('click', cerrar);
    if (esenciaContacto) esenciaContacto.addEventListener('click', cerrar);

    esenciaBackdrop.addEventListener('click', (e) => {
      if (e.target === esenciaBackdrop) cerrar();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && esenciaBackdrop.classList.contains('open')) cerrar();
    });
  }

  /* ---------- Mobile side panel ---------- */
  const hamburger = document.getElementById('hamburger');
  const mobilePanel = document.getElementById('mobilePanel');
  const mobileBackdrop = document.getElementById('mobileBackdrop');
  const mobilePanelClose = document.getElementById('mobilePanelClose');

  const openMobilePanel = () => {
    mobilePanel.classList.add('open');
    mobileBackdrop.classList.add('open');
    mobilePanel.setAttribute('aria-hidden', 'false');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  };
  const closeMobilePanel = () => {
    mobilePanel.classList.remove('open');
    mobileBackdrop.classList.remove('open');
    mobilePanel.setAttribute('aria-hidden', 'true');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  };

  hamburger.addEventListener('click', () => {
    if (mobilePanel.classList.contains('open')) {
      closeMobilePanel();
    } else {
      openMobilePanel();
    }
  });
  mobilePanelClose.addEventListener('click', closeMobilePanel);
  mobileBackdrop.addEventListener('click', closeMobilePanel);
  mobilePanel.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMobilePanel);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobilePanel.classList.contains('open')) closeMobilePanel();
  });

  /* ---------- "Más" desktop dropdown ---------- */
  const navMore = document.getElementById('navMore');
  const navMoreTrigger = document.getElementById('navMoreTrigger');

  const closeNavMore = () => {
    navMore.classList.remove('open');
    navMoreTrigger.setAttribute('aria-expanded', 'false');
  };

  if (navMore && navMoreTrigger) {
    navMoreTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = navMore.classList.toggle('open');
      navMoreTrigger.setAttribute('aria-expanded', String(isOpen));
    });
    document.addEventListener('click', (e) => {
      if (!navMore.contains(e.target)) closeNavMore();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeNavMore();
    });
    navMore.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeNavMore);
    });
  }

  /* ---------- Active section indicator (desktop nav + "Más" + mobile panel) ---------- */
  const allNavLinks = [...document.querySelectorAll('.main-nav a[href^="#"], .nav-more-panel a[href^="#"], .mobile-panel-nav a[href^="#"]')];
  const sections = [...new Set(allNavLinks.map(l => l.getAttribute('href')))]
    .map(href => document.querySelector(href))
    .filter(Boolean);

  if (sections.length) {
    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const href = '#' + entry.target.id;
        allNavLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') === href));
        const isInMore = href === '#sabiduria' || href === '#proyectos' || href === '#participar';
        if (navMoreTrigger) navMoreTrigger.classList.toggle('active', isInMore);
      });
    }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });

    sections.forEach(sec => sectionObserver.observe(sec));
  }

  /* ---------- Hero parallax (desktop only, skipped for reduced motion) ---------- */
  const heroBg = document.getElementById('heroBg');
  if (heroBg && !prefersReducedMotion) {
    let ticking = false;
    const applyParallax = () => {
      if (window.innerWidth > 1024) {
        const offset = Math.min(window.scrollY, window.innerHeight) * 0.18;
        heroBg.style.transform = `translateY(${offset}px)`;
      } else {
        heroBg.style.transform = '';
      }
      ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(applyParallax);
        ticking = true;
      }
    }, { passive: true });
  }

  /* ---------- Hero warm light particles (skipped for reduced motion) ---------- */
  const particlesHost = document.getElementById('heroParticles');
  if (particlesHost && !prefersReducedMotion) {
    const count = 14;
    for (let i = 0; i < count; i++) {
      const p = document.createElement('span');
      p.className = 'particle';
      const left = Math.random() * 100;
      const duration = 9 + Math.random() * 8;
      const delay = Math.random() * 10;
      const drift = (Math.random() * 40 - 20).toFixed(0) + 'px';
      p.style.left = left + '%';
      p.style.setProperty('--drift', drift);
      p.style.animationDuration = duration + 's';
      p.style.animationDelay = delay + 's';
      particlesHost.appendChild(p);
    }
  }

  /* ---------- Scroll reveal system ---------- */
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

  const registerReveal = (el, direction, delay) => {
    el.classList.add(direction);
    if (delay) el.style.setProperty('--reveal-delay', delay + 'ms');
    revealObserver.observe(el);
  };

  const staggerGroup = (selector, direction = 'reveal-up', step = 100, max = 400) => {
    document.querySelectorAll(selector).forEach(group => {
      [...group.children].forEach((child, i) => {
        registerReveal(child, direction, Math.min(i * step, max));
      });
    });
  };

  // Section headings across the page (excluding hero, split panels and grids, handled separately)
  document.querySelectorAll('main section:not(.hero) > .container > h2').forEach(el => {
    registerReveal(el, 'reveal-up');
  });
  document.querySelectorAll('.lead, .manifesto p, .wisdom-list, .tagline').forEach(el => {
    registerReveal(el, 'reveal-up', 80);
  });

  // Alternating left/right for split image + copy panels (Swami Kena, Ubicación, Sabiduría ancestral)
  document.querySelectorAll('.container.split').forEach(split => {
    const kids = [...split.children];
    if (kids[0]) registerReveal(kids[0], 'reveal-left');
    if (kids[1]) registerReveal(kids[1], 'reveal-right', 120);
  });

  // Staggered card/tile grids
  staggerGroup('.card-grid', 'reveal-up', 110);
  staggerGroup('.tile-grid', 'reveal-up', 110);
  staggerGroup('.event-list', 'reveal-up', 110);
  staggerGroup('.story-grid', 'reveal-up', 110);
  staggerGroup('.facilities-grid', 'reveal-up', 90);
  staggerGroup('.values-copy', 'reveal-up', 90);
  staggerGroup('.mv-grid', 'reveal-up', 120);
  staggerGroup('.tag-row', 'reveal-up', 40, 280);

  /* ---------- Lightbox for the facilities gallery ---------- */
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxClose = document.getElementById('lightboxClose');
  const lightboxPrev = document.getElementById('lightboxPrev');
  const lightboxNext = document.getElementById('lightboxNext');

  const galleryTiles = [...document.querySelectorAll('.facility-tile')];
  const gallery = galleryTiles.map(tile => ({
    src: tile.querySelector('img').getAttribute('src'),
    alt: tile.querySelector('img').getAttribute('alt'),
    caption: tile.querySelector('figcaption') ? tile.querySelector('figcaption').textContent : ''
  }));

  let currentIndex = 0;

  const showSlide = (index) => {
    currentIndex = (index + gallery.length) % gallery.length;
    const item = gallery[currentIndex];
    lightboxImg.src = item.src;
    lightboxImg.alt = item.alt;
    lightboxCaption.textContent = item.caption;
  };

  const openLightbox = (index) => {
    if (!gallery.length) return;
    showSlide(index);
    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeLightbox = () => {
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  galleryTiles.forEach((tile, i) => {
    tile.addEventListener('click', (e) => {
      e.preventDefault();
      openLightbox(i);
    });
  });

  if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
  if (lightboxPrev) lightboxPrev.addEventListener('click', () => showSlide(currentIndex - 1));
  if (lightboxNext) lightboxNext.addEventListener('click', () => showSlide(currentIndex + 1));

  if (lightbox) {
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') showSlide(currentIndex - 1);
    if (e.key === 'ArrowRight') showSlide(currentIndex + 1);
  });

  // Touch swipe navigation for mobile
  let touchStartX = 0;
  if (lightbox) {
    lightbox.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });
    lightbox.addEventListener('touchend', (e) => {
      const delta = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(delta) > 40) {
        showSlide(currentIndex + (delta < 0 ? 1 : -1));
      }
    }, { passive: true });
  }
});
