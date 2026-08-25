<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return is_logged_in() ? (int) $_SESSION['user_id'] : null;
}

function current_user_name(): string
{
    return is_logged_in() ? (string) ($_SESSION['user_name'] ?? 'User') : '';
}

function current_user_type(): string
{
    return is_logged_in() ? (string) ($_SESSION['user_type'] ?? '') : '';
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_guest(): void
{
    if (is_logged_in()) {
        redirect('dashboard/index.php');
    }
}
