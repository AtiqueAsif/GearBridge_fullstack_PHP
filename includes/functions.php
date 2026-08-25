<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path === '' ? '' : '/' . $path);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash(): ?array
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function set_form_data(array $data): void
{
    $_SESSION['form_data'] = $data;
}

function pull_form_data(): array
{
    if (empty($_SESSION['form_data']) || !is_array($_SESSION['form_data'])) {
        return [];
    }

    $data = $_SESSION['form_data'];
    unset($_SESSION['form_data']);

    return $data;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function format_date(?string $date, string $format = 'M j, Y'): string
{
    if (!$date) {
        return '—';
    }

    try {
        return (new DateTimeImmutable($date))->format($format);
    } catch (Throwable) {
        return (string) $date;
    }
}

function status_label(string $status): string
{
    return match ($status) {
        'available' => 'Available',
        'borrowed' => 'Borrowed',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

function status_class(string $status): string
{
    return match ($status) {
        'available', 'returned' => 'status-success',
        'pending' => 'status-warning',
        'approved', 'borrowed' => 'status-info',
        'rejected' => 'status-danger',
        'cancelled' => 'status-muted',
        default => '',
    };
}

function page_url(string $path, array $query = []): string
{
    $query = array_filter(
        $query,
        static fn ($value) => $value !== '' && $value !== null
    );

    return url($path) . ($query ? '?' . http_build_query($query) : '');
}

function safe_unlink_item_image(?string $imagePath): void
{
    if (!$imagePath || !str_starts_with($imagePath, 'uploads/items/')) {
        return;
    }

    $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
    $full = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relative;
    $uploadsRoot = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items');

    if ($uploadsRoot === false || !is_file($full)) {
        return;
    }

    $real = realpath($full);
    if ($real !== false && str_starts_with($real, $uploadsRoot . DIRECTORY_SEPARATOR)) {
        @unlink($real);
    }
}

function is_overdue(string $status, ?string $borrowUntil): bool
{
    if ($status !== 'approved' || !$borrowUntil) {
        return false;
    }

    try {
        $today = new DateTimeImmutable('today');
        $until = new DateTimeImmutable($borrowUntil);
        return $today > $until;
    } catch (Throwable) {
        return false;
    }
}
