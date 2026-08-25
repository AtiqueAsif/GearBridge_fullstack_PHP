<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!is_post()) {
    redirect('register.php');
}

if (is_logged_in()) {
    redirect('dashboard/index.php');
}

$fullName = required_text($_POST['full_name'] ?? null, 100);
$email = valid_email($_POST['email'] ?? null);
$userType = valid_user_type($_POST['user_type'] ?? null);
$password = valid_password($_POST['password'] ?? null);
$passwordConfirmation = $_POST['password_confirmation'] ?? null;

set_form_data([
    'full_name' => is_string($_POST['full_name'] ?? null) ? trim((string) $_POST['full_name']) : '',
    'email' => is_string($_POST['email'] ?? null) ? trim((string) $_POST['email']) : '',
    'user_type' => is_string($_POST['user_type'] ?? null) ? (string) $_POST['user_type'] : '',
]);

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('register.php');
}

if ($fullName === null || $email === null || $userType === null || $password === null) {
    set_flash('error', 'Please complete all fields correctly. Passwords must be at least 8 characters.');
    redirect('register.php');
}

if (!is_string($passwordConfirmation) || !hash_equals($password, $passwordConfirmation)) {
    set_flash('error', 'Password confirmation does not match.');
    redirect('register.php');
}

try {
    $pdo = db();

    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute(['email' => $email]);

    if ($check->fetch()) {
        set_flash('error', 'An account with this email already exists.');
        redirect('register.php');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        throw new RuntimeException('Unable to hash password.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, user_type)
         VALUES (:full_name, :email, :password_hash, :user_type)'
    );
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => $passwordHash,
        'user_type' => $userType,
    ]);

    unset($_SESSION['form_data']);
    set_flash('success', 'Account created successfully. You can now log in.');
    redirect('login.php');
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        set_flash('error', 'An account with this email already exists.');
    } else {
        set_flash('error', 'Registration could not be completed. Please try again.');
    }
    redirect('register.php');
} catch (Throwable $e) {
    set_flash('error', 'Registration could not be completed. Please try again.');
    redirect('register.php');
}
