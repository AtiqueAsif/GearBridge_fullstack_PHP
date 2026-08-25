<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

require_login();
$requestId = positive_int($_POST['request_id'] ?? null);

if (!is_post() || $requestId === null) {
    redirect('dashboard/active-borrowings.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('dashboard/active-borrowings.php');
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT br.id, br.item_id, br.status, i.owner_id, i.availability_status
         FROM borrow_requests br
         INNER JOIN items i ON i.id = br.item_id
         WHERE br.id = :id
         FOR UPDATE"
    );
    $stmt->execute(['id' => $requestId]);
    $request = $stmt->fetch();

    if (!$request || (int) $request['owner_id'] !== current_user_id()) {
        $pdo->rollBack();
        set_flash('error', 'You are not authorized to confirm this return.');
        redirect('dashboard/active-borrowings.php');
    }

    if ((string) $request['status'] !== 'approved' || (string) $request['availability_status'] !== 'borrowed') {
        $pdo->rollBack();
        set_flash('error', 'This borrowing is not active.');
        redirect('dashboard/active-borrowings.php');
    }

    $updateRequest = $pdo->prepare(
        "UPDATE borrow_requests
         SET status = 'returned', returned_at = CURRENT_TIMESTAMP
         WHERE id = :id AND status = 'approved'"
    );
    $updateRequest->execute(['id' => $requestId]);

    $updateItem = $pdo->prepare(
        "UPDATE items
         SET availability_status = 'available'
         WHERE id = :item_id AND availability_status = 'borrowed'"
    );
    $updateItem->execute(['item_id' => (int) $request['item_id']]);

    if ($updateRequest->rowCount() !== 1 || $updateItem->rowCount() !== 1) {
        throw new RuntimeException('Return state could not be updated.');
    }

    $pdo->commit();
    set_flash('success', 'Return confirmed. The equipment is available again.');
    redirect('dashboard/active-borrowings.php');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'The return could not be confirmed. Please try again.');
    redirect('dashboard/active-borrowings.php');
}
