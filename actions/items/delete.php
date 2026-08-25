<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

require_login();

$itemId = positive_int($_POST['item_id'] ?? null);

if (!is_post() || $itemId === null) {
    redirect('dashboard/my-items.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('dashboard/my-items.php');
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $itemStmt = $pdo->prepare(
        'SELECT id, owner_id, availability_status
         FROM items
         WHERE id = :id AND deleted_at IS NULL
         FOR UPDATE'
    );
    $itemStmt->execute(['id' => $itemId]);
    $item = $itemStmt->fetch();

    if (!$item || (int) $item['owner_id'] !== current_user_id()) {
        $pdo->rollBack();
        set_flash('error', 'You are not authorized to delete this item.');
        redirect('dashboard/my-items.php');
    }

    if ((string) $item['availability_status'] === 'borrowed') {
        $pdo->rollBack();
        set_flash('error', 'A currently borrowed item cannot be deleted.');
        redirect('dashboard/my-items.php');
    }

    $cancel = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'cancelled', decision_at = CURRENT_TIMESTAMP
         WHERE item_id = :item_id AND status = 'pending'"
    );
    $cancel->execute(['item_id' => $itemId]);

    $delete = $pdo->prepare(
        'UPDATE items SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND owner_id = :owner_id'
    );
    $delete->execute(['id' => $itemId, 'owner_id' => current_user_id()]);

    $pdo->commit();

    set_flash('success', 'Equipment removed. Any pending requests were cancelled.');
    redirect('dashboard/my-items.php');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'The item could not be removed. Please try again.');
    redirect('dashboard/my-items.php');
}
