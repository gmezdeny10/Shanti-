<?php
require_once __DIR__ . '/../includes/auth.php';

if (!sla_admin_exists()) {
    header('Location: instalar.php');
    exit;
}

if (sla_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error    = '';
$bloqueado = sla_is_locked_out();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sla_check_csrf();

    if ($bloqueado) {
        $error = 'Demasiados intentos fallidos. Espera ' . sla_lockout_minutes_left() . ' minuto(s) antes de volver a intentar.';
    } else {
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (sla_attempt_login($user, $pass)) {
            header('Location: index.php');
            exit;
        }

        usleep(400000); // pequeña espera para desalentar intentos automatizados
        $bloqueado = sla_is_locked_out();

        if ($bloqueado) {
            $error = 'Demasiados intentos fallidos. Por seguridad, espera ' . sla_lockout_minutes_left() . ' minuto(s).';
        } else {
            $restantes = SLA_MAX_INTENTOS - sla_failed_attempts();
            $error = 'Usuario o contraseña incorrectos.'
                   . ($restantes <= 2 ? ' Te quedan ' . $restantes . ' intento(s).' : '');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso — Shanti Lanka Ashram</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-auth">
  <div class="auth-card">
    <p class="auth-eyebrow">Shanti Lanka Ashram</p>
    <h1>Panel de administración</h1>

    <?php if ($error): ?>
      <p class="alert alert-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?= sla_csrf_field() ?>
      <label>Usuario
        <input type="text" name="username" required autofocus <?= $bloqueado ? 'disabled' : '' ?>>
      </label>
      <label>Contraseña
        <input type="password" name="password" required <?= $bloqueado ? 'disabled' : '' ?>>
      </label>
      <button type="submit" class="btn btn-primary" <?= $bloqueado ? 'disabled' : '' ?>>
        <?= $bloqueado ? 'Bloqueado temporalmente' : 'Entrar' ?>
      </button>
    </form>

    <a class="auth-back" href="../index.php">← Volver al sitio</a>
  </div>
</body>
</html>
