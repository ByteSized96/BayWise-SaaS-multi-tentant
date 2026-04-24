<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/auth.php';
require_once __DIR__ . '/../app/Core/helpers.php';

session_destroy();

header('Location: ' . app_url('login.php'));
exit;
?>