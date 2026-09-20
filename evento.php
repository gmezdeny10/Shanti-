<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/model.php';

$sla_home = 'index.php';

$id     = (int) ($_GET['id'] ?? 0);
$evento = sla_find_event($id);

if (!$evento || !$evento['is_published']) {
    http_response_code(404);
    $evento = null;
}

$error = '';
$ok    = isset($_GET['ok']);

if ($evento && $_SERVER['REQUEST_METHOD'] === 'POST') {
    sla_check_csrf();
    $nombre = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $tel    = trim($_POST['phone'] ?? '');

    if ($nombre === '') {
        $error = 'Por favor escribe tu nombre.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo no parece válido.';
    } elseif ($email === '' && $tel === '') {
        $error = 'Déjanos un correo o un teléfono para poder confirmarte.';
    } else {
        sla_add_registration($evento['id'], [
            'name'   => $nombre,
            'email'  => $email,
            'phone'  => $tel,
            'people' => (int) ($_POST['people'] ?? 1),
            'notes'  => trim($_POST['notes'] ?? ''),
        ]);
        header('Location: evento.php?id=' . $evento['id'] . '&ok=1');
        exit;
    }
}

$inscritos = $evento ? (sla_registration_counts()[$evento['id']] ?? 0) : 0;
$lugares   = $evento && $evento['capacity'] ? max(0, (int) $evento['capacity'] - $inscritos) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $evento ? e($evento['title']) : 'Evento no encontrado' ?> — Shanti Lanka Ashram</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500&family=Karla:wght@300;400;500;600&family=Caveat:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=14">
</head>
<body class="subpage">

<?php include __DIR__ . '/partials/header.php'; ?>

<main class="subpage-main">
  <div class="container narrow">
    <a class="link-arrow back-link" href="index.php#eventos">← Volver a eventos</a>

    <?php if (!$evento): ?>
      <h1 class="subpage-title">Este evento ya no está disponible</h1>
      <p class="lead">Puede que haya terminado o que se haya despublicado. Mira el calendario para ver lo que viene.</p>
      <a class="btn btn-dark" href="calendario.php">Ver el calendario</a>
    <?php else: ?>
      <?php [$d, $m] = sla_date_parts($evento['event_date']); ?>
      <p class="eyebrow"><?= e($d . ' ' . $m) ?> · <?= e($evento['kind']) ?></p>
      <h1 class="subpage-title"><?= e($evento['title']) ?></h1>

      <?php $galeria = sla_event_gallery($evento); ?>
      <?php if (count($galeria) > 1): ?>
        <div class="event-carousel" data-carousel>
          <div class="event-carousel-track">
            <?php foreach ($galeria as $i => $foto): ?>
              <div class="event-carousel-slide" <?= $i === 0 ? '' : 'hidden' ?>>
                <img src="<?= e($foto) ?>" alt="<?= e($evento['title']) ?> — foto <?= $i + 1 ?> de <?= count($galeria) ?>">
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="carousel-arrow prev" data-carousel-prev aria-label="Foto anterior">‹</button>
          <button type="button" class="carousel-arrow next" data-carousel-next aria-label="Foto siguiente">›</button>
          <div class="carousel-dots" data-carousel-dots>
            <?php foreach ($galeria as $i => $foto): ?>
              <button type="button" class="carousel-dot <?= $i === 0 ? 'active' : '' ?>" data-carousel-dot="<?= $i ?>" aria-label="Ir a la foto <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php elseif ($galeria): ?>
        <div class="event-hero"><img src="<?= e($galeria[0]) ?>" alt="<?= e($evento['title']) ?>"></div>
      <?php endif; ?>

      <dl class="event-facts">
        <div><dt>Fecha</dt><dd><?= e(sla_date_long($evento['event_date'])) ?></dd></div>
        <?php if ($evento['location']): ?><div><dt>Lugar</dt><dd><?= e($evento['location']) ?></dd></div><?php endif; ?>
        <div><dt>Modalidad</dt><dd><?= e($evento['modality']) ?></dd></div>
        <?php if ($evento['organizer']): ?><div><dt>Organiza</dt><dd><?= e($evento['organizer']) ?></dd></div><?php endif; ?>
        <?php if ($evento['price_note']): ?><div><dt>Aporte</dt><dd><?= e($evento['price_note']) ?></dd></div><?php endif; ?>
        <?php if ($lugares !== null): ?>
          <div><dt>Lugares</dt><dd><?= $lugares > 0 ? $lugares . ' disponibles' : 'Cupo lleno' ?></dd></div>
        <?php endif; ?>
      </dl>

      <?php if ($evento['description']): ?>
        <p class="event-description"><?= nl2br(e($evento['description'])) ?></p>
      <?php endif; ?>

      <section class="signup-box" id="inscripcion">
        <h2>Inscribirme</h2>

        <?php if ($ok): ?>
          <p class="alert-ok">¡Listo! Recibimos tu inscripción. Te contactaremos para confirmarte los detalles.</p>
        <?php elseif ($lugares !== null && $lugares <= 0): ?>
          <p class="alert-info">Este evento ya llegó a su cupo. Escríbenos por WhatsApp si quieres quedar en lista de espera.</p>
        <?php else: ?>
          <?php if ($error): ?><p class="alert-error"><?= e($error) ?></p><?php endif; ?>
          <form method="post" class="site-form">
            <?= sla_csrf_field() ?>
            <label>Tu nombre *
              <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            </label>
            <div class="form-row">
              <label>Correo
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">
              </label>
              <label>Teléfono / WhatsApp
                <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
              </label>
            </div>
            <label class="short">¿Cuántas personas?
              <input type="number" name="people" min="1" max="20" value="<?= e($_POST['people'] ?? '1') ?>">
            </label>
            <label>¿Algo que debamos saber?
              <textarea name="notes" rows="3"><?= e($_POST['notes'] ?? '') ?></textarea>
            </label>
            <button type="submit" class="btn btn-gold">Enviar inscripción</button>
          </form>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script src="script.js?v=5"></script>
</body>
</html>
