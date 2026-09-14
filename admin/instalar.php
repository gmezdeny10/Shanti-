<?php
/**
 * Creación de la primera cuenta de administrador.
 * Solo funciona mientras no exista ninguna cuenta.
 */
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$done  = false;

if (sla_admin_exists()) {
    $error = 'Ya existe una cuenta de administrador. Entra desde la página de acceso.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user  = trim($_POST['username'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($user) < 3) {
        $error = 'El usuario debe tener al menos 3 caracteres.';
    } elseif (strlen($pass) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        sla_create_admin($user, $pass);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear administrador — Shanti Lanka Ashram</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-auth">
  <div class="auth-card">
    <p class="auth-eyebrow">Shanti Lanka Ashram</p>
    <h1>Crear administrador</h1>

    <?php if ($done): ?>
      <p class="alert alert-ok">Cuenta creada correctamente.</p>
      <a class="btn btn-primary" href="login.php">Entrar al panel</a>
    <?php else: ?>
      <?php if ($error): ?>
        <p class="alert alert-error"><?= e($error) ?></p>
      <?php endif; ?>

      <?php if (!sla_admin_exists()): ?>
        <p class="auth-hint">Esta pantalla solo aparece una vez, para crear la cuenta con la que vas a administrar los eventos y mensajes.</p>
        <form method="post" autocomplete="off">
          <label>Usuario
            <input type="text" name="username" required minlength="3" value="<?= e($_POST['username'] ?? '') ?>">
          </label>
          <label>Contraseña (mínimo 8 caracteres)
            <input type="password" name="password" required minlength="8">
          </label>
          <label>Repite la contraseña
            <input type="password" name="password2" required minlength="8">
          </label>
          <button type="submit" class="btn btn-primary">Crear cuenta</button>
        </form>
      <?php else: ?>
        <a class="btn btn-primary" href="login.php">Ir al acceso</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>
</html>
