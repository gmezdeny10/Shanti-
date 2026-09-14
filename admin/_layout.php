<?php
/**
 * Cabecera y pie comunes del panel. Requiere sesión activa.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/model.php';
sla_require_login();

function sla_admin_header(string $title, string $active = ''): void
{
    $unread = sla_unread_count();
    $nav = [
        'index.php'          => 'Inicio',
        'eventos.php'        => 'Eventos',
        'inscripciones.php'  => 'Inscripciones',
        'mensajes.php'       => 'Mensajes',
        'usuarios.php'       => 'Usuarios',
    ];
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> — Panel Shanti Lanka</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="admin-bar">
  <div class="admin-bar-inner">
    <a class="admin-logo" href="index.php">
      <span class="admin-logo-mark"></span>
      Shanti Lanka <em>Panel</em>
    </a>
    <nav class="admin-nav">
      <?php foreach ($nav as $href => $label): ?>
        <a href="<?= e($href) ?>" class="<?= $active === $href ? 'active' : '' ?>">
          <?= e($label) ?><?php if ($href === 'mensajes.php' && $unread > 0): ?><span class="badge"><?= $unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-bar-right">
      <a class="admin-site-link" href="../index.php" target="_blank">Ver el sitio ↗</a>
      <span class="admin-user"><?= e(sla_current_admin()) ?></span>
      <a class="btn btn-ghost btn-sm" href="logout.php">Salir</a>
    </div>
  </div>
</header>
<main class="admin-main">
    <?php
}

function sla_admin_footer(): void
{
    ?>
</main>
</body>
</html>
    <?php
}
