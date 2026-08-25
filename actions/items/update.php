<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/upload.php';

require_login();

$itemId = positive_int($_POST['item_id'] ?? null);
if (!is_post() || $itemId === null) {
    redirect('dashboard/my-items.php');
}

$redirectPath = 'dashboard/edit-item.php?id=' . $itemId;

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect($redirectPath);
}

$title = required_text($_POST['title'] ?? null, 150);
$categoryId = positive_int($_POST['category_id'] ?? null);
$condition = valid_condition($_POST['condition_status'] ?? null);
$description = required_text($_POST['description'] ?? null, 5000);

if ($title === null || $categoryId === null || $condition === null || $description === null) {
    set_flash('error', 'Please complete all item fields correctly.');
    redirect($redirectPath);
}

$newImagePath = null;

try {
    $pdo = db();

    $itemStmt = $pdo->prepare(
        'SELECT id, owner_id, image_path FROM items WHERE id = :id AND deleted_at IS NULL LIMIT 1'
    );
    $itemStmt->execute(['id' => $itemId]);
    $item = $itemStmt->fetch();

    if (!$item || (int) $item['owner_id'] !== current_user_id()) {
        set_flash('error', 'You are not authorized to edit this item.');
        redirect('dashboard/my-items.php');
    }

    $catStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
    $catStmt->execute(['id' => $categoryId]);
    if (!$catStmt->fetch()) {
        set_flash('error', 'Please choose a valid category.');
        redirect($redirectPath);
    }

    $imagePath = $item['image_path'];

    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $upload = process_item_image($_FILES['image']);
        if (!$upload['ok']) {
            set_flash('error', (string) $upload['error']);
            redirect($redirectPath);
        }
        $newImagePath = $upload['path'];
        $imagePath = $newImagePath;
    }

    $stmt = $pdo->prepare(
        'UPDATE items
         SET category_id = :category_id, title = :title, description = :description,
             condition_status = :condition_status, image_path = :image_path
         WHERE id = :id AND owner_id = :owner_id AND deleted_at IS NULL'
    );
    $stmt->execute([
        'category_id' => $categoryId,
        'title' => $title,
        'description' => $description,
        'condition_status' => $condition,
        'image_path' => $imagePath,
        'id' => $itemId,
        'owner_id' => current_user_id(),
    ]);

    if ($newImagePath && !empty($item['image_path'])) {
        safe_unlink_item_image((string) $item['image_path']);
    }

    set_flash('success', 'Equipment updated successfully.');
    redirect('dashboard/my-items.php');
} catch (Throwable $e) {
    if ($newImagePath) {
        safe_unlink_item_image($newImagePath);
    }
    set_flash('error', 'The equipment could not be updated. Please try again.');
    redirect($redirectPath);
}
