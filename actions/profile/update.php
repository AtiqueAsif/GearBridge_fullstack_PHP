<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

require_login();

if (!is_post()) {
    redirect('dashboard/profile.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('dashboard/profile.php');
}

$fullName = required_text($_POST['full_name'] ?? null, 100);
$email = valid_email($_POST['email'] ?? null);

if ($fullName === null || $email === null) {
    set_flash('error', 'Enter a valid name and email address.');
    redirect('dashboard/profile.php');
}

try {
    $pdo = db();

    $duplicate = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
    $duplicate->execute(['email' => $email, 'id' => current_user_id()]);
    if ($duplicate->fetch()) {
        set_flash('error', 'That email address is already in use.');
        redirect('dashboard/profile.php');
    }

    $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name, email = :email WHERE id = :id');
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'id' => current_user_id(),
    ]);

    $_SESSION['user_name'] = $fullName;
    set_flash('success', 'Profile updated successfully.');
} catch (Throwable $e) {
    set_flash('error', 'Your profile could not be updated.');
}

redirect('dashboard/profile.php');
