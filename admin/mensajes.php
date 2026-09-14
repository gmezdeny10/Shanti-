<?php
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sla_check_csrf();
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'leido' && $id) {
        sla_mark_message_read($id, (int) ($_POST['value'] ?? 1));
    } elseif ($action === 'borrar' && $id) {
        sla_delete_message($id);
    } elseif ($action === 'responder' && $id) {
        $texto = trim($_POST['reply'] ?? '');
        if ($texto !== '') {
            sla_save_reply($id, mb_substr($texto, 0, 4000));
            header('Location: mensajes.php?respondido=' . $id . '#msg-' . $id);
            exit;
        }
    }
    header('Location: mensajes.php');
    exit;
}

$messages    = sla_messages();
$respondido  = (int) ($_GET['respondido'] ?? 0);
$abrir       = (int) ($_GET['responder'] ?? 0);

sla_admin_header('Mensajes', 'mensajes.php');
?>
<div class="admin-container">
  <div class="page-head">
    <h1>Mensajes</h1>
    <span class="muted"><?= sla_unread_count() ?> sin leer de <?= count($messages) ?></span>
  </div>

  <?php if ($respondido): ?>
    <p class="alert-ok">
      Respuesta guardada. Envíasela a la persona con los botones de WhatsApp o correo del mensaje.
    </p>
  <?php endif; ?>

  <?php if (!$messages): ?>
    <section class="panel">
      <p class="empty">Todavía no llegan mensajes. Los que envíen desde el formulario del sitio aparecerán aquí.</p>
    </section>
  <?php else: ?>
    <div class="message-list">
      <?php foreach ($messages as $m):
        $wa       = $m['phone'] ? sla_whatsapp_number($m['phone']) : null;
        $abierto  = ($abrir === (int) $m['id']) || ($respondido === (int) $m['id']);
        $saludo   = 'Hola ' . trim(explode(' ', $m['name'])[0]) . ', gracias por escribir a Shanti Lanka Ashram.';
        $textoEnv = $m['reply'] ?: $saludo;
      ?>
        <article class="message-card <?= $m['is_read'] ? '' : 'unread' ?>" id="msg-<?= (int) $m['id'] ?>">
          <div class="message-head">
            <div>
              <strong><?= e($m['name']) ?></strong>
              <?php if (!$m['is_read']): ?><span class="dot-new" title="Sin leer"></span><?php endif; ?>
              <span class="muted block">
                <?php if ($m['email']): ?><a class="link" href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?php endif; ?>
                <?php if ($m['phone']): ?> · <?= e($m['phone']) ?><?php endif; ?>
              </span>
            </div>
            <time class="muted"><?= e(sla_date_long(substr($m['created_at'], 0, 10))) ?></time>
          </div>

          <p class="message-body"><?= nl2br(e($m['body'])) ?></p>

          <?php if ($m['reply']): ?>
            <div class="message-reply">
              <span class="reply-label">Tu respuesta · <?= e(sla_date_long(substr($m['replied_at'], 0, 10))) ?></span>
              <p><?= nl2br(e($m['reply'])) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($abierto || !$m['reply']): ?>
            <form method="post" class="reply-form">
              <?= sla_csrf_field() ?>
              <input type="hidden" name="action" value="responder">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <label>
                <?= $m['reply'] ? 'Editar la respuesta' : 'Escribir respuesta' ?>
                <textarea name="reply" rows="3" placeholder="<?= e($saludo) ?>"><?= e($m['reply']) ?></textarea>
              </label>
              <button type="submit" class="btn btn-primary btn-sm">Guardar respuesta</button>
            </form>
          <?php endif; ?>

          <div class="message-actions">
            <?php if ($wa): ?>
              <a class="btn btn-wa btn-sm"
                 href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($textoEnv) ?>"
                 target="_blank" rel="noopener">Responder por WhatsApp</a>
            <?php endif; ?>

            <?php if ($m['email']): ?>
              <a class="btn btn-ghost btn-sm"
                 href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Shanti Lanka Ashram') ?>&body=<?= rawurlencode($textoEnv) ?>">Responder por correo</a>
            <?php endif; ?>

            <?php if ($m['reply'] && !$abierto): ?>
              <a class="link" href="mensajes.php?responder=<?= (int) $m['id'] ?>#msg-<?= (int) $m['id'] ?>">Editar respuesta</a>
            <?php endif; ?>

            <form method="post" class="inline">
              <?= sla_csrf_field() ?>
              <input type="hidden" name="action" value="leido">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <input type="hidden" name="value" value="<?= $m['is_read'] ? 0 : 1 ?>">
              <button type="submit" class="link"><?= $m['is_read'] ? 'Marcar como no leído' : 'Marcar como leído' ?></button>
            </form>

            <form method="post" class="inline" onsubmit="return confirm('¿Eliminar este mensaje?');">
              <?= sla_csrf_field() ?>
              <input type="hidden" name="action" value="borrar">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button type="submit" class="link danger">Borrar</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php sla_admin_footer(); ?>
