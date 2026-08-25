<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/upload.php';

require_login();

if (!is_post()) {
    redirect('dashboard/add-item.php');
}

$title = required_text($_POST['title'] ?? null, 150);
$categoryId = positive_int($_POST['category_id'] ?? null);
$condition = valid_condition($_POST['condition_status'] ?? null);
$description = required_text($_POST['description'] ?? null, 5000);

set_form_data([
    'title' => is_string($_POST['title'] ?? null) ? trim((string) $_POST['title']) : '',
    'category_id' => (string) ($_POST['category_id'] ?? ''),
    'condition_status' => (string) ($_POST['condition_status'] ?? ''),
    'description' => is_string($_POST['description'] ?? null) ? trim((string) $_POST['description']) : '',
]);

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('dashboard/add-item.php');
}

if ($title === null || $categoryId === null || $condition === null || $description === null) {
    set_flash('error', 'Please complete all item fields correctly.');
    redirect('dashboard/add-item.php');
}

$imagePath = null;

try {
    $pdo = db();

    $catStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
    $catStmt->execute(['id' => $categoryId]);
    if (!$catStmt->fetch()) {
        set_flash('error', 'Please choose a valid category.');
        redirect('dashboard/add-item.php');
    }

    if (isset($_FILES['image'])) {
        $upload = process_item_image($_FILES['image']);
        if (!$upload['ok']) {
            set_flash('error', (string) $upload['error']);
            redirect('dashboard/add-item.php');
        }
        $imagePath = $upload['path'];
    }

    $stmt = $pdo->prepare(
        "INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status)
         VALUES (:owner_id, :category_id, :title, :description, :condition_status, :image_path, 'available')"
    );
    $stmt->execute([
        'owner_id' => current_user_id(),
        'category_id' => $categoryId,
        'title' => $title,
        'description' => $description,
        'condition_status' => $condition,
        'image_path' => $imagePath,
    ]);

    unset($_SESSION['form_data']);
    set_flash('success', 'Equipment added successfully.');
    redirect('dashboard/my-items.php');
} catch (Throwable $e) {
    if ($imagePath) {
        safe_unlink_item_image($imagePath);
    }
    set_flash('error', 'The equipment could not be added. Please try again.');
    redirect('dashboard/add-item.php');
}
