<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';
require_login();

$pageTitle = 'Incoming Requests';
$itemFilter = positive_int($_GET['item'] ?? null);
$statusFilter = (string) ($_GET['status'] ?? 'pending');
$allowedStatuses = ['all', 'pending', 'approved', 'rejected', 'cancelled', 'returned'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'pending';
}

$requests = [];

try {
    $sql = "SELECT br.*, i.title AS item_title, i.availability_status, u.full_name AS borrower_name, u.user_type AS borrower_type
            FROM borrow_requests br
            INNER JOIN items i ON i.id = br.item_id
            INNER JOIN users u ON u.id = br.borrower_id
            WHERE i.owner_id = :owner_id AND i.deleted_at IS NULL";
    $params = ['owner_id' => current_user_id()];

    if ($statusFilter !== 'all') {
        $sql .= ' AND br.status = :status';
        $params['status'] = $statusFilter;
    }
    if ($itemFilter !== null) {
        $sql .= ' AND br.item_id = :item_id';
        $params['item_id'] = $itemFilter;
    }

    $sql .= ' ORDER BY br.requested_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Incoming requests could not be loaded.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Lending</p>
            <h1>Incoming Requests</h1>
            <p class="panel-copy">Review requests from students and staff who want to borrow your equipment.</p>
        </div>
    </div>

    <div class="filter-chips">
        <?php foreach ($allowedStatuses as $filter): ?>
            <a class="<?= $statusFilter === $filter ? 'is-active' : '' ?>" href="<?= e(page_url('dashboard/incoming-requests.php', ['status' => $filter, 'item' => $itemFilter])) ?>"><?= e(ucfirst($filter)) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($requests): ?>
        <div class="request-list">
            <?php foreach ($requests as $request): ?>
                <article class="request-card">
                    <div class="request-card-main">
                        <div>
                            <p class="eyebrow"><?= e(ucfirst((string) $request['borrower_type'])) ?> request</p>
                            <h3><?= e($request['item_title']) ?></h3>
                            <p><strong><?= e($request['borrower_name']) ?></strong> · <?= e(format_date((string) $request['borrow_from'])) ?> → <?= e(format_date((string) $request['borrow_until'])) ?></p>
                            <small>Requested <?= e(format_date((string) $request['requested_at'], 'M j, Y g:i A')) ?></small>
                        </div>
                        <span class="status-badge <?= e(status_class((string) $request['status'])) ?>"><?= e(status_label((string) $request['status'])) ?></span>
                    </div>

                    <?php if (!empty($request['note'])): ?>
                        <div class="request-note"><strong>Borrower note:</strong> <?= e($request['note']) ?></div>
                    <?php endif; ?>

                    <?php if ((string) $request['status'] === 'pending'): ?>
                        <div class="action-row request-actions">
                            <form class="inline-form confirm-form" data-confirm="Reject this borrow request?" action="<?= e(url('actions/borrow/reject.php')) ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                <button class="button button-secondary" type="submit">Reject</button>
                            </form>
                            <form class="inline-form confirm-form" data-confirm="Approve this request? The item will become unavailable and other pending requests for it will be rejected." action="<?= e(url('actions/borrow/approve.php')) ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                <button class="button" type="submit">Approve</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty">
            <h2>No matching incoming requests</h2>
            <p>New borrowing requests for your items will appear here.</p>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>
