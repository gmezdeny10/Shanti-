<?php
require_once __DIR__ . '/../includes/auth.php';
sla_logout();
header('Location: login.php');
exit;
