<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!is_post()) {
    redirect('login.php');
}

if (is_logged_in()) {
    redirect('dashboard/index.php');
}

$email = valid_email($_POST['email'] ?? null);
$password = is_string($_POST['password'] ?? null) ? (string) $_POST['password'] : '';

set_form_data([
    'email' => is_string($_POST['email'] ?? null) ? trim((string) $_POST['email']) : '',
]);

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('login.php');
}

if ($email === null || $password === '') {
    set_flash('error', 'Invalid email or password.');
    redirect('login.php');
}

try {
    $stmt = db()->prepare(
        'SELECT id, full_name, email, password_hash, user_type
         FROM users
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        set_flash('error', 'Invalid email or password.');
        redirect('login.php');
    }

    if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        if ($newHash !== false) {
            $rehash = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $rehash->execute(['hash' => $newHash, 'id' => (int) $user['id']]);
        }
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = (string) $user['full_name'];
    $_SESSION['user_type'] = (string) $user['user_type'];
    unset($_SESSION['form_data']);

    set_flash('success', 'Welcome back, ' . (string) $user['full_name'] . '.');
    redirect('dashboard/index.php');
} catch (Throwable $e) {
    set_flash('error', 'Login is temporarily unavailable. Please try again.');
    redirect('login.php');
}
