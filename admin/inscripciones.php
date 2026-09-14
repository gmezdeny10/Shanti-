<?php
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sla_check_csrf();
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'estado' && $id) {
        sla_set_registration_status($id, $_POST['status'] ?? 'pendiente');
    } elseif ($action === 'borrar' && $id) {
        sla_delete_registration($id);
    }
    header('Location: inscripciones.php' . (!empty($_POST['filtro']) ? '?evento=' . (int) $_POST['filtro'] : ''));
    exit;
}

$filterEvent   = !empty($_GET['evento']) ? (int) $_GET['evento'] : null;
$registrations = sla_registrations($filterEvent);
$events        = sla_all_events();

sla_admin_header('Inscripciones', 'inscripciones.php');
?>
<div class="admin-container">
  <div class="page-head">
    <h1>Inscripciones</h1>
    <form method="get" class="filter-form">
      <label class="inline-label">Filtrar por evento
        <select name="evento" onchange="this.form.submit()">
          <option value="">Todos los eventos</option>
          <?php foreach ($events as $ev): ?>
            <option value="<?= (int) $ev['id'] ?>" <?= $filterEvent === (int) $ev['id'] ? 'selected' : '' ?>>
              <?= e($ev['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
  </div>

  <?php if (!$registrations): ?>
    <section class="panel"><p class="empty">Todavía no hay inscripciones<?= $filterEvent ? ' para este evento' : '' ?>.</p></section>
  <?php else: ?>
    <section class="panel">
      <table class="admin-table">
        <thead>
          <tr><th>Persona</th><th>Contacto</th><th>Evento</th><th>Personas</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($registrations as $r): ?>
          <tr>
            <td>
              <strong><?= e($r['name']) ?></strong>
              <?php if ($r['notes']): ?><span class="muted block"><?= e($r['notes']) ?></span><?php endif; ?>
            </td>
            <td>
              <?php if ($r['email']): ?><a class="link" href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a><br><?php endif; ?>
              <?php if ($r['phone']): ?><span class="muted"><?= e($r['phone']) ?></span><?php endif; ?>
            </td>
            <td>
              <?= e($r['event_title']) ?>
              <span class="muted block"><?= e(sla_date_long($r['event_date'])) ?></span>
            </td>
            <td><?= (int) $r['people'] ?></td>
            <td>
              <form method="post" class="inline">
                <?= sla_csrf_field() ?>
                <input type="hidden" name="action" value="estado">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <input type="hidden" name="filtro" value="<?= (int) $filterEvent ?>">
                <select name="status" onchange="this.form.submit()" class="status-select status-<?= e($r['status']) ?>">
                  <?php foreach (['pendiente', 'confirmada', 'cancelada'] as $s): ?>
                    <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td class="nowrap">
              <form method="post" class="inline" onsubmit="return confirm('¿Eliminar esta inscripción?');">
                <?= sla_csrf_field() ?>
                <input type="hidden" name="action" value="borrar">
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <input type="hidden" name="filtro" value="<?= (int) $filterEvent ?>">
                <button type="submit" class="link danger">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  <?php endif; ?>
</div>
<?php sla_admin_footer(); ?>
