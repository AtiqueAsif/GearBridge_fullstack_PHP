<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Borrow History';
$view = (string) ($_GET['view'] ?? 'all');
if (!in_array($view, ['all', 'borrowed', 'lent'], true)) {
    $view = 'all';
}

$history = [];

try {
    $where = ["br.status = 'returned'"];
    $params = [];

    if ($view === 'borrowed') {
        $where[] = 'br.borrower_id = :borrower_id';
        $params['borrower_id'] = current_user_id();
    } elseif ($view === 'lent') {
        $where[] = 'i.owner_id = :owner_id';
        $params['owner_id'] = current_user_id();
    } else {
        $where[] = '(br.borrower_id = :borrower_id OR i.owner_id = :owner_id)';
        $params['borrower_id'] = current_user_id();
        $params['owner_id'] = current_user_id();
    }

    $stmt = db()->prepare(
        "SELECT br.*, i.title AS item_title, i.owner_id,
                owner.full_name AS owner_name, borrower.full_name AS borrower_name
         FROM borrow_requests br
         INNER JOIN items i ON i.id = br.item_id
         INNER JOIN users owner ON owner.id = i.owner_id
         INNER JOIN users borrower ON borrower.id = br.borrower_id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY br.returned_at DESC"
    );
    $stmt->execute($params);
    $history = $stmt->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Borrow history could not be loaded.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Completed activity</p>
            <h1>Borrow History</h1>
            <p class="panel-copy">A record of equipment you borrowed or lent and later returned.</p>
        </div>
    </div>

    <div class="filter-chips">
        <?php foreach (['all' => 'All', 'borrowed' => 'Borrowed', 'lent' => 'Lent'] as $key => $label): ?>
            <a class="<?= $view === $key ? 'is-active' : '' ?>" href="<?= e(page_url('dashboard/borrow-history.php', ['view' => $key])) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($history): ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Role</th>
                    <th>Other User</th>
                    <th>Borrow Period</th>
                    <th>Returned</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $record): ?>
                    <?php $isBorrower = (int) $record['borrower_id'] === current_user_id(); ?>
                    <tr>
                        <td><strong><?= e($record['item_title']) ?></strong></td>
                        <td><?= $isBorrower ? 'Borrowed' : 'Lent' ?></td>
                        <td><?= e($isBorrower ? $record['owner_name'] : $record['borrower_name']) ?></td>
                        <td><?= e(format_date((string) $record['borrow_from'])) ?> → <?= e(format_date((string) $record['borrow_until'])) ?></td>
                        <td><?= e(format_date((string) $record['returned_at'], 'M j, Y g:i A')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty">
            <h2>No completed borrowing history</h2>
            <p>Returned borrowing records will appear here.</p>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>
