<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

require_login();

$itemId = positive_int($_POST['item_id'] ?? null);
$borrowFrom = valid_date_value($_POST['borrow_from'] ?? null);
$borrowUntil = valid_date_value($_POST['borrow_until'] ?? null);
$note = optional_text($_POST['note'] ?? '', 500);

if (!is_post() || $itemId === null) {
    redirect('browse.php');
}

$redirectPath = 'item-details.php?id=' . $itemId;

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect($redirectPath);
}

if ($borrowFrom === null || $borrowUntil === null || $note === null || !valid_date_range($borrowFrom, $borrowUntil, false)) {
    set_flash('error', 'Choose a valid borrowing period starting today or later.');
    redirect($redirectPath);
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

    if (!$item) {
        $pdo->rollBack();
        set_flash('error', 'This equipment is no longer available.');
        redirect('browse.php');
    }

    if ((int) $item['owner_id'] === current_user_id()) {
        $pdo->rollBack();
        set_flash('error', 'You cannot borrow your own item.');
        redirect($redirectPath);
    }

    if ((string) $item['availability_status'] !== 'available') {
        $pdo->rollBack();
        set_flash('error', 'This equipment is currently unavailable.');
        redirect($redirectPath);
    }

    $duplicate = $pdo->prepare(
        "SELECT id FROM borrow_requests
         WHERE item_id = :item_id AND borrower_id = :borrower_id AND status = 'pending'
         LIMIT 1"
    );
    $duplicate->execute([
        'item_id' => $itemId,
        'borrower_id' => current_user_id(),
    ]);

    if ($duplicate->fetch()) {
        $pdo->rollBack();
        set_flash('error', 'You already have a pending request for this item.');
        redirect($redirectPath);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status)
         VALUES (:item_id, :borrower_id, :borrow_from, :borrow_until, :note, 'pending')"
    );
    $stmt->execute([
        'item_id' => $itemId,
        'borrower_id' => current_user_id(),
        'borrow_from' => $borrowFrom,
        'borrow_until' => $borrowUntil,
        'note' => $note !== '' ? $note : null,
    ]);

    $pdo->commit();
    set_flash('success', 'Borrow request sent successfully.');
    redirect('dashboard/my-requests.php');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'The borrow request could not be sent. Please try again.');
    redirect($redirectPath);
}
