<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function process_item_image(array $file): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => 'Invalid image upload request.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'The image could not be uploaded.'];
    }

    if (($file['size'] ?? 0) <= 0 || (int) $file['size'] > MAX_ITEM_IMAGE_BYTES) {
        return ['ok' => false, 'error' => 'Item images must be 5 MB or smaller.'];
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        return ['ok' => false, 'error' => 'Invalid uploaded image.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string) $file['tmp_name']);

    if (!is_string($mime) || !isset(ALLOWED_ITEM_IMAGE_TYPES[$mime])) {
        return ['ok' => false, 'error' => 'Use a JPG, PNG, or WEBP image.'];
    }

    $extension = ALLOWED_ITEM_IMAGE_TYPES[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;

    $directory = dirname(__DIR__) . '/uploads/items';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        return ['ok' => false, 'error' => 'The upload directory is not available.'];
    }

    $destination = $directory . '/' . $filename;
    if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'The image could not be saved.'];
    }

    return ['ok' => true, 'path' => 'uploads/items/' . $filename];
}
