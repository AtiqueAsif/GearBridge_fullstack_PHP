<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';

require_login();
$requestId = positive_int($_POST['request_id'] ?? null);

if (!is_post() || $requestId === null) {
    redirect('dashboard/my-requests.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Your form session expired. Please try again.');
    redirect('dashboard/my-requests.php');
}

try {
    $stmt = db()->prepare(
        "UPDATE borrow_requests
         SET status = 'cancelled', decision_at = CURRENT_TIMESTAMP
         WHERE id = :id AND borrower_id = :borrower_id AND status = 'pending'"
    );
    $stmt->execute([
        'id' => $requestId,
        'borrower_id' => current_user_id(),
    ]);

    if ($stmt->rowCount() < 1) {
        set_flash('error', 'Only your own pending request can be cancelled.');
    } else {
        set_flash('success', 'Borrow request cancelled.');
    }
} catch (Throwable $e) {
    set_flash('error', 'The request could not be cancelled.');
}

redirect('dashboard/my-requests.php');
