<?php

declare(strict_types=1);

function text_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function text_substr(string $value, int $start, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, $start, $length) : substr($value, $start, $length);
}

function required_text(mixed $value, int $maxLength = 255): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);

    if ($value === '' || text_length($value) > $maxLength) {
        return null;
    }

    return $value;
}

function optional_text(mixed $value, int $maxLength = 255): ?string
{
    if ($value === null || $value === '') {
        return '';
    }

    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    return text_length($value) <= $maxLength ? $value : null;
}

function valid_email(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = strtolower(trim($value));

    if (text_length($value) > 150) {
        return null;
    }

    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
}

function valid_user_type(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    return in_array($value, ['student', 'staff'], true) ? $value : null;
}

function valid_password(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $length = text_length($value);
    return ($length >= 8 && $length <= 255) ? $value : null;
}

function positive_int(mixed $value): ?int
{
    $filtered = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $filtered === false ? null : (int) $filtered;
}

function valid_condition(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    return in_array($value, ['excellent', 'good', 'fair'], true) ? $value : null;
}

function valid_date_value(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();

    if (!$date) {
        return null;
    }

    if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
        return null;
    }

    return $date->format('Y-m-d') === $value ? $value : null;
}

function valid_date_range(string $from, string $until, bool $allowPast = true): bool
{
    $fromValue = valid_date_value($from);
    $untilValue = valid_date_value($until);

    if ($fromValue === null || $untilValue === null) {
        return false;
    }

    $fromDate = new DateTimeImmutable($fromValue);
    $untilDate = new DateTimeImmutable($untilValue);

    if ($untilDate < $fromDate) {
        return false;
    }

    if (!$allowPast && $fromDate < new DateTimeImmutable('today')) {
        return false;
    }

    return true;
}
