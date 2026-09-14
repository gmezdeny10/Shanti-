<?php
require_once __DIR__ . '/_layout.php';

$error  = '';
$ok     = '';
$yoId   = sla_current_admin_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sla_check_csrf();
    $action = $_POST['action'] ?? '';

    /* ---- dar acceso a una persona nueva ---- */
    if ($action === 'crear') {
        $user  = trim($_POST['username'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        if (strlen($user) < 3) {
            $error = 'El usuario debe tener al menos 3 caracteres.';
        } elseif (!preg_match('/^[\w.\- ]+$/u', $user)) {
            $error = 'El usuario solo puede tener letras, números, puntos o guiones.';
        } elseif (sla_admin_username_taken($user)) {
            $error = 'Ya existe una persona con ese usuario.';
        } elseif (strlen($pass) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($pass !== $pass2) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            sla_create_admin($user, $pass);
            header('Location: usuarios.php?ok=creado');
            exit;
        }
    }

    /* ---- quitar el acceso a alguien ---- */
    if ($action === 'borrar') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id === $yoId) {
            $error = 'No puedes quitarte el acceso a ti misma.';
        } elseif (count(sla_admins()) <= 1) {
            $error = 'Debe quedar al menos una persona con acceso.';
        } else {
            sla_delete_admin($id);
            header('Location: usuarios.php?ok=borrado');
            exit;
        }
    }

    /* ---- cambiar mi propia contraseña ---- */
    if ($action === 'clave') {
        $actual = $_POST['actual'] ?? '';
        $nueva  = $_POST['nueva'] ?? '';
        $nueva2 = $_POST['nueva2'] ?? '';

        if (!sla_verify_password($yoId, $actual)) {
            $error = 'Tu contraseña actual no es correcta.';
        } elseif (strlen($nueva) < 8) {
            $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif ($nueva !== $nueva2) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } else {
            sla_change_password($yoId, $nueva);
            header('Location: usuarios.php?ok=clave');
            exit;
        }
    }
}

$mensajesOk = [
    'creado'  => 'Listo. Esa persona ya puede entrar con su usuario y contraseña.',
    'borrado' => 'Se quitó el acceso.',
    'clave'   => 'Tu contraseña se actualizó.',
];
$ok = $mensajesOk[$_GET['ok'] ?? ''] ?? '';

$admins = sla_admins();

sla_admin_header('Usuarios', 'usuarios.php');
?>
<div class="admin-container">
  <h1>Personas con acceso</h1>
  <p class="admin-lead">Aquí decides quién puede entrar al panel a manejar eventos, inscripciones y mensajes.</p>

  <?php if ($ok): ?><p class="alert alert-ok"><?= e($ok) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="alert alert-error"><?= e($error) ?></p><?php endif; ?>

  <section class="panel">
    <h2>Quiénes tienen acceso</h2>
    <table class="admin-table">
      <thead><tr><th>Usuario</th><th>Desde</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($admins as $a): ?>
        <tr>
          <td>
            <strong><?= e($a['username']) ?></strong>
            <?php if ((int) $a['id'] === $yoId): ?><span class="tag-state on">Tú</span><?php endif; ?>
          </td>
          <td class="muted"><?= e(sla_date_long(substr($a['created_at'], 0, 10))) ?></td>
          <td class="nowrap">
            <?php if ((int) $a['id'] !== $yoId && count($admins) > 1): ?>
              <form method="post" class="inline" onsubmit="return confirm('¿Quitar el acceso a <?= e($a['username']) ?>?');">
                <?= sla_csrf_field() ?>
                <input type="hidden" name="action" value="borrar">
                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <button type="submit" class="link danger">Quitar acceso</button>
              </form>
            <?php else: ?>
              <span class="muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="panel">
    <h2>Dar acceso a alguien más</h2>
    <p class="muted">Elige un usuario y una contraseña, y entrégaselos a esa persona. Podrá cambiarla después desde aquí.</p>
    <form method="post" class="form-grid" autocomplete="off">
      <?= sla_csrf_field() ?>
      <input type="hidden" name="action" value="crear">

      <label>Usuario
        <input type="text" name="username" required minlength="3" placeholder="nombre de la persona">
      </label>
      <label>Contraseña (mínimo 8 caracteres)
        <input type="password" name="password" required minlength="8">
      </label>
      <label>Repetir contraseña
        <input type="password" name="password2" required minlength="8">
      </label>

      <div class="form-actions col-2">
        <button type="submit" class="btn btn-primary">Dar acceso</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <h2>Cambiar mi contraseña</h2>
    <form method="post" class="form-grid" autocomplete="off">
      <?= sla_csrf_field() ?>
      <input type="hidden" name="action" value="clave">

      <label>Contraseña actual
        <input type="password" name="actual" required>
      </label>
      <label>Nueva contraseña
        <input type="password" name="nueva" required minlength="8">
      </label>
      <label>Repetir la nueva
        <input type="password" name="nueva2" required minlength="8">
      </label>

      <div class="form-actions col-2">
        <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
      </div>
    </form>
  </section>
</div>
<?php sla_admin_footer(); ?>
