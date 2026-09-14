<?php
/**
 * Recibe el formulario de contacto de la portada y guarda el mensaje.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/model.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contacto');
    exit;
}

sla_check_csrf();

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$body  = trim($_POST['body'] ?? '');

if ($name === '' || $body === '') {
    header('Location: index.php?mensaje=error#contacto');
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?mensaje=error#contacto');
    exit;
}

sla_add_message([
    'name'  => mb_substr($name, 0, 120),
    'email' => mb_substr($email, 0, 160),
    'phone' => mb_substr($phone, 0, 60),
    'body'  => mb_substr($body, 0, 4000),
]);

header('Location: index.php?mensaje=ok#contacto');
exit;
