<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/csrf.php';

$itemId = positive_int($_GET['id'] ?? null);
$item = null;
$hasPendingRequest = false;

if ($itemId !== null) {
    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT i.*, c.name AS category_name, u.full_name AS owner_name, u.user_type
             FROM items i
             INNER JOIN categories c ON c.id = i.category_id
             INNER JOIN users u ON u.id = i.owner_id
             WHERE i.id = :id AND i.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute(['id' => $itemId]);
        $item = $stmt->fetch() ?: null;

        if ($item && is_logged_in() && current_user_id() !== (int) $item['owner_id']) {
            $pending = $pdo->prepare(
                "SELECT id FROM borrow_requests
                 WHERE item_id = :item_id AND borrower_id = :borrower_id AND status = 'pending'
                 LIMIT 1"
            );
            $pending->execute([
                'item_id' => $itemId,
                'borrower_id' => current_user_id(),
            ]);
            $hasPendingRequest = (bool) $pending->fetch();
        }
    } catch (Throwable $e) {
        $item = null;
    }
}

$pageTitle = $item ? (string) $item['title'] : 'Equipment Not Found';
require __DIR__ . '/components/public-header.php';
?>
<section class="section">
    <div class="container">
        <?php if (!$item): ?>
            <div class="empty-state">
                <h1>Equipment not found</h1>
                <p>The item may have been removed or the link may be invalid.</p>
                <a class="button" href="<?= e(url('browse.php')) ?>">Back to Browse</a>
            </div>
        <?php else: ?>
            <?php $image = $item['image_path'] ? url((string) $item['image_path']) : url('assets/images/items/default-item.jpg'); ?>
            <div class="details-grid">
                <div class="details-media">
                    <img class="details-image" src="<?= e($image) ?>" alt="<?= e((string) $item['title']) ?>">
                </div>

                <div class="details-copy">
                    <p class="eyebrow"><?= e((string) $item['category_name']) ?></p>
                    <h1><?= e((string) $item['title']) ?></h1>

                    <div class="detail-meta-grid">
                        <div><span>Condition</span><strong><?= e(ucfirst((string) $item['condition_status'])) ?></strong></div>
                        <div><span>Availability</span><strong><span class="status-badge <?= e(status_class((string) $item['availability_status'])) ?>"><?= e(status_label((string) $item['availability_status'])) ?></span></strong></div>
                        <div><span>Shared by</span><strong><?= e((string) $item['owner_name']) ?></strong></div>
                        <div><span>Member type</span><strong><?= e(ucfirst((string) $item['user_type'])) ?></strong></div>
                    </div>

                    <div class="details-actions">
                        <?php if (is_logged_in() && current_user_id() === (int) $item['owner_id']): ?>
                            <a class="button" href="<?= e(url('dashboard/edit-item.php?id=' . (int) $item['id'])) ?>">Edit Item</a>
                            <a class="button button-secondary" href="<?= e(url('dashboard/incoming-requests.php?item=' . (int) $item['id'])) ?>">View Requests</a>
                        <?php elseif (!is_logged_in()): ?>
                            <a class="button" href="<?= e(url('login.php')) ?>">Login to Borrow</a>
                        <?php elseif ((string) $item['availability_status'] !== 'available'): ?>
                            <span class="status-message">This equipment is currently unavailable.</span>
                        <?php elseif ($hasPendingRequest): ?>
                            <span class="status-message">You already have a pending request for this item.</span>
                            <a class="button button-secondary" href="<?= e(url('dashboard/my-requests.php')) ?>">View My Requests</a>
                        <?php else: ?>
                            <button class="button" type="button" data-modal-open="borrow-modal">Request to Borrow</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="content-panel">
                <p class="eyebrow">Item information</p>
                <h2>Description</h2>
                <p><?= nl2br(e((string) $item['description'])) ?></p>
            </div>

            <div class="content-panel borrowing-note">
                <h2>Borrowing process</h2>
                <p>Send a request for the dates you need. The item owner reviews the request. If approved, the item is marked as borrowed until the owner confirms its return.</p>
            </div>

            <?php if (is_logged_in() && current_user_id() !== (int) $item['owner_id'] && (string) $item['availability_status'] === 'available' && !$hasPendingRequest): ?>
                <div class="modal" id="borrow-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="borrow-modal-title">
                    <div class="modal-backdrop" data-modal-close></div>
                    <div class="modal-card" role="document">
                        <button class="modal-close" type="button" data-modal-close aria-label="Close">×</button>
                        <p class="eyebrow">Borrow request</p>
                        <h2 id="borrow-modal-title">Request <?= e((string) $item['title']) ?></h2>
                        <form action="<?= e(url('actions/borrow/request.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">

                            <div class="form-grid two-col">
                                <label for="borrow_from">Borrow From
                                    <input id="borrow_from" name="borrow_from" type="date" min="<?= e((new DateTimeImmutable('today'))->format('Y-m-d')) ?>" required>
                                </label>

                                <label for="borrow_until">Return By
                                    <input id="borrow_until" name="borrow_until" type="date" min="<?= e((new DateTimeImmutable('today'))->format('Y-m-d')) ?>" required>
                                </label>
                            </div>

                            <label for="note">Note <span class="label-optional">(optional)</span>
                                <textarea id="note" name="note" rows="4" maxlength="500" placeholder="Briefly explain what you need the item for or any relevant details."></textarea>
                            </label>

                            <div class="form-actions">
                                <button class="button button-secondary" type="button" data-modal-close>Cancel</button>
                                <button class="button" type="submit">Send Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/components/public-footer.php'; ?>
