<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!is_post()) {
    redirect(is_logged_in() ? 'dashboard/index.php' : 'index.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Unable to verify the logout request. Please try again.');
    redirect(is_logged_in() ? 'dashboard/index.php' : 'index.php');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'] ?: '/',
        'domain' => $params['domain'] ?? '',
        'secure' => (bool) ($params['secure'] ?? false),
        'httponly' => (bool) ($params['httponly'] ?? true),
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

session_destroy();
session_id('');
session_start();
session_regenerate_id(true);

set_flash('success', 'You have been logged out.');
redirect('index.php');
