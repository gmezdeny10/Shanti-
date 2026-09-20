<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/model.php';

$sla_home = 'index.php';

// Mes mostrado (YYYY-MM), por defecto el actual
$ym = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
    $ym = date('Y-m');
}

$first     = strtotime($ym . '-01');
$daysInMon = (int) date('t', $first);
$startDow  = (int) date('N', $first);           // 1 = lunes
$prevMonth = date('Y-m', strtotime('-1 month', $first));
$nextMonth = date('Y-m', strtotime('+1 month', $first));

$meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$titulo = $meses[(int) date('n', $first)] . ' ' . date('Y', $first);

$eventosDelMes = sla_events_in_month($ym);
$proximos      = sla_upcoming_events(6);
$hoy           = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendario — Shanti Lanka Ashram</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500&family=Karla:wght@300;400;500;600&family=Caveat:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=12">
</head>
<body class="subpage">

<?php include __DIR__ . '/partials/header.php'; ?>

<main class="subpage-main">
  <div class="container">
    <a class="link-arrow back-link" href="index.php#eventos">← Volver al inicio</a>
    <p class="eyebrow">Calendario</p>
    <h1 class="subpage-title">Lo que viene en el ashram</h1>

    <div class="cal-head">
      <a class="cal-nav" href="calendario.php?mes=<?= e($prevMonth) ?>" aria-label="Mes anterior">‹</a>
      <h2 class="cal-month"><?= e($titulo) ?></h2>
      <a class="cal-nav" href="calendario.php?mes=<?= e($nextMonth) ?>" aria-label="Mes siguiente">›</a>
      <?php if ($ym !== date('Y-m')): ?>
        <a class="cal-today" href="calendario.php">Ir a hoy</a>
      <?php endif; ?>
    </div>

    <div class="calendar">
      <?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dow): ?>
        <div class="cal-dow"><?= $dow ?></div>
      <?php endforeach; ?>

      <?php for ($i = 1; $i < $startDow; $i++): ?>
        <div class="cal-cell empty"></div>
      <?php endfor; ?>

      <?php for ($day = 1; $day <= $daysInMon; $day++):
        $iso     = sprintf('%s-%02d', $ym, $day);
        $delDia  = $eventosDelMes[$day] ?? [];
        $esHoy   = $iso === $hoy;
      ?>
        <div class="cal-cell <?= $delDia ? 'has-events' : '' ?> <?= $esHoy ? 'is-today' : '' ?>">
          <span class="cal-daynum"><?= $day ?></span>
          <?php foreach ($delDia as $ev): ?>
            <a class="cal-event" href="evento.php?id=<?= (int) $ev['id'] ?>" title="<?= e($ev['title']) ?>">
              <?= e($ev['title']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>

    <section class="cal-upcoming">
      <h2>Próximos encuentros</h2>
      <?php if (!$proximos): ?>
        <p class="empty-note">Aún no hay eventos programados.</p>
      <?php else: ?>
        <ul class="cal-list">
          <?php foreach ($proximos as $ev): [$d, $m] = sla_date_parts($ev['event_date']); ?>
            <li>
              <span class="cal-list-date"><strong><?= e($d) ?></strong><span><?= e($m) ?></span></span>
              <div>
                <a class="cal-list-title" href="evento.php?id=<?= (int) $ev['id'] ?>"><?= e($ev['title']) ?></a>
                <span class="muted-line"><?= e($ev['location']) ?><?= $ev['modality'] ? ' — ' . e($ev['modality']) : '' ?></span>
              </div>
              <a class="link-arrow" href="evento.php?id=<?= (int) $ev['id'] ?>">Inscribirme →</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script src="script.js?v=5"></script>
</body>
</html>
