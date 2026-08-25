<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$pageTitle = 'My Requests';
$statusFilter = (string) ($_GET['status'] ?? 'all');
$allowed = ['all', 'pending', 'approved', 'rejected', 'cancelled', 'returned'];
if (!in_array($statusFilter, $allowed, true)) {
    $statusFilter = 'all';
}

$requests = [];

try {
    $sql = "SELECT br.*, i.title AS item_title, i.image_path, u.full_name AS owner_name
            FROM borrow_requests br
            INNER JOIN items i ON i.id = br.item_id
            INNER JOIN users u ON u.id = i.owner_id
            WHERE br.borrower_id = :borrower_id";
    $params = ['borrower_id' => current_user_id()];

    if ($statusFilter !== 'all') {
        $sql .= ' AND br.status = :status';
        $params['status'] = $statusFilter;
    }

    $sql .= ' ORDER BY br.requested_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Your borrow requests could not be loaded.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Borrowing</p>
            <h1>My Borrow Requests</h1>
            <p class="panel-copy">Track requests you have sent to other campus members.</p>
        </div>
        <a class="button" href="<?= e(url('browse.php')) ?>">Browse Equipment</a>
    </div>

    <div class="filter-chips">
        <?php foreach ($allowed as $filter): ?>
            <a class="<?= $statusFilter === $filter ? 'is-active' : '' ?>" href="<?= e(page_url('dashboard/my-requests.php', ['status' => $filter])) ?>"><?= e(ucfirst($filter)) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($requests): ?>
        <div class="request-list">
            <?php foreach ($requests as $request): ?>
                <article class="request-card">
                    <div class="request-card-main">
                        <div>
                            <p class="eyebrow"><?= e($request['owner_name']) ?></p>
                            <h3><?= e($request['item_title']) ?></h3>
                            <p><?= e(format_date((string) $request['borrow_from'])) ?> → <?= e(format_date((string) $request['borrow_until'])) ?></p>
                            <small>Requested <?= e(format_date((string) $request['requested_at'], 'M j, Y g:i A')) ?></small>
                        </div>
                        <span class="status-badge <?= e(status_class((string) $request['status'])) ?>">
                            <?= is_overdue((string) $request['status'], (string) $request['borrow_until']) ? 'Overdue' : e(status_label((string) $request['status'])) ?>
                        </span>
                    </div>
                    <?php if (!empty($request['note'])): ?>
                        <p class="request-note"><?= e($request['note']) ?></p>
                    <?php endif; ?>
                    <div class="action-row">
                        <a class="button button-compact button-secondary" href="<?= e(url('item-details.php?id=' . (int) $request['item_id'])) ?>">View Item</a>
                        <?php if ((string) $request['status'] === 'pending'): ?>
                            <form class="inline-form confirm-form" data-confirm="Cancel this pending borrow request?" action="<?= e(url('actions/borrow/cancel.php')) ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                <button class="button button-compact button-danger-outline" type="submit">Cancel Request</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty">
            <h2>No matching requests</h2>
            <p>Your borrowing requests will appear here.</p>
            <a class="button button-secondary" href="<?= e(url('browse.php')) ?>">Browse Equipment</a>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>
