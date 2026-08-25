<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

require_login();
$requestId = positive_int($_POST['request_id'] ?? null);

if (!is_post() || $requestId === null) {
    redirect('dashboard/incoming-requests.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('dashboard/incoming-requests.php');
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $requestStmt = $pdo->prepare(
        "SELECT id, item_id, status
         FROM borrow_requests
         WHERE id = :id
         FOR UPDATE"
    );
    $requestStmt->execute(['id' => $requestId]);
    $request = $requestStmt->fetch();

    if (!$request || (string) $request['status'] !== 'pending') {
        $pdo->rollBack();
        set_flash('error', 'This request can no longer be approved.');
        redirect('dashboard/incoming-requests.php');
    }

    $itemStmt = $pdo->prepare(
        "SELECT id, owner_id, availability_status, deleted_at
         FROM items
         WHERE id = :id
         FOR UPDATE"
    );
    $itemStmt->execute(['id' => (int) $request['item_id']]);
    $item = $itemStmt->fetch();

    if (!$item || (int) $item['owner_id'] !== current_user_id() || $item['deleted_at'] !== null) {
        $pdo->rollBack();
        set_flash('error', 'You are not authorized to approve this request.');
        redirect('dashboard/incoming-requests.php');
    }

    if ((string) $item['availability_status'] !== 'available') {
        $pdo->rollBack();
        set_flash('error', 'This equipment is no longer available.');
        redirect('dashboard/incoming-requests.php');
    }

    $approve = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'approved', decision_at = CURRENT_TIMESTAMP
         WHERE id = :id AND status = 'pending'"
    );
    $approve->execute(['id' => $requestId]);

    if ($approve->rowCount() !== 1) {
        throw new RuntimeException('Request status changed before approval.');
    }

    $updateItem = $pdo->prepare(
        "UPDATE items
         SET availability_status = 'borrowed'
         WHERE id = :item_id AND availability_status = 'available' AND deleted_at IS NULL"
    );
    $updateItem->execute(['item_id' => (int) $request['item_id']]);

    if ($updateItem->rowCount() !== 1) {
        throw new RuntimeException('Item state changed before approval.');
    }

    $rejectOthers = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'rejected', decision_at = CURRENT_TIMESTAMP
         WHERE item_id = :item_id AND id <> :approved_id AND status = 'pending'"
    );
    $rejectOthers->execute([
        'item_id' => (int) $request['item_id'],
        'approved_id' => $requestId,
    ]);

    $pdo->commit();
    set_flash('success', 'Borrow request approved. The item is now marked as borrowed.');
    redirect('dashboard/incoming-requests.php');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'The request could not be approved. It may have already changed.');
    redirect('dashboard/incoming-requests.php');
}
