<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$pageTitle = 'Active Borrowings';
$tab = (string) ($_GET['tab'] ?? 'borrowed');
if (!in_array($tab, ['borrowed', 'lent'], true)) {
    $tab = 'borrowed';
}

$records = [];

try {
    if ($tab === 'borrowed') {
        $stmt = db()->prepare(
            "SELECT br.*, i.title AS item_title, i.image_path, u.full_name AS person_name
             FROM borrow_requests br
             INNER JOIN items i ON i.id = br.item_id
             INNER JOIN users u ON u.id = i.owner_id
             WHERE br.borrower_id = :user_id AND br.status = 'approved'
             ORDER BY br.borrow_until ASC"
        );
    } else {
        $stmt = db()->prepare(
            "SELECT br.*, i.title AS item_title, i.image_path, u.full_name AS person_name
             FROM borrow_requests br
             INNER JOIN items i ON i.id = br.item_id
             INNER JOIN users u ON u.id = br.borrower_id
             WHERE i.owner_id = :user_id AND br.status = 'approved'
             ORDER BY br.borrow_until ASC"
        );
    }

    $stmt->execute(['user_id' => current_user_id()]);
    $records = $stmt->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Active borrowings could not be loaded.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Current activity</p>
            <h1>Active Borrowings</h1>
            <p class="panel-copy">See items you currently borrow and items you have lent to others.</p>
        </div>
    </div>

    <div class="tab-nav">
        <a class="<?= $tab === 'borrowed' ? 'is-active' : '' ?>" href="<?= e(url('dashboard/active-borrowings.php?tab=borrowed')) ?>">Borrowed by Me</a>
        <a class="<?= $tab === 'lent' ? 'is-active' : '' ?>" href="<?= e(url('dashboard/active-borrowings.php?tab=lent')) ?>">Lent by Me</a>
    </div>

    <?php if ($records): ?>
        <div class="request-list">
            <?php foreach ($records as $record): ?>
                <article class="request-card">
                    <div class="request-card-main">
                        <div>
                            <p class="eyebrow"><?= $tab === 'borrowed' ? 'Owner' : 'Borrower' ?> · <?= e($record['person_name']) ?></p>
                            <h3><?= e($record['item_title']) ?></h3>
                            <p><?= e(format_date((string) $record['borrow_from'])) ?> → <?= e(format_date((string) $record['borrow_until'])) ?></p>
                        </div>
                        <?php if (is_overdue((string) $record['status'], (string) $record['borrow_until'])): ?>
                            <span class="status-badge status-danger">Overdue</span>
                        <?php else: ?>
                            <span class="status-badge status-info">Active</span>
                        <?php endif; ?>
                    </div>

                    <div class="action-row">
                        <a class="button button-compact button-secondary" href="<?= e(url('item-details.php?id=' . (int) $record['item_id'])) ?>">View Item</a>
                        <?php if ($tab === 'lent'): ?>
                            <form class="inline-form confirm-form" data-confirm="Confirm that this item has been physically returned to you?" action="<?= e(url('actions/borrow/return.php')) ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $record['id'] ?>">
                                <button class="button button-compact" type="submit">Mark Returned</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty">
            <h2>No active <?= $tab === 'borrowed' ? 'borrowings' : 'lending' ?></h2>
            <p>Active records will appear here after a request is approved.</p>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>
