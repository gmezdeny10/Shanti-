<?php
require_once __DIR__ . '/_layout.php';

$upcoming      = sla_upcoming_events(5);
$registrations = sla_registrations();
$messages      = sla_messages();
$counts        = sla_registration_counts();

sla_admin_header('Inicio', 'index.php');
?>
<div class="admin-container">
  <h1>Hola, <?= e(sla_current_admin()) ?></h1>
  <p class="admin-lead">Desde aquí administras los eventos del ashram, las inscripciones y los mensajes que llegan del sitio.</p>

  <div class="stat-grid">
    <a class="stat-card" href="eventos.php">
      <span class="stat-num"><?= count(sla_all_events()) ?></span>
      <span class="stat-label">Eventos creados</span>
    </a>
    <a class="stat-card" href="inscripciones.php">
      <span class="stat-num"><?= count($registrations) ?></span>
      <span class="stat-label">Inscripciones</span>
    </a>
    <a class="stat-card" href="mensajes.php">
      <span class="stat-num"><?= sla_unread_count() ?></span>
      <span class="stat-label">Mensajes sin leer</span>
    </a>
  </div>

  <section class="panel">
    <div class="panel-head">
      <h2>Próximos eventos</h2>
      <a class="btn btn-primary btn-sm" href="eventos.php?nuevo=1">+ Nuevo evento</a>
    </div>

    <?php if (!$upcoming): ?>
      <p class="empty">Todavía no hay eventos próximos. Crea el primero.</p>
    <?php else: ?>
      <table class="admin-table">
        <thead><tr><th>Fecha</th><th>Evento</th><th>Lugar</th><th>Inscritos</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($upcoming as $ev): ?>
          <tr>
            <td class="nowrap"><?= e(sla_date_long($ev['event_date'])) ?></td>
            <td><strong><?= e($ev['title']) ?></strong></td>
            <td><?= e($ev['location']) ?></td>
            <td><?= (int) ($counts[$ev['id']] ?? 0) ?><?= $ev['capacity'] ? ' / ' . (int) $ev['capacity'] : '' ?></td>
            <td class="nowrap"><a class="link" href="eventos.php?editar=<?= (int) $ev['id'] ?>">Editar</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Últimos mensajes</h2><a class="link" href="mensajes.php">Ver todos</a></div>
    <?php if (!$messages): ?>
      <p class="empty">Aún no has recibido mensajes.</p>
    <?php else: ?>
      <ul class="mini-list">
        <?php foreach (array_slice($messages, 0, 4) as $m): ?>
          <li>
            <strong><?= e($m['name']) ?></strong>
            <span class="muted"><?= e($m['email']) ?></span>
            <p><?= e(mb_substr($m['body'], 0, 120)) ?><?= mb_strlen($m['body']) > 120 ? '…' : '' ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
<?php sla_admin_footer(); ?>
