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
    $stmt = db()->prepare(
        "UPDATE borrow_requests br
         INNER JOIN items i ON i.id = br.item_id
         SET br.status = 'rejected', br.decision_at = CURRENT_TIMESTAMP
         WHERE br.id = :id AND br.status = 'pending' AND i.owner_id = :owner_id AND i.deleted_at IS NULL"
    );
    $stmt->execute([
        'id' => $requestId,
        'owner_id' => current_user_id(),
    ]);

    if ($stmt->rowCount() < 1) {
        set_flash('error', 'Only a pending request for your item can be rejected.');
    } else {
        set_flash('success', 'Borrow request rejected.');
    }
} catch (Throwable $e) {
    set_flash('error', 'The request could not be rejected.');
}

redirect('dashboard/incoming-requests.php');
