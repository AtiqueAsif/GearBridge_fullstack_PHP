<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Dashboard';
$userId = current_user_id();

$stats = [
    'my_items' => 0,
    'borrowed_by_me' => 0,
    'pending_requests' => 0,
    'incoming_requests' => 0,
];

$recentRequests = [];
$recentIncoming = [];
$recentItems = [];

try {
    $pdo = db();

    $statsStmt = $pdo->prepare(
        "SELECT
            (SELECT COUNT(*) FROM items WHERE owner_id = :uid1 AND deleted_at IS NULL) AS my_items,
            (SELECT COUNT(*) FROM borrow_requests WHERE borrower_id = :uid2 AND status = 'approved') AS borrowed_by_me,
            (SELECT COUNT(*) FROM borrow_requests WHERE borrower_id = :uid3 AND status = 'pending') AS pending_requests,
            (SELECT COUNT(*)
             FROM borrow_requests br
             INNER JOIN items i ON i.id = br.item_id
             WHERE i.owner_id = :uid4 AND i.deleted_at IS NULL AND br.status = 'pending') AS incoming_requests"
    );
    $statsStmt->execute([
        'uid1' => $userId,
        'uid2' => $userId,
        'uid3' => $userId,
        'uid4' => $userId,
    ]);
    $result = $statsStmt->fetch();
    if ($result) {
        $stats = array_map('intval', $result);
    }

    $recentStmt = $pdo->prepare(
        "SELECT br.id, br.status, br.borrow_from, br.borrow_until, br.requested_at,
                i.id AS item_id, i.title AS item_title, u.full_name AS owner_name
         FROM borrow_requests br
         INNER JOIN items i ON i.id = br.item_id
         INNER JOIN users u ON u.id = i.owner_id
         WHERE br.borrower_id = :user_id
         ORDER BY br.requested_at DESC
         LIMIT 5"
    );
    $recentStmt->execute(['user_id' => $userId]);
    $recentRequests = $recentStmt->fetchAll();

    $incomingStmt = $pdo->prepare(
        "SELECT br.id, br.status, br.borrow_from, br.borrow_until, br.requested_at,
                i.title AS item_title, u.full_name AS borrower_name
         FROM borrow_requests br
         INNER JOIN items i ON i.id = br.item_id
         INNER JOIN users u ON u.id = br.borrower_id
         WHERE i.owner_id = :user_id AND i.deleted_at IS NULL
         ORDER BY br.requested_at DESC
         LIMIT 5"
    );
    $incomingStmt->execute(['user_id' => $userId]);
    $recentIncoming = $incomingStmt->fetchAll();

    $itemsStmt = $pdo->prepare(
        "SELECT i.id, i.title, i.availability_status, i.created_at, c.name AS category_name
         FROM items i
         INNER JOIN categories c ON c.id = i.category_id
         WHERE i.owner_id = :user_id AND i.deleted_at IS NULL
         ORDER BY i.created_at DESC
         LIMIT 5"
    );
    $itemsStmt->execute(['user_id' => $userId]);
    $recentItems = $itemsStmt->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Dashboard data could not be loaded. Check the database setup and try again.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-welcome">
    <div>
        <p class="eyebrow">Account workspace</p>
        <h1>Welcome, <?= e(current_user_name()) ?></h1>
        <p>Manage your shared equipment and borrowing activity from one place.</p>
    </div>
</section>

<section class="dashboard-stat-grid" aria-label="Dashboard summary">
    <article class="dashboard-stat-card">
        <span>My Listed Items</span>
        <strong><?= (int) $stats['my_items'] ?></strong>
    </article>
    <article class="dashboard-stat-card">
        <span>Borrowed by Me</span>
        <strong><?= (int) $stats['borrowed_by_me'] ?></strong>
    </article>
    <article class="dashboard-stat-card">
        <span>My Pending Requests</span>
        <strong><?= (int) $stats['pending_requests'] ?></strong>
    </article>
    <article class="dashboard-stat-card">
        <span>Incoming Requests</span>
        <strong><?= (int) $stats['incoming_requests'] ?></strong>
    </article>
</section>

<div class="dashboard-two-column">
    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div>
                <p class="eyebrow">Borrowing</p>
                <h2>My recent requests</h2>
            </div>
            <a class="text-link" href="<?= e(url('dashboard/my-requests.php')) ?>">View all →</a>
        </div>

        <?php if ($recentRequests): ?>
            <div class="activity-list">
                <?php foreach ($recentRequests as $request): ?>
                    <a href="<?= e(url('item-details.php?id=' . (int) $request['item_id'])) ?>" class="activity-row">
                        <div>
                            <strong><?= e($request['item_title']) ?></strong>
                            <span><?= e($request['owner_name']) ?> · <?= e(format_date((string) $request['borrow_from'])) ?> → <?= e(format_date((string) $request['borrow_until'])) ?></span>
                        </div>
                        <span class="status-badge <?= e(status_class((string) $request['status'])) ?>"><?= e(status_label((string) $request['status'])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state compact-empty">
                <h3>No borrowing activity yet</h3>
                <p>Your requests will appear here.</p>
                <a class="button button-secondary button-compact" href="<?= e(url('browse.php')) ?>">Browse Equipment</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div>
                <p class="eyebrow">Lending</p>
                <h2>Recent incoming requests</h2>
            </div>
            <a class="text-link" href="<?= e(url('dashboard/incoming-requests.php')) ?>">View all →</a>
        </div>

        <?php if ($recentIncoming): ?>
            <div class="activity-list">
                <?php foreach ($recentIncoming as $request): ?>
                    <a href="<?= e(url('dashboard/incoming-requests.php')) ?>" class="activity-row">
                        <div>
                            <strong><?= e($request['item_title']) ?></strong>
                            <span><?= e($request['borrower_name']) ?> · requested <?= e(format_date((string) $request['requested_at'], 'M j')) ?></span>
                        </div>
                        <span class="status-badge <?= e(status_class((string) $request['status'])) ?>"><?= e(status_label((string) $request['status'])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state compact-empty">
                <h3>No incoming requests yet</h3>
                <p>Requests for your listed equipment will appear here.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<section class="dashboard-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">My listings</p>
            <h2>Recently added equipment</h2>
        </div>
        <a class="button button-secondary button-compact" href="<?= e(url('dashboard/add-item.php')) ?>">+ Add Item</a>
    </div>

    <?php if ($recentItems): ?>
        <div class="activity-list">
            <?php foreach ($recentItems as $item): ?>
                <a href="<?= e(url('item-details.php?id=' . (int) $item['id'])) ?>" class="activity-row">
                    <div>
                        <strong><?= e($item['title']) ?></strong>
                        <span><?= e($item['category_name']) ?> · added <?= e(format_date((string) $item['created_at'], 'M j, Y')) ?></span>
                    </div>
                    <span class="status-badge <?= e(status_class((string) $item['availability_status'])) ?>"><?= e(status_label((string) $item['availability_status'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty">
            <h3>You have not listed any equipment yet</h3>
            <p>Add an item to start sharing with the campus community.</p>
            <a class="button" href="<?= e(url('dashboard/add-item.php')) ?>">Add Your First Item</a>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>
