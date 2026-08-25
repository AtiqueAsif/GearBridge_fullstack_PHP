<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$pageTitle = 'My Items';
$userId = current_user_id();
$items = [];

try {
    $stmt = db()->prepare(
        "SELECT i.id, i.title, i.condition_status, i.availability_status, i.image_path,
                i.created_at, c.name AS category_name,
                (SELECT COUNT(*) FROM borrow_requests br WHERE br.item_id = i.id AND br.status = 'pending') AS pending_count
         FROM items i
         INNER JOIN categories c ON c.id = i.category_id
         WHERE i.owner_id = :owner_id AND i.deleted_at IS NULL
         ORDER BY i.created_at DESC"
    );
    $stmt->execute(['owner_id' => $userId]);
    $items = $stmt->fetchAll();
} catch (Throwable $e) {
    set_flash('error', 'Your items could not be loaded.');
}

require __DIR__ . '/../components/dashboard-header.php';
?>
<section class="dashboard-panel">
    <div class="dashboard-panel-heading">
        <div>
            <p class="eyebrow">Equipment management</p>
            <h1>My Items</h1>
            <p class="panel-copy">Manage the equipment you have shared with the campus community.</p>
        </div>
        <a class="button" href="<?= e(url('dashboard/add-item.php')) ?>">+ Add Item</a>
    </div>

    <?php if ($items): ?>
        <div class="table-wrap desktop-table">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Condition</th>
                    <th>Status</th>
                    <th>Pending Requests</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="table-item">
                                <?php $image = $item['image_path'] ? url((string) $item['image_path']) : url('assets/images/items/default-item.jpg'); ?>
                                <img src="<?= e($image) ?>" alt="">
                                <div>
                                    <strong><?= e($item['title']) ?></strong>
                                    <small>Added <?= e(format_date((string) $item['created_at'])) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($item['category_name']) ?></td>
                        <td><?= e(ucfirst((string) $item['condition_status'])) ?></td>
                        <td><span class="status-badge <?= e(status_class((string) $item['availability_status'])) ?>"><?= e(status_label((string) $item['availability_status'])) ?></span></td>
                        <td><?= (int) $item['pending_count'] ?></td>
                        <td>
                            <div class="action-row">
                                <a class="button button-compact button-secondary" href="<?= e(url('item-details.php?id=' . (int) $item['id'])) ?>">View</a>
                                <a class="button button-compact button-secondary" href="<?= e(url('dashboard/edit-item.php?id=' . (int) $item['id'])) ?>">Edit</a>
                                <?php if ((int) $item['pending_count'] > 0): ?>
                                    <a class="button button-compact button-secondary" href="<?= e(url('dashboard/incoming-requests.php?item=' . (int) $item['id'])) ?>">Requests</a>
                                <?php endif; ?>
                                <form class="inline-form confirm-form" data-confirm="Remove this equipment? Pending requests will be cancelled. Borrowed items cannot be removed." action="<?= e(url('actions/items/delete.php')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                    <button class="button button-compact button-danger-outline" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-card-list">
            <?php foreach ($items as $item): ?>
                <article class="mobile-data-card">
                    <div class="mobile-data-card-head">
                        <strong><?= e($item['title']) ?></strong>
                        <span class="status-badge <?= e(status_class((string) $item['availability_status'])) ?>"><?= e(status_label((string) $item['availability_status'])) ?></span>
                    </div>
                    <p><?= e($item['category_name']) ?> · <?= e(ucfirst((string) $item['condition_status'])) ?></p>
                    <p><?= (int) $item['pending_count'] ?> pending request(s)</p>
                    <div class="action-row">
                        <a class="button button-compact button-secondary" href="<?= e(url('item-details.php?id=' . (int) $item['id'])) ?>">View</a>
                        <a class="button button-compact button-secondary" href="<?= e(url('dashboard/edit-item.php?id=' . (int) $item['id'])) ?>">Edit</a>
                        <?php if ((int) $item['pending_count'] > 0): ?>
                            <a class="button button-compact button-secondary" href="<?= e(url('dashboard/incoming-requests.php?item=' . (int) $item['id'])) ?>">Requests</a>
                        <?php endif; ?>
                        <form class="inline-form confirm-form" data-confirm="Remove this equipment? Pending requests will be cancelled. Borrowed items cannot be removed." action="<?= e(url('actions/items/delete.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                            <button class="button button-compact button-danger-outline" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty">
            <h2>No equipment listed yet</h2>
            <p>Add your first item to make it available to other students and staff.</p>
            <a class="button" href="<?= e(url('dashboard/add-item.php')) ?>">Add Your First Item</a>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../components/dashboard-footer.php'; ?>
